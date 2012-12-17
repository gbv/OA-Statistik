<?php
/**
 * Parser for lines from standard webserver log files
 *
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.2 Marc Giesmann, 27.09.2012
 */

require_once(dirname(__FILE__).'/oasparser.php');
require_once(dirname(__FILE__).'/logutils.php');
require_once(dirname(__FILE__).'/ctxbuilder.php');

class OASParserWebserverStandardException extends Exception {}

class OASParserWebserverStandard extends OASParser {
    
	var $async_tasks=array();
    
    /**
     * Parse the logfile
     */
	function parse() {
		$this->dbh = new PDO(
				$this->config['database'],
				$this->config['username'],
				$this->config['password'],
				array(PDO::ATTR_PERSISTENT => false));
		$this->dbh->beginTransaction();
		$lnr=0;
		
		$tfile=$tdir=false;
		$out_count=0;
		$fout=false;
	
		if(false===($fin=fopen($this->config['file_in'],'r')))
		    throw new OASParserWebserverStandardException("Cannot open <$file_in> for reading!");
		
		while(is_resource($fin) && !feof($fin)) {
		    // read log file line by line
		    
		    $ldata_arr=array(); // container for parsed logfile lines
		    
		    while((count($ldata_arr)<$this->config['per_ent']) && false!==($line=fgets($fin))) {
				// read up to $config[per_file] lines from config file
				if($ldata=$this->parse_line($line, ++$lnr)) {
				    $ldata_arr[]=$ldata;
				}
		    }
		    
		    // now initiate the asynchronous worker threads for the lines we've read
		    if(count($ldata_arr)>0) {
				if($this->config['async']) {
				    $this->spawn_async($ldata_arr, $lnr);
				} else {
				    $this->parse_async($ldata_arr);
				}
		    }
		}
		$this->close_finished_async(true);
		fclose($fin);
		$this->dbh->commit();
    }

    /**
     * Spawn an asynchronous worker process
     * @param $values values pushed to the spawned process
     * @param $lnr line number
     */
    function spawn_async($values, $lnr) {
		while(count($this->async_tasks) >= $this->config['maxchilds']) {
		    $this->close_finished_async();
		}
		$data=array('pipes'=>array(), 'line'=>$lnr, 'out'=>'');
		$data['res']=proc_open(
			$this->config['callback'],
			array(
			    0 => array('pipe', 'r'),
			    1 => array('pipe', 'w')
			),
			$data['pipes']);
		if(!is_resource($data['res'])) {
		    throw new OASParserWebserverStandardException("Cannot instanciate asynchronous worker process!");
		} else {
		    fwrite($data['pipes'][0], serialize($values)); // push values via stdin to worker process
		    fclose($data['pipes'][0]);
		    stream_set_blocking($data['pipes'][1], 0); // set output stream to non-blocking mode
		    $this->async_tasks[]=$data;
		}
    }

    /**
     * Read back data from asynchronous worker processes
     */
    function read_async_data() {
		foreach(array_keys($this->async_tasks) as $id) {
		    $write_streams=NULL;
		    $except_streams=NULL;
		    $read_streams=array($this->async_tasks[$id]['pipes'][1]);
		    if(stream_select($read_streams, $write_streams, $except_streams, 0, 0)) {
				foreach($read_streams as $stream) {
				    $this->async_tasks[$id]['out'].=($read=fread($stream, 8192));
				    //$this->_log("<L:{$this->async_tasks[$id]['line']}> READING: $read");
				}
		    }
		}
    }

    /**
     * Close asynchronous worker processes
     * @param $wait_for_all set true to wait for all worker processes to finish before closing
     */
    function close_finished_async($wait_for_all=false) {
		do {
		    $this->read_async_data();
		    foreach(array_keys($this->async_tasks) as $id) {
			$status = proc_get_status($this->async_tasks[$id]['res']);
			if(!$status['running']) {
				    stream_set_blocking($this->async_tasks[$id]['pipes'][1], 1); // set output stream to blocking mode
				    while(!feof($this->async_tasks[$id]['pipes'][1])) {
					$this->async_tasks[$id]['out'].=($read=fread($this->async_tasks[$id]['pipes'][1], 8192));
					//$this->_log("<L:{$this->async_tasks[$id]['line']}> READING: $read");
				    }
				    fclose($this->async_tasks[$id]['pipes'][1]);
				    proc_close($this->async_tasks[$id]['res']);
				    $this->write_data($this->async_tasks[$id]['line'], $this->async_tasks[$id]['out']);
				    unset($this->async_tasks[$id]);
				}
		    }
		    usleep(100);
		} while($wait_for_all && count($this->async_tasks));
    }

