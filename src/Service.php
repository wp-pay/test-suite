<?php

class Pronamic_WP_Pay_TestSuite_Service {
	public function __construct( $cli, $command, $stdout_file ) {
		$this->cli         = $cli;
		$this->command     = $command;
		$this->stdout_file = $stdout_file;
		$this->stdout_file = '/dev/null';

		$this->start();
	}

	public function start() {
		$command = sprintf(
			'%s > %s 2> %s & echo $!',
			$this->command,
			escapeshellarg( $this->stdout_file ),
			escapeshellarg( $this->stdout_file )
		);

		$this->pid = $this->cli->shell_exec( $command );
	}

	public function stop() {
		$this->cli->passthru( 'kill ' . $this->pid );
	}

	public function __destruct() {
		$this->stop();
	}
}
