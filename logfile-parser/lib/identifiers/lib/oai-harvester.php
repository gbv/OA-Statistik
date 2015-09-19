<?php
/**
 * OAI Harvester
 * 
 * Frontend to the OAI Service Provider functions,
 * stores metadata in database.
 * 
 * Requires DOM extension
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

require_once(dirname(__FILE__).'/oai-service-provider.php');


if(!extension_loaded('dom'))
	trigger_error('DOM extension not found.', E_USER_ERROR);

class OAIHarvester {
	var $dbh=false;
	var $config=false;
	var $logger=false;

	/**
	 * Prepares instance variables
	 * @param $config configuration to be used
	 * @param $logger optional: set custom logger
	 */
	function __construct($config, $logger=false) {
		$this->config=$config;
		$this->logger=$logger;		
		$this->prepare();
	}

	/**
	 * Controls the actual harvesting
	 */
	function harvest() {
		$oaisp=new OAIServiceProvider($this->config['oai_server'], $this->logger);
		
		try {
			$params=array('metadataPrefix'=>$this->config['metadataPrefix']);
			if(!@$this->config['full_harvest']) {
				$params['from']=$this->get_last_datestamp(); 
			}
			$oaisp->query_listrecords(array($this,'record_reader'),$params);
		} catch(OAIServiceProviderOAIErrorNoRecordsMatchException $e) {
			$this->log('no new records.');
		}
	}

	/**
	 * Reads latest datestamp from db
	 * Dummy function; must be overridden in child classes!
	 */
	function get_last_datestamp() {
		// override me!
		return gmdate('Y-m-d\TH:i:s\Z',0); // 1970-01-01
	}

	/**
	 * processes read records
	 * @param $data the xml data to process
	 * Dummy function; must be overridden in child classes!
	 */
	function record_reader($data) {
		// override me!
	}
	
	/**
	 * Callback logger function
	 * @param $text string being written to logfile
	 * @param $level optional: level between 0 and 10
	 */
	function _log($text, $level=10) {
		if($this->logger) call_user_func($this->logger, $text, $level);
	}
}

class OAIHarvesterException extends Exception {}
