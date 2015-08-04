#!/bin/sh
QUEUE_FILE=/vagrant/sites/youtube2mp3/cronjob/queue_checker.php
(sleep 15 && php $QUEUE_FILE) &
(sleep 30 && php $QUEUE_FILE) &
(sleep 45 && php $QUEUE_FILE) &
(sleep 60 && php $QUEUE_FILE) &
