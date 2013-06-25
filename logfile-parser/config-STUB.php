<?php
/**
 * Configuration stub. Use this to make configurations
 * for the logfileparser. If special configurations are
 * needed, look into the "config.php" to override the provided
 * parameters. Advice: Search for "STUB" keyword to find everything
 * which has to be editied first!
 * 
 * Name this file, as you want to.
 * For instance our dspace configuration is named config-dspace.php
 * In this case, the name for our Repositorysoftware is "STUB" and its name
 * is EXAMPLE.
 * 
 * @author Marc Giesmann <giesmann@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

//You need to create this file. A examplefile is given at this path.
require_once(dirname(__FILE__).'/lib/oasparser-webserver-STUB.php');
require_once(dirname(__FILE__).'/config.php');//Dont change this line!

$config=array(
        //The parsername. This has to be exactly the name, you gave your
        //parser in the oasparser-webserver-STUB.php class
	'parser'	=> 'OASParserWebserverSTUB',
        
        //This is specific for your mySQL configuration
        'database'	=> "mysql:host=localhost;dbname=oas_data_provider_STUB",
	'username'	=> 'STUB_username',
	'password'	=> 'STUB_password',
            
        //identifications/names
	'service_id'	=> 'http://EXAMPLE.uni-goettingen.de/',
	'baseurl'	=> 'http://EXAMPLE.uni-goettingen.de',
	'url_prefix'	=> 'http://EXAMPLE.uni-goettingen.de',
    
        //adress of the oai-pmh interface of your repository. The logfile
        //parser needs it to identify documents via harvest-identifiers.php.
	'oai_server'	=> 'http://EXAMPLE.uni-goettingen.de/STUB-oai-Server/request',
        'metadataPrefix'=> 'oai_dc',
    
        //Cache database for oai-pmh. This is just a name for internal handling.
        //Name it as needed.
        'db_identifier'	=> dirname(__FILE__).'/data/oai-harvester-STUB.db',

        //-----------------------------------------------------------------
        //  ***                    CUSTOM FLAGS                        ***
        //     All flags listet from here are CUSTOM! Handle with care!
        //     To determine, which flags are possible look 
        //     at "config.php".
        //-----------------------------------------------------------------
        
    
        /*Here is an example of a configuration which can be overridden. For other
        possibilities of configuring the logfile-parser see "config.php".
        
        The fileextensions, which should be ignored, because they're not relevant
        for the service provider. If your repository offers files which are listed
        here and should be counted by our service provider, delete the specific line
        or comment it. If you're adding a new extension, make sure its lowercase!
        The filter is useful to improve parsing-performance significantly. */
        
        //'extensionfilter'      => array('css',
        //
        //                                'js',
        //                                'vbs',
        //                                'png',
        //                                'jpg',
        //                                'jpeg',
        //                                'gif'
        //                               ),
    
        
	)+$config;
