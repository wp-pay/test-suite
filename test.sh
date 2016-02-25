#!/usr/bin/env bash

# Selenium - http://www.seleniumhq.org/
if [ -f "selenium-server-standalone.jar" ]; then
	echo "Selenium exists"
else
	echo "Selenium does not exist"

	curl --output selenium-server-standalone.jar http://selenium-release.storage.googleapis.com/2.52/selenium-server-standalone-2.52.0.jar
fi

# ChromeDriver - https://sites.google.com/a/chromium.org/chromedriver/
curl --output chromedriver_linux64.zip http://chromedriver.storage.googleapis.com/2.21/chromedriver_linux64.zip

unzip chromedriver_linux64.zip

chmod +x chromedriver

# WordPress
if [ -d "wordpress" ]; then
	rm -rf ./wordpress/
fi

wp --info

wp core download

wp core config

wp db drop

wp db create

wp core install

# ps aux | grep php
# @see http://linuxcommando.blogspot.nl/2007/11/how-to-look-up-process.html
wp server &

PHP_SERVER_PID=$!

echo $PHP_SERVER_PID

## Selenium
xvfb-run java -jar selenium-server-standalone.jar &

SELENIUM_SERVER_PID=$!

wget --retry-connrefused --tries=60 --waitretry=1 http://127.0.0.1:4444/wd/hub/status -O /dev/null

if [ ! $? -eq 0 ]; then
    echo "Selenium Server not started"
else
    echo "Finished setup"
fi

echo "PHP Server Process ID: $PHP_SERVER_PID"

echo "Selenium Server Process ID: $SELENIUM_SERVER_PID"

kill $PHP_SERVER_PID
kill $SELENIUM_SERVER_PID
