<?php
/**
 * PHP 8.0 or later
 *
 * @package    KALEIDPIXEL
 * @author     KAZUKI Otsuhata
 * @copyright  2023 (C) Kaleid Pixel
 * @license    MIT License
 * @version    0.0.1
 **/

namespace kaleidpixel;

class URLStatusChecker {
	private $url;
	private $status_code;
	private $response_time;
	private $benchmark_time;
	private $batch_size;

	public function __construct( array $url, int $batch_size = 100 ) {
		$this->url        = $url;
		$this->batch_size = $batch_size;

		$this->checkUrls( $this->url );
	}

	/**
	 * @param float $responseTime
	 *
	 * @return string
	 */
	public function formatResponseTime( float $responseTime ): string {
		if ( $responseTime < 0.001 ) {
			return round( $responseTime * 1000000, 2 ) . 'μs';
		} elseif ( $responseTime < 1 ) {
			return round( $responseTime * 1000, 2 ) . 'ms';
		} elseif ( $responseTime < 60 ) {
			return round( $responseTime, 2 ) . 's';
		} else {
			return round( $responseTime / 60, 2 ) . 'min';
		}
	}

	/**
	 * @param array $urls
	 *
	 * @return void
	 */
	public function checkUrls( array $urls ): void {
		$start      = microtime( true );
		$urlBatches = array_chunk( $urls, $this->batch_size );

		foreach ( $urlBatches as $urlBatche ) {
			$multiHandle = curl_multi_init();
			$curlArray   = [];

			foreach ( $urlBatche as $i => $url ) {
				$curlArray[$i] = curl_init( $url );

				curl_setopt_array( $curlArray[$i], [
					CURLOPT_URL            => $url,
					CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_AUTOREFERER    => true,
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_FORBID_REUSE   => true,
					CURLOPT_FRESH_CONNECT  => true,
					CURLOPT_HEADER         => false,
					CURLOPT_TIMEOUT        => 30,
				] );
				curl_multi_add_handle( $multiHandle, $curlArray[$i] );
			}

			unset( $i, $url );

			do {
				$status = curl_multi_exec( $multiHandle, $running );

				if ( $running ) {
					curl_multi_select( $multiHandle );
				}
			} while ( $running && $status == CURLM_OK );

			foreach ( $curlArray as $i => $curl ) {
				$info                      = curl_getinfo( $curl );
				$url                       = rtrim( $info['url'], '/\\' );
				$responseTime              = $this->formatResponseTime( $info['total_time'] );
				$status                    = $info['http_code'];
				$this->status_code[$url]   = $status;
				$this->response_time[$url] = $responseTime;

				curl_multi_remove_handle( $multiHandle, $curl );
				curl_close( $curl );
			}

			unset( $curl );

			curl_multi_close( $multiHandle );
		} // endforeach

		unset( $urlBatche );

		$this->benchmark_time = $this->formatResponseTime( microtime( true ) - $start );
	}

	/**
	 * @return mixed
	 */
	public function getStatusCode() {
		return $this->status_code;
	}

	/**
	 * @return mixed
	 */
	public function getResponseTime() {
		return $this->response_time;
	}

	/**
	 * @return mixed
	 */
	public function getBenchmarkTime() {
		return $this->benchmark_time;
	}
}
