#!/bin/bash
nohup php artisan queue:work database --queue=default --timeout=60 --sleep=5 --tries=3 >> ./storage/logs/queue.log 2>&1 &