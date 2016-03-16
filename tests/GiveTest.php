<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverDimension;

class Pronamic_WP_Pay_TestSuite_GiveTest extends Pronamic_WP_Pay_TestSuite_TestCase {
	public function setUp() {
		parent::setUp();

		global $helper;
		global $cli;
		global $config;

		// Versions
		$this->version_pronamic_ideal = 'develop';
		$this->version_give           = '1.3.6';

		// Pronamic iDEAL
		$helper->install_pronamic_ideal( $this->version_pronamic_ideal );

		// Screenshots
		$this->screenshots_dir = $config->get_screenshots_dir() . sprintf( '/%s/give/%s/buckaroo/', $this->version_pronamic_ideal, $this->version_give );
		$this->screenshots_i   = 1;

		if ( ! is_dir( $this->screenshots_dir ) ) {
			mkdir( $this->screenshots_dir, 0777, true );
		}

		// Give
		// $cli->passthru( sprintf( 'wp plugin install give --activate --version=%s', $this->version_give ) );

		// @see https://github.com/WordImpress/Give/blob/1.3.6/includes/admin/welcome.php#L586-L589
		// $cli->passthru( 'wp transient delete _give_activation_redirect' );

		// WebDriver
		$this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', DesiredCapabilities::firefox(), 50000 );
		$this->webDriver->manage()->window()->setSize( new WebDriverDimension( 1280, 1024 ) );
	}

	public function test_give() {
		$this->wp_login();

		$this->install_plugin( 'give' );

		$this->new_buckaroo_gateway();

		$this->give_settings();

		$this->give_gateway_settings();

		$this->give_new_form();

		$this->give_test_form();

		$this->wait_for_user_input();
	}

	public function give_settings() {
		$this->webDriver->get( 'http://test.dev/wp-admin/edit.php?post_type=give_forms&page=give-settings' );

		$select = new WebDriverSelect( $this->webDriver->findElement( WebDriverBy::id( 'currency' ) ) );
		$select->selectByValue( 'EUR' );

		$this->webDriver->findElement( WebDriverBy::id( 'thousands_separator' ) )->clear()->sendKeys( '.' );
		$this->webDriver->findElement( WebDriverBy::id( 'decimal_separator' ) )->clear()->sendKeys( ',' );

		$this->take_screenshot( 'give-settings' );

		$this->webDriver->findElement( WebDriverBy::cssSelector( '.wrap .button-primary' ) )->click();
	}

	public function give_gateway_settings() {
		$this->webDriver->get( 'http://test.dev/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=gateways' );
		
		$this->webDriver->findElement( WebDriverBy::id( 'test_mode' ) )->click();

		$this->webDriver->findElement( WebDriverBy::id( 'gateways[pronamic_pay_ideal]' ) )->click();

		$select = new WebDriverSelect( $this->webDriver->findElement( WebDriverBy::id( 'give_pronamic_pay_ideal_configuration' ) ) );
		$select->selectByValue( $this->gateway_id );

		$this->take_screenshot( 'give-gateway-settings' );

		$this->webDriver->findElement( WebDriverBy::cssSelector( '.wrap .button-primary' ) )->click();
	}

	public function give_new_form() {
		$this->webDriver->get( 'http://test.dev/wp-admin/post-new.php?post_type=give_forms' );

		// Title
		$title_field = $this->webDriver->findElement( WebDriverBy::id( 'title' ) );
		$title_field->sendKeys( 'Test' );

		// Publish
		$this->wait_for_jquery_ajax();

		$by = WebDriverBy::id( 'publish' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$publish_button = $this->webDriver->findElement( $by );
		$publish_button->click();
	}

	public function give_test_form() {
		$this->webDriver->get( 'http://test.dev/donations/test/' );

		$this->webDriver->findElement( WebDriverBy::cssSelector( 'input[value="pronamic_pay_ideal"]' ) )->click();

		$this->wait_for_jquery_ajax();

		$this->webDriver->findElement( WebDriverBy::id( 'give-first' ) )->sendKeys( 'Test' );
		$this->webDriver->findElement( WebDriverBy::id( 'give-last' ) )->sendKeys( 'Test' );

		$this->take_screenshot( 'give-donation-form' );

		$this->webDriver->findElement( WebDriverBy::id( 'give-purchase-button' ) )->click();

		$this->handle_buckaroo( 'Give donation %d' );

		$this->take_screenshot( 'end' );
	}

	public function wp_login() {
		$this->webDriver->get( 'http://test.dev/wp-admin/' );

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
}
