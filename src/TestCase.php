<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Pronamic_WP_Pay_TestSuite_TestCase extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		global $helper;

		$helper->start_services();
	}

	public function wait_for_visibility_element( $by ) {
		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( $by ) );

		return $this->webDriver->findElement( $by );
	}

	public function install_plugin( $slug ) {
		$this->webDriver->get( 'http://test.dev/wp-admin/plugin-install.php' );

		$search_field = $this->wait_for_visibility_element( WebDriverBy::cssSelector( 'input[name="s"]' ) );
		$search_field->sendKeys( $slug )->submit();

		$install_link = $this->wait_for_visibility_element( WebDriverBy::cssSelector( sprintf( 'a.install-now[data-slug="%s"]', $slug ) ) );
		$this->take_screenshot( 'plugin-install' );
		$install_link->click();

		$activate_link = $this->wait_for_visibility_element( WebDriverBy::cssSelector( '.wrap a[href*="action=activate"]' ) );
		$this->take_screenshot( 'plugin-activate' );
		$activate_link->click();
	}

	public function take_screenshot( $name, $by = null ) {
		$file = $this->screenshots_dir . sprintf( '%1$02d', $this->screenshots_i++ ) . '-' . $name . '.png';

		$this->webDriver->takeScreenshot( $file );

		if ( isset( $by ) ) {
			$elements = $this->webDriver->findElements( $by );

			$img1 = imagecreatefrompng( $file );

			foreach ( $elements as $element ) {
				$width  = $element->getSize()->getWidth();
				$height = $element->getSize()->getHeight();

				$x = $element->getLocation()->getX();
				$y = $element->getLocation()->getY();

				// @see http://stackoverflow.com/questions/20114956/php-gd-blur-part-of-an-image
				// @see https://github.com/facebook/php-webdriver/wiki/Taking-Full-Screenshot-and-of-an-Element
				// @see http://image.intervention.io/api/blur

				$img2 = imagecreatetruecolor( $width, $height );
				
				imagecopy( $img2, $img1, 0, 0, $x, $y, $width, $height ); 

				foreach ( range( 1, 16 ) as $i ) {
					imagefilter( $img2, IMG_FILTER_GAUSSIAN_BLUR );
				}

				imagecopymerge( $img1, $img2, $x, $y, 0, 0, $width, $height, 100 ); // merge img2 in img1

				imagedestroy( $img2 );
			}

			imagepng( $img1, $file );

			imagedestroy( $img1 );
		}
	}

	/**
	 * Setup
	 *
	 * @see https://github.com/giorgiosironi/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php
	 */
	protected function setUp() {
		global $helper;

		$helper->install_wp();

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

	public function new_buckaroo_gateway() {
		// New gateway
		$this->webDriver->get( 'http://test.dev/wp-admin/post-new.php?post_type=pronamic_gateway' );

		// Post ID
		$this->gateway_id = $this->webDriver->findElement( WebDriverBy::id( 'post_ID' ) )->getAttribute( 'value' );

		// Title
		$title_field = $this->webDriver->findElement( WebDriverBy::id( 'title' ) );
		$title_field->sendKeys( 'Buckaroo' );

		// Gateway
		$gateway_field = $this->webDriver->findElement( WebDriverBy::id( 'pronamic_gateway_id' ) );

		$select = new WebDriverSelect( $gateway_field );
		$select->selectByValue( 'buckaroo' );

		// Buckaroo
		$key_field = $this->webDriver->findElement( WebDriverBy::id( '_pronamic_gateway_buckaroo_website_key' ) );
		$key_field->sendKeys( getenv( 'BUCKAROO_WEBSITE_KEY' ) );

		$key_field = $this->webDriver->findElement( WebDriverBy::id( '_pronamic_gateway_buckaroo_secret_key' ) );
		$key_field->sendKeys( getenv( 'BUCKAROO_SECRET_KEY' ) );

		$this->take_screenshot( 'new-gateway', WebDriverBy::cssSelector( '#_pronamic_gateway_buckaroo_website_key, #_pronamic_gateway_buckaroo_secret_key' ) );

		// Publish
		$this->wait_for_jquery_ajax();

		$by = WebDriverBy::id( 'publish' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$publish_button = $this->webDriver->findElement( $by );
		$publish_button->click();
	}

	public function handle_buckaroo( $expected_description = 'Test %d' ) {
		// Select iDEAL issuer
		$this->take_screenshot( 'buckaroo-payment' );

		$ideal_issuer = $this->wait_for_visibility_element( WebDriverBy::id( 'brq_SERVICE_ideal_issuer' ) );

		$select = new WebDriverSelect( $ideal_issuer );
		$select->selectByValue( 'RABONL2U' );

		// Check description
		$description = $this->webDriver->findElement( WebDriverBy::cssSelector( 'tr.bpe_payment_description td' ) )->getText();
		
		$this->assertStringMatchesFormat( $expected_description, $description );

		// Continue
		$this->webDriver->findElement( WebDriverBy::id( 'button_continue' ) )->click();

		// Payment Status
		$this->take_screenshot( 'buckaroo-payment-status' );

		$this->webDriver->findElement( WebDriverBy::cssSelector( 'input[type="submit"]' ) )->click();

		// Alert Accept
		// @see https://github.com/facebook/php-webdriver/wiki/Alert,-Window-Tab,-frame-iframe-and-active-element
		$this->webDriver->switchTo()->alert()->accept();
	}
}
