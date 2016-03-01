<?php

require_once 'load.php';

$test = new Test( false );

// Terminate avconv
$test->kill( 'pids/%s-avconv.pid' );
$test->kill( 'pids/%s-ffmpeg.pid' );

// Terminate Selenium
$test->kill( 'pids/%s-selenium.pid' );

// Terminate XVFB
$test->kill( 'pids/%s-xvfb.pid' );

// Terminate WordPress server
$test->kill( 'pids/%s-wp-server.pid' );

$test->passthru( 'killall -r php' );
