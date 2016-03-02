<?php

require_once 'load.php';

$test = new Test( false );

// Run
if ( true ) {
	$test->passthru( 'rm -r ./wordpress' );
	$test->passthru( 'mkdir ./wordpress' );
	$test->passthru( 'wp core download' );
	$test->passthru( sprintf( 'wp core config --dbpass=%s', 'root' ) );

	$test->passthru( 'wp core language install nl_NL' );
	$test->passthru( 'wp theme install storefront' );
	$test->passthru( 'wp plugin install woocommerce' );
	$test->passthru( 'wp plugin install pronamic-ideal' );
}

$test->passthru( 'wp db drop --yes --quiet' );
$test->passthru( 'wp db create' );

$test->passthru( 'wp core install --skip-email' );

$test->passthru( 'wp core language activate nl_NL' );
$test->passthru( 'wp theme activate storefront' );
$test->passthru( 'wp plugin activate woocommerce' );
$test->passthru( 'wp plugin activate pronamic-ideal' );

$test->passthru( 'wp option update pronamic_pay_license_status valid' );
$test->passthru( 'wp user meta update test pronamic_pay_ignore_tour 1' );
$test->passthru( 'wp user meta update test show_admin_bar_front 0' );
$test->passthru( 'wp user meta update test billing_first_name Test' );
$test->passthru( 'wp user meta update test billing_last_name Test' );
$test->passthru( 'wp user meta update test billing_company Test' );
$test->passthru( 'wp user meta update test billing_address_1 "Test 1"' );
$test->passthru( 'wp user meta update test billing_address_2 ""' );
$test->passthru( 'wp user meta update test billing_city Test' );
$test->passthru( 'wp user meta update test billing_postcode "1234 TE"' );
$test->passthru( 'wp user meta update test billing_country NL' );
$test->passthru( 'wp user meta update test billing_state Test' );
$test->passthru( 'wp user meta update test billing_phone 1234567890' );
$test->passthru( 'wp user meta update test billing_email test@wordpress.dev' );

$product_count = $test->shell_exec( 'wp post list --post_type=product --format=count' );

if ( 0 == $product_count ) {
	$test->passthru( 'wp plugin install wordpress-importer --activate' );
	
	$test->passthru( 'wp import wordpress/wp-content/plugins/woocommerce/dummy-data/dummy-data.xml --authors=create --skip=image_resize' );
}

$display = null;

if ( $test->is_executable( 'Xvfb' ) ) {
	$display = ':90.0';

	$test->process( sprintf( 'Xvfb %s -ac -screen 0 1920x1080x24', $display ), 'logs/xvfb.log', 'pids/%s-xvfb.pid' );

	if ( $test->is_executable( 'avconv' ) ) {
		$test->process( sprintf( 'avconv -an -f x11grab -y -r 5 -s 1920x1080 -i %s -vcodec libtheora -qmin 31 -b 1024k test.ogg', $display ), '/dev/null', 'pids/%s-avconv.pid' );

		// @see http://unix.stackexchange.com/a/117623
		// $test->process( sprintf( 'avconv -f x11grab -s 1920x1080 -r 30 -i %s -vcodec h264 test.mkv', $display ), '/dev/null', 'pids/%s-avconv.pid' );

		// @see http://stackoverflow.com/questions/10166204/ffmpeg-screencast-recording-which-codecs-to-use
		// @see http://unix.stackexchange.com/a/117623
		// $test->process( sprintf( 'avconv -f x11grab -s 1920x1080 -r 30 -i %s -qscale 0 -vcodec h264 test.mkv', $display ), '/dev/null', 'pids/%s-avconv.pid' );

		// @see https://trac.ffmpeg.org/wiki/Encode/MPEG-4
		// @see http://www.andrewhazelden.com/blog/2014/08/screen-video-recording-on-linux/
	} elseif ( $test->is_executable( 'ffmpeg' ) ) {
		$test->process( sprintf( 'ffmpeg -an -f x11grab -y -r 5 -s 1920x1080 -i %s -vcodec ffvhuff -qmin 31 -b 1024k test.mkv', $display ), '/dev/null', 'pids/%s-ffmpeg.pid' );
	}
}

$prefix = $display ? 'export DISPLAY=localhost:90.0' . ' && ' : '';

$test->process( $prefix . 'java -jar selenium-server-standalone.jar', 'logs/selenium.log', 'pids/%s-selenium.pid' );

$test->passthru( 'wget --retry-connrefused --tries=10 --waitretry=1 http://127.0.0.1:4444/wd/hub/status -O /dev/null' );

// $test->process( 'wp server', 'logs/wp-server.log', 'pids/%s-wp-server.pid' );
// http://php.net/manual/en/features.commandline.webserver.php
$test->process( 'php -S localhost:8080 -c php.ini -t wordpress vendor/wp-cli/wp-cli/php/router.php', 'logs/php-server.log', 'pids/%s-php-server.pid' );

$test->passthru( './vendor/bin/phpunit ./tests/WPTest.php --verbose --debug' );

// Terminate avconv
$test->kill( 'pids/%s-avconv.pid' );
$test->kill( 'pids/%s-ffmpeg.pid' );

// Terminate Selenium
$test->kill( 'pids/%s-selenium.pid' );

// Terminate XVFB
$test->kill( 'pids/%s-xvfb.pid' );

// Terminate WordPress server
$test->kill( 'pids/%s-wp-server.pid' );

$test->passthru( 'killall -r php' );