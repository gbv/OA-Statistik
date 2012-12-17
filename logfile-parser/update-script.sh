#Updatescript for parsing new logfiles (cronjob recommended!)
#IMPORTANT: EDIT AND/OR UNCOMMENT BEFORE USING


# This is an example: rsync a logfile
#!/bin/sh
rsync -av dspace:logs/ /home/develop/dspace-logs/

YESTERDAYDSPACE=$(date +'%Y-%m-%d' -d yesterday)

# Step 1: Harvest Metadata for identifier assignment
php -f harvest-identifiers.php -- -c config-dspace.php

# Step 2: Convert Webserver log files
#EDIT THIS!
php -f log2ctx.php -- -c config-dspace.php -I INSERT_LOGFILEPATH_AND_NAME_HERE -i dspace-$YESTERDAYDSPACE