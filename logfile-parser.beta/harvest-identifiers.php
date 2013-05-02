<?php
/**
 * Harvester frontend for the identifier harvester
 * 
 * Assigns identifiers to documents. Therefor, reads metadata of all documents in
 * a repository from the OAI interface. Implementation is repository-specific, but
 * should run smoothly with all Dublin Core compatible repositories
 *
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

require_once(dirname(__FILE__).'/lib/identifiers/lib/identifier-harvester.php');

$options=getopt('c:');
if(@!$options['c']) {
	// if no options are given, the configuration file defaults to "config.php"
	// within the same directory as this file
	require_once(dirname(__FILE__).'/config.php');
} else {
	// otherwise, the "-c" parameter to this script specifies the config file
	require_once($options['c']);
}

// basic console logger
function logger($what, $level) { echo "$what\n"; }

$oaih=new IdentifierHarvester($config, 'logger');
$oaih->harvest();
