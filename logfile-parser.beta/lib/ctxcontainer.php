<?php
/**
 * Context object (CtxO)- container builder
 * 
 * @author Marc Giesmann <giesmann@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

require_once(dirname(__FILE__).'/myxmlwriter.php');
  
//Commented, cause already defined in ctxbuilder.php
//define('XMLNS_CTX', 'info:ofi/fmt:xml:xsd:ctx');
//define('XMLSCHEMALOC_CTX', 'http://www.openurl.info/registry/docs/xsd/info:ofi/fmt:xml:xsd:ctx');

class CtxContainer extends MyXmlWriter {
    var $counter;
    var $container_closed = false;
    
    function start() {
        $this->openMemory();
        
        $this->startElementNS(NULL,'context-objects',XMLNS_CTX);
        $this->writeAttributeNS('xsi','schemaLocation',XMLNS_SCHEMA,XMLNS_CTX.' '.XMLSCHEMALOC_CTX);
    }
    
    
    /**
     * Closes the container
    */
    function done() {
        if(!$this->container_closed){
            $this->endElement(); // context-objects
            $container_closed = true;
        }
    }
    
    
    /**
     * Adds a contextobject to xml
     * $ctxo has to be a ctxbuilder object
    */
    function addCtxo($ctxo)
    {
        $this->appendXML($ctxo);
        $this->counter++;
    }
    
    function countCtxos()
    {return $this->counter;}
    
    function getXML()
    {
        $this->done();
        return $this->outputMemory();
    }
    
}


?>
