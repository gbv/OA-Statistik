<?php
/** 
 * Default configuration
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 1.2
 * 
 * DON'T CUSTOMIZE ANYTHING IN HERE!!!
 * For configuration purposes create a new file like "config-STUB.php"
 * and override all needed parameters there. All parameters here
 * are FALLBACK! Not all possible parameters are listed in the
 * specific config file like "config-stub.php". Absent parameters can
 * be added by copying a given parameter from HERE to the specific configuration
 * to override them. See config-stub.php for further information.
 */

include "lib/.new";

$config=array(
	// which parser to use 
        //DO NOT CHANGE WANTED PARSER HERE! THIS IS FALLBACK!
	'parser'	=> 'OASParserWebserverStandard',
	// salt for hashes
	'hashsalt'	=> $salt,
	// context objects in a contextobjects container
	'per_ent'	=> 50,
	// PDO database connection string
	'database'	=> 'mysql:host=localhost;dbname=oas_data_provider_demo',
	// db user
	'username'      => 'oas_demo',
	// db password
	'password'      => 'oas_demo',
	// name of the table within the database
	'tablename'	=> 'contextobjects',
        // postfix of 'tablename'. name of the table with the harvesthistory.
        // example with tablename 'contextobjects' and harvesthistory-postfix 'harvesthistory' (standardvalues): 
        // "contextobjects_harvesthistory"
        'harvesthistory'=> 'harvesthistory', 
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
        //or comment it. If you're adding a new extension, make sure it's lowercase!
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
        'send_anys' => false,
	);
