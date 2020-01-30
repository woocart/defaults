<?php
/**
 * Handles cache management for the store.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.7.4
 */

namespace Niteo\WooCart\Defaults {

	use Predis\Client;
	use Predis\Collection\Iterator\Keyspace;

	/**
	 * Class CacheManager
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class CacheManager {

		/**
		 * The Redis client.
		 *
		 * @var mixed
		 */
		public $redis;

		/**
		 * Track if Redis is available.
		 *
		 * @var bool
		 */
		public $connected = false;

		/**
		 * FCGI Cache path.
		 *
		 * @var string
		 */
		public $fcgi_path = '/var/www/cache/fcgi';

		/**
		 * Redis credentials
		 *
		 * @var array
		 */
		public $redis_credentials = array();

		/**
		 * Beaver Builder credentials
		 */
		protected $fl_builder;
		protected $fl_customizer;

		/**
		 * CacheManager constructor.
		 */
		public function __construct() {
			add_action( 'admin_bar_menu', array( &$this, 'admin_button' ), 100 );
			add_action( 'admin_init', array( &$this, 'check_cache_request' ) );

			// Hooks for OPcache.
			// Plugin activation.
			add_action( 'activated_plugin', array( &$this, 'flush_opcache' ) );

			// Plugin de-activation.
			add_action( 'deactivated_plugin', array( &$this, 'flush_opcache' ) );

			// When the upgrade process is completed (for theme, plugin, core).
			add_action( 'upgrader_process_complete', array( &$this, 'flush_opcache' ) );

			// On theme switch.
			add_action( 'check_theme_switched', array( &$this, 'flush_opcache' ) );

			// Hooks for Redis & FCGI cache.
			// Runs after a post is saved (after the database process is complete).
			add_action( 'save_post', array( &$this, 'flush_redis_cache' ) );
			add_action( 'save_post', array( &$this, 'flush_fcgi_cache' ) );

			// Post delete.
			add_action( 'after_delete_post', array( &$this, 'flush_redis_cache' ) );
			add_action( 'after_delete_post', array( &$this, 'flush_fcgi_cache' ) );

			// Runs after Customizer settings have been saved.
			add_action( 'customize_save_after', array( &$this, 'flush_redis_cache' ) );
			add_action( 'customize_save_after', array( &$this, 'flush_fcgi_cache' ) );

			// On product shipping (inventory decreases).
			add_action( 'woocommerce_reduce_order_stock', array( &$this, 'flush_redis_cache' ) );
			add_action( 'woocommerce_reduce_order_stock', array( &$this, 'flush_fcgi_cache' ) );

			// Hook to the theme & plugin editor AJAX function.
			// Priority set to -1 so that it runs before anything else.
			add_action( 'wp_ajax_edit_theme_plugin_file', array( &$this, 'flush_cache' ), PHP_INT_MAX );

			// WooCommerce attributes
			add_action( 'woocommerce_after_add_attribute_fields', array( &$this, 'flush_redis_cache' ) );
			add_action( 'woocommerce_after_edit_attribute_fields', array( &$this, 'flush_redis_cache' ) );

			// Fires before option is updated.
			add_action( 'updated_option', array( &$this, 'check_updated_option' ), 10, 3 );

			/**
			 * If FCGI_CACHE_PATH is defined in wp-config.php, use that.
			 */
			if ( defined( 'FCGI_CACHE_PATH' ) ) {
				$this->fcgi_path = FCGI_CACHE_PATH;
			}

			/**
			 * If the connection constants are defined, we use them.
			 */
			if ( defined( 'WP_REDIS_SCHEME' ) && defined( 'WP_REDIS_PATH' ) ) {
				$this->redis_credentials = array(
					'scheme' => WP_REDIS_SCHEME,
					'path'   => WP_REDIS_PATH,
				);
			}
		}

		/**
		 * Set static class for the Beaver Builder plugin (FlBuilderModel).
		 */
		public function setFlbuilder( \FLBuilderModel $fl_builder ) {
			$this->fl_builder = $fl_builder;
		}

		/**
		 * Set static class for the Beaver Builder plugin (FLCustomizer).
		 */
		public function setFlcustomizer( \FLCustomizer $fl_customizer ) {
			$this->fl_customizer = $fl_customizer;
		}

		/**
		 * Button for flushing cache in the admin panel bar.
		 *
		 * @param $admin_bar
		 * @return bool
		 */
		public function admin_button( $admin_bar ) {
			if ( is_admin() ) {
				// Check if the admin toolbar is shown.
				if ( ! is_admin_bar_showing() ) {
					return false;
				}

				// Button parameters.
				$flush_url = add_query_arg( array( 'wc_cache' => 'flush' ) );
				$nonce_url = wp_nonce_url( $flush_url, 'wc_cache_nonce' );

				// Add button to the bar.
				$admin_bar->add_menu(
					array(
						'parent' => '',
						'id'     => 'wc_cache_button',
						'title'  => esc_html__( 'Flush Cache', 'woocart-defaults' ),
						'meta'   => array(
							'title' => esc_html__( 'Flush Cache', 'woocart-defaults' ),
						),
						'href'   => $nonce_url,
					)
				);
			}
		}

