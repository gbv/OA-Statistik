<?php
/**
 * Common library for logfile parsers
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

class OASParser {
    var $logger=false;
    var $config=false;

    /**
	 * Constructor
	 * @param $config optional: config to use
	 * @param $logger optional: logger to use
	 * @return OASParserWebserverStandard instance
	 */
    function __construct($config=false, $logger=false) {
	    $this->logger=$logger;
	    $this->config=$config;
    }

    /**
     * Logging function
     * @param $what text to log
     * @param $level optional: log level
     */
    function _log($what, $level=10) {
	    if($this->logger) call_user_func($this->logger, $what, $level);
    }
    
    /**
     * Check if string is a valid IP address
     * 
	 * @param $name string to check
	 * @return bool true if IP is valid
     */
    function is_ip($name) {
	    if(preg_match('/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/',$name))
		    return true;
	    return false;
    }

    /**
     * Check if HTTP status code is valid (i.e. counts as a hit)
	 * 
     * @param $status status code to check
     * @return bool true if valid, otherwise false
     */
    function statuscode_filter($status) {
	    if($status==200) return true;
	    if($status==206) return true;
	    if($status==301) return true;
	    if($status==302) return true;
	    if($status==304) return true;
	    return false;
    }

    /**
     * Check if HTTP request method is valid (i.e. counts as a hit)
	 * 
	 * @param $method request method to check
	 * @return bool true if valid
	 */
    function method_filter($method) {
	    if($method=='GET') return true;
	    if($method=='POST') return true;
	    //if($method=='HEAD') return true;
	    //if($method=='PUT') return true;
	    return false;
    }

	/**
	 * Get "C-class" of an IP adress, i.e. the first three bytes
	 * @param $ip IP to shorten
	 * @return string C-class in form xxx.xxx.xxx.0
	 */
    function get_c_class_net($ip) {
		return preg_replace('/^([0-9]+\.[0-9]+\.[0-9]+)\.[0-9]+$/','\1.0',$ip);
    }

    /**
     * Get top level domain name
     * 
     * Shortens a given domain to top level, e.g. d12345.ab.xyz.gwdg.de -> gwdg.de
     * 
     * @param $long_domain domain to shorten
     * @return string TLD only
     */
    function get_first_level_domain($long_domain) {
		if(preg_match('/(.+\.)?([^.]+\.[^.]+)$/',$long_domain,$match)) {
		    return $match[2];
		} else {
		    return false;
		}
    }
}
