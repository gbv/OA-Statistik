#! /usr/bin/env bash
##############################################################################
# 
# get random salt for anonymizing ips.
# 
##############################################################################

# file to save salt in
SAVE_PATH="./lib/"

# random value
RANDOMVAL=$((($RANDOM * 32768 + $RANDOM)))
##############################################################################
# get verbose flag
for par in $*; do
	if [ "$par" = "-v" ]; then
		VERBOSE=1
	fi
done

# run until got a new salt

if [ "$VERBOSE" = 1 ]; then
    echo "<?php \$salt = '"$RANDOMVAL"'; ?>"
fi

echo "<?php \$salt = '"$RANDOMVAL"'; ?>" > "$SAVE_PATH".new

