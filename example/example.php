<?php
require dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use kaleidpixel\URLStatusChecker;

try {
	$url = [
		'https://www.tiktok.com/en',
		'https://twitter.com',
		'https://www.facebook.com',
		'https://www.google.com',
		'https://www.yahoo.com',
	];
	$checker = new URLStatusChecker( $url );

	var_dump($checker->getStatusCode());
	var_dump($checker->getResponseTime());
	var_dump($checker->getBenchmarkTime());
} catch ( Exception $e ) {
	echo $e->getMessage() . PHP_EOL;
}
