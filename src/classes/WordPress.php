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

		use Extend\Dashboard;

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
			add_action( 'admin_bar_menu', array( $this, 'block_status_admin_button' ), 100 );
			add_action( 'init', array( $this, 'http_block_status' ) );
			add_action( 'init', array( $this, 'remove_heartbeat' ), PHP_INT_MAX );
			add_action( 'wp_footer', array( $this, 'wpcf7_cache' ), PHP_INT_MAX );
			add_filter( 'file_mod_allowed', array( $this, 'read_only_filesystem' ), PHP_INT_MAX, 2 );
			add_filter( 'pre_reschedule_event', array( $this, 'delay_cronjobs' ), PHP_INT_MAX, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_init', array( $this, 'check_block_request' ) );
			add_action( 'admin_init', array( $this, 'restrict_pagination' ), PHP_INT_MAX );
			add_action( 'admin_init', array( $this, 'restrict_pagination_ui' ) );

			// Runs only when WP_REWRITE_URLS is set to true and for staging stores
			if ( $this->is_rewrite_urls() && $this->is_staging() ) {
				add_action( 'wp_loaded', array( $this, 'start_buffering' ), ~PHP_INT_MAX );

				// End buffer
				$this->end_buffering();
			}
		}

		/**
		 * Adds a button to the admin bar if block mode for
		 * HTTP calls is active.
		 *
		 * @return void
		 */
		public function block_status_admin_button( $admin_bar ) : void {
			if ( ! is_admin() ) {
				return;
			}

			// Check if the admin toolbar is shown.
			if ( ! is_admin_bar_showing() ) {
				return;
			}

			// Status should be enabled.
			if ( ! get_option( 'woocart_http_status' ) ) {
				return;
			}

			// Button parameters.
			$status_url = add_query_arg( array( 'wc_http_block' => 'deactivate' ) );
			$nonce_url  = wp_nonce_url( $status_url, 'wc_block_nonce' );

			// Add button to the bar.
			$admin_bar->add_menu(
				array(
					'parent' => '',
					'id'     => 'wc_http_block',
					'title'  => esc_html__( 'Blocking HTTP Calls', 'woocart-defaults' ),
					'meta'   => array(
						'title' => esc_html__( 'External HTTP calls are getting blocked', 'woocart-defaults' ),
					),
					'href'   => '#',
				)
			);

			// Add deactivate option.
			$admin_bar->add_menu(
				array(
					'parent' => 'wc_http_block',
					'id'     => 'wc_http_block_button',
					'title'  => esc_html__( 'Deactivate', 'woocart-defaults' ),
					'meta'   => array(
						'title' => esc_html__( 'Turn it off', 'woocart-defaults' ),
					),
					'href'   => $nonce_url,
				)
			);
		}

		/**
		 * Checks whether to block HTTP calls or not by looking in the
		 * `wp_options` for the status.
		 */
		public function http_block_status() : void {
			if ( ! get_option( 'woocart_http_status' ) ) {
				return;
			}

			add_filter( 'pre_http_request', array( $this, 'http_requests' ), ~PHP_INT_MAX, 3 );
		}

		/**
		 * De-register hearbeart script.
		 */
		public function remove_heartbeat() : void {
			wp_deregister_script( 'heartbeat' );
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
			if ( ! defined( 'WPCF7_PLUGIN' ) ) {
				return;
			}

			echo '<script>if (typeof wpcf7 !== "undefined") { wpcf7.cached = 0; }</script>';
		}

		/**
		 * Add scripts for the admin panel.
		 *
		 * @return void
		 */
		public function admin_scripts( $hook ) : void {
			wp_enqueue_style( 'woocart-admin', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin.css', array(), Release::Version, 'all' );
		}

		/**
		 * Checks for the request and deactivates the HTTP block calls mode.
		 *
		 * @return void
		 */
		public function check_block_request() : void {
			if ( ! is_admin() ) {
				return;
			}

			if ( ! isset( $_REQUEST['wc_http_block'] ) ) {
				return;
			}

			// Show notice after cache is flushed.
			$action = sanitize_key( $_REQUEST['wc_http_block'] );

			if ( 'deactivate' !== $action ) {
				return;
			}

			// Verify nonce.
			if ( ! check_admin_referer( 'wc_block_nonce' ) ) {
				return;
			}

			// HTTP block calls is deactivate here.
			update_option( 'woocart_http_status', false );

			// Remove arg from the query.
			wp_redirect(
				esc_url_raw(
					remove_query_arg( 'wc_http_block' )
				)
			);
		}

		/**
		 * Starts output buffering for overriding staging URL's.
		 *
		 * @return void
		 * @codeCoverageIgnore
		 */
		public function start_buffering() : void {
			ob_start( array( $this, 'staging_url_override' ) );
		}

		/**
		 * Overrides URL's for the staging store.
		 *
		 * @param string $buffer Page content as string.
		 * @return string
		 */
		public function staging_url_override( string &$buffer ) : string {
			// Staging URL
			if ( isset( $_ENV['DOMAIN'] ) ) {
				$buffer = str_replace( array( 'http://' . $_ENV['DOMAIN'], 'https://' . $_ENV['DOMAIN'] ), '', $buffer );
			}

			// Live URL
			if ( isset( $_ENV['PARENT_DOMAIN'] ) ) {
				$buffer = str_replace( array( 'http://' . $_ENV['PARENT_DOMAIN'], 'https://' . $_ENV['PARENT_DOMAIN'] ), '', $buffer );
			}

			return $buffer;
		}

		/**
		 * Turn off output buffer.
		 *
		 * @return void
		 * @codeCoverageIgnore
		 */
		public function end_buffering() : void {
			register_shutdown_function(
				function() {
					ob_end_flush();
				}
			);
		}

		/**
		 * Restrict admin pagination for orders, products, pages, posts, users, etc.
		 *
		 * @return void
		 */
		public function restrict_pagination() {
			global $pagenow;

			// Check if this feature is not disabled.
			if ( get_option( 'woocart_disable_restrict_pagination' ) ) {
				return;
			}

			$user             = \wp_get_current_user();
			$pagination_limit = 20;

			if ( ! $user ) {
				return;
			}

			// Posts (includes posts, pages, products, and orders)
			$post_options = array(
				'post',
				'page',
				'product',
				'shop_order',
			);

			if ( 'edit.php' === $pagenow ) {
				if ( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], $post_options ) ) {
					$option     = \sanitize_text_field( $_GET['post_type'] );
					$pagination = \get_user_meta( $user->ID, "edit_{$option}_per_page", true );

					if ( ! $pagination ) {
						\update_user_meta( $user->ID, "edit_{$option}_per_page", $pagination_limit );
					}

					if ( $pagination_limit >= $pagination ) {
						return;
					}

					\update_user_meta( $user->ID, "edit_{$option}_per_page", $pagination_limit );
				} else {
					$pagination = \get_user_meta( $user->ID, 'edit_post_per_page', true );

					if ( ! $pagination ) {
						\update_user_meta( $user->ID, 'edit_post_per_page', $pagination_limit );
					}

					if ( $pagination_limit >= $pagination ) {
						return;
					}

					\update_user_meta( $user->ID, 'edit_post_per_page', $pagination_limit );
				}
			}

			// Comments
			if ( 'edit-comments.php' === $pagenow ) {
				$pagination = \get_user_meta( $user->ID, 'edit_comments_per_page', true );

				if ( ! $pagination ) {
					\update_user_meta( $user->ID, 'edit_comments_per_page', $pagination_limit );
				}

				if ( $pagination_limit >= $pagination ) {
					return;
				}

				\update_user_meta( $user->ID, 'edit_comments_per_page', $pagination_limit );
			}

			// Taxonomies (includes categories and tags)
			$taxonomy_options = array(
				'category',
				'post_tag',
			);

			if ( 'edit-tags.php' === $pagenow && in_array( $_GET['taxonomy'], $taxonomy_options ) ) {
				$option     = \sanitize_text_field( $_GET['taxonomy'] );
				$pagination = \get_user_meta( $user->ID, "edit_{$option}_per_page", true );

				if ( ! $pagination ) {
					\update_user_meta( $user->ID, "edit_{$option}_per_page", $pagination_limit );
				}

				if ( $pagination_limit >= $pagination ) {
					return;
				}

				\update_user_meta( $user->ID, "edit_{$option}_per_page", $pagination_limit );
			}

			// Plugins
			if ( 'plugins.php' === $pagenow ) {
				$pagination = \get_user_meta( $user->ID, 'plugins_per_page', true );

				if ( ! $pagination ) {
					\update_user_meta( $user->ID, 'plugins_per_page', $pagination_limit );
				}

				if ( $pagination_limit >= $pagination ) {
					return;
				}

				\update_user_meta( $user->ID, 'plugins_per_page', $pagination_limit );
			}

			// Users
			if ( 'users.php' === $pagenow ) {
				$pagination = \get_user_meta( $user->ID, 'users_per_page', true );

				if ( ! $pagination ) {
					\update_user_meta( $user->ID, 'users_per_page', $pagination_limit );
				}

				if ( $pagination_limit >= $pagination ) {
					return;
				}

				\update_user_meta( $user->ID, 'users_per_page', $pagination_limit );
			}
		}

		/**
		 * Disable the pagination area in the admin panel.
		 *
		 * @return void
		 */
		public function restrict_pagination_ui() {
			// Check if this feature is not disabled.
			if ( get_option( 'woocart_disable_restrict_pagination' ) ) {
				return;
			}

			$js = '(function($) {
				$(document).ready(function() {
					if ($(".screen-per-page").length) {
						$(".screen-per-page").prop("disabled", true);
					}
				});
			})(jQuery);';

			// Hooked it to `jquery` handle as it is always loaded in WP admin.
			\wp_add_inline_script( 'jquery', $js );
		}

	}

}