    /**
     * Write Context Object data to database
     * @param $line line to write
     * @param $ctxo CtxO to write to
     */
    function write_data($line, $ctxo) {
		try {
		    $stmt = $this->dbh->prepare('INSERT INTO '.$this->config['tablename'].' (timestamp, identifier, line, data) VALUES (?, ?, ?, ?)');
		    $stmt->bindParam(1, time());
		    $stmt->bindParam(2, $this->config['identifier']);
		    $stmt->bindParam(3, $line);
		    $stmt->bindParam(4, $ctxo);
		    $stmt->execute();
		    $this->_log("<L:$line> OK: context objects written to DB");
		} catch (PDOException $e) {
		    $this->_log("<L:$line> ERROR: cannot interface with database: ".$e->getMessage());
		}
    }

    /**
     * Parse values asynchronously
     * 
     * This function cares for the asynchronous part of processing, handled by
     * child processes that are spawn from the main controlling process
     * 
     * @param $values array of CtxO values
     */
    function parse_async($values) {
                $initiated = false;
                $ctxbuild=new CtxBuilder();
                
		foreach($values as $ldata) {
                    
                    //Get details 
                    $ldata['details']=$this->get_document_details($ldata['document_url']);

                    //determine, if we want to compute...
                    if(!($this->config['send_anys'])){
                        
                        //If we want to exclude anys, (!$config[send_anys]) don't exclude robots.txt
                        if(!(pathinfo(parse_url($ldata['document_url'],PHP_URL_PATH),PATHINFO_FILENAME) == "robots.txt")) {
                            
                            foreach($ldata['details']['types'] as $type){
                                if($type == "any"){
                                    //$this->_log("<L:{$ldata['line']}> Skipped 'any'-document");
                                    continue 2; //Skip this foreach, and the next foreach
                                }
                            }
                        }else{
                            $this->_log("<L:{$ldata['line']}> found a robots.txt !!!");
                            
                            
                        }
                        
                    }
                    
                    /* IP <-> Hostname */
		    if(!$ldata['ip']) {
			    $ldata['ip'] = gethostbyname($ldata['hostname']);
		    } else {
			    $ldata['hostname'] = gethostbyaddr($ldata['ip']);
		    }
		    
		    if(!OASParser::is_ip($ldata['ip'])) {
			    $this->_log("<L:{$ldata['line']}> Log Entry: cannot resolve hostname '{$ldata['ip']}'");
			    continue;
		    }
                    
                    $ctx=array(
                        'status'=>$ldata['status'],
                        'size'=>$ldata['size'],
                        'document_size'=> 'TODO',
                        'time'=>$ldata['time'],
                        'format'=>$ldata['document_type'],
                        'document_url'=>$this->config['url_prefix'].$ldata['document_url'],
                        'ip-hashed'=>hash_it_the_oas_way($ldata['ip']),
                        'ip-c-hashed'=>hash_it_the_oas_way(OASParser::get_c_class_net($ldata['ip'])),
                        'stripped-hostname'=>OASParser::get_first_level_domain($ldata['hostname']),
                        'classification'=>$this->get_requester_classification($ldata['ip']),
                        'user-agent'=>($ldata['user-agent']=='-')?false:$ldata['user-agent'],
                        'referring-entity'=>($ldata['referer']=='-')?false:$ldata['referer'],
                        'service_id'=>$this->config['service_id'],
                        'document_ids'=>$ldata['details']['ids'],
                        'service_types'=>$ldata['details']['types']
                        );
                    
                    //If all objects were skipped dont build the main-tree
                    if(!$initiated)
                    {
                        $initiated = true;
                        
                        //Build xml-tree
                        $ctxbuild->setIndentString($this->config['indent']);
                        $ctxbuild->start();
                    }else{
                        //Only if initiated
                        $ctxbuild->add_ctxo($ctx);
                    }
                    
		}
                
                //Only if initiated
                if($initiated){
                    $ctxbuild->done();
                    if($this->config['async']) {
                        echo $ctxbuild->outputMemory();
                        fclose(STDOUT);
                        die();
                    } else {
                        $this->write_data($ldata['line'],$ctxbuild->outputMemory());
                    }
                }
    }

    /**
     * Recursive version of mkdir to create deep-rootet directory structure
     * 
     * @param $dir directory path
     * @param $mode optional: access rights (chmod style)
     * @return string return value of mkdir
     */
    function mkdir_recursive($dir,$mode=0777) {
		if($dir=='.')
		    return true;
		if($d=dirname($dir))
		    if(!$this->mkdir_recursive($d))
			return false;
		if(is_dir($dir))
		    return true;
		return mkdir($dir,$mode);
    }
    
    /**
     * Mapping of document URL -> document ID and service type
     *
     * DUMMY FUNCTION!
     * Implement this function in child classes
     * Will be called asynchronously
     * 
     * @param $document document path
     * @return bool always false if not implemented
     */
    function get_document_details($document) {
	    return false;
    }

