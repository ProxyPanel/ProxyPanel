#!/bin/bash
cd `dirname $0`
ps -ef | grep queue:work | grep -v grep
if [ $? -ne 0 ]
then
    echo "Queue start listen....."
    nohup php artisan queue:work database --queue=default --timeout=60 --sleep=5 --tries=3 >> ./queue.log 2>&1 &
else
    echo "Queue is listening....."
fi