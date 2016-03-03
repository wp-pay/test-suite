<?php

class Pronamic_WP_Pay_TestSuite_CLI {
	public function __construct( $dry_run = false ) {
		$this->dry_run = $dry_run;
	}

	/**
	 * Output
	 *
	 * @see https://phpunit.de/manual/current/en/phpunit-book.html
	 * @see http://stackoverflow.com/questions/7493102/phpunit-cli-output-during-test-debugging-possible
	 */
	private function output( $string ) {
		fwrite( STDOUT, $string );
	}

	public function shell_exec( $command, $dry_run = null ) {
		$dry_run = is_null( $dry_run ) ? $this->dry_run : $dry_run;

		$this->output( $command );
		$this->output( PHP_EOL );

		$result = null;

		if ( ! $dry_run ) {
			$result = shell_exec( $command );

			$this->output( $result );
		}

		$this->output( PHP_EOL );

		return $result;
	}

	public function passthru( $command ) {
		$this->output( $command );
		$this->output( PHP_EOL );

		if ( ! $this->dry_run ) {
			// PHPUnit uses an output buffer
			$ob = ob_get_clean();

			passthru( $command );

			ob_start();

			echo $ob;
		}

		$this->output( PHP_EOL );
	}

	/**
	 * Is executable
	 *
	 * @see http://stackoverflow.com/a/12425023
	 */
	public function is_executable( $command ) {
		$path = trim( $this->shell_exec( sprintf( 'which %s', $command ), false ) );

		return is_executable( $path );
	}
}