    /**
     * Mapping of IP -> user groups
     * 
     * DUMMY FUNCTION!
     * Implement this function in child classes
     * Will be called asynchronously
     * 
     * @param $ip IP to check
     * @return array always empty if not implemented
    */
    function get_requester_classification($ip) {
	    return array();
    }

	/**
	 * Parse one line of the logfile
	 *
	 * @param $line text of the logfile line to parse
	 * @param $lnr line number
	 * @return array of values for CtxO building
	 */
    function parse_line($line, $lnr) {
		$val=array('line'=>$lnr);
		
		/*               host/ip     user    realm       date       query     status    size     referer   useragent  
		if(!preg_match('/^([^ ]+) +([^ ]+) +([^ ]+) +\[([^\]]+)\] +"([^"]+)" +([^ ]+) +([^ ]+) +"([^"]*)" +"([^"]*)"$/', trim($line), $match)) {
                        $this->_log("<L:$lnr> Ignore malformed log entry: $line");
                        return false;
		}*/
                
                //              host/ip     user    realm       date             query         status   size         referer       useragent 
                if(!preg_match('/^([^ ]+) +([^ ]+) +([^ ]+) +\[([^\]]+)\] +"(..*?)(?<!\\\\)" +([^ ]+) +([^ ]+) +"(.*?)(?<!\\\\)" +"([^"]*)"$/' , trim($line), $match)) {
                        $this->_log("<L:$lnr> Ignore malformed log entry: $line");
                        return false;
		}
                
		/* Statuscode */
		if(!$this->statuscode_filter($val['status']=$match[6])) {
			$this->_log("<L:$lnr> Ignore since HTTP status code is {$val['status']}");
			return false;
		}
                
                /* HTTP-Infos zum abgerufenen Dokument */
		$http_data=split(' ',$match[5]);
		$val['method']=$http_data[0]; /* Query method */
		if(!$this->method_filter($val['method'])) {
			$this->_log("<L:$lnr> Ignore since HTTP method {$val['method']} is not known/supported.");
			return false;
		}
		$val['document_url']=$http_data[1]; /* abgerufenes Dokument */
                
                /* Dateiendungsüberprüfung */
                $fileextension =  strtolower(pathinfo(parse_url($val['document_url'],PHP_URL_PATH),PATHINFO_EXTENSION));
                
                /*  Dateien, die unwesentlich für den Serviceprovicer sind
                 *  sollen gefiltert werden */ 
                foreach ($this->config['extensionfilter'] as $forbiddenextention) {
                    if($fileextension==$forbiddenextention){
                        //$this->_log("<L:$lnr> Ignore since .".$forbiddenextention."-files are not relevant for serviceprovider.");
                        return false;
                    }
                }
		
		/* IP <-> Hostname */ // eigentliche Auflösung verschoben in asynchrone Bearbeitung
		if(!$this->is_ip($match[1])) {
			$val['hostname'] = $match[1];
			$val['ip'] = false;
		} else {
			$val['ip'] = $match[1];
			$val['hostname'] = false;
		}
	
		/* Zeit/Datum */
		$val['time']=strtotime($match[4]);
	
		/* Uebertragene Daten */
		$val['size'] = $match[7];
                
                /* Dateiformat; MIME */
                switch($fileextension)
                {
                     case 'pdf' :$itemformat = 'application/pdf'        ;   break;
                     case 'ps'  :$itemformat = 'application/postscript' ;   break;
                     case 'eps' :$itemformat = 'application/postscript' ;   break;
                     case 'dvi' :$itemformat = 'application/x-dvi'      ;   break;
                     case 'doc' :$itemformat = 'application/msword'     ;   break;
                     case 'docx':$itemformat = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';break;
                     case 'png' :$itemformat = 'image/png'              ;   break;
                     case 'psd' :$itemformat = 'application/octet-stream';  break;
                     case 'jpeg':$itemformat = 'image/jpeg'              ;  break;
                     case 'jpg' :$itemformat = 'image/jpeg'              ;  break;
                     case 'jpe' :$itemformat = 'image/jpeg'              ;  break;
                     case 'tif' :$itemformat = 'image/tiff'              ;  break;
                     case 'tiff':$itemformat = 'image/tiff'              ;  break;
                     case 'gif':$itemformat  = 'image/gif'              ;  break;
                 
                     /* Fallback */
                     default: $itemformat = 'text/html';
                     break;
                }
                $val['document_type'] = $itemformat;
		
		/* Referer-Auswertung */
		$val['referer']=$match[8];
	
		/* User-Agent */
		$val['user-agent']=$match[9];
		
		return $val;
    }
}
