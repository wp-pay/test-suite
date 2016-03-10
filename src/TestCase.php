<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class Pronamic_WP_Pay_TestSuite_TestCase extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		global $helper;

		$helper->start_services();
	}

	/**
	 * Setup
	 *
	 * @see https://github.com/giorgiosironi/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php
	 */
	protected function setUp() {
		global $helper;

		$helper->install_wp();

		// WebDriver
		$this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', DesiredCapabilities::firefox() );

		// @see https://github.com/facebook/php-webdriver/wiki/Example-command-reference
		// $this->webDriver->manage()->window()->maximize();

		// Steps
		$this->step = 1;
	}

	public function tearDown() {
    	$this->webDriver->quit();
	}

	protected function wait_for_autosave() {
		$this->waitUntil( function( $testCase ) {
			try {
				$testCase->byCssSelector( '#publish.disabled' );
			} catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
				return true;
			}
		}, 2000 );
	}

	protected function wait_for_ajax() {
		$this->waitUntil( function( $testCase ) {
			$result = $this->execute( array(
				'script' => 'return jQuery.active == 0;',
				'args'   => array(),
			) );

			return $result ? $result : null;
		}, 2000 );
	}

	protected function wait_for_jquery_animation() {
		$this->waitUntil( function( $testCase ) {
			$result = $this->execute( array(
				'script' => 'return jQuery.animation.queue == 0;',
				'args'   => array(),
			) );

			return $result ? $result : null;
		}, 2000 );
	}

	/**
	 * @see https://github.com/woothemes/woocommerce/blob/2.4.13/assets/js/frontend/checkout.js#L111-L131
	 * @see http://blog.wedoqa.com/2015/10/wedbriver-wait-for-ajax-to-finish-and-jquery-animation/
	 */
	protected function wait_for_element_animation( $selector, $timeout = 2000 ) {
		$this->waitUntil( function( $testCase ) use ( $selector ) {
			$result = $this->execute( array(
				'script' => sprintf( "return jQuery( '%s' ).is( ':animated' );", $selector ),
				'args'   => array()
			) );

			return $result ? null : true;
		}, $timeout );
	}

	/**
	 * Wait for user input.
	 *
	 * @see http://codeception.com/11-12-2013/working-with-phpunit-and-selenium-webdriver.html
	 */
	protected function wait_for_user_input() {
		if ( trim( fgets( fopen( 'php://stdin', 'r' ) ) ) !== chr( 13 ) ) {
			return;
		}
	}

    public function screenshot( $name ) {
    	$file = __DIR__ . '/../screenshots/woocommerce-' . sprintf( '%1$02d', $this->step++ ) . '-' . $name . '.png';
        $filedata = $this->currentScreenshot();
        file_put_contents($file, $filedata);
    }
}
