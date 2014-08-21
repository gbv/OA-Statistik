<?php
/**
 * Context object (CtxO) builder
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @edited by Marc Giesmann
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.3
 */

require_once(dirname(__FILE__).'/myxmlwriter.php');
    
define('XMLNS_CTX', 'info:ofi/fmt:xml:xsd:ctx');
define('XMLNS_SERVICE', 'info:ofi/fmt:xml:xsd:sch_svc');
define('XMLNS_SCHEMA', 'http://www.w3.org/2001/XMLSchema-instance');
define('XMLNS_OASA', 'http://dini.de/namespace/oas-admin');
define('XMLNS_OASI', 'http://dini.de/namespace/oas-info');
define('XMLNS_OASRI', 'http://dini.de/namespace/oas-requesterinfo');
// Obsolete:
//define('XMLNS_OASID', 'http://dini.de/namespace/oas-document-id');
define('XMLSCHEMALOC_CTX', 'http://www.openurl.info/registry/docs/xsd/info:ofi/fmt:xml:xsd:ctx');

class CtxBuilder extends MyXmlWriter {
    var $first_time=false;
    var $last_time=false;
    var $output_count;
    /**
     * Creates a ctxo out of given parameters
     * @param $data xml data to add
     */
    function add_ctxo($data) {
                //Init
                $this->openMemory();
        
        
		if(!$this->first_time)
		    $this->first_time=$data['time'];
		$this->last_time=$data['time'];
		
		$this->startElementNS(NULL,'context-object',XMLNS_CTX);
		$this->writeAttribute('timestamp',$this->format_time($data['time']));
                
		$this->startElementNS(NULL,'administration',XMLNS_CTX);
		
                $this->startElementNS(NULL,'oa-statistics',XMLNS_OASI);
                $this->writeElementNS(NULL,'status_code',XMLNS_OASI,$data['status']);
		
                //TODO: Document size: Where is the place to calculate that?
                //Dirty hack, to please xml-schema...
                if(!is_numeric($data['size']))
                    $data['size'] = 0;
                
                if(!is_numeric($data['document_size']))
                    $data['document_size'] = 0;
                    
                $this->writeElementNS(NULL,'size',XMLNS_OASI,$data['size']);
		$this->writeElementNS(NULL,'document_size',XMLNS_OASI,$data['document_size']);
		
                
                $this->writeElementNS(NULL,'format',XMLNS_OASI,$data['format']);
		$this->writeElementNS(NULL,'service',XMLNS_OASI,$data['service_id']);
	
		$this->endElement(); // oa-statistics
		$this->endElement(); // administration
		
		$this->startElementNS(NULL,'referent',XMLNS_CTX);
		$this->writeElementNS(NULL,'identifier',XMLNS_CTX,$data['document_url']);
	
		if(isset($data['document_ids']) && count($data['document_ids'])) {
		    foreach($data['document_ids'] as $id) {
			$this->writeElementNS(NULL,'identifier',XMLNS_CTX,$id);
		    }
		}
		$this->endElement(); // referent
	
		if(isset($data['referring-entity']) && $data['referring-entity']) {
		    $this->startElementNS(NULL,'referring-entity',XMLNS_CTX);
		    $this->writeElementNS(NULL,'identifier',XMLNS_CTX,$data['referring-entity']);
		    if(isset($data['referring-entity_ids'])) {
			foreach($data['referring-entity_ids'] as $id) {
			    $this->writeElementNS(NULL,'identifier',XMLNS_OASID,$id);
			}
		    }
                    $this->endElement(); // referring-entity
		}
                
                $this->startElementNS(NULL,'requester',XMLNS_CTX);
                $this->startElementNS(NULL,'metadata-by-val',XMLNS_CTX);
                $this->writeElementNS(NULL,'format',XMLNS_CTX,XMLNS_OASRI);
                $this->startElementNS(NULL,'metadata',XMLNS_CTX);
                $this->startElementNS(NULL,'requesterinfo',XMLNS_OASRI);
                $this->writeElementNS(NULL,'hashed-ip',XMLNS_OASRI,$data['ip-hashed']);
                $this->writeElementNS(NULL,'hashed-c',XMLNS_OASRI,$data['ip-c-hashed']);
                if($data['stripped-hostname'])
                    $this->writeElementNS(NULL,'hostname',XMLNS_OASRI,$data['stripped-hostname']);
                if(is_array($data['classification']) && count($data['classification']))
                    foreach($data['classification'] as $classification)
                        $this->writeElementNS(NULL,'classification',XMLNS_OASRI,$classification);
                if($data['user-agent'])
                    $this->writeElementNS(NULL,'user-agent',XMLNS_OASRI,$data['user-agent']);
                $this->endElement(); // requesterinfo
                $this->endElement(); // metadata
                $this->endElement(); // metadata-by-val
                $this->endElement(); // requester
                
                if((isset($data['service_types']) && count($data['service_types']))) {
		    $this->startElementNS(NULL,'service-type',XMLNS_CTX);
		    $this->startElementNS(NULL,'metadata-by-val',XMLNS_CTX);
		    $this->writeElementNS(NULL,'format',XMLNS_CTX,XMLNS_SERVICE);
		    $this->startElementNS(NULL,'metadata',XMLNS_CTX);
		    foreach($data['service_types'] as $svc) {
			$this->writeElementNS(NULL,$svc,XMLNS_SERVICE,'yes');
		    }
		    $this->endElement(); // metadata
		    $this->endElement(); // metadata-by-val
		    $this->endElement(); // service-type
		}
		if(isset($data['resolver'])) {
		    $this->startElementNS(NULL,'resolver',XMLNS_CTX);
		    $this->writeElementNS(NULL,'identifier',XMLNS_CTX,$data['resolver']);
		    $this->endElement(); // resolver
		}
	
		if(isset($data['referrer'])) {                
		    $this->startElementNS(NULL,'referrer',XMLNS_CTX);
		    $this->writeElementNS(NULL,'identifier',XMLNS_CTX,$data['referrer']);
		    $this->endElement(); // referrer
                }

		$this->endElement(); // context-object
    }

    /**
     * Helper function: create CtxO-compatible timestamp from posix timestamp
     * @param $timestamp_unix posix/unix timestamp
     * @return string timestamp as needed for context objects
     */
    function format_time($timestamp_unix) {
		return gmdate('Y-m-d\TH:i:s\Z',$timestamp_unix);
    }
    
    
    function outputMemory($flush = false,$force=false) {
        $out = parent::outputMemory($flush);
        
        if($this->first_time || $force)
        {
            return $out;
        }
        
        return '';
    }
    
} 
