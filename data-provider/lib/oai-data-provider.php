<?php
/**
 * OAI 2.0 Data Provider
 * 
 * Base class only, extend for custom tasks
 * Requires DOM extension
 *
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage oai-data-provider
 * @version 0.1
 */

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
		$this->_verbnode->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'repositoryName', $this->identify['repositoryName']));
		$this->_verbnode->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'baseURL', $this->identify['baseURL']));
		$this->_verbnode->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'protocolVersion', $this->identify['protocolVersion']));
                
                $this->_verbnode->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'adminEmail', $this->identify['adminEmail']));
                
                $this->_verbnode->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'earliestDatestamp', $this->identify['earliestDatestamp']));
		$this->_verbnode->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'deletedRecord', $this->identify['deletedRecord']));
		$this->_verbnode->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'granularity', $this->identify['granularity']));
		/*foreach($this->identify['adminEmail'] as $adminEmail)
			$this->_verbnode->appendChild($this->_response->createElementNS(
				'http://www.openarchives.org/OAI/2.0/', 'adminEmail', $adminEmail)); */
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
		$this->_verbnode->appendChild($format_dc=$this->_response->createElementNS($ns='http://www.openarchives.org/OAI/2.0/', 'metadataFormat'));
		$format_dc->appendChild($this->_response->createElementNS($ns,'metadataPrefix','oai_dc'));
		$format_dc->appendChild($this->_response->createElementNS($ns,'schema','http://www.openarchives.org/OAI/2.0/oai_dc.xsd'));
		$format_dc->appendChild($this->_response->createElementNS($ns,'metadataNamespace','http://www.openarchives.org/OAI/2.0/oai_dc/'));
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
		$this->_verbnode->appendChild($rtnode=$this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'resumptionToken',$resume_at?$token:''));
		if(is_array($additional_info)) foreach($additional_info as $key=>$value)
			$rtnode->setAttribute($key,$value);
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
		$this->_response=new DOMDocument('1.0');
		if($stylesheet) {
		    $xslt=$this->_response->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'.$stylesheet.'"');
		    $this->_response->appendChild($xslt);
		}
		$this->_response->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'OAI-PMH'));
		$this->_response->documentElement->setAttributeNS(
			'http://www.w3.org/2001/XMLSchema-instance',
			'xsi:schemaLocation',
			'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');

		$this->_response->documentElement->appendChild($this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'responseDate', gmdate('Y-m-d\TH:i:s\Z')));
		$this->_response->documentElement->appendChild($requestnode=$this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'request', $this->identify['baseURL']));

		$this->_params+=array()+$_GET+$_POST;

		if(!isset($this->_params['verb'])) {
			$this->_OAI20ErrorBadVerb();
		} else {
			$this->_verbnode=$this->_response->createElementNS(
				'http://www.openarchives.org/OAI/2.0/', $verb=$this->_params['verb']);
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
				   !(($this->_check_params_allowed(array('from','until','set','metadataPrefix')) &&
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

		if($this->_print_parameters) foreach($this->_params as $key=>$value)
			$requestnode->setAttribute($key,$value);
		if($this->_had_no_error)
			$this->_response->documentElement->appendChild($this->_verbnode);

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
		$this->_response->documentElement->appendChild($errornode=$this->_response->createElementNS(
			'http://www.openarchives.org/OAI/2.0/', 'error', $message));
		$errornode->setAttribute('code',$errorcode);
	}
}
?>
