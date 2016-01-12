<?php

class WebTest extends PHPUnit_Extensions_Selenium2TestCase {
	/**
	 * Setup
	 *
	 * @see https://github.com/giorgiosironi/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php
	 */
	protected function setUp() {
		$this->setBrowser( 'firefox' );
		$this->setBrowserUrl( 'http://wp-pay-test.dev/' );

		$this->step = 1;
	}

	public function test_wc() {
		$this->wp_login();
		$this->new_gateway();
		//$this->new_mollie_gateway();
		$this->wc_settings();
		$this->wc_shop();
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
				'args'   => array()
			) );

			return $result ? $result : null;
		}, 2000 );
	}

	public function wp_login() {
		$this->url( 'wp-admin' );

		// Login
		$login_form = $this->byId( 'loginform' );

		$this->byId( 'user_pass' )->value( 'remcotolsma' );

		$this->byId( 'user_login' )->value( 'remcotolsma' );

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
	}

	public function wc_settings() {
		// Check
//		$message = $this->byCssSelector( '.updated.notice-success' );

//		$this->assertContains( 'updated notice notice-success is-dismissible', $message->attribute( 'class' ) );

		$this->screenshot( 'gateway-updated' );

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

		$this->byId( 'payment_method_pronamic_pay_ideal' )->click();

		$this->screenshot( 'checkout-ideal' );
	}

    public function screenshot($name) 
    {
    	$file = __DIR__ . '/../screenshots/woocommerce-' . sprintf( '%1$02d', $this->step++ ) . '-' . $name . '.png';
        $filedata = $this->currentScreenshot();
        file_put_contents($file, $filedata);
    }
}
