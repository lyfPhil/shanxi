#!/bin/bash
BASEDIR=$(cd "$(dirname "$0")"; pwd)
cd $BASEDIR
M=`date +"%Y%m"`

TASK_FILE=$BASEDIR/task_lists
LOG_DIR=/www/log

LOG_FILE=$LOG_DIR/qly_task_$M.log
ERR_FILE=$LOG_DIR/qly_task_$M.err
NOW=$(date +%H:%M)

for TASK in `cat $TASK_FILE`;
do
    PROC_PID=`ps ax|grep "$TASK" |grep -v 'grep'|awk '{print $1}'`
    if [ -z $PROC_PID ]
    then
        echo "start php start_task.php $TASK"
        nohup sudo -u www-data /usr/bin/php start_task.php $TASK >>$LOG_FILE 2>>$ERR_FILE &
    fi
done

TASK_FILE_ONCE=$BASEDIR/task_lists_once
while read -r LINE
do
    TASK=`echo "$LINE"|awk '{print $1}'`
    AT=`echo "$LINE"|awk '{print $2}'`
    PROC_PID=`ps ax|grep "$TASK" |grep -v 'grep'|awk '{print $1}'`
    if [ -z $PROC_PID ]
    then
        if [ "$NOW" == "$AT" ]
        then
            echo "start php start_task.php $TASK"
            nohup sudo -u www-data /usr/bin/php start_task.php $TASK >>$LOG_FILE 2>>$ERR_FILE &
        fi
    fi
done < $TASK_FILE_ONCE

find "$LOG_DIR" -mtime +30 -name "*qly_task*"|xargs rm -f
