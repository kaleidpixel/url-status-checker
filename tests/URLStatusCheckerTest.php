<?php

namespace kaleidpixel\Tests;

use PHPUnit\Framework\TestCase;
use kaleidpixel\URLStatusChecker;

class URLStatusCheckerTest extends TestCase {
	private static $checker;
	private static $url;

	protected function setUp(): void {
		self::$url     = 'https://www.google.com';
		self::$checker = new URLStatusChecker( (array) self::$url );
	}

	public function testGetStatusCode() {
		$status = self::$checker->getStatusCode();

		$this->assertMatchesRegularExpression(
			'/^0|\d{3}$/',
			$status[self::$url]
		);
	}

	public function testGetResponseTime() {
		$response_time = self::$checker->getResponseTime();

		$this->assertMatchesRegularExpression(
			'/^\d{1,3}(\.\d{1,3})?(min|s|ms|μs)$/',
			$response_time[self::$url]
		);
	}

	public function testGetBenchmarkTime() {
		$this->assertMatchesRegularExpression(
			'/^\d{1,3}(\.\d{1,3})?(min|s|ms|μs)$/',
			self::$checker->getBenchmarkTime()
		);
	}
}
