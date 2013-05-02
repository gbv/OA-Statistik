<?php
/**
 * Various classes for error handling of the HTTP backend
 *
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package oai-service-provider
 * @version 1.0
 */

/*!
	we will throw exceptions based on this interface for HTTP errors
*/
class HTTPError extends Exception {
	/*! HTTP status code */
	var $status=false;
	/*!
		create a new instance of this exception class

		\param $status the HTTP status code
	*/
	function __construct($status=false) {
		parent::__construct('HTTP Status '.($status?$status:'(none)'));
		$this->status=$status;
	}
}
/*! child exception class for permanent errors */
class HTTPErrorPermanent extends HTTPError {}
/*! child exception class for temporary errors */
class HTTPErrorTemporary extends HTTPError {
	/*! can hold information on when to retry the access */
	var $retry_after=false;
	/*!
		create new instance of this exception class

		\param $status HTTP status code
		\param $retry_after when to retry the HTTP query
	*/
	function __construct($status,$retry_after=false) {
		parent::__construct($status);
		$this->retry_after=$retry_after;
	}
}
/*! child exception class for all remaining errors */
class HTTPErrorGeneralOrTimeout extends HTTPError {}

/*!
	backend for doing actual HTTP queries
*/
class HTTPBackend {
	/*!
		HTTP GET query

		\param $url HTTP query URL
		\param $user_agent the HTTP useragent string to use
		\return a single BLOB with the data returned by the server
	*/
	function fetch($url,$user_agent='HTTPBackend PHP Class') {
		$buffer='';

		$ctx=stream_context_create(array(
			'http'=>array('user_agent'=>$user_agent)));

		if(false===($http=@fopen($url,'r',false,$ctx))) {
			$resultline=@explode(' ', $http_response_header[0]);
			switch(@$resultline[1]) {
			case 400: // Bad Request
			case 401: // Unauthorized
			case 402: // Payment Required
			case 403: // Forbidden
			case 404: // Not Found
			case 405: // Method Not Allowed
			case 406: // Not Acceptable
			case 407: // Proxy Authentication Required
			case 408: // Request Timeout
			case 409: // Conflict
			case 410: // Gone
			case 411: // Length Required
			case 412: // Precondition Failed
			case 413: // Request Entity Too Large
			case 414: // Request-URI Too Long
			case 415: // Unsupported Media Type
			case 416: // Requested Range Not Satisfiable
			case 417: // Expectation Failed
			case 501: // Not Implemented
			case 505: // HTTP Version Not Supported
				throw new HTTPErrorPermanent($resultline[1]);
			case 500: // Internal Server Error
			case 502: // Bad Gateway
			case 504: // Gateway Timeout
				throw new HTTPError($resultline[1]);
			case 503: // Service Unavailable
				$scan='Retry-After: ';
				$retry=false;
				for($l=1; $l < count($http_response_header); $l++)
					if(strpos($http_response_header[$l],$scan)===0)
						$retry=substr($http_response_header[$l],strlen($scan));
				throw new HTTPErrorTemporary($resultline[1],$retry);
			default:
				throw new HTTPErrorGeneralOrTimeout();
			}
			return false;
		}

		while(!feof($http)) $buffer.=fread($http,8192);
		fclose($http);

		return $buffer;
	}
}
