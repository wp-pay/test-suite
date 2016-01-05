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
	}

	public function testAddGateway() {
		$this->url( 'wp-admin' );

		// Login
		$login_form = $this->byId( 'loginform' );

		$this->byId( 'user_pass' )->value( 'remcotolsma' );

		$this->byId( 'user_login' )->value( 'remcotolsma' );

 		$login_form->submit();

		// New gateway
		$this->url( 'wp-admin/post-new.php?post_type=pronamic_gateway' );
		
		$post_form = $this->byId( 'post' );

		$this->byId( 'title' )->value( 'iDEAL Simulator - iDEAL Lite / Basic' );

		$select = $this->select( $this->byId( 'pronamic_gateway_id' ) );
		$select->selectOptionByValue( 'ideal-simulator-ideal-basic' );

		$this->byId( '_pronamic_gateway_ideal_merchant_id' )->value( '123456789' );

		$this->byId( 'pronamic_ideal_sub_id' )->value( '0' );

		$this->byId( '_pronamic_gateway_ideal_hash_key' )->value( 'Password' );

		$post_form->submit();

		$this->acceptAlert();

		// Check
		$message = $this->byCssSelector( '.updated.notice-success' );

		$this->assertContains( 'updated notice notice-success is-dismissible', $message->attribute( 'class' ) );
	}
}
