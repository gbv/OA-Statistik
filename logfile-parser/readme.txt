log2ctx 1.4.0b: a webserver logfile to context object container (XML) converter
(c) 2009-2014 OA-Statistik (GBV)

Author: Marc Giesmann <giesmann@gbv.de> for Verbundzentrale Göttingen
Author: Hans-Werner Hilse <hilse@sub.uni-goettingen.de> for SUB Göttingen

USAGE:

php -f log2ctx.php -- [ -c configfile.php ]
                     -O
                     -R age_in_days
                     -i identifier [ -I inputfile ]

  -c configfile.php : use configfile.php as configuration file (default: config.php)
  -O                : initialize database, create table (none of the following options
                      must be specified!)
  -R age_in_days    : clean up database, remove old data sets older than age_in_days days
                      (no other options must be specified!)
  -i identifier     : use this as identifier string for the records
  -S                : run in synchronized mode, do not fork (slower but more reliable)
  -I input_file     : Read from specified file (instead of STDIN)



Before first use, edit all STUB-files and rename as you wish.
(
 -> config-STUB.php 
 -> ./lib/oasparser-webserver-STUB.php
 -> ./lib/identifiers/lib-STUB.php
)

IMPORTANT:
If you don't fetch the identifiers out of the URL (see
"./lib/identifiers/lib-STUB.php" for details) you have to harvest
the OAI PMH interface provided by your repository. For this purpose
always harvest the identifiers regularly before parsing the logfile.

Harvesting identifiers is done by calling:
php -f harvest-identifiers.php

Examples:
"./lib/identifiers/lib-toc.php" is an example to harvest identifiers out of URLs,
and "./lib/identifiers/lib-dspace" is an example for an OAI-PMH compliant solution. 
(Last one works only if harvest-identifiers.php is called before).