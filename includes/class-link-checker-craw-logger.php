<?php

use GuzzleHttp\Exception\RequestException;
use League\Flysystem\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlLogger extends CrawlObserver {
	const UNRESPONSIVE_HOST = 'Host did not respond';
	const REDIRECT          = 'Redirect';

	/**
	 * @var array
	 */
	protected $crawledUrls = array();

	/**
	 * @var string|null
	 */
	protected $outputFile = null;


	/**
	 * Called when the crawl will crawl the url.
	 *
	 * @param \Psr\Http\Message\UriInterface $url
	 */
	public function willCrawl( UriInterface $url ): void {
	}

	/**
	 * Called when the crawl has ended.
	 */
	public function finishedCrawling(): void {
		ksort( $this->crawledUrls );

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once (ABSPATH . '/wp-admin/includes/file.php');
			WP_Filesystem();
		}

		$crawl_content = array(
			"date"   => date( "Y-m-d H:i:s" ),
			"results" => $this->crawledUrls,
		);

		$last_result_file = plugin_dir_path(__DIR__) . 'link-checker-last-result.json';
		$wp_filesystem->put_contents( $last_result_file, json_encode($crawl_content), 0644);
	}


	public function crawled(
		UriInterface $url,
		ResponseInterface $response,
		?UriInterface $foundOnUrl = null
	): void {
		if ( $this->addRedirectedResult( $url, $response, $foundOnUrl ) ) {
			return;
		}

		// exclude 200 responses
		if ( $response->getStatusCode() != "200" ) {
			$this->addResult(
				(string) $url,
				(string) $foundOnUrl,
				$response->getStatusCode(),
				$response->getReasonPhrase()
			);
		}

	}

	public function crawlFailed(
		UriInterface $url,
		RequestException $requestException,
		?UriInterface $foundOnUrl = null
	): void {
		if ( $response = $requestException->getResponse() ) {
			$this->crawled( $url, $response, $foundOnUrl );
		} else {
			$this->addResult( (string) $url, (string) $foundOnUrl, '---', self::UNRESPONSIVE_HOST );
		}
	}

	public function addResult( $url, $foundOnUrl, $statusCode, $reason ) {
		/*
		* don't display duplicate results
		* this happens if a redirect is followed to an existing page
		*/
		if ( isset( $this->crawledUrls[ $statusCode ] ) && in_array( $url, $this->crawledUrls[ $statusCode ] ) ) {
			return;
		}
		$this->crawledUrls[ $statusCode ][] = $url;
	}

	/*
	* https://github.com/guzzle/guzzle/blob/master/docs/faq.rst#how-can-i-track-redirected-requests
	*/
	public function addRedirectedResult(
		UriInterface $url,
		ResponseInterface $response,
		?UriInterface $foundOnUrl = null
	) {
		// if its not a redirect the return false
		if ( ! $response->getHeader( 'X-Guzzle-Redirect-History' ) ) {
			return false;
		}

		// retrieve Redirect URI history
		$redirectUriHistory = $response->getHeader( 'X-Guzzle-Redirect-History' );

		// retrieve Redirect HTTP Status history
		$redirectCodeHistory = $response->getHeader( 'X-Guzzle-Redirect-Status-History' );

		// Add the initial URI requested to the (beginning of) URI history
		array_unshift( $redirectUriHistory, (string) $url );

		// Add the final HTTP status code to the end of HTTP response history
		array_push( $redirectCodeHistory, $response->getStatusCode() );

		// Combine the items of each array into a single result set
		$fullRedirectReport = array();
		foreach ( $redirectUriHistory as $key => $value ) {
			$fullRedirectReport[ $key ] = array(
				'location' => $value,
				'code'     => $redirectCodeHistory[ $key ],
			);
		}

		// Add the redirects and final URL as results
		foreach ( $fullRedirectReport as $k => $redirect ) {
			$this->addResult(
				(string) $redirect['location'],
				(string) $foundOnUrl,
				$redirect['code'],
				$k + 1 == count( $fullRedirectReport ) ? $response->getReasonPhrase() : self::REDIRECT
			);
		}

		return true;
	}
}
