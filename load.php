<?php

// Functions
class Test {
	public function __construct( $dry_run = false ) {
		$this->dry_run = $dry_run;
	}

	public function shell_exec( $command, $dry_run = null ) {
		$dry_run = is_null( $dry_run ) ? $this->dry_run : $dry_run;

		echo $command, PHP_EOL;

		$result = null;

		if ( ! $dry_run ) {
			$result = shell_exec( $command );

			echo $result;
		}

		echo PHP_EOL;

		return $result;
	}

	public function passthru( $command ) {
		echo $command, PHP_EOL;

		if ( ! $this->dry_run ) {
			passthru( $command );
		}

		echo PHP_EOL;
	}

	/**
	 * Process
	 *
	 * @see http://unix.stackexchange.com/questions/30370/how-to-get-the-pid-of-the-last-executed-command-in-shell-script
	 * @see http://tldp.org/HOWTO/Bash-Prog-Intro-HOWTO-3.html
	 * @see https://trac.ffmpeg.org/wiki/PHP
	 * @see http://tldp.org/LDP/abs/html/io-redirection.html
	 */
	public function process( $command, $stdout_file, $pid_file ) {
		$this->kill( $pid_file );

		$pid_file = sprintf( $pid_file, getenv( 'USER' ) );

		$command = sprintf(
			'%s > %s 2> %s & echo $! > %s',
			$command,
			escapeshellarg( $stdout_file ),
			escapeshellarg( $stdout_file ),
			escapeshellarg( $pid_file )
		);

		$this->passthru( $command );
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

	public function kill( $pid_file ) {
		$pid_file = sprintf( $pid_file, getenv( 'USER' ) );

		if ( is_readable( $pid_file ) ) {
			$pid = file_get_contents( $pid_file );

			$this->passthru( 'kill ' . $pid );

			if ( ! $this->dry_run ) {
				unlink( $pid_file );
			}
		}
	}
}
