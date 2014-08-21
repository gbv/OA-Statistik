#! /usr/bin/env bash
##############################################################################
# 
# get SALT from server in Saarbruecken
# 
# update configuration below before first usage!
# 
# get verbose output with -v flag
# 
# mw, 06/2012
# 
##############################################################################
# login data
USER="mimkes@sub.uni-goettingen.de"
PASSWORD="xeehuG5E"
# URL of salt file
SALT_URL="http://oas.sulb.uni-saarland.de/salt/salt_value.txt"
# file to save salt in
SAVE_PATH="/home/oas_demo/logfile-parser/lib/"
# interval between attempts to get a new salt
SLEEP_INTERVAL=300
##############################################################################
# get verbose flag
for par in $*; do
	if [ "$par" = "-v" ]; then
		VERBOSE=1
	fi
done
# run until got a new salt
while true; do
  # get current salt from server
  wget --http-user="$USER" --http-passwd="$PASSWORD" "$SALT_URL" -O "${SAVE_PATH}.new" > /dev/null 2>&1
  # previous version exists
  if [ -e "${SAVE_PATH}.new" -a -e "${SAVE_PATH}" ]; then
    # compare if got a new version
    diff "${SAVE_PATH}.new" "${SAVE_PATH}" > /dev/null 2>&1
    EXIT_CODE=$?
    # got new version, stop here
    if [ "$EXIT_CODE" -ne 0 ]; then
	    mv "${SAVE_PATH}.new" "${SAVE_PATH}"
	    if [ "$VERBOSE" = 1 ]; then
	      echo Updated salt in ${SAVE_PATH}.
	    fi
	    break
	  fi
  # no old version found, keep current version and exit
	else
	  mv "${SAVE_PATH}.new" "${SAVE_PATH}"
	  if [ "$VERBOSE" = 1 ]; then
	    echo No old version found to compare, saved current salt to ${SAVE_PATH}.
	  fi
	  break
	fi
	# wait some time before next attempt
	if [ "$VERBOSE" = 1 ]; then
	  echo No new version found, sleeping $SLEEP_INTERVAL seconds before next try...
	fi
  sleep $SLEEP_INTERVAL
done

