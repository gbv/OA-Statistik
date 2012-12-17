<?php
/**********************************************************************
* OAI Service Provider
*
* This class provides an easy interface to OAI server harvesting.
* PHP > 5.1.0 is required, as well as the DOM extension.
*/

require_once(dirname(__FILE__).'/http-backend.php');

if(!extension_loaded('dom'))
	trigger_error('DOM extension not found.',E_USER_ERROR);

/**
* Exceptions thrown by the OAIServiceProvider class
* (and their derivates)
*/

/*! basic exception class interface for the OAI Service Provider */
class OAIServiceProviderException 
	extends Exception {}
/*! exceptions upon XML parsing */
class OAIServiceProviderXMLException 
	extends OAIServiceProviderException {}
/*! exceptions upon HTTP fetching */
class OAIServiceProviderHTTPException 
	extends OAIServiceProviderException {}
/*! exceptions due to protocol submitted errors */
class OAIServiceProviderOAIErrorException
	extends OAIServiceProviderException {
	/*! stores the OAI PMH error message */
	var $oai_errors=false;
	/*!
		create new protocol submitted error exception

		\param $message exception error message
		\param $oai_errors OAI error message
	*/
	function __construct($message='',$oai_errors=false) {
		parent::__construct($message);
		$this->oai_errors=$oai_errors;
	}
}
/*! a protocol submitted error */
class OAIServiceProviderOAIErrorBadArgumentException 
	extends OAIServiceProviderOAIErrorException {}
/*! a protocol submitted error */
class OAIServiceProviderOAIErrorBadResumptionTokenException 
	extends OAIServiceProviderOAIErrorException {}
/*! a protocol submitted error */
class OAIServiceProviderOAIErrorBadVerbException 
	extends OAIServiceProviderOAIErrorException {}
/*! a protocol submitted error */
class OAIServiceProviderOAIErrorCannotDisseminateFormatException 
	extends OAIServiceProviderOAIErrorException {}
/*! a protocol submitted error */
class OAIServiceProviderOAIErrorIdDoesNotExistException 
	extends OAIServiceProviderOAIErrorException {}
/*! a protocol submitted error */
class OAIServiceProviderOAIErrorNoRecordsMatchException 
	extends OAIServiceProviderOAIErrorException {}
/*! a protocol submitted error */
class OAIServiceProviderOAIErrorNoMetadataFormatsException 
	extends OAIServiceProviderOAIErrorException {}
/*! a protocol submitted error */
class OAIServiceProviderOAIErrorNoSetHierarchyException 
	extends OAIServiceProviderOAIErrorException {}

/*! Protocol violation exception */
class OAIServiceProviderOAIParserException
	extends OAIServiceProviderException {}

/*! Signal or user triggered abort condition occured */
class OAIServiceProviderAbortException
	extends OAIServiceProviderException {}


/*!
	Main class: OAIServiceProvider

	when instantiating, you need to pass the URL of the OAI server to be queried
	to the constructor.
*/
class OAIServiceProvider {
	/*! the version of this provider implementation */
	const Version='0.2';
	/*! XML namespace for OAI PMH */
	const OAI20_XML_NAMESPACE='http://www.openarchives.org/OAI/2.0/';
	/*! XML sub-namespace for OAI PMH */
	const OAI20_ABOUT_PROVENANCE_XML_NAMESPACE='http://www.openarchives.org/OAI/2.0/provenance';
	/*! instance configuration */
	var $config=false;
	/*! will save the first timestamp for the currently used OAI PMH data provider */
	var $first_server_timestamp=false;
	/*! name of the logger callback function */
	var $logger=false;
	/*! a callback function name used for checking whether we should abort harvesting */
	var $abort_callback=false;

	/*!
		create and configure OAI Service Provider instance

		\param $server_url the BaseURL of the OAI PMH Data Provider
		\param $logger optional name of the logger callback function
		\param $inputfilter optional callback for sanitizing input from OAI PMH Data Provider
		\param $useragent optional specification of our user agent string
		\param $abort_callback is the name of a function that is called to determine if we should quit
	*/
	function __construct($server_url,$logger=false,$inputfilter=false,$useragent=false,$abort_callback=false) {
		$this->config=array(
			'serverurl'    => $server_url,
			'inputfilter'  => $inputfilter,
			'user_agent'   => $useragent?$useragent:('OAIServiceProvider '.self::Version),
			'retry_after'  => 120, // number of seconds to wait when error happened on HTTP level
			'retry_errors' => 24*60*60 / 120,   // retry this many times for HTTP errors
			);
		$this->logger=$logger;
		$this->abort_callback=$abort_callback;
	}

	/*********************************************************************
	* Public interface:
	* Functions for each possible OAI query
	*
	* They all take a callback specification as first parameter. That
	* might be a function name (as a string) or an object method
	* (as array with object as first, method name as second parameter).
	*
	* Depending on the OAI query there might be mandatory or optional
	* parameters, too. Those would be passed as second parameter as
	* key=>value array.
	*/

	/*!
		run "identify" query

		\param $list_entry_callback callback function for parsing reply
	*/
	function query_identify($list_entry_callback) {
		return $this->_oai20_list_element_callback(
			'Identify',
			'',
			'/OAI20:OAI-PMH/OAI20:Identify',
			$list_entry_callback);
	}

	/*!
		run "listSets" query

		\param $list_entry_callback callback function for parsing reply
	*/
	function query_listsets($list_entry_callback) {
		return $this->_oai20_list_element_callback(
			'ListSets',
			'',
			'/OAI20:OAI-PMH/OAI20:ListSets/OAI20:set',
			$list_entry_callback);
	}

	/*!
		run "listMetadataFormats" query

		\param $list_entry_callback callback function for parsing reply
		\param $params parameters for the OAI PMH query (associative)
	*/
	function query_listmetadataformats($list_entry_callback, $params=array()) {
		$pstring='';
		foreach($params as $p=>$v)
			$pstring.='&'.$p.'='.$v;
		return $this->_oai20_list_element_callback(
			'ListMetadataFormats',
			$this->_oai_serialize_params($params),
			'/OAI20:OAI-PMH/OAI20:ListMetadataFormats'.
				'/OAI20:metadataFormat',
			$list_entry_callback);
	}

	/*!
		run "listIdentifiers" query

		\param $list_entry_callback callback function for parsing reply
		\param $params parameters for the OAI PMH query (associative)
	*/
	function query_listidentifiers($list_entry_callback, $params=array()) {
		$pstring='';
		foreach($params as $p=>$v)
			$pstring.='&'.$p.'='.$v;
		return $this->_oai20_list_element_callback(
			'ListIdentifiers',
			$this->_oai_serialize_params($params),
			'/OAI20:OAI-PMH/OAI20:ListIdentifiers'.
				'/OAI20:header',
			$list_entry_callback);
	}

	/*!
		run "listRecords" query

		\param $list_entry_callback callback function for parsing reply
		\param $params parameters for the OAI PMH query (associative)
	*/
	function query_listrecords($list_entry_callback, $params=array()) {
		$pstring='';
		foreach($params as $p=>$v)
			$pstring.='&'.$p.'='.$v;
		return $this->_oai20_list_element_callback(
			'ListRecords',
			$this->_oai_serialize_params($params),
			'/OAI20:OAI-PMH/OAI20:ListRecords'.
				'/OAI20:record',
			$list_entry_callback);
	}

	/*!
		run "getRecord" query

		\param $list_entry_callback callback function for parsing reply
		\param $params parameters for the OAI PMH query (associative)
	*/
	function query_getrecord($list_entry_callback, $params=array()) {
		return $this->_oai20_list_element_callback(
			'GetRecord',
			$this->_oai_serialize_params($params),
			'/OAI20:OAI-PMH/OAI20:GetRecord'.
				'/OAI20:record',
			$list_entry_callback);
	}

	/*********************************************************************
	* private functions
	*/

	/*!
		generate query part of URL for OAI-PMH query from parameter array

		\param $params an array containing parameter names as keys and their values as values
		\return a query string
	*/
	function _oai_serialize_params($params) {
		$pstring='';
		foreach($params as $p=>$v)
			$pstring.='&'.$p.'='.rawurlencode($v);
		return $pstring;
	}

	/*!
		callback logger function

		we use this to log information with various levels

		\param $text the log message
		\param $level the log level (<10: error, <20: warning/important, >20: info)
	*/
	function _log($text,$level=10) {
		if($this->logger) call_user_func($this->logger, $text, $level);
	}

