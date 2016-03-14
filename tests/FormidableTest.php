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

	public function new_page() {
		$this->webDriver->get( 'http://localhost:8080/wp-admin/post-new.php?post_type=page' );

		// Title
		$this->webDriver->findElement( WebDriverBy::id( 'title' ) )->sendKeys( 'Test' );

		$this->take_screenshot( 'new-page' );

		// Insert Form
		$this->webDriver->findElement( WebDriverBy::cssSelector( 'a.frm_insert_form' ) )->click();

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'frmsc_formidable_id' ) ) );

		$form_field = $this->webDriver->findElement( WebDriverBy::id( 'frmsc_formidable_id' ) );

		$select = new WebDriverSelect( $form_field );
		$select->selectByValue( $this->form_id );

		$this->take_screenshot( 'insert-form' );

		$this->webDriver->findElement( WebDriverBy::id( 'frm_insert_shortcode' ) )->click();

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::not( WebDriverExpectedCondition::visibilityOfElementLocated( WebDriverBy::id( 'frm_insert_shortcode' ) ) ) );

		// Publish Page
		$by = WebDriverBy::id( 'publish' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$this->webDriver->findElement( $by )->click();

		$this->take_screenshot( 'page-published' );

		// View Page
		$by = WebDriverBy::cssSelector( '#message.updated a' );

		$this->webDriver->wait( 5, 200 )->until( WebDriverExpectedCondition::elementToBeClickable( $by ) );

		$this->webDriver->findElement( $by )->click();

		$this->take_screenshot( 'view-page' );

		// Enter amount
		$this->webDriver->findElement( WebDriverBy::cssSelector( sprintf( 'input[name="item_meta[%s]"]', $this->field_id ) ) )->sendKeys( '123' );

		$this->take_screenshot( 'view-form' );

		$this->webDriver->findElement( WebDriverBy::cssSelector( '.frm_submit input' ) )->click();

		// Select iDEAL issuer
		$this->take_screenshot( 'buckaroo-payment' );

		$select = new WebDriverSelect( $this->webDriver->findElement( WebDriverBy::id( 'brq_SERVICE_ideal_issuer' ) ) );
		$select->selectByValue( 'RABONL2U' );

		$this->webDriver->findElement( WebDriverBy::id( 'button_continue' ) )->click();

		// Payment Status
		$this->take_screenshot( 'buckaroo-payment-status' );

		$this->webDriver->findElement( WebDriverBy::cssSelector( 'input[type="submit"]' ) )->click();

		// Alert Accept
		// @see https://github.com/facebook/php-webdriver/wiki/Alert,-Window-Tab,-frame-iframe-and-active-element
		$this->webDriver->switchTo()->alert()->accept();;

		$this->take_screenshot( 'end' );
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

		// $this->wait_for_user_input();

		// Adjust field title
		// $element = $this->webDriver->findElement( WebDriverBy::id( sprintf( 'field_label_%s', $this->field_id ) ) );

		// Element is made editable on mouseenter
		// @see https://github.com/wp-premium/formidable/blob/2.0.22/js/formidable_admin.js#L842-L848
		// $this->webDriver->getMouse()->mouseMove( $element->getCoordinates() );

		// $element->clear()->sendKeys( 'Bedrag' );

		// $this->wait_for_user_input();

		$this->take_screenshot( 'new-form' );

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

		$this->take_screenshot( 'new-form-action' );

		// Submit Button
		$this->webDriver->findElement( WebDriverBy::id( 'frm_submit_side_top' ) )->click();
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
