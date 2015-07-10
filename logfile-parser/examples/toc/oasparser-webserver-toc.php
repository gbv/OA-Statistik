<?php
/*
 * @author Marco Recke <recke@gbv.de> fuer VZG table of contents
 * @package data-provider
 * @subpackage logfile-parser
 * @path lib/
 * @version 0.2 2013-01-11
 * This version will assemble identifier from the URL
 */

require_once(dirname(__FILE__).'/oasparser-webserver-standard.php');
require_once(dirname(__FILE__).'/identifiers/lib-toc.php');

class OASParserWebserverTOC extends OASParserWebserverStandard {
	
	var $toc_toolbox=false;
	
	/**
	 * Constructor
	 * @param $config optional: config to use
	 * @param $logger optional: logger to use
	 * @return OASParserWebserverStandard instance
	 */
	function __construct($config=false, $logger=false) {
		$this->toc_toolbox=new TOCToolbox($config, $logger);
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
		$data=$this->toc_toolbox->get_details($document);
		return $data;
	}
	
	/**
	 * Check if an IP is classified as administrative or intitutional 
	 * @param $ip IP to check
	 * @return array empty or consisting of one or two of the elements "administrative" and "institutional"
	 * @see logfile-parser/lib/OASParserWebserverStandard#get_requester_classification($ip)
	 */
	function get_requester_classification($ip) {
		if($ip == '195.37.139.186')
		    return array('administrative');
		
		if(substr($ip,0,7) === '193.174.')
		    return array('institutional');
		
		return array();
	}
}
