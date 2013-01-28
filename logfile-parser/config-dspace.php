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
        
        // PDO database connection string and login-parameters
	'database'	=> 'mysql:host=localhost;dbname=oas_data_provider_demo',
	'username'      => 'oas_demo',
	'password'      => 'oas_demo',
        
        //Specific config.php overrides
        'per_ent'	=> 30,
	'maxchilds'	=> 25,
    
	// specific adresses for the DSpace server integration:
	'db_identifier'	=> dirname(__FILE__).'/data/oai-harvester-dspace.db',
	'service_id'	=> 'http://goedoc.uni-goettingen.de/',
	'baseurl'	=> 'http://goedoc.uni-goettingen.de',
	'url_prefix'	=> 'http://goedoc.uni-goettingen.de',
	'oai_server'	=> 'http://goedoc.uni-goettingen.de/goescholar-oai/request',
	'metadataPrefix'=> 'oai_dc',
	'full_harvest'	=> false,
	)+$config;
