<?php
/**
 * Configuration, partly specific for the DSpace server
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

require_once(dirname(__FILE__).'/lib/oasparser-webserver-dspace.php');
require_once(dirname(__FILE__).'/config.php');

$config=array(
	'parser'	=> 'OASParserWebserverDSpace',
	
	// specific for the DSpace server integration:
	'db_identifier'	=> dirname(__FILE__).'/data/oai-harvester-dspace.db',
	'service_id'	=> 'http://goedoc.uni-goettingen.de/',
	'baseurl'		=> 'http://goedoc.uni-goettingen.de',
	'url_prefix'	=> 'http://goedoc.uni-goettingen.de',
	'oai_server'	=> 'http://goedoc.uni-goettingen.de/goescholar-oai/request',
	'metadataPrefix'=> 'oai_dc',
	'full_harvest'	=> false,
	)+$config;
