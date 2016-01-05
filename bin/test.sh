#!/bin/bash

# WordPress test dir
WP_PAY_TEST_DIR="/Users/remco/Websites/wp-pay-test.dev/"
WP_PAY_TEST_PLUGIN="/Users/remco/Projects/wp-pronamic-ideal"
WP_PAY_TEST_URL="http://wp-pay-test.dev/"
WP_PAY_TEST_TITLE="WordPress Pay Test"
WP_PAY_TEST_ADMIN_USER="remcotolsma"
WP_PAY_TEST_ADMIN_PASSWORD="remcotolsma"
WP_PAY_TEST_ADMIN_EMAIL="info@remcotolsma.nl"
WP_PAY_TEST_LOCALE="nl_NL"
WP_PAY_TEST_DB_USER="root"
WP_PAY_TEST_DB_PASS="test1234"
WP_PAY_TEST_DB_NAME="wp-pay-test"
WP_PAY_CLI_BIN="vendor/wp-cli/wp-cli/bin/wp"

WP_PAY_TEST_SUITE_DIR=$PWD

# Delete WordPress test dir
if [ -d "$WP_PAY_TEST_DIR" ]; then
	echo "WordPress pay test dir exists: $WP_PAY_TEST_DIR"
else
	echo "WordPress pay test dir does not exists: $WP_PAY_TEST_DIR"

	mkdir $WP_PAY_TEST_DIR
fi

cd $WP_PAY_TEST_DIR

if [ -f "$WP_PAY_TEST_DIR/wp-load.php" ]; then
	echo "WordPress files seem to already be present here."
fi

wp core download --path=$WP_PAY_TEST_DIR --locale=$WP_PAY_TEST_LOCALE

wp core config --dbname=$WP_PAY_TEST_DB_NAME --dbuser=$WP_PAY_TEST_DB_USER --dbpass=$WP_PAY_TEST_DB_PASS --locale=$WP_PAY_TEST_LOCALE

wp db create

wp core install --url=$WP_PAY_TEST_URL --title="$WP_PAY_TEST_TITLE" --admin_user=$WP_PAY_TEST_ADMIN_USER --admin_password=$WP_PAY_TEST_ADMIN_PASSWORD --admin_email=$WP_PAY_TEST_ADMIN_EMAIL

# Pronamic iDEAL
ln -s $WP_PAY_TEST_PLUGIN $WP_PAY_TEST_DIR/wp-content/plugins/pronamic-ideal

wp plugin activate pronamic-ideal

# WooCommerce
wp plugin install woocommerce --activate

# https://github.com/woothemes/woocommerce/blob/2.4.12/includes/admin/class-wc-admin.php#L96-L106
wp transient delete _wc_activation_redirect

# Back to working directory
echo $WP_PAY_TEST_SUITE_DIR

cd $WP_PAY_TEST_SUITE_DIR

# Selenium
selenium-server -p 4444 &

SELENIUM_SERVER_PID=$!

echo $SELENIUM_SERVER_PID

# Waiting for Selenium
wget --retry-connrefused --tries=60 --waitretry=1 http://127.0.0.1:4444/wd/hub/status -O /dev/null

# PHPUnit
phpunit --verbose

# Selenium
kill $SELENIUM_SERVER_PID

# Delete database
# wp db drop

# Delete directory
# rm -r $WP_PAY_TEST_DIR

