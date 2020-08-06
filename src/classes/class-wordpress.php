<?php

namespace Niteo\WooCart\Defaults {

	use DateTime;
	use DateTimeZone;

	/**
	 * Class WooCommerce
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class WordPress {

		/**
		 * @var string
		 */
		public $start_time = '03:00';

		/**
		 * @var string
		 */
		public $end_time = '04:00';

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'http_block_status' ) );
			add_action( 'init', array( $this, 'control_cronjobs' ), PHP_INT_MAX );
			add_filter( 'file_mod_allowed', array( $this, 'read_only_filesystem' ), PHP_INT_MAX, 2 );
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
			if ( false !== strpos( $url, apply_filters( 'woocart_whitelist_http_url', 'api.wordpress.org' ) ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Control cronjobs to run at a specific time during a day for the store.
		 *
		 * @return void
		 */
		public function control_cronjobs() : void {
			$timezone = wp_timezone_string();

			$cron_start = DateTime::createFromFormat( 'H:i', $this->start_time, new DateTimeZone( $timezone ) );
			$cron_end   = DateTime::createFromFormat( 'H:i', $this->end_time, new DateTimeZone( $timezone ) );
			$time_now   = $this->time_now( $timezone );

			if ( $time_now >= $cron_start && $time_now <= $cron_end ) {
				return;
			}

			// Remove cronjobs via filter
			add_filter( 'pre_get_ready_cron_jobs', array( $this, 'empty_cronjobs' ) );
		}

		/**
		 * Returns empty array for cronjobs.
		 *
		 * @return array
		 */
		public function empty_cronjobs() : array {
			return array();
		}

		/**
		 * Returns DateTime object for current time.
		 *
		 * @param string $timezone Store timezone to fetch the local time
		 * @return object
		 */
		public function time_now( string $timezone ) : object {
			return new DateTime( 'now', new DateTimeZone( $timezone ) );
		}

		/**
		 * Makes the filesystem read-only.
		 *
		 * @param bool   $file_mod_allowed Whether file modifications are allowed.
		 * @param string $context The usage context.
		 *
		 * @return bool
		 */
		public function read_only_filesystem( bool $file_mod_allowed, string $context ) : bool {
			return ! get_option( 'woocart_readonly_filesystem', false );
		}

	}

}
