<?php
/**
 * Library containing functions that deal with specifica of the
 * "Webdoc" document server of the SUB Goettingen, Germany
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

class WebdocToolbox {
	var $config=false;
	
	/**
	 * prepares database connection and instance variables
	 * @param $config config to be used
	 * @param $logger set true to enable logging
	 */
	function __construct($config, $logger=false) {
		$this->config=$config;
		$this->logger=$logger;
		
	}
    
	/**
	 * Checks a document path to determine if dealing with fulltext or abstract document,
	 * then queries database for details
	 * 
	 * @param $path path to be checked
	 * @return array consisting of ids (id list) and typed (type list)
	 */
	function get_details($path) {
		// open database
		$dbh=new PDO('sqlite:'.$this->config['db_identifier']);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$search=false;
		$id_list=array();
		$types=array();
		
                //Für den Univerlag auskommentiert
                /* 
		if(preg_match('/^(\/diss\/[0-9]+\/[^\/]+\/)/', $path, $match)) {
			// Dissertation
			$search=$this->config['baseurl'].$match[1].'%';

			if(preg_match('/(index\.html?|inhalt\.html?)$/i', $path)) {
				$types[]='abstract';
			} elseif(preg_match('/\.(pdf|ps)$/i', $path)) {
				$types[]='fulltext';
			} else {
				$types[]='any';
			}
		}
                 
                 
		
		if(preg_match('/^(\/univerlag\/.*\.(pdf|ps|PDF|PS))$/', $path, $match)) {
			$search=$this->config['baseurl'].$match[1];
			$types[]='fulltext';
		}
                 
                 */
                
                //Is it a fulltext?
                $types[] = 'any';
                $urlparts = explode("/",$path);
                $extension = strtolower(pathinfo(parse_url($path,PHP_URL_PATH),PATHINFO_EXTENSION));
                if($urlparts[1] == "univerlag")
                {
                    /* Dateiendungsüberprüfung */
                    if($extension=='pdf' || $extension=='ps')
                    {
                        $types=array();
                        $search=$this->config['baseurl']."/".$urlparts[1]."/";
                        $types[]='fulltext';
                    }
                    
                }
                
                /*
                 * Auskommentiert: Für den Univerlag existiert zur Zeit keine
                 * OAI-PMH Schnittstelle! Demenstprechend muss die SQ-Lite hier nicht genutzt werden.
                 * Für alle Anderen Dataprovider ist es immens wichtig, diese Stelle wieder zu
                 * entkommentieren!
                 
		if($search) {
			foreach($dbh->query('SELECT oaiid, assigned_identifier FROM data WHERE assigned_identifier LIKE '.$dbh->quote($search).';') as $data) {
				$id_list[]=$data['oaiid'];
				foreach($dbh->query(
					    'SELECT assigned_identifier FROM data WHERE oaiid='.
					    $dbh->quote($data['oaiid']).
					    ' AND assigned_identifier!='.
					    $dbh->quote($data['assigned_identifier']).
					    ';') as $additional_id) {
					$id_list[]=$additional_id['assigned_identifier'];
				}
			}
		}
		*/
		return array('ids'=>$id_list, 'types'=>$types);
	}
}
