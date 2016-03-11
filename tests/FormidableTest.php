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

		$helper->install_pronamic_ideal();

		// Version
		$this->version = '2.0.22';

		// Screenshots
		$this->screenshots_dir = $config->get_screenshots_dir() . sprintf( '/formidable/%s/', $this->version );
		$this->screenshots_i   = 1;

		if ( ! is_dir( $this->screenshots_dir ) ) {
			mkdir( $this->screenshots_dir, 0777, true );
		}

		// Gravity Forms
		$cli->passthru( sprintf( 'wp plugin install formidable --activate --version=%s', $this->version ) );

		// User
		// @see https://github.com/wp-premium/formidable/blob/2.0.22/classes/controllers/FrmAppController.php#L210-L217
		$cli->passthru( 'wp user meta update test frm_ignore_tour 1' );

		// WebDriver
		$this->webDriver = RemoteWebDriver::create( 'http://localhost:4444/wd/hub', DesiredCapabilities::firefox() );
	}

	public function test_gravityforms() {
		$this->wp_login();

		$this->new_buckaroo_gateway();

		$this->new_formidable_form();

		$this->new_page();

		$this->wait_for_user_input();
	}

	public function take_screenshot( $name ) {
		$file = $this->screenshots_dir . sprintf( '%1$02d', $this->screenshots_i++ ) . '-' . $name . '.png';

		$this->takeScreenshot( $file );
	}

	public function new_page() {
		$this->webDriver->get( 'http://localhost:8080/wp-admin/post-new.php?post_type=page' );

		$this->take_screenshot( 'new-page' );

		// Title
		$this->webDriver->findElement( WebDriverBy::id( 'title' ) )->sendKeys( 'Test' );

		// Insert Form
		$this->webDriver->findElement( WebDriverBy::cssSelector( 'a.frm_insert_form' ) )->click();

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'frmsc_formidable_id' ) ) );

		$form_field = $this->webDriver->findElement( WebDriverBy::id( 'frmsc_formidable_id' ) );

		$select = new WebDriverSelect( $form_field );
		$select->selectByValue( $this->form_id );

		$this->webDriver->findElement( WebDriverBy::id( 'frm_insert_shortcode' ) )->click();

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::not( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'frm_insert_shortcode' ) ) ) );

		// Publish
		$by = WebDriverBy::id( 'publish' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$this->webDriver->findElement( $by )->click();
	}

	public function new_formidable_form() {
		$this->webDriver->get( 'http://localhost:8080/wp-admin/admin.php?page=formidable&frm_action=new' );

		// ID
		$this->form_id = $this->webDriver->findElement( WebDriverBy::id( 'form_id' ) )->getAttribute( 'value' );

		// Title
		$this->webDriver->findElement( WebDriverBy::id( 'title' ) )->sendKeys( 'Test' );

		// Add Text Field
		$this->webDriver->findElement( WebDriverBy::id( 'text' ) )->click();

		$this->wait_for_jquery_ajax();

		$this->field_id = $this->webDriver->findElement( WebDriverBy::cssSelector( '#new_fields .form-field:last-child' ) )->getAttribute( 'data-fid' );

		// Submit Button
		$this->webDriver->findElement( WebDriverBy::id( 'frm_submit_side_top' ) )->click();

		// Settings
		$this->webDriver->get( 'http://localhost:8080/wp-admin/admin.php?page=formidable&frm_action=settings&id=' . $this->form_id );

		$this->webDriver->findElement( WebDriverBy::cssSelector( 'a[href="#email_settings"]' ) )->click();

		// Add action
		$this->webDriver->findElement( WebDriverBy::cssSelector( 'a.frm_pronamic_pay_action' ) )->click();

		$this->wait_for_jquery_ajax();

		$amount_field = $this->webDriver->findElement( WebDriverBy::cssSelector( '.frm_single_pronamic_pay_settings select[name*="pronamic_pay_amount_field"]' ) );

		$select = new WebDriverSelect( $amount_field );
		$select->selectByValue( $this->field_id );

		// Submit Button
		$this->webDriver->findElement( WebDriverBy::id( 'frm_submit_side_top' ) )->click();
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

		// Publish
		$this->wait_for_jquery_ajax();

		$by = WebDriverBy::id( 'publish' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$publish_button = $this->webDriver->findElement( $by );
		$publish_button->click();
	}
}