		/**
		 * Flushes cache if the request is valid.
		 */
		public function check_cache_request() {
			if ( isset( $_REQUEST['wc_cache'] ) ) {
				// Admin verification.
				if ( ! is_admin() ) {
					wp_die(
						esc_html__( 'Your request does not seem to be a valid one.', 'woocart-defaults' )
					);
				}

				// Show notice after cache is flushed.
				$action = sanitize_key( $_REQUEST['wc_cache'] );

				if ( 'done' === $action ) {
					add_action( 'admin_notices', array( &$this, 'show_notices' ) );
				} elseif ( 'flush' === $action ) {
					// Check for nonce.
					check_admin_referer( 'wc_cache_nonce' );

					// Flush cache.
					$this->flush_cache();

					// Redirect after the cache is flushed.
					wp_redirect(
						esc_url_raw(
							add_query_arg(
								array( 'wc_cache' => 'done' )
							)
						)
					);
				}
			}
		}

		/**
		 * Flush cache (OPcache, Redis object cache, and FCGI cache).
		 */
		protected function flush_cache() {
			// OPcache.
			$this->flush_opcache();

			// Flush Redis cache.
			$this->flush_redis_cache();

			// Flush FCGI cache.
			$this->flush_fcgi_cache( $this->fcgi_path );

			// Flush Beaver Builder cache.
			$this->flush_bb_cache();
		}

		/**
		 * Displays notice after the cache is flushed.
		 *
		 * @codeCoverageIgnore
		 */
		public function show_notices() {
			?>
	  <div class="notice notice-success is-dismissible">
		<p><strong><?php esc_html_e( 'Cache has been flushed successfully.', 'woocart-defaults' ); ?></strong></p>
		<button type="button" class="notice-dismiss">
		  <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'woocart-defaults' ); ?></span>
		</button>
	  </div>
			<?php
		}

		/**
		 * Flush OPcache.
		 */
		public function flush_opcache() {
			// Flush OPcache.
			opcache_reset();
		}

		/**
		 * Flush Redis cache.
		 */
		public function flush_redis_cache() {
			// Flush WordPress cache object.
			wp_cache_flush();

			// To access whether cache was flushed or not.
			$deleted = false;

			// Purge redis keys.
			// Connect to Redis instance.
			$this->redis_connect();

			// Continue if the connection was successful.
			if ( $this->connected ) {
				// Find keys matching - cache:*
				// It has been URI encoded to cache%3A*
				$pattern = 'cache%3A*';

				foreach ( new Keyspace( $this->redis, $pattern ) as $key ) {
					$this->redis->del( $key );
				}
			}
		}

		/**
		 * Flush FCGI cache.
		 */
		public function flush_fcgi_cache( $directory ) {
			// Check for cache folder.
			if ( ! is_dir( $directory ) || ! file_exists( $directory ) ) {
				return;
			}

			// Cache location.
			$scan = new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS );

			// Scan directory for files.
			$files = new \RecursiveIteratorIterator( $scan, \RecursiveIteratorIterator::CHILD_FIRST );

			// Check if any files deleted.
			$deleted = false;

			// Loop over the object.
			foreach ( $files as $file ) {
				// Remove file from the directory.
				if ( ! $file->isDir() ) {
					unlink( $file->getRealPath() );

					// Set it to true since we deleted a file.
					$deleted = true;
				}
			}

			return $deleted;
		}

		/**
		 * Flush cache for Beaver builder.
		 */
		public function flush_bb_cache() {
			// Clear builder cache.
			if ( class_exists( 'FLBuilderModel' ) ) {
				$this->setFlbuilder( new \FLBuilderModel() );
				$this->fl_builder::delete_asset_cache_for_all_posts();
			}

			// Clear theme cache.
			if ( class_exists( 'FLCustomizer' ) ) {
				$this->setFlcustomizer( new \FLCustomizer() );
				$this->fl_customizer::clear_all_css_cache();
			}
		}

		/**
		 * Attempting connection with Redis.
		 *
		 * @codeCoverageIgnore
		 */
		protected function redis_connect() {
			// Make connection.
			$this->redis = new Client( $this->redis_credentials );
			$this->redis->connect();

			// Throws exception if Redis is unavailable.
			if ( $this->redis->isConnected() ) {
				// Connection set to true.
				$this->connected = true;
			}

			return $this->connected;
		}

		/**
		 * Check for updated option to determine if cache needs to be flushed.
		 */
		public function check_updated_option( $key, $old_value, $value ) {
			// Check if the updated option is an widget.
			if ( strpos( $key, 'widget_' ) !== false ) {
				// Flush redis cache for the widget option.
				$this->flush_redis_cache();
			}
		}

	}
}
