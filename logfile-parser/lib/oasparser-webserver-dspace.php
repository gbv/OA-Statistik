<?php
/**
 * Example parser for logfiles from the DSpace server run by the SUB Goettingen
 *
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

require_once(dirname(__FILE__).'/oasparser-webserver-standard.php');
require_once(dirname(__FILE__).'/identifiers/lib-dspace.php');

class OASParserWebserverDSpace extends OASParserWebserverStandard {
	
	var $dspace_toolbox=false;
	
	/**
	 * Constructor
	 * @param $config optional: config to use
	 * @param $logger optional: logger to use
	 * @return OASParserWebserverStandard instance
	 */
	function __construct($config=false, $logger=false) {
		$this->dspace_toolbox=new DSpaceToolbox($config, $logger);
		return parent::__construct($config, $logger);
	}
	
	/**
	 * Checks a document path to determine if dealing with fulltext or abstract document,
	 * then queries database for details
	 * 
	 * @param $path path to be checked
	 * @return array consisting of ids (id list) and types (type list)
	 * @see logfile-parser/lib/OASParserWebserverStandard#get_document_details($document)
	 */
	function get_document_details($document) {
		$data=$this->dspace_toolbox->get_details($document);
		return $data;
	}
	
	/**
	 * Check if an IP is classified as administrative or intitutional 
	 * @param $ip IP to check
	 * @return array empty or consisting of one or two of the elements "administrative" and "institutional"
	 * @see logfile-parser/lib/OASParserWebserverStandard#get_requester_classification($ip)
	 */
	function get_requester_classification($ip) {
		if($ip == '134.76.162.165')
		    return array('administrative');
		
		if(substr($ip,0,7) === '134.76.')
		    return array('institutional');
		
		return array();
	}
}
