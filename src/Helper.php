<?php

class Pronamic_WP_Pay_TestSuite_Helper {
	public function __construct( $config, $cli ) {
		$this->config = $config;
		$this->cli    = $cli;
	}

	public function install_wp() {
		$dir = $this->config->get_dir();

		if ( is_dir( $dir ) ) {
			$this->cli->passthru( sprintf( 'rm -r %s', $dir ) );
		}

		$this->cli->passthru( sprintf( 'mkdir %s', $dir ) );

		$this->cli->passthru( 'wp core download' );

		$this->cli->passthru( sprintf( 'wp core config --dbpass=%s', $this->config->get_db_pass() ) );

		$this->cli->passthru( 'wp db drop --yes --quiet' );
		$this->cli->passthru( 'wp db create' );

		$this->cli->passthru( 'wp core install --skip-email' );

		$this->cli->passthru( sprintf( 'wp core language install %s', $this->config->get_locale() ) );
		$this->cli->passthru( sprintf( 'wp core language activate %s', $this->config->get_locale() ) );
	}

	public function install_pronamic_ideal( $version ) {
		if ( 'develop' === $version ) {
			$this->cli->passthru( sprintf( 'ln -s ~/Workspace/wp-pronamic-ideal  %s/wp-content/plugins/pronamic-ideal', $this->config->get_dir() ) );
		} else {
			$this->cli->passthru( sprintf( 'wp plugin install pronamic-ideal --version=%s', $version ) );
		}

		$this->cli->passthru( 'wp plugin activate pronamic-ideal' );

		$this->cli->passthru( 'wp option update pronamic_pay_license_status valid' );
		$this->cli->passthru( sprintf( 'wp option update pronamic_pay_version %s', $version ) );

		$this->cli->passthru( 'wp user meta update test pronamic_pay_ignore_tour 1' );

		$this->cli->passthru( 'wp transient delete pronamic_pay_admin_redirect' );

		// Pages
		$parent_id = $this->cli->shell_exec( 'wp post create --post_type=page --post_title="iDEAL" --post_status=publish --porcelain' );

		$pages = array(
			'error'     => 'iDEAL-betalingsfout',
			'cancel'    =>'iDEAL-betaling geannuleerd',
			'unknown'   => 'iDEAL-betaling onbekend',
			'expired'   => 'iDEAL-betaling verlopen',
			'completed' => 'iDEAL-betaling voltooid',
		);

		foreach ( $pages as $key => $title ) {
			$page_id = $this->cli->shell_exec( sprintf( 'wp post create --post_type=page --post_title="%s" --post_status=publish --post_parent=%d --porcelain', $title, $parent_id ) );

			$option = sprintf( 'pronamic_pay_%s_page_id', $key );

			$this->cli->passthru( sprintf( 'wp option update %s %d', $option, $page_id ) );
		}
	}

	public function start_services() {
		$this->start_xvfb();

		$this->start_selenium();

		//$this->start_wp_server();
	}

	public function start_xvfb() {
		$this->display = null;

		if ( $this->cli->is_executable( 'Xvfb' ) ) {
			$this->display = ':90.0';

			$xvfb = new Pronamic_WP_Pay_TestSuite_Service( $this->cli, sprintf( 'Xvfb %s -ac -screen 0 1920x1080x24', $this->display ), 'logs/xvfb.log' );

			if ( $this->cli->is_executable( 'avconv' ) ) {
				$this->avconv = new Pronamic_WP_Pay_TestSuite_Service( $this->cli, sprintf( 'avconv -an -f x11grab -y -r 5 -s 1920x1080 -i %s -vcodec libtheora -qmin 31 -b 1024k test.ogg', $this->display ), '/dev/null' );

				// @see http://unix.stackexchange.com/a/117623
				// $test->process( sprintf( 'avconv -f x11grab -s 1920x1080 -r 30 -i %s -vcodec h264 test.mkv', $display ), '/dev/null', 'pids/%s-avconv.pid' );

				// @see http://stackoverflow.com/questions/10166204/ffmpeg-screencast-recording-which-codecs-to-use
				// @see http://unix.stackexchange.com/a/117623
				// $test->process( sprintf( 'avconv -f x11grab -s 1920x1080 -r 30 -i %s -qscale 0 -vcodec h264 test.mkv', $display ), '/dev/null', 'pids/%s-avconv.pid' );

				// @see https://trac.ffmpeg.org/wiki/Encode/MPEG-4
				// @see http://www.andrewhazelden.com/blog/2014/08/screen-video-recording-on-linux/
			} elseif ( $this->cli->is_executable( 'ffmpeg' ) ) {
				$this->xvfb = new Pronamic_WP_Pay_TestSuite_Service( $this->cli, sprintf( 'ffmpeg -an -f x11grab -y -r 5 -s 1920x1080 -i %s -vcodec ffvhuff -qmin 31 -b 1024k test.mkv', $this->display ), '/dev/null' );
			}
		}
	}

	public function start_selenium() {
		$prefix = $this->display ? 'export DISPLAY=localhost:90.0' . ' && ' : '';

		$this->selenium = new Pronamic_WP_Pay_TestSuite_Service( $this->cli, $prefix . 'java -jar apps/selenium-server-standalone.jar', 'logs/selenium.log' );

		$this->cli->passthru( 'wget --retry-connrefused --tries=10 --waitretry=1 http://127.0.0.1:4444/wd/hub/status -O /dev/null' );
	}

	public function start_wp_server() {
		// $test->process( 'wp server', 'logs/wp-server.log', 'pids/%s-wp-server.pid' );
		// http://php.net/manual/en/features.commandline.webserver.php
		$this->php_server = new Pronamic_WP_Pay_TestSuite_Service( $this->cli, 'wp server', 'logs/wp-server.log' );
	}
}
