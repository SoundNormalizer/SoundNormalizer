youtube2mp3-site
=========

Authors
----------

* Nehal Patel
* Weidi Zhang


License
--------

All rights reserved


ffmpeg
---------

```
sudo add-apt-repository ppa:mc3man/trusty-media
sudo apt-get update
sudo apt-get install ffmpeg
```


youtube-dl
----------

https://rg3.github.io/youtube-dl/download.html

```
sudo curl https://yt-dl.org/downloads/2015.07.28/youtube-dl -o /usr/local/bin/youtube-dl
sudo chmod a+rx /usr/local/bin/youtube-dl
```


queue_helper.sh
-----------------

```
sudo apt-get install dos2unix
dos2unix cronjob/queue_helper.sh
```


mod_xsendfile
----------

```
sudo apt-get install libapache2-mod-xsendfile
```

After installing, add this to the ```<Directory>``` in your vhost:

```
XSendFile on
XSendFilePath "/path/to/youtube2mp3/converted"
```

crontab
----------

```
crontab -e

0 * * * * /usr/bin/php /vagrant/sites/youtube2mp3/cronjob/delete_checker.php
* * * * * /bin/sh /vagrant/sites/youtube2mp3/cronjob/queue_helper.sh
```

mp3gain
--------

```
sudo apt-get install mp3gain
```
