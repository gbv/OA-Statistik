<?php
/**
 * OAI 2.0 Data Provider
 * 
 * Base class only, extend for custom tasks
 * Requires myxmlwriter
 *
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage oai-data-provider
 * @version 0.3
 */


//Useful Constants
define('CTXO_NAMESPACE','info:ofi/fmt:xml:xsd:ctx');
define('CTXO_SCHEMA','http://www.openurl.info/registry/docs/xsd/info:ofi/fmt:xml:xsd:ctx');
define('CTXO_METADATAPREFIX','oas');

define('XML_SCHEMA_INSTANCE','http://www.w3.org/2001/XMLSchema-instance');

define ('OAI20','http://www.openarchives.org/OAI/2.0/');
define ('OAI20_XSD','http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');

define('OAIDC_ELEMENTS','http://purl.org/dc/elements/1.1/');
define('OAIDC','http://www.openarchives.org/OAI/2.0/oai_dc/');
define('OAIDC_XSD','http://www.openarchives.org/OAI/2.0/oai_dc.xsd');

class OAI20DataProvider {
	
	var $config=array('max_items'=>false);
	var $identify=array(
		'repositoryName'=>'Dummy Repository',
		'baseURL'=>false,
		'protocolVersion'=>'2.0',
		'earliestDatestamp'=>'2000-01-01',
		'deletedRecord'=>'persistent',
		'granularity'=>'YYYY-MM-DDThh:mm:ssZ',
		'adminEmail'=>'not@valid.example.org'
		);
	var $_params=array();
	var $_response=false;
	var $_print_parameters=true;
	var $_had_no_error=true;
	var $_verbnode=false;
	
	/**
	 * Creates new instance
	 * @param $baseURL base URL of the repository
	 * @param $max_items optional: number of items to be harvested; increase with caution
	 * @param $repository optional: name of own repository
	 * @param $adminEmail optional: set administrator e-mail address
	 */
	function __construct($baseURL, $max_items=100, $repositoryName='OAI Aggregator', $adminEmail=false) {
		$this->config['max_items']=$max_items;
		$this->identify['baseURL']=$baseURL;
		$this->identify['repositoryName']=$repositoryName;
		if($adminEmail) $this->identify['adminEmail']=$adminEmail;
	}

	/**
	 * Create XML answer to 'Identifiy' request
	 */
	function OAI20Identify() {            
                $this->_verbnode->writeElementNS(NULL,'repositoryName'     ,OAI20,$this->identify['repositoryName']);
                $this->_verbnode->writeElementNS(NULL,'baseURL'            ,OAI20,$this->identify['baseURL']);
                $this->_verbnode->writeElementNS(NULL,'protocolVersion'    ,OAI20,$this->identify['protocolVersion']);
                $this->_verbnode->writeElementNS(NULL,'adminEmail'         ,OAI20,$this->identify['adminEmail']);        
                $this->_verbnode->writeElementNS(NULL,'earliestDatestamp'  ,OAI20,$this->identify['earliestDatestamp']);  
                $this->_verbnode->writeElementNS(NULL,'deletedRecord'      ,OAI20,$this->identify['deletedRecord']);
                $this->_verbnode->writeElementNS(NULL,'granularity'        ,OAI20,$this->identify['granularity']);
	}
        
	/**
	 * Handle 'GetRecord' request
	 * MUST BE OVERLOADED IN CHILD CLASSES
	 */
	function OAI20GetRecord() {
		//overload me...
	}

	/**
	 * Handle 'ListMetadataFormats' request
	 */
	function OAI20ListMetadataFormats() {
            $this->_verbnode->startElementNS(NULL,'metadataFormat'         ,OAI20);
                $this->_verbnode->writeElementNS(NULL,'metadataPrefix'     ,OAI20,'oai_dc');
                $this->_verbnode->writeElementNS(NULL,'schema'             ,OAI20,'http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
                $this->_verbnode->writeElementNS(NULL,'metadataNamespace'  ,OAI20,'http://www.openarchives.org/OAI/2.0/oai_dc');
            $this->_verbnode->endElement(); // metadataFormat
            // overload to offer more metadataFormats
	}

	/**
	 * Handle 'ListSets' request
	 * @return bool always false if not overloaded
	 */
	function OAI20ListSets() {
		//overload me if sets are supported...
		return false;
	}

	/**
	 * Handle 'ListRecords' request
	 * MUST BE OVERLOADED IN CHILD CLASSES
	 */
	function OAI20ListRecords() {
		//overload me...
	}

	/**
	 * Handle 'ListIdentifiers' request
	 */
	function OAI20ListIdentifiers() {
		//overload me...
	}

	/**
	 * Handle ResumptionToken
	 * @param $resume_at optional: resume from this position
	 * @param $params optional: array of parameters
	 * @param $additional_info optional: additional info, set as attrubutes
	 */
	function _OAI20ResumptionToken($resume_at=false,$params=false,$additional_info=false) {
		if($resume_at) $token='pos:'.$resume_at;
		if(is_array($params)) foreach($params as $key=>$value)
			if(!in_array($key,array('verb','pos','resumptionToken')))
				$token.=','.$key.':'.$value;
                        
                $this->_verbnode->startElementNS(NULL,'resumptionToken',NULL);
                if(is_array($additional_info)){
                    foreach($additional_info as $key=>$value)
			$this->_verbnode->writeAttribute($key,$value);
                }
                
                if($resume_at)
                   $this->_verbnode->addContent($token);
                
                $this->_verbnode->endElement(); // resumptionToken
	}

	/**
	 * Check if all required parameters are set
	 * @param $required array of parameters
	 * @return bool true if complete
	 */
	function _check_params_required($required) {
		$complete=true;
		foreach($required as $r)
			if(!isset($this->_params[$r]))
				$complete=false;

		return $complete;
	}

	/**
	 * Check if only allowed parameters are set
	 * @param $allowed array of parameters
	 * @return bool true if ok
	 */
	function _check_params_allowed($allowed) {
		$ok=true;
		$allowed_full=$allowed+array(-1=>'verb',-2=>'pos',-3=>'rqt');
		foreach(array_keys($this->_params) as $p)
			if(!in_array($p,$allowed_full))
				$ok=false;
		return $ok;
	}

	/**
	 * Converts date string to Unix timestamp
	 * @param $datestamp date to parse
	 * @return int|bool datestamp if valid date supplied, otherwise false 
	 */
	function _OAI20_parse_datestamp($datestamp) {
		if(preg_match('/^\d\d\d\d-\d\d-\d\d(T\d\d:\d\d:\d\dZ)?$/',$datestamp)) {
			//return str_replace(array('-','T',':','Z'),'',$datestamp);
			return strtotime($datestamp);
		}
		return false;
	}

	/**
	 * Checks resumption token parameters
	 * @return bool true if ok
	 */
	function _OAI20_parse_resumptionToken() {
		if(isset($this->_params['resumptionToken'])) {
			// parse resumptionToken
			foreach(explode(',',$this->_params['resumptionToken']) as $i) {
				if(!($pos=strpos($i,':'))) {
					$this->_OAI20ErrorBadResumptionToken();
					return false;
				}
				$this->_params[substr($i,0,$pos)]=substr($i,$pos+1);
			}
		} else {
			// initialize flow control
			$this->_params['pos']=0;
			$this->_params['rqt']=gmdate('YmdHis');
		}
		if(!isset($this->_params['pos'])) {
			$this->_OAI20ErrorBadResumptionToken();
			return false;
		}
		return true;
	}

	/**
	 * Handles OAI request and generates XML output
	 * @param $stylesheet link to XSL stylesheet
	 * @return DOMDocument
	 */
	function handle_request($stylesheet=false) {
                 //Getting Params
                 $this->_params+=array()+$_GET+$_POST;
            
                 //init myXMFLWriter
                $this->_response=new MyXmlWriter();
                $this->_response->openMemory();
                $this->_response->startDocument();
                
               if($stylesheet) {
                    $this->_response->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'.$stylesheet.'"');
                }
                
                //Open OAI-PMH Protocol
                $this->_response->startElementNS(NULL,'OAI-PMH',OAI20);
                $this->_response->writeAttributeNS('xsi','schemaLocation',XML_SCHEMA_INSTANCE,OAI20 . ' ' .OAI20_XSD); //CHECK THIS!
                
                    $this->_response->writeElementNS(NULL,'responseDate',OAI20,gmdate('Y-m-d\TH:i:s\Z'));

                    $this->_response->startElementNS(NULL,'request',OAI20);
                        if(isset($this->_params['verb']))
                            $this->_response->writeAttribute('verb',$this->_params['verb']);
                        
                        if(isset($this->_params['metadataPrefix']))
                            $this->_response->writeAttribute('metadataPrefix',$this->_params['metadataPrefix']);
                        
                        $this->_response->addContent($this->identify['baseURL']);
                    $this->_response->endElement();//request


		if(!isset($this->_params['verb'])) {
			$this->_OAI20ErrorBadVerb();
		} else {
			$verb=$this->_params['verb'];
                        
                        //init myXMLWriter for "verbnode", e.g. contextobjects-content or identify
                        $this->_verbnode=new MyXmlWriter();
                        $this->_verbnode->openMemory(); //just open memory, because we want do append this to the responsenode later
                        
			if($verb=='GetRecord') {
				if(!$this->_check_params_required(array('identifier','metadataPrefix'))||
					!$this->_check_params_allowed(array('identifier','metadataPrefix'))) {
					$this->_OAI20ErrorBadArgument();
				} else
					$this->OAI20GetRecord();
			} elseif($verb=='Identify') {
				if(!$this->_check_params_allowed(array())) {
					$this->_OAI20ErrorBadArgument();
				} else
					$this->OAI20Identify($this->_response);
			} elseif($verb=='ListMetadataFormats') {
				if(!$this->_check_params_allowed(array('identifier'))) {
					$this->_OAI20ErrorBadArgument();
				} else
					$this->OAI20ListMetadataFormats();
			} elseif($verb=='ListSets') {
				if(!$this->_check_params_allowed(array('resumptionToken'))) {
					$this->_OAI20ErrorBadArgument();
				} else {
					$this->_OAI20_parse_resumptionToken();
					if($this->OAI20ListSets()===false)
						$this->_OAI20ErrorNoSetHierarchy();
				}
			} elseif($verb=='ListRecords'||$verb=='ListIdentifiers') {
				if(!(($this->_check_params_allowed(array('resumptionToken')) &&
					$this->_check_params_required(array('resumptionToken')))) &&
				   !(($this->_check_params_allowed(array('from','until','set','metadataPrefix','harvesterIdentifier')) &&
					$this->_check_params_required(array('metadataPrefix')))) ) {
					$this->_OAI20ErrorBadArgument();
				} else {
					$this->_OAI20_parse_resumptionToken();
					if($verb=='ListRecords') {
						$this->OAI20ListRecords();
					} elseif($verb=='ListIdentifiers') {
						$this->OAI20ListIdentifiers();
					}
				}
			} else
				$this->_OAI20ErrorBadVerb();
		}

		unset($this->_params['pos']);
		unset($this->_params['rqt']);

		
                if($this->_had_no_error){
                        $this->_response->writeElementNS(NULL, $verb,OAI20 , $this->_verbnode->outputMemory(), false);
                        $this->_response->endElement(); //metadata
                    }
                
                $this->_response->endElement(); //oai pmh
		return $this->_response;
	}
	
	//*** Functions for internal error handling below ***//
	
	function _OAI20ErrorBadVerb() {
		$this->_print_parameters=false;
		$this->_OAI20Error('badVerb',
			'Value of the verb argument is not a legal OAI-PMH verb, '.
			'the verb argument is missing, or the verb argument is repeated.');
	}
	function _OAI20ErrorBadArgument() {
		$this->_print_parameters=false;
		$this->_OAI20Error('badArgument',
			'The request includes illegal arguments, is missing required arguments, '.
			'includes a repeated argument, or values for arguments have an illegal syntax.');
	}
	function _OAI20ErrorBadResumptionToken() {
		$this->_OAI20Error('badResumptionToken',
			'The value of the resumptionToken argument is invalid or expired.');
	}
	function _OAI20ErrorCannotDisseminateFormat() {
		$this->_OAI20Error('cannotDisseminateFormat',
			'The metadata format identified by the value given for the metadataPrefix '.
			'argument is not supported by the item or by the repository.');
	}
	function _OAI20ErrorIdDoesNotExist() {
		$this->_OAI20Error('idDoesNotExist',
			'The value of the identifier argument is unknown or illegal in this repository.');
	}
	function _OAI20ErrorNoRecordsMatch() {
		$this->_OAI20Error('noRecordsMatch',
			'The combination of the values of the from, until, set and metadataPrefix '.
			'arguments results in an empty list.');
	}
	function _OAI20ErrorNoMetadataFormats() {
		$this->_OAI20Error('noMetadataFormats',
			'There are no metadata formats available for the specified item.');
	}
	function _OAI20ErrorNoSetHierarchy() {
		$this->_OAI20Error('noSetHierarchy',
			'The repository does not support sets.');
	}
	function _OAI20Error($errorcode,$message) {
		$this->_had_no_error=false;

                $this->_response->startElementNS(NULL,'error',OAI20);
                    $this->_response->writeAttribute('code',$errorcode);
                    $this->_response->addContent($message);
                $this->_response->endElement();
	}
}
?>
