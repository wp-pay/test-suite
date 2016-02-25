#!/usr/bin/env bash

# https://github.com/wp-pay/travis-ci-test
echo 'Hello World!'

apt-get update

# apt-cache search 

# http://christopher.su/2015/selenium-chromedriver-ubuntu/

# https://www.digitalocean.com/community/tutorials/how-to-install-java-on-ubuntu-with-apt-get
# apt-get -y install openjdk-7-jre
apt-get -y install default-jre

# Google Chrome
# http://www.howopensource.com/2011/10/install-google-chrome-in-ubuntu-11-10-11-04-10-10-10-04/
# http://askubuntu.com/questions/79280/how-to-install-chrome-browser-properly-via-command-line
# apt-get -y install google-chrome-stable
apt-get -y install chromium-browser

apt-get -y install unzip

apt-get -y install node

apt-get -y install php5-fpm
apt-get -y install php5-cli

apt-get -y install php5-common
apt-get -y install php5-dev

apt-get -y install php5-mysql
apt-get -y install php5-curl

apt-get -y install mysql-server

apt-get -y install xvfb

apt-get -y install phpunit
apt-get -y install phpunit-selenium

## FFPMG
# http://www.faqforge.com/linux/how-to-install-ffmpeg-on-ubuntu-14-04/
# http://ffmpeg.org/download.html#LinuxBuilds
sudo add-apt-repository ppa:mc3man/trusty-media

sudo apt-get update

sudo apt-get dist-upgrade

sudo apt-get install ffmpeg

# http://wp-cli.org/#install
# https://github.com/Varying-Vagrant-Vagrants/VVV/blob/v1.1/provision/provision.sh#L360-L373
curl --silent -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar

php wp-cli.phar --info

chmod +x wp-cli.phar

sudo mv wp-cli.phar /usr/local/bin/wp

wp --info --allow-root
