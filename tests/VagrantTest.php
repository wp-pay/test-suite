<?php

class VagrantTest extends PHPUnit_Extensions_Selenium2TestCase {
	/**
	 * Setup
	 *
	 * @see https://github.com/giorgiosironi/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php
	 */
	protected function setUp() {
		// $this->setBrowser( 'chrome' );
		$this->setBrowser( 'firefox' );
		$this->setBrowserUrl( 'http://pronamic.nl/' );
	}

	public function testGoogle() {
		$this->url( 'weblog/' );

		$this->byName( 's' )->value( 'pronamic' );
		$this->byId( 'searchform' )->submit();
	}
}
