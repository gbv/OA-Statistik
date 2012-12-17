<?php
/**
 * Configuration, partly specific for the DSpace server
 * 
 * configuration aspects, which are declared here have a higher priority
 * than configurations made in config.php. So if you need specific overrides
 * in some configurations-scenarios, edit them here!
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Goettingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

require_once(dirname(__FILE__).'/lib/oasparser-webserver-dspace.php');
require_once(dirname(__FILE__).'/config.php');

$config=array(
	'parser'	=> 'OASParserWebserverDSpace',
	
	/* specific for the DSpace server integration; for internal use only.
         * this sqlite stores metadata, which is harvested with "harvest-identifiers.php".
         * 
         * IMPORTANT: The determination, if a file is a fulltext or an abstract is made
         * in ./lib/identifiers/lib-dspace.php and ./lib/oasparser-webserver-dspace.php.
         * You need to change this manually, to make it compatible to your repository!
         */
	'db_identifier'	=> dirname(__FILE__).'/data/oai-harvester-dspace.db', 
	'service_id'	=> 'http://goedoc.uni-goettingen.de/',
	'baseurl'	=> 'http://goedoc.uni-goettingen.de',
	'url_prefix'	=> 'http://goedoc.uni-goettingen.de',
    
        //The oai-interface which is provided by your repository.
	'oai_server'	=> 'http://goedoc.uni-goettingen.de/goescholar-oai/request',
	'metadataPrefix'=> 'oai_dc',
	'full_harvest'	=> false,
	)+$config;
