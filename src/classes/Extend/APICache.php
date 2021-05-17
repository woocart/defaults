<?php
/**
 * Cache WooCommerce API requests.
 *
 * @package Niteo\WooCart\Defaults
 */

namespace Niteo\WooCart\Defaults\Extend;

trait APICache {

	/**
	 * Endpoint to cache.
	 *
	 * @var string
	 */
	private $endpoint_to_cache = 'wc/v3/reports';

	/**
	 * Unique identifier for the cache.
	 *
	 * @var string
	 */
	private $cache_key;

	/**
	 * Cache key prefix.
	 *
	 * @var string
	 */
	private $cache_key_prefix = 'wc_reports_';

	/**
	 * Initialize API cache.
	 *
	 * @return void
	 */
	public function api_init() : void {
		// Build cache key using API url.
		$this->create_cache_key();

		// Check if cache needs to be skipped.
		if ( ! $this->cache_key ) {
			return;
		}

		add_filter( 'rest_pre_dispatch', array( $this, 'serve_cache' ), PHP_INT_MAX, 3 );
		add_filter( 'rest_pre_echo_response', array( $this, 'set_cache' ), PHP_INT_MAX, 3 );
	}

	/**
	 * Create cache key using the requested API url.
	 *
	 * @return void
	 */
	public function create_cache_key() : void {
		// Requested API url which is used to create cache.
		$request_url = sanitize_text_field( $_SERVER['REQUEST_URI'] );

		if ( ! $request_url ) {
			return;
		}

		// Parse URL to check for path and query args.
		$parsed_url = wp_parse_url( $request_url );

		if ( ! isset( $parsed_url['path'] ) || empty( $parsed_url['path'] ) ) {
			return;
		}

		// Do nothing if not a cacheable endpoint.
		if ( false === strpos( $parsed_url['path'], $this->endpoint_to_cache ) ) {
			return;
		}

		// Check for query args.
		$params = array();

		if ( isset( $parsed_url['query'] ) && ! empty( $parsed_url['query'] ) ) {
			$params = wp_parse_args( $parsed_url['query'] );
		}

		// Set cache key.
		$this->cache_key = $this->cache_key_prefix . md5( $parsed_url['path'] . wp_json_encode( $params ) );
	}

	/**
	 * Serve data from cache if key exists.
	 *
	 * @param null             $result  Response data to send to the client.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 *
	 * @return null|array
	 */
	public function serve_cache( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
		// Check if cache exists.
		$output = \get_transient( $this->cache_key );

		if ( $output ) {
			// Set content type to JSON and output response.
			$this->set_header( 'Content-Type: application/json' );
			echo wp_json_encode( $output );

			// Terminate as the request is complete.
			$this->exit();
		}

		return $result;
	}

	/**
	 * Set cache based on the endpoint and request type.
	 *
	 * @param array            $result  Response data to send to the client.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 *
	 * @return array
	 */
	public function set_cache( $result, \WP_REST_Server $server, \WP_REST_Request $request ) : array {
		// Ensure result is not empty.
		if ( empty( $result ) ) {
			return $result;
		}

		// Ignore any response other than 200.
		if ( is_array( $result )
		&& isset( $result['data']['status'] )
		&& 200 !== (int) $result['data']['status']
		) {
			return $result;
		}

		// Set transient.
		\set_transient( $this->cache_key, $result, HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Wrapper for the header function.
	 *
	 * @param  string $header Header value to be set.
	 * @return void
	 * @codeCoverageIgnore
	 */
	protected function set_header( $header ) : void {
		header( $header );
	}

	/**
	 * Wrapper for the exit function.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	protected function exit() : void {
		exit;
	}

}
