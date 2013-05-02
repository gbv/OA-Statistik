<?php
/** 
 * Test configuration
 * 
 * FOR INTERNAL USE ONLY!
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

$config=array(
	// which parser to use
	'parser'	=> 'OASParserWebserverStandard',
	// salt for hashes
	'hashsalt'	=> sprintf('%c%c%c%c%c%c%c%c%c%c', 0xcf, 0x85, 0xa0, 0x0a, 0x9f, 0x2b, 0x5c, 0x31, 0xf4, 0x1a),
	// context objects in a contextobjects container
	'per_ent'	=> 1,
	// PDO database connection string
	'database'	=> "mysql:host=localhost;dbname=oas_data_provider_test",
	// db user
	'username'      => 'oas',
	// db password
	'password'      => 'oas',
	// name of the table within the database
	'tablename'	=> 'contextobjects',
	// where to read data from
	'file_in'	=> 'php://stdin',
	// prefix this to the path read from config file to form a full URL
	'url_prefix'	=> '',
	// use this as indent string for the XML
	'indent'	=> '',
	// number of worker child processes
	'maxchilds'	=> 10,
	// identifier prefix (".<line>" is added)
	'identifier'    => 'none',
	// the service_id for annotating the context object
	'service_id'	=> 'http://example.com/oastatistik',
	);
