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
		 * @var array
		 */
		private $_blacklisted_hooks = array(
			'wc_facebook_generate_product_catalog_feed', // facebook-for-woocommerce
			'woosea_cron_hook', // woo-product-feed-pro
			'couponwheel_cron', // wp-optin-wheel
		);

		/**
		 * Class constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'http_block_status' ) );
			if ( defined( 'WPCF7_PLUGIN' ) ) {
				add_action( 'wp_footer', array( $this, 'wpcf7_cache' ), PHP_INT_MAX );
			}
			add_filter( 'file_mod_allowed', array( $this, 'read_only_filesystem' ), PHP_INT_MAX, 2 );
			add_filter( 'pre_reschedule_event', array( $this, 'delay_cronjobs' ), PHP_INT_MAX, 2 );
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
		 * Delay certain cronjobs to run only once during the day instead of its
		 * regular schedule.
		 *
		 * @param null|bool $pre Value to return instead. Default null to continue adding the event
		 * @param stdClass  $event {
		 *      An object containing an event's data.
		 *
		 *      @type string       $hook      Action hook to execute when the event is run.
		 *      @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
		 *      @type string|false $schedule  How often the event should subsequently recur.
		 *      @type array        $args      Array containing each separate argument to pass to the hook's callback function.
		 *      @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
		 * }
		 *
		 * @return object
		 */
		public function delay_cronjobs( $pre, $event ) {
			$timezone = wp_timezone_string();

			$cron_start = DateTime::createFromFormat( 'H:i', $this->start_time, new DateTimeZone( $timezone ) )->getTimestamp();
			$time_now   = $this->time_now( $timezone )->getTimestamp();

			// If the start time has already passed, schedule it for the next day
			if ( $time_now >= $cron_start ) {
				$cron_start = strtotime( '+1 day', $cron_start );
			}

			// Check for the desired hooks
			if ( in_array( $event->hook, $this->_blacklisted_hooks ) ) {
				$event->timestamp = $cron_start;

				return $event;
			}

			return $pre;
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

		/**
		 * Disables loading of refill script for Contact Form 7.
		 */
		public function wpcf7_cache() : void {
			echo '<script>if (typeof wpcf7 !== "undefined") { wpcf7.cached = 0; }</script>';
		}

	}

}
