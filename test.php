<?php

// Functions
class Test {
	public function __construct( $dry_run = false ) {
		$this->dry_run = $dry_run;
	}

	public function shell_exec( $command ) {
		echo $command, PHP_EOL;

		$result = null;

		if ( ! $this->dry_run ) {
			$result = shell_exec( $command );

			echo $result;
		}

		echo PHP_EOL;

		return $result;
	}

	public function passthru( $command ) {
		echo $command, PHP_EOL;

		if ( ! $this->dry_run ) {
			passthru( $command );
		}

		echo PHP_EOL;
	}

	/**
	 * Process
	 *
	 * @see http://unix.stackexchange.com/questions/30370/how-to-get-the-pid-of-the-last-executed-command-in-shell-script
	 * @see http://tldp.org/HOWTO/Bash-Prog-Intro-HOWTO-3.html
	 * @see https://trac.ffmpeg.org/wiki/PHP
	 * @see http://tldp.org/LDP/abs/html/io-redirection.html
	 */
	public function process( $command, $stdout_file, $pid_file ) {
		$this->kill( $pid_file );

		$pid_file = sprintf( $pid_file, getenv( 'USER' ) );

		$command = sprintf(
			'%s > %s & echo $! > %s',
			$command,
			escapeshellarg( $stdout_file ),
			escapeshellarg( $pid_file )
		);

		$this->passthru( $command );
	}

	public function kill( $pid_file ) {
		$pid_file = sprintf( $pid_file, getenv( 'USER' ) );

		if ( is_readable( $pid_file ) ) {
			$pid = file_get_contents( $pid_file );

			$this->passthru( 'kill ' . $pid );

			if ( ! $this->dry_run ) {
				unlink( $pid_file );
			}
		}
	}
}

$test = new Test();

// Run
if ( false ) {
	$test->passthru( 'rm -r ./wordpress' );
	$test->passthru( 'mkdir ./wordpress' );
	$test->passthru( 'wp core download' );
	$test->passthru( 'wp core config' );

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

if ( 'vagrant' === getenv( 'USER' ) ) {
	$display = ':90.0';

	$test->process( sprintf( 'Xvfb %s -ac -screen 0 1920x1080x24', $display ), 'logs/xvfb.log', 'pids/%s-xvfb.pid' );

	$test->process( sprintf( 'avconv -an -f x11grab -y -r 5 -s 1920x1080 -i %s -vcodec libtheora -qmin 31 -b 1024k test.ogg', $display ), '/dev/null', 'pids/%s-avconv.pid' );
}

$prefix = $display ? 'export DISPLAY=localhost:90.0' . ' && ' : '';

$test->process( $prefix . 'java -jar selenium-server-standalone.jar', 'logs/selenium.log', 'pids/%s-selenium.pid' );

$test->passthru( 'wget --retry-connrefused --tries=10 --waitretry=1 http://127.0.0.1:4444/wd/hub/status -O /dev/null' );

$test->process( 'wp server', 'logs/wp-server.log', 'pids/%s-wp-server.pid' );

$test->passthru( './vendor/bin/phpunit ./tests/WPTest.php --verbose --debug' );

// Terminate avconv
$test->kill( 'pids/%s-avconv.pid' );

// Terminate Selenium
$test->kill( 'pids/%s-selenium.pid' );

// Terminate XVFB
$test->kill( 'pids/%s-xvfb.pid' );

// Terminate WordPress server
$test->kill( 'pids/%s-wp-server.pid' );
