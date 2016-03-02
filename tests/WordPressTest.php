<?php

class WPTest extends Pronamic_WP_Pay_TestSuite_Selenium2TestCase {
	public function test_wp() {
		$this->wp_login();
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
}