	/*!
		check an OAI-PMH server response for error messages

		\param $result the XML document to check for errors
		\return true when there were no errors, otherwise according exceptions will be thrown
	*/
	function _oai20_checkerrors($result) {
		$xpath=$this->_oai_xpath($result);
		if(!$this->first_server_timestamp) {
			// server timestamp is saved
			$this->first_server_timestamp=
				$xpath->query('/OAI20:OAI-PMH/OAI20:responseDate')->item(0)->nodeValue;
			$this->_log('server timestamp: '.$this->first_server_timestamp, 20);
		}
		$nodes=$xpath->query('/OAI20:OAI-PMH/OAI20:error');
		if(! $nodes->length)
			return true;

		$oai_errors=array();
		foreach($nodes as $node) {
			$code=$xpath->query('@code',$node)->item(0)->nodeValue;
			if(!$code)
				throw new OAIServiceProviderOAIParserException(
					'illegal OAI server output: No error code');
			$oai_errors[]=array(
				'code'=>$code,
				'message'=>$node->nodeValue);
		}
		if(count($nodes)==1) {
			// a single error was reported, 
			switch($oai_errors[0]['code']) {
			case 'badArgument':
				$exception=new OAIServiceProviderOAIErrorBadArgumentException(
					'OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			case 'badResumptionToken':
				$exception=new OAIServiceProviderOAIErrorBadResumptionTokenException(
					'OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			case 'badVerb':
				$exception=new OAIServiceProviderOAIErrorBadVerbException(
					'OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			case 'cannotDisseminateFormat':
				$exception=new OAIServiceProviderOAIErrorCannotDisseminateFormatException(
					'OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			case 'idDoesNotExist':
				$exception=new OAIServiceProviderOAIErrorIdDoesNotExistException(
					'OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			case 'noRecordsMatch':
				$exception=new OAIServiceProviderOAIErrorNoRecordsMatchException(
					'OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			case 'noMetadataFormats':
				$exception=new OAIServiceProviderOAIErrorNoMetadataFormatsException(
					'OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			case 'noSetHierarchy':
				$exception=new OAIServiceProviderOAIErrorNoSetHierarchyException(
					'OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			default:
				$exception=new OAIServiceProviderOAIErrorException(
					'Invalid OAI Server error: '.$oai_errors[0]['message'],$oai_errors);
				break;
			}
		} else {
			// multiple errors:
			$exception=new OAIServiceProviderOAIErrorException(
				"OAI: error messages from server:\n".$result->saveXML(),$oai_errors);
		}
		throw $exception;
	}

	/*!
		Issue OAI-PMH query and call a callback function for each received entity

		\param $verb the query verb
		\param $params the query string
		\param $xpathquery the xpath for the elements that will be handed over to the callback (currently not used!)
		\param $callback the callback function for parsing/handling replies
		\return true on success
	*/
	function _oai20_list_element_callback($verb, $params, $xpathquery, $callback) { 
		//JM: resumptiontoken in Datenbank schreiben und auslesen
		$haveresumptiontokens=false;
		$res=$this->_oai_fetch($verb.$params);
		while($res) {
			if($this->abort_callback && call_user_func($this->abort_callback)) {
				// we were killed in the meantime, so abort
				throw new OAIServiceProviderAbortException('aborted by signal or user request');
			}
			$this->_oai20_checkerrors($res);    // check for server-side error messages (throws exception)
			$rt=$this->_oai20_get_resumption_token($res,$verb);
			if($rt) {
				$this->_log('flow control: resumptionToken found. Expires: '.
					(isset($rt['expirationDate'])?$rt['expirationDate']:'n/a').', cursor: '.
					(isset($rt['cursor'])?$rt['cursor']:'n/a').', completeListSize: '.
					(isset($rt['completeListSize'])?$rt['completeListSize']:'n/a').'.', 19);
			}
			$xpath=$this->_oai_xpath($res);     // XPath context, w/ registered namespaces
			// execute XPath query for result entities
			$nodes=$xpath->query($xpathquery);

			// pass on to parser callback function
			call_user_func($callback, $res);

			// check for resumptionToken and query next parts if found
			if($rt) {
				if(!$haveresumptiontokens) $haveresumptiontokens=true;
				if($rt['token'])
					$res=$this->_oai_fetch($verb.'&resumptionToken='.rawurlencode($rt['token']));
				else
					$res=false;
			} else {
				if($haveresumptiontokens) {
					// sanity check: OAI-PMH 2.0 defines that the last part
					// MUST include an empty resumptionToken
					throw new OAIServiceProviderOAIParserException(
						'illegal OAI server output: No final empty resumptionToken');
				}
				$res=false;
			}
		}
		return true;
	}

	/*!
		search for resumptionToken

		\param $result the xml document containing the OAI PMH reply
		\param $verb the query verb
		\return false if no resumption token was found, otherwise a table containing the resumptionToken information
	*/
	function _oai20_get_resumption_token($result, $verb) {
		$xpath=$this->_oai_xpath($result);
		if(!($rtnode=$xpath->query('/OAI20:OAI-PMH/OAI20:'.$verb.'/OAI20:resumptionToken')->item(0)))
			return false;
		$resumptionToken=array('token'=>$rtnode->nodeValue);
		if($a=$xpath->query('@expirationDate',$rtnode)->item(0))
			$resumptionToken['expirationDate']=$a->nodeValue;
		if($a=$xpath->query('@completeListSize',$rtnode)->item(0))
			$resumptionToken['completeListSize']=$a->nodeValue;
		if($a=$xpath->query('@cursor',$rtnode)->item(0))
			$resumptionToken['cursor']=$a->nodeValue;
		return $resumptionToken;
	}

	/*!
		initialize XPath context and register OAI namespaces

		\param $domdocument the DOM document containing the OAI PMH reply
		\return a new XPath context that has namespace prefixes initialized
	*/
	function _oai_xpath($domdocument) {
		if(!($xpath=new DOMXPath($domdocument)))
			throw new OAIServiceProviderXMLException('Cannot create DOMXPath');
		$xpath->registerNamespace('OAI20',OAIServiceProvider::OAI20_XML_NAMESPACE);
		$xpath->registerNamespace('OAI20PROV',OAIServiceProvider::OAI20_ABOUT_PROVENANCE_XML_NAMESPACE);
		return $xpath;
	}

	/*!
		do a HTTP OAI-PMH query and return resulting XML DOM Document

		\param $querystring the query string, starting with the name (!) of the verb, followed by other arguments
		\return a DOM document containing the OAI PMH query reply
	*/
	function _oai_fetch($querystring) {
		$buffer='';
		$retries=$this->config['retry_errors'];
		$url=$this->config['serverurl'].'?verb='.$querystring;

		while(!$buffer) {
			try {
				$this->_log('HTTP GET: '.$url, 20);
				$buffer=HTTPBackend::fetch($url,$this->config['user_agent']);
			} catch(HTTPErrorTemporary $e) {
				$seconds=$e->retry_after?$e->retry_after:$this->config['retry_after'];
				$this->_log('HTTP: temporary error, retrying in '.$seconds.'s');
				sleep($seconds);
			} catch(HTTPError $e) {
				if($retries == 0) {
					$this->_log('HTTP: error, maximum number of retries reached, aborting.');
					throw($e);
				} else {
					$this->_log('HTTP: error, retrying in '.$this->config['retry_after'].'s');
					$retries--;
					sleep($this->config['retry_after']);
				}
			}
		}

		$this->_log(strlen($buffer).' bytes read.', 20);

		// callback for pluggable input filter function
		if($this->config['inputfilter'])
			$buffer=call_user_func($this->config['inputfilter'],$buffer);

		// generate DOM object from answer
		$xml=new DOMDocument(); 
		if(!$xml->loadXML($buffer))
			throw new OAIServiceProviderXMLException(
				"error loading XML from <$url>"); 
		return $xml;
	}

	/*!
		tests

		\param $server_url a BaseURL for a test OAI PMH data provider
	*/
	function __test($server_url) {
		try {
			$oaisp=new OAIServiceProvider($server_url);
			$oaisp->query_identify(array($oaisp,'__test_xml_dump'));
			//$oaisp->query_listsets(array($oaisp,'__test_xml_dump'));
			$oaisp->query_listmetadataformats(array($oaisp,'__test_xml_dump'));
			$oaisp->query_listidentifiers(array($oaisp,'__test_xml_dump'),array(
				'metadataPrefix'=>'oas'));
			$oaisp->query_listrecords(array($oaisp,'__test_xml_dump'),array(
				'metadataPrefix'=>'oas'));
		} catch (Exception $e) {
			echo "Error:\n";
			var_dump($e);
		}
	}
	/*!
		test callback

		\param $doc the DOM document containing the OAI PMH query reply
	*/
	function __test_xml_dump($doc) {
		echo $doc->saveXML()."\n";
	}
}
