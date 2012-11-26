#!/bin/bash

PATH=$PATH:/usr/bin:/usr/local/bin
BASEDIR=/home/tristan/coding/sysd
ACTION_FILE="$BASEDIR/var/cron/action"
VERSION_FILE="$BASEDIR/var/version"
EXEC="$BASEDIR/launcher"
if [[ -f $ACTION_FILE ]]; then
    ACTION=$(cat $ACTION_FILE)
else
    echo "no action file"
    exit 0
fi

case $ACTION in
    start|stop|restart)
        echo "launcher $ACTION"
        $EXEC $ACTION
        date +%Y.%-m.%-d_%T > $VERSION_FILE
        rm $ACTION_FILE
        exit 0
        ;;
    *)
        echo "usage: launcher start|stop|restart"
        exit 1
        ;;
esac