<?php
/**
 * Parser toolbox stub. Functions provided here have to be rewritten
 * for your specific case.
 * 
 * Name this file, as you decided to name your parser in "config-STUB.php."
 * For instance our dspace parser-toolbox is named lib-dspace.php
 * In this case, the name of our Repository is "STUB" and its name
 * is EXAMPLE. Advice: Search for "STUB" keyword to find everything
 * which has to be editied first!
 * 
 * @author Marc Giesmann <giesmann@sub.uni-goettingen.de> for SUB Göttingen
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

class STUBToolbox {
	var $dbh=false;
	var $config=false;
	
	/**
	 * prepares database connection and instance variables
	 * @param $config config to be used
	 * @param $logger optional: set custom logger
	 */
	function __construct($config, $logger=false) {
		$this->config=$config;
		$this->logger=$logger;
		
		// open database
		$this->dbh=new PDO('sqlite:'.$this->config['db_identifier']);
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
    
	/**
	 * Checks a document path to determine if dealing with fulltext or abstract document,
	 * then queries database for details
	 * 
	 * @param $path path to be checked
	 * @return array consisting of ids (id list) and types (type list)
	 */
	function get_details($path) {
		$check=false;
		$id_list=array();
		$types=array();
		$search=false;
		
                
                //Example of a repository from university goettingen and its url-handling.
                //Determine, if we have an abstract or a fulltext
		if(preg_match('/\/bitstream\/[^\/]+\/([0-9]+)\//', $path, $match)) {
			// Houston, we have a fulltext -- probably.
			$types[]='fulltext';
			$search="oai:goedoc.uni-goettingen.de:goescholar/$match[1]";
		} elseif(preg_match('/\/handle\/[^\/]+\/([0-9]+)/', $path, $match)) {
			// This is probably a metadata page
			$types[]='abstract';
			$search="oai:goedoc.uni-goettingen.de:goescholar/$match[1]";
			$check=true;
		} else {
			$types[]='any';
		}
		if($search) {
			$oaiid=false;
                        //Reads the internal sqlite database. It's a buffer for the oai-pmh interface of your repository
                        //to avoid redundant querys. To keep this database up to date, call harvest-identifiers.php frequently.
			foreach($this->dbh->query($sql='SELECT oaiid, assigned_identifier FROM data WHERE oaiid LIKE '.$this->dbh->quote($search).';') as $data) {
				$id_list[]=$data['assigned_identifier'];
				$oaiid=$data['oaiid'];
			}
			if($check && (0==count($id_list))) {
				//Nothing found, so not really a document (but rather a browsing page or
                                //similar, which are identified like abstract pages)
				$types=array('any');
			
                                
                        } elseif($oaiid) {
				// prepend oai identifier to list of document identifiers for abstract and fulltext pages
				$id_list=array($oaiid)+$id_list;
			}
		}
		return array('ids'=>$id_list, 'types'=>$types);
	}
}
