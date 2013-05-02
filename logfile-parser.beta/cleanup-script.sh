#!/bin/sh

cd /home/develop/parser

# the following will clean all records that are older than 14 days:
php -f log2ctx.php -- -c config-webdoc.php -R 14


