<?php
/** 
 * Default configuration
 * 
 * SAMPLE CONFIG, CUSTOMIZE BEFORE USE!
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 1.1
 */

include "lib/.new";

$config=array(
	// which parser to use 
        //DO NOT CHANGE WANTED PARSER HERE! THIS IS FALLBACK!
	'parser'	=> 'OASParserWebserverStandard',
	// salt for hashes
	'hashsalt'	=> $salt,
	// context objects in a contextobjects container
	'per_ent'	=> 100,
	// PDO database connection string
	'database'	=> 'mysql:host=localhost;dbname=oas_data_provider_demo',
	// db user
	'username'      => 'dbuser',
	// db password
	'password'      => 'dbpassword',
	// name of the table within the database
	'tablename'	=> 'contextobjects',
	// where to read data from
	'file_in'	=> 'php://stdin',
	// prefix this to the path read from config file to form a full URL
	'url_prefix'	=> 'http://www.oas_demo.de/',
	// use this as indent string for the XML
	'indent'	=> '',
	// number of worker child processes
	'maxchilds'	=> 10,
	// identifier prefix (".<line>" is added)
	'identifier'    => 'oai:demo',
	// the service_id for annotating the context object
	'service_id'	=> 'http://www.oas_demo.de/oastatistik',
    
        //This is specific for broken OAI PMH Implementations.
        'full_harvest'	=> false,
        
        //the fileextensions, which should be ignored, because they're not relevant
        //for the service provider. If your repository offers files which are listed
        //here and should be counted by our service provider, delete the specific line
        //or comment it. If you're adding a new extension, make sure its lowercase!
        //The filter is useful to improve parsing-performance.
        'extensionfilter'      => array('css',
                                        'js',
                                        'vbs',
                                        'png',
                                        'jpg',
                                        'jpeg',
                                        'gif'
                                       ),
    
        //Flag to determine, if documents, which arent 'fulltext' or 'abstract'
        //should be sent to the DataProvider. (Attention: Turning this to true makes
        //the datavolume send to the serviceprovider much higher!) 
        //Default: false
        'send_anys' => false
	
	);
