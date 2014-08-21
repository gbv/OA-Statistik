#!/bin/sh
IDENTIFIER=logger-test-$PID
MYDIR=$(dirname "$0")
exec env php -f "$MYDIR/log2ctx.php" -- -c "$MYDIR/config-logger-test.php" -i logger-test -S
