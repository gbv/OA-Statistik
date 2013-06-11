<?php
/**
 * OAI Data Provider Config
 * 
 * FOR INTERNAL USE ONLY!
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage oai-data-provider
 * @version 0.1
 */

$config=array(
        'repo_name'            => 'OA-Statistik DEMO Data Provider',
        'db_uri'               => 'mysql:host=localhost;dbname=oas_data_provider',
        'db_table'             => 'contextobjects',
        // postfix of 'tablename'. name of the table with the harvesthistory.
        // example with tablename 'contextobjects' and harvesthistory-postfix 'harvesthistory' (standardvalues): 
        // "contextobjects_harvesthistory"
        'harvesthistory'       => 'harvesthistory',
        'db_user'              => 'db_user',
        'db_password'          => 'db_password',
        'oai_base_url'         => 'http://oa-statistik.sub.uni-goettingen.de/demo-data-provider/',
        'oai_server_admin'     => 'mimkes@sub.uni-goettingen.de',
        'oai_identifier_prefix'=> 'oai:oa-statistik.sub.uni-goettingen.de:demo',
        'max_items'            => 2
        );


?>
