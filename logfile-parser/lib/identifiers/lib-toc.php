<?php
/**
 * 
 * @author Marco Recke <recke@gbv.de> fuer VZG table of contents
 * @package data-provider
 * @subpackage logfile-parser
 * @path lib/identifiers
 * @version 0.2 2013-01-11
 * This version will assemble identifier from the URL
 */

class TOCToolbox {
  private $dbh=false;
  private $config=false;
  /**
    * prepares database connection and instance variables
    * @param $config config to be used
    * @param $logger optional: set custom logger
    */
  function __construct($config, $logger=false) {
    $this->config=$config;
    $this->logger=$logger;
  }
  /**
    * Checks a document path to determine if dealing with fulltext or abstract document,
    * then queries database for details
    *
    * @param $path path to be checked
    * @return array consisting of ids (id list) and types (type list)
    */
  function get_details($path) {
    $id_list=array();
    $types=array();

    //Is it a fulltext?
    $types[]    = 'any';
    $urlparts   = explode("/",$path);
    $extension  = strtolower(pathinfo(parse_url($path,PHP_URL_PATH),PATHINFO_EXTENSION));
    $filename   = strtolower(pathinfo(parse_url($path,PHP_URL_PATH),PATHINFO_FILENAME));

    if($urlparts[1] == "dms"){
        /* Dateiendungsüberprüfung */
        if($extension=='pdf' || $extension=='ps'){
            unset($types);
            $types=array();
            $types[]='abstract';

            //Identifier anfuegen
           $id_list[] = $filename;
           $id_list[] = "oai:www.gbv.de-toc:" . $filename;
        }
    }
    
    return array('ids'=>$id_list, 'types'=>$types);
    
    }
}
