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

# XVFB
# http://www.w3schools.com/browsers/browsers_display.asp
# -help		prints message with these options
# -ac		disable access control restrictions
# -screen	set screen's width, height, depth
Xvfb :90.0 -ac -screen 0 1920x1080x24 &

export DISPLAY=localhost:90.0

## Selenium
java -jar selenium-server-standalone.jar &

SELENIUM_SERVER_PID=$!

wget --retry-connrefused --tries=60 --waitretry=1 http://127.0.0.1:4444/wd/hub/status -O /dev/null

if [ ! $? -eq 0 ]; then
    echo "Selenium Server not started"
else
    echo "Finished setup"
fi

## FFMPEG
# https://github.com/FFmpeg/FFmpeg
# https://github.com/Linuxbrew/linuxbrew
# https://trac.ffmpeg.org/wiki/CompilationGuide/Ubuntu
# http://soledadpenades.com/2010/04/26/unknown-input-or-output-format-x11grab-ubuntu/
# http://wiki.oz9aec.net/index.php/High_quality_screen_capture_with_Ffmpeg
# https://trac.ffmpeg.org/ticket/821
# http://ask.xmodulo.com/compile-ffmpeg-ubuntu-debian.html
# http://askubuntu.com/questions/432542/is-ffmpeg-missing-from-the-official-repositories-in-14-04
# http://ubuntuguide.org/wiki/Screencasts#Install_the_newest_version_of_FFMPEG_with_x11grab
# http://unix.stackexchange.com/questions/73622/how-to-get-near-perfect-screen-recording-quality
# https://trac.ffmpeg.org/wiki/Capture/Desktop
# https://ffmpeg.org/ffmpeg.html#Main-options
# https://trac.ffmpeg.org/ticket/1314
# https://www.ffmpeg.org/ffmpeg-devices.html#Options-19
# https://ffmpeg.org/ffmpeg.html#X11-grabbing
# https://trac.ffmpeg.org/wiki/TheoraVorbisEncodingGuide
# https://ffmpeg.org/ffmpeg-codecs.html#libtheora
# https://www.ffmpeg.org/ffmpeg-codecs.html#Options-20
# http://www.tecmint.com/record-ubuntu-desktop-screen-using-avconv/
# http://askubuntu.com/questions/277238/why-does-avconv-not-work-to-record-my-screen
# http://askubuntu.com/a/301676
# https://trac.ffmpeg.org/wiki/PHP
# ffmpeg --help
# -an                 disable audio
# -f fmt              force format
# -y                  overwrite output files
# -r rate             set frame rate (Hz value, fraction or abbreviation)
# -s size             set frame size (WxH or abbreviation)
# -i filename         input file name
# -vcodec codec       Set the video codec. This is an alias for -codec:v.
# -b                  Set the video bitrate in bit/s for CBR (Constant Bit Rate) mode. In case VBR (Variable Bit Rate) mode is enabled this option is ignored.
ffmpeg -an -f x11grab -y -r 5 -s 1920x1080 -i :99 -vcodec libtheora -qmin 31 -b 1024k test.ogg
avconv -an -f x11grab -y -r 5 -s 1920x1080 -i $DISPLAY -vcodec libtheora -qmin 31 -b 1024k test.ogg >/dev/null 2>/dev/null &

./vendor/bin/phpunit tests/VagrantTest.php



echo "PHP Server Process ID: $PHP_SERVER_PID"

echo "Selenium Server Process ID: $SELENIUM_SERVER_PID"

kill $PHP_SERVER_PID
kill $SELENIUM_SERVER_PID
