#Updatescript for parsing new logfiles (cronjob recommended!)
#IMPORTANT: EDIT AND/OR UNCOMMENT BEFORE USING

#!/bin/sh
#rsync -av webdoc:webdoc/aplogs/ /home/develop/webdoc-logs/
#rsync -av dspace:logs/ /home/develop/dspace-logs/

YESTERDAYWEBDOC=$(date +'%Y%m%d' -d yesterday)
YESTERDAYDSPACE=$(date +'%Y-%m-%d' -d yesterday)

cd /home/develop/parser

# Step 1: Harvest Metadata for identifier assignment
#php -f harvest-identifiers.php -- -c config-dspace.php
#php -f harvest-identifiers.php -- -c config-webdoc.php

# Step 2: Convert Webserver log files
#bzcat /home/develop/webdoc-logs/webdoc.access_log-$YESTERDAYWEBDOC.bz2 | php -f log2ctx.php -- -c config-webdoc.php -i webdoc-$YESTERDAYWEBDOC
#cat /home/develop/dspace-logs/localhost_access_log.$YESTERDAYDSPACE.txt | php -f log2ctx.php -- -c config-dspace.php -i dspace-$YESTERDAYDSPACE