#!/bin/bash
cd `dirname $0`
ps -ef | grep queue:work | grep -v grep
if [ $? -ne 0 ]
then
    echo "启动队列监听"
    nohup php artisan queue:work redis --daemon --queue=default --timeout=120 --tries=3 -vvv >> ./queue.log 2>&1 &
else
    echo "队列监听中"
fi
