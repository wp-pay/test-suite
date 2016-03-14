<?php

class Pronamic_WP_Pay_TestSuite_Config {
	public $path;

	public $db_pass;

	public $locale;

	public function get_dir() {
		return realpath( __DIR__ . '/../' . $this->path );
	}

	public function get_screenshots_dir() {
		return realpath( __DIR__ . '/../manuals/' );
	}

	public function get_db_pass() {
		return $this->db_pass;		
	}

	public function get_locale() {
		return $this->locale;
	}
}
