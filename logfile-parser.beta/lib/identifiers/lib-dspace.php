<?php
/**
 * Library containing functions that deal with specifica of the
 * DSpace document server of the SUB Goettingen, Germany
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

class DSpaceToolbox {
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
			foreach($this->dbh->query($sql='SELECT oaiid, assigned_identifier FROM data WHERE oaiid LIKE '.$this->dbh->quote($search).';') as $data) {
				$id_list[]=$data['assigned_identifier'];
				$oaiid=$data['oaiid'];
			}
			if($check && (0==count($id_list))) {
				// not really a document (but rather a browsing page or similar, which are identified like abstract pages)
				$types=array('any');
			} elseif($oaiid) {
				// prepend oai identifier to list of document identifiers for abstract and fulltext pages
				$id_list=array($oaiid)+$id_list;
			}
		}
		return array('ids'=>$id_list, 'types'=>$types);
	}
}
