<?php
/**
 * CLI tool for converting logfiles to ContextObject containers
 *
 * @author Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen
 * @package data-provider
 * @subpackage logfile-parser
 * @version 1.3.6b
 */

$version='1.3.6b';

// Check if required PHP extensions are available
if(!function_exists('preg_match'))
    trigger_error('Perl Regular Expressions needed! Make sure extension is loaded!', E_USER_ERROR);
if(!function_exists('gethostbyname') || !function_exists('gethostbyaddr'))
    trigger_error('gethostbyname() and gethostbyaddr() are needed! Running some obscure OS?', E_USER_ERROR);

require_once(dirname(__FILE__).'/lib/oasparser-webserver-standard.php');

// Parse command line options
$options=getopt('c:I:i:R:OAhSv');


$addargs = "";
if(isset($options['v']))
    $addargs = " -v";

if(@$options['c']) {
	// config file specified as script parameter:
	require_once($options['c']);
	$config['callback']='php -f '.escapeshellarg(__FILE__).' -- -A -c '.escapeshellarg($options['c']).$addargs;
} else {
	// fallback: config.php
	if(!(include dirname(__FILE__).'/config.php'))
	    $options['h'] = true; // show help
	$config['callback']='php -f '.escapeshellarg(__FILE__).' -- -A -c '.escapeshellarg(dirname(__FILE__).'/config.php').$addargs;
}


//
$config['verbose'] = (isset($options['v'])==true);

if(isset($options['h'])) {
	// help requested
	fwrite(STDERR,
		"log2ctx ".$version.": a webserver logfile to context object container (XML) converter\n".
		"(c) 2009-2013 OA-Statistik (DFG) / SUB Goettingen / Hans-Werner Hilse\n".
		"<hilse@sub.uni-goettingen.de>\n".
		"\n".
		"USAGE:\n".
		"\n".
		"php -f log2ctx.php -- [ -c configfile.php ]\n".
		"                     -O\n".
		"                     -R age_in_days\n".
		"                     -i identifier [ -I inputfile ]\n".
		"\n".
		"  -c configfile.php : use configfile.php as configuration file (default: config.php)\n".
		"  -O                : initialize database, create table (none of the following options\n".
	        "                      must be specified!)\n".  
		"  -R age_in_days    : clean up database, remove old data sets older than age_in_days days\n".
	        "                      (no other options must be specified!)\n".
		"  -i identifier     : use this as identifier string for the records\n".
		"  -S                : run in synchronized mode, do not fork (slower but more reliable)\n".
		"  -I input_file     : Read from specified file (instead of STDIN)\n".
		"\n");
	die(0);
}

// input file specified
if(@$options['I']) $config['file_in']=$options['I'];

// identifier specified
if(@$options['i']) $config['identifier']=$options['i'];

$config['async']=true;

// do not use multiple threads
if(isset($options['S'])) $config['async']=false;

if(isset($options['O'])) {
    // special case: initialize database
    try {
        
        //Create table for contextobjects
        $sqlquery = 'CREATE TABLE '.$config['tablename'].' ( timestamp INT(11) DEFAULT NULL,'. 
                                                        'identifier VARCHAR(255) DEFAULT NULL,'.
                                                        'line INT(11) DEFAULT NULL,'.
                                                        'data LONGBLOB,'.
                                                        'KEY timestamp (timestamp)) '.
                                                        
                                                        'ENGINE=MyISAM DEFAULT CHARSET=latin1';
        
        
        //Give some infos
        logger ('---------------------------------------');
        logger ('Trying to create table in database "'.$config['database'] .'"....');
        logger (' ');
        logger ('Query:');
        logger ($sqlquery.';');
        logger ('---------------------------------------');

        $tempdbh = new PDO(
            $config['database'],
            $config['username'],
            $config['password'],
            array(PDO::ATTR_PERSISTENT => false));

        $tempdbh->beginTransaction();

        $stmt = $tempdbh->prepare($sqlquery);
        $stmt->execute();
        $tempdbh->commit();

    } catch (Exception $e) {
                    logger("<Database ERROR> Cannot interface with database: ".$e->getMessage());
    }
    
    
    try{
        mkdir('./data/');
    }catch(Exception $e) {
                    logger('Unable to create directory "./data/" with "chmod 0700". This is needed for the sqlite cache ');
                    logger('"'.$config['db_identifier'].'!"');
    }
    
    
    logger( "\nOK.\n");
    
} elseif(@$options['R']) {
    // special case: remove old data sets
    if($options['R']==='all') {
	$max_age = 0;
    } else {
	$max_age = (int)$options['R'];
	if($max_age == 0) {
	    echo "Invalid age_in_days specified.\n";
	    die();
	}
    }
    $dbh = new PDO(
	    $config['database'],
	    $config['username'],
	    $config['password']);
    $stmt = $dbh->prepare('DELETE FROM '.$config['tablename'].' WHERE timestamp < ?');
    $delete_up_to = time() - ($max_age*24*60*60);
    $stmt->bindParam(1, $delete_up_to);
    $dbh->beginTransaction();
    $stmt->execute();
    $dbh->commit();
    $stmt2 = $dbh->prepare('VACUUM');
    @$stmt2->execute();
    $stmt2 = $dbh->prepare('OPTIMIZE TABLE '.$config['tablename']);
    @$stmt2->execute();
    echo "OK.\n";
} elseif(isset($options['A'])) {
    // asynchronous child process
    $parser=new $config['parser']($config, 'logger');
    $datastr='';
    while(!feof(STDIN)) $datastr.=fread(STDIN, 1024);
    $data=unserialize($datastr);
    if(is_array($data))
	$parser->parse_async($data);
} else {
    // default: parse
    $parser=new $config['parser']($config, 'logger');
    $parser->parse();
}

/**
 * Echoes log messages directly to standard error output stream
 * @param $what string to log
 */
function logger($what) {
	fwrite(STDERR, "$what\n");
}
