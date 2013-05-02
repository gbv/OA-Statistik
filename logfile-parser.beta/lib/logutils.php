<?php
/**
 * Common OAS specific functions, extend as needed
 * 
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 0.1
 */

/**
 * Creates SHA256 hash to obfuscate ips
 *
 * @param $ip ip address to be hashed
 * @return string sha256-hashed ip
 */
function hash_it_the_oas_way($ip) {
    global $config;
	
    $str = $ip.$config['hashsalt'];

    // hashen (SHA256)
    if(function_exists('mhash')) {
		// mhash-Extension geladen
		return bin2hex(mhash(MHASH_SHA256,$str));
	} elseif(function_exists('hash')) {
		// hash-Extension geladen
		return hash('sha256',$str); // untested
	} else {
		// native PHP-Implementation als (langsame) Alternative / Fallback
		require_once('sha256.php');
		return SHA256::hash($str); // untested
    }
}
