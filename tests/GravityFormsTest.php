<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Pronamic_WP_Pay_TestSuite_GravityFormsTest extends Pronamic_WP_Pay_TestSuite_TestCase {
	public function setUp() {
		parent::setUp();

		global $helper;
		global $cli;

		$helper->install_pronamic_ideal();

		// Gravity Forms
		$cli->passthru( 'wp plugin install gravityforms-nl --activate' );
		$cli->passthru( 'wp plugin install https://github.com/wp-premium/gravityforms/archive/master.zip --activate' );
		$cli->passthru( 'wp plugin install https://github.com/pronamic/wp-pronamic-donations/archive/master.zip --activate' );

		// @see https://github.com/wp-premium/gravityforms/blob/1.9.17/gravityforms.php#L380
		$cli->passthru( sprintf( 'wp option update rg_form_version %s', '1.0.0' ) );
		$cli->passthru( sprintf( 'wp option update rg_gforms_key %s', md5( getenv( 'GRAVITY_FORMS_KEY' ) ) ) );
		$cli->passthru( sprintf( 'wp option update gform_enable_noconflict %s', 0 ) );
		$cli->passthru( sprintf( 'wp option update rg_gforms_currency %s', 'EUR' ) );

		// WebDriver
		$this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', DesiredCapabilities::firefox() );
	}

	public function test_gravityforms() {
		$this->wp_login();
		//$this->new_gateway();
		$this->new_buckaroo_gateway();

		$this->new_gf_form();

		$this->wait_for_user_input();
	}

	public function new_gf_form() {
		$this->webDriver->get( 'http://localhost:8080/wp-admin/admin.php?page=gf_new_form' );

		// Title has to be unique.
		$title_field = $this->webDriver->findElement( WebDriverBy::id( 'new_form_title' ) );
		$title_field->sendKeys( 'Test ' . time() );

		$submit_button = $this->webDriver->findElement( WebDriverBy::id( 'save_new_form' ) );
		$submit_button->click();

		// Advanced Fields
		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'add_advanced_fields' ) ) );

		$add_advanced_fields = $this->webDriver->findElement( WebDriverBy::id( 'add_advanced_fields' ) );
		$add_advanced_fields->click();

		// Name
		// @see https://github.com/facebook/php-webdriver/blob/1.1.1/lib/WebDriverExpectedCondition.php
		$by = WebDriverBy::cssSelector( 'input[data-type="name"]' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$email_field_button = $add_advanced_fields->findElement( $by )->click();

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'field_1' ) ) );

		// Email
		$by = WebDriverBy::cssSelector( 'input[data-type="email"]' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$email_field_button = $add_advanced_fields->findElement( $by )->click();

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'field_2' ) ) );

		// Pricing Fields
		$add_pricing_fields = $this->webDriver->findElement( WebDriverBy::id( 'add_pricing_fields' ) );
		$add_pricing_fields->click();

		// Product
		$by = WebDriverBy::cssSelector( 'input[data-type="product"]' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$product_field_button = $add_pricing_fields->findElement( $by )->click();

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'field_3' ) ) );

		// Edit Product Fields
		$product_field = $this->webDriver->findElement( WebDriverBy::id( 'field_3' ) );
		$product_field->click();

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'product_field_type' ) ) );

		$select = new WebDriverSelect( $this->webDriver->findElement( WebDriverBy::id( 'product_field_type' ) ) );
		$select->selectByValue( 'price' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::cssSelector( '#field_3 .ginput_amount' ) ) );

		// Update Form
		$update_button = $this->webDriver->findElement( WebDriverBy::cssSelector( 'input.update-form' ) );
		$update_button->click();
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
    public function wait_for_jquery_ajax( $timeout = 5, $interval = 200 ) {
        $this->webDriver->wait( $timeout, $interval )->until( function() {
            $condition = 'return jQuery.active;';

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

		// Publish
		$by = WebDriverBy::id( 'publish' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$publish_button = $this->webDriver->findElement( $by );
		$publish_button->click();
	}
}
