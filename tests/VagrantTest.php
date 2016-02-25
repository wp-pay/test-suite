<?php

class VagrantTest extends PHPUnit_Extensions_Selenium2TestCase {
	/**
	 * Setup
	 *
	 * @see https://github.com/giorgiosironi/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php
	 */
	protected function setUp() {
		$this->setBrowser( 'chrome' );
		$this->setBrowserUrl( 'http://www.google.nl/' );
	}

	public function testGoogle() {
		$this->byName( 'q' )->value( 'pronamic' );
		$this->byName( 'btnG' )->click();	
	}
}
