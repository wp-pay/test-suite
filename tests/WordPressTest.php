<?php

use Facebook\WebDriver\WebDriverBy;

class WPTest extends Pronamic_WP_Pay_TestSuite_TestCase {
	public function test_wp() {
		$this->wp_login();
	}

	public function wp_login() {
		$this->webDriver->get( 'http://localhost:8080/wp-admin/' );

		$user_login = $this->webDriver->findElement( WebDriverBy::id( 'user_login' ) );
		$user_login->click();
		
		$this->webDriver->getKeyboard()->sendKeys( 'test' );

		$user_pass = $this->webDriver->findElement( WebDriverBy::id( 'user_pass' ) );
		$user_pass->click();
		
		$this->webDriver->getKeyboard()->sendKeys( 'test' );

		$this->webDriver->takeScreenshot( __DIR__ . '/../screenshots/screenshot2.png' );

		$submit = $this->webDriver->findElement( WebDriverBy::id( 'wp-submit' ) );
		$submit->click();
	}
}
