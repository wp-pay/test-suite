<?php

class WPTest extends PHPUnit_Extensions_Selenium2TestCase {
	/**
	 * Setup
	 *
	 * @see https://github.com/giorgiosironi/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php
	 */
	protected function setUp() {
		$this->setBrowser( 'firefox' );
		$this->setBrowserUrl( 'http://localhost:8080/' );

		$this->step = 1;
	}

	public function test_wp() {
		$this->wp_login();
	}

	private function wait_for_autosave() {
		$this->waitUntil( function( $testCase ) {
			try {
				$testCase->byCssSelector( '#publish.disabled' );
			} catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
				return true;
			}
		}, 2000 );
	}

	private function wait_for_ajax() {
		$this->waitUntil( function( $testCase ) {
			$result = $this->execute( array(
				'script' => 'return jQuery.active == 0;',
				'args'   => array(),
			) );

			return $result ? $result : null;
		}, 2000 );
	}

	private function wait_for_jquery_animation() {
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
	private function wait_for_element_animation( $selector, $timeout = 2000 ) {
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
	private function wait_for_user_input() {
		if ( trim( fgets( fopen( 'php://stdin', 'r' ) ) ) !== chr( 13 ) ) {
			return;
		}
    }

	public function wp_login() {
		$this->url( 'wp-admin' );

		// Login
		$login_form = $this->byId( 'loginform' );

		$this->byId( 'user_pass' )->value( 'test' );

		$this->byId( 'user_login' )->value( 'test' );

		$this->screenshot( 'login' );

 		$login_form->submit();
	}

    public function screenshot($name) 
    {
    	$file = __DIR__ . '/../screenshots/woocommerce-' . sprintf( '%1$02d', $this->step++ ) . '-' . $name . '.png';
        $filedata = $this->currentScreenshot();
        file_put_contents($file, $filedata);
    }
}