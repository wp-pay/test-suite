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

	public function test_wc() {
		$this->wp_login();
		$this->wc_setup();
		//$this->new_gateway();
		$this->new_mollie_gateway();
		$this->wc_settings();
		$this->wc_shop();
	}

	public function wc_setup() {
		$this->url( 'wp-admin/admin.php?page=wc-setup' );

		// Welcome
		$this->screenshot( 'wc-setup-welcome' );

		$this->byCssSelector( '.wc-setup-actions .button-primary' )->click();

		$this->wait_for_ajax();

		// Pages
		$this->screenshot( 'wc-setup-pages' );

		$this->byCssSelector( '.wc-setup-actions .button-primary' )->click();

		$this->wait_for_ajax();

		// Locale
		$this->screenshot( 'wc-setup-locale' );

		$this->byCssSelector( '.wc-setup-actions .button-primary' )->click();

		$this->wait_for_ajax();

		// Shipping and Taxes
		$this->screenshot( 'wc-setup-shipping-taxes' );

		$this->byCssSelector( '.wc-setup-actions .button-primary' )->click();

		$this->wait_for_ajax();

		// Payments
		$this->byId( 'woocommerce_enable_cheque' )->click();
		$this->byId( 'woocommerce_enable_cod' )->click();
		$this->byId( 'woocommerce_enable_bacs' )->click();

		$this->screenshot( 'wc-setup-shipping-payments' );

		$this->byCssSelector( '.wc-setup-actions .button-primary' )->click();

		$this->wait_for_ajax();

		// Return 
		$this->byCssSelector( '.wc-return-to-dashboard' )->click();	
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

	public function new_gateway() {
		// New gateway
		$this->url( 'wp-admin/post-new.php?post_type=pronamic_gateway' );

		$this->config_id = $this->byId( 'post_ID' )->value();
		
		$post_form = $this->byId( 'post' );

		$this->byId( 'title' )->value( 'iDEAL Simulator - iDEAL Lite / Basic' );

		$select = $this->select( $this->byId( 'pronamic_gateway_id' ) );
		$select->selectOptionByValue( 'ideal-simulator-ideal-basic' );

		$this->byId( '_pronamic_gateway_ideal_merchant_id' )->value( '123456789' );

		$this->byId( 'pronamic_ideal_sub_id' )->value( '0' );

		$this->byId( '_pronamic_gateway_ideal_hash_key' )->value( 'Password' );

		$this->wait_for_autosave();

		$this->screenshot( 'gateway' );

		$this->byId( 'publish' )->click();

		$this->screenshot( 'gateway-saved' );
	}

	public function new_mollie_gateway() {
		// New gateway
		$this->url( 'wp-admin/post-new.php?post_type=pronamic_gateway' );

		$this->config_id = $this->byId( 'post_ID' )->value();
		
		$post_form = $this->byId( 'post' );

		$this->byId( 'title' )->value( 'Mollie' );

		$select = $this->select( $this->byId( 'pronamic_gateway_id' ) );
		$select->selectOptionByValue( 'mollie' );

		$this->byId( '_pronamic_gateway_mollie_api_key' )->value( getenv( 'MOLLIE_API_KEY') );

		$this->wait_for_autosave();

		$this->screenshot( 'gateway' );

		$this->byId( 'publish' )->click();

		$this->screenshot( 'gateway-saved' );
	}

	public function wc_settings() {
		// Check
//		$message = $this->byCssSelector( '.updated.notice-success' );

//		$this->assertContains( 'updated notice notice-success is-dismissible', $message->attribute( 'class' ) );

		// WooCommerce Settings
		$this->url( 'wp-admin/admin.php?page=wc-settings&tab=checkout&section=pronamic_wp_pay_extensions_woocommerce_idealgateway' );

		$chekbox = $this->byId( 'woocommerce_pronamic_pay_ideal_enabled' );

		if ( ! $chekbox->selected() ) {
			$chekbox->click();
		}

		$select = $this->select( $this->byId( 'woocommerce_pronamic_pay_ideal_config_id' ) );
		$select->selectOptionByValue( $this->config_id );

		$this->screenshot( 'settings' );

		$this->byName( 'save' )->click();

		$this->screenshot( 'settings-saved' );
	}

	public function wc_shop() {
		$this->url( 'wp-admin/edit.php?post_type=product' );

		$this->screenshot( 'products' );

		$this->moveto( $this->byCssSelector( '#the-list tr:first-child .row-title') );

		$this->byCssSelector( '#the-list tr:first-child .row-actions .view a' )->click();

		$this->screenshot( 'product' );

		$this->byCssSelector( '.cart button' )->click();

		$this->screenshot( 'product-in-cart' );

		$this->byCssSelector( '.woocommerce-message a.button.wc-forward' )->click();
		
		$this->screenshot( 'cart' );

		$this->byCssSelector( '.checkout-button' )->click();

		$this->wait_for_ajax();

		$this->screenshot( 'checkout' );

		$billing_first_name = $this->byId( 'billing_first_name' );
		$billing_first_name->clear();
		$billing_first_name->value( 'Test' );

		$billing_last_name = $this->byId( 'billing_last_name' );
		$billing_last_name->clear();
		$billing_last_name->value( 'Test' );

		$billing_phone = $this->byId( 'billing_phone' );
		$billing_phone->clear();
		$billing_phone->value( '1234567890' );

		$billing_address_1 = $this->byId( 'billing_address_1' );
		$billing_address_1->clear();
		$billing_address_1->value( 'Test 1' );
		
		$billing_postcode = $this->byId( 'billing_postcode' );
		$billing_postcode->clear();
		$billing_postcode->value( '1234 TE' );

		$billing_city = $this->byId( 'billing_city' );
		$billing_city->clear();
		$billing_city->value( 'Test' );

		$this->wait_for_ajax();
		$this->wait_for_user_input();

		$this->byId( 'payment_method_pronamic_pay_ideal' )->click();

		$this->wait_for_element_animation( '.payment_method_pronamic_pay_ideal', 5000 );

		$this->screenshot( 'checkout-ideal' );

		$this->byId( 'place_order' )->click();

		$this->wait_for_ajax();

		$this->screenshot( 'mollie-test' );

		$this->byCssSelector( 'form button' )->click();

		$this->screenshot( 'order-received' );
	}

    public function screenshot($name) 
    {
    	$file = __DIR__ . '/../screenshots/woocommerce-' . sprintf( '%1$02d', $this->step++ ) . '-' . $name . '.png';
        $filedata = $this->currentScreenshot();
        file_put_contents($file, $filedata);
    }
}
