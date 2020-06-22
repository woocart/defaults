<?php

namespace Niteo\WooCart\Defaults {

	/**
	 * Class WooCommerce
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class WordPress {

		public function __construct() {
			add_action( 'init', array( $this, 'http_block_status' ) );
		}

		/**
		 * Checks whether to block HTTP calls or not by looking in the
		 * `wp_options` for the status.
		 */
		public function http_block_status() : void {
			if ( get_option( 'woocart_http_status', false ) ) {
				add_filter( 'pre_http_request', array( $this, 'http_requests' ), ~PHP_INT_MAX, 3 );
			}
		}

		/**
		 * Check for HTTP requests and block all external ones except for the
		 * calls made to api.wordpress.org
		 *
		 * @param false|array|WP_Error $preempt Whether to preempt an HTTP request's return value. Default false
		 * @param array                $args HTTP request arguments
		 * @param string               $url The request URL
		 */
		public function http_requests( $preempt, array $args, string $url ) : bool {
			if ( false !== strpos( $url, 'api.wordpress.org' ) ) {
				return false;
			}

			return true;
		}

	}

}
