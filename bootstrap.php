<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config.php';

$cli = new Pronamic_WP_Pay_TestSuite_CLI( false );

$helper = new Pronamic_WP_Pay_TestSuite_Helper( $config, $cli );
