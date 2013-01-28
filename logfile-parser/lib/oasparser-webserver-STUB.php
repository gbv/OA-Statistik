<?php
/**
 * Parser stub. Functions provided here have to be rewritten
 * for your specific case.
 * 
 * Name this file, as you decided to name your parser in "config-STUB.php."
 * For instance our dspace parser is named oasparser-webserver-dspace.php
 * In this case, the name of our Repository is "STUB" and its name
 * is EXAMPLE. Advice: Search for "STUB" keyword to find everything
 * which has to be editied first!
 * 
 * @author Marc Giesmann <giesmann@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

require_once(dirname(__FILE__).'/oasparser-webserver-standard.php');

//You need to create this file. A examplefile is given at it's path.
require_once(dirname(__FILE__).'/identifiers/lib-STUB.php');

class OASParserWebserverSTUB extends OASParserWebserverStandard {
	var $STUB_toolbox=false;
	
	/**
	 * Constructor
	 * @param $config optional: config to use
	 * @param $logger optional: logger to use
	 * @return OASParserWebserverStandard instance
	 */
	function __construct($config=false, $logger=false) {
		$this->STUB_toolbox=new STUBToolbox($config, $logger);
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
		$data=$this->STUB_toolbox->get_details($document);
		return $data;
	}
	
	/**
	 * Check if an IP is classified as administrative or intitutional 
         * Change this to your specific IP ranges. 
         * 
	 * @param $ip IP to check
	 * @return array empty or consisting of one or two of the elements "administrative" and "institutional"
	 * @see logfile-parser/lib/OASParserWebserverStandard#get_requester_classification($ip)
	 */
	function get_requester_classification($ip) {
		if($ip == '123.45.678.90')//Example IP
		    return array('administrative');
		
		if(substr($ip,0,7) === '124.68.')//Example IP Range
		    return array('institutional');
		
		return array();
	}
}
