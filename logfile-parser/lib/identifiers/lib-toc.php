<?php
/**
 * lib-toc.php
 * @author Marco Recke <recke@gbv.de> fuer VZG table of contents
 * @package data-provider
 * @subpackage logfile-parser
 * @path lib/identifiers
 * @version 0.3 2013-11-01
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
    $distinctIdentifiers = array('weimar','faz-rez','spk/sbb','spk/iai','sbb-berlin','hbz','casalini','ub-kiel','goettingen','tib-ub-hannover','toc/bs','hebis-darmstadt','bowker','hbk-bs-toc','zbw','sub','ilmenau','hebis-mainz','lbmv','ohb-opac','hsu','bsz','vst', 'ohb','spk/iai','tlda','sub-hamburg','greifswald','goettingen-geschichte-toc','gei' );

  //Is it a fulltext?
                $types[] = 'any';
                $urlParts = explode("/",$path);
                $tocFound = false;

                $extension = strtolower(pathinfo(parse_url($path,PHP_URL_PATH),PATHINFO_EXTENSION));
                $filename = strtolower(pathinfo(parse_url($path,PHP_URL_PATH),PATHINFO_FILENAME));

                if($urlParts[1] == "dms")
                {
                    /* Dateiendungsüberprüfung */
                    if($extension=='pdf')
                    {
                        unset($types);
                        $types=array();
                        $types[]='abstract';
                    }
                }


     foreach ($distinctIdentifiers as $distinctIdentifier)
     {
        foreach ($urlParts as $key => $urlPart)
         {
                if ($urlPart=='toc' ||  $urlPart==$distinctIdentifier) 
                {
                                $tocFound = true;
                                $urlArrayKey = $key;
               }
        }
     }

/**
*
* Jetzt muss noch der Identifier gesetzt werden:
* Da toc an beliebiger Stelle vorkommen kann, nimmt man alle vorigen 
* urlparts dazu, dafur ich mir die Position von "toc" in der Schleife eben gemerkt:
**/
              If ($tocFound) 
              {
                   $identifier = "";
                   For ($i=0;$i<=$urlArrayKey; $i++)
                     {
                         $identifier.=$urlParts[$i] . ':';
                     }
              $id_list[] = $filename;
               $id_list[] = "oai.gbv.de" . $identifier. "toc:" . $filename;
              }



  return array('ids'=>$id_list, 'types'=>$types);
        }
}
