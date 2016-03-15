<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Pronamic_WP_Pay_TestSuite_FormidableTest extends Pronamic_WP_Pay_TestSuite_TestCase {
	public function setUp() {
		parent::setUp();

		global $helper;
		global $cli;
		global $config;

		// Versions
		$this->version_pronamic_ideal = 'develop';
		$this->version_wp_e_commerce  = '3.11.2';

		// Pronamic iDEAL
		$helper->install_pronamic_ideal( $this->version_pronamic_ideal );

		// Screenshots
		$this->screenshots_dir = $config->get_screenshots_dir() . sprintf( '/%s/wp-e-commerce/%s/buckaroo/', $this->version_pronamic_ideal, $this->version_wp_e_commerce );
		$this->screenshots_i   = 1;

		if ( ! is_dir( $this->screenshots_dir ) ) {
			mkdir( $this->screenshots_dir, 0777, true );
		}

		// WP e-Commerce
		$cli->passthru( sprintf( 'wp plugin install wp-e-commerce --activate --version=%s', $this->version_wp_e_commerce ) );

		// User
		// @see https://github.com/wp-e-commerce/WP-e-Commerce/blob/3.11.2/wpsc-components/marketplace-core-v1/library/Sputnik/Pointers.php#L10-L25
		$cli->passthru( 'wp user meta update test dismissed_wp_pointers wpsc_marketplace_pointer' );

		// WebDriver
		$this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', DesiredCapabilities::firefox() );
	}

	public function test_wp_e_commerce() {
		$this->wp_login();

		$this->new_buckaroo_gateway();

		$this->configure_wp_e_commerce();

		$this->wait_for_user_input();
	}

	public function configure_wp_e_commerce() {
		$this->webDriver->get( 'http://localhost:8080/wp-admin/options-general.php?page=wpsc-settings&tab=gateway&payment_gateway_id=wpsc_merchant_pronamic_ideal' );

		$this->webDriver->findElement( WebDriverBy::id( 'wpsc_merchant_pronamic_ideal_id' ) )->click();

		$select = new WebDriverSelect( $this->webDriver->findElement( WebDriverBy::id( 'pronamic_pay_ideal_wpsc_config_id' ) ) );
		$select->selectByValue( $this->gateway_id );

		$this->take_screenshot( 'wpsc-settings' );

		$this->webDriver->findElement( WebDriverBy::cssSelector( '#gateway_settings_wpsc_merchant_pronamic_ideal_form input[type="submit"]' ) )->click();
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

	public function wp_login() {
		$this->webDriver->get( 'http://localhost:8080/wp-admin/' );

		$user_login = $this->webDriver->findElement( WebDriverBy::id( 'user_login' ) );
		$user_login->sendKeys( 'test' );

		$user_pass = $this->webDriver->findElement( WebDriverBy::id( 'user_pass' ) );
		$user_pass->sendKeys( 'test' );

		$this->take_screenshot( 'login' );

		$submit = $this->webDriver->findElement( WebDriverBy::id( 'wp-submit' ) );
		$submit->click();
	}

    /**
    * waitForAjax : wait for all ajax request to close
    * @see https://gist.github.com/luxcem/8240758
    * @param  integer $timeout  timeout in seconds
    * @param  integer $interval interval in miliseconds
    * @return void            
    */
    public function wait_for_jquery_ajax( $timeout = 5, $interval = 200 ) {
        $this->webDriver->wait( $timeout, $interval )->until( function() {
            $condition = 'return jQuery.active == 0;';

            return $this->webDriver->executeScript( $condition );
        } );
    }

    /**
    * waitForAjax : wait for all ajax request to close
    * @see https://gist.github.com/luxcem/8240758
    * @param  integer $timeout  timeout in seconds
    * @param  integer $interval interval in miliseconds
    * @return void            
    */
    public function wait_for_element( $timeout = 5, $interval = 200 ) {
        $this->webDriver->wait( $timeout, $interval )->until( function() {
            $condition = 'return jQuery.animation.queue == 0;';

            return $this->webDriver->executeScript( $condition );
        } );
    }

	public function new_buckaroo_gateway() {
		// New gateway
		$this->webDriver->get( 'http://localhost:8080/wp-admin/post-new.php?post_type=pronamic_gateway' );

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
}
