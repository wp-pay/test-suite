<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;

class Pronamic_WP_Pay_TestSuite_GravityFormsTest extends Pronamic_WP_Pay_TestSuite_TestCase {
	public function setUp() {
		/*
		parent::setUp();

		global $helper;
		global $cli;

		$helper->install_pronamic_ideal();

		// Gravity Forms
		$cli->passthru( 'wp plugin install gravityforms-nl --activate' );
		$cli->passthru( 'wp plugin install https://github.com/wp-premium/gravityforms/archive/master.zip --activate' );

		$cli->passthru( 'wp option delete gform_pending_installation' );

		$cli->passthru( sprintf( 'wp option update rg_gforms_key %s', md5( getenv( 'GRAVITY_FORMS_KEY' ) ) ) );
		$cli->passthru( sprintf( 'wp option update gform_enable_noconflict %s', 0 ) );
		$cli->passthru( sprintf( 'wp option update rg_gforms_currency %s', 'EUR' ) );

		// User
		$cli->passthru( 'wp user meta update test show_admin_bar_front 0' );
		*/

		// WebDriver
		$this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', DesiredCapabilities::firefox() );

		// @see https://github.com/facebook/php-webdriver/wiki/Example-command-reference
		// $this->webDriver->manage()->window()->maximize();
	}

	public function test_gravityforms() {
		$this->wp_login();
		//$this->new_gateway();
		$this->new_buckaroo_gateway();

		$this->wait_for_user_input();
	}

	public function wp_login() {
		$this->webDriver->get( 'http://localhost:8080/wp-admin/' );

		$user_login = $this->webDriver->findElement( WebDriverBy::id( 'user_login' ) );
		$user_login->sendKeys( 'test' );

		$user_pass = $this->webDriver->findElement( WebDriverBy::id( 'user_pass' ) );
		$user_pass->sendKeys( 'test' );

		$this->webDriver->takeScreenshot( __DIR__ . '/../screenshots/screenshot2.png' );

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
    public function waitForAjax( $timeout = 5, $interval = 200 ) {
        $this->webDriver->wait( $timeout, $interval )->until( function() {
            $condition = 'return jQuery.active == 0;';

            return $this->webDriver->executeScript( $condition );
        } );
    }

	public function new_buckaroo_gateway() {
		// New gateway
		$this->webDriver->get( 'http://localhost:8080/wp-admin/post-new.php?post_type=pronamic_gateway' );

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

		// Wait for autosave
		$this->waitForAjax();

		// Publish
		$publish_button = $this->webDriver->findElement( WebDriverBy::id( 'publish' ) );
		$publish_button->click();
	}
}
