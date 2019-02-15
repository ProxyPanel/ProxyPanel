#!/bin/bash
ps -ef | grep queue:work | grep -v grep
if [ $? -ne 0 ]
then
    echo "start queue process successfully....."
    nohup php artisan queue:work database --queue=default --timeout=60 --sleep=5 --tries=3 >> ./queue.log 2>&1 &
else
    echo "queue is running....."
fi