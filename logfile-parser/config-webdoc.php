<?php

/**
 * Configuration, partly specific for "Webdoc" server of the SUB Goettingen
 * 
 * FOR INTERNAL USE ONLY!
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

require_once(dirname(__FILE__).'/lib/oasparser-webserver-webdoc.php');
require_once(dirname(__FILE__).'/config.php');

$config=array(

	'database'	=> "mysql:host=localhost;dbname=oas_data_provider_webdoc",
	'username'	=> 'oas',
	'password'	=> 'oas',
	'parser'	=> 'OASParserWebserverWebdoc',
	'per_ent'	=> 10,
	'maxchilds'	=> 25,
	
	// specific for the webdoc server integration (identifier assignment database):
	'db_identifier'	=> dirname(__FILE__).'/data/oai-harvester-webdoc.db',

	'service_id'	=> 'http://webdoc.sub.gwdg.de/',
	'baseurl'	=> 'http://webdoc.sub.gwdg.de',
	'url_prefix'	=> 'http://webdoc.sub.gwdg.de',
	'oai_server'	=> 'http://www.gbv.de/goai/gbvrep.php',
	'metadataPrefix'=> 'oai_dc',
	'full_harvest'	=> true, // because the webdoc OAI-PMH interface is broken
	
	)+$config; // merge into default config
