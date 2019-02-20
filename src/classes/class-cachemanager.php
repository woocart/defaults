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


	/**
	 * Class CacheManager
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class CacheManager {

		/**
		 * CacheManager constructor.
		 */
		public function __construct() {
			add_action( 'admin_bar_menu', [ &$this, 'admin_button' ], 100 );
			add_action( 'admin_init', [ &$this, 'check_cache_request' ] );

			// Hooks for OPcache.
			// Plugin activation.
			add_action( 'activated_plugin', [ &$this, 'flush_opcache' ] );

			// Plugin de-activation.
			add_action( 'deactivated_plugin', [ &$this, 'flush_opcache' ] );

			// When the upgrade process is completed (for theme, plugin, core).
			add_action( 'upgrader_process_complete', [ &$this, 'flush_opcache' ] );

			// On theme switch.
			add_action( 'check_theme_switched', [ &$this, 'flush_opcache' ] );

			// Hooks for Redis & FCGI cache.
			// Runs after a post is saved (after the database process is complete).
			add_action( 'save_post', [ &$this, 'flush_redis_cache' ] );
			add_action( 'save_post', [ &$this, 'flush_fcgi_cache' ] );

			// Post delete.
			add_action( 'after_delete_post', [ &$this, 'flush_redis_cache' ] );
			add_action( 'after_delete_post', [ &$this, 'flush_fcgi_cache' ] );

			// Runs after Customizer settings have been saved.
			add_action( 'customize_save_after', [ &$this, 'flush_redis_cache' ] );
			add_action( 'customize_save_after', [ &$this, 'flush_fcgi_cache' ] );

			// On product shipping (inventory decreases).
			add_action( 'woocommerce_reduce_order_stock', [ &$this, 'flush_redis_cache' ] );
			add_action( 'woocommerce_reduce_order_stock', [ &$this, 'flush_fcgi_cache' ] );
		}

		/**
		 * Button for flushing cache in the admin panel bar.
		 *
		 * @param $admin_bar
		 */
		public function admin_button( $admin_bar ) {
			if ( is_admin() ) {
				// Check if the admin toolbar is shown.
				if ( ! is_admin_bar_showing() ) {
					return false;
				}

				// Button parameters.
				$flush_url = add_query_arg( [ 'wc_cache' => 'flush' ] );
				$nonce_url = wp_nonce_url( $flush_url, 'wc_cache_nonce' );

				// Add button to the bar.
				$admin_bar->add_menu(
					[
						'parent' => '',
						'id'     => 'wc_cache_button',
						'title'  => esc_html__( 'Flush Cache', 'woocart-defaults' ),
						'meta'   => [
							'title' => esc_html__( 'Flush Cache', 'woocart-defaults' ),
						],
						'href'   => $nonce_url,
					]
				);
			}
		}

		/**
		 * Flushes cache if the request is valid.
		 */
		public function check_cache_request() {
			if ( ! isset( $_REQUEST['wc_cache'] ) ) {
				return;
			}

			// Admin verification.
			if ( ! is_admin() ) {
				wp_die(
					esc_html__( 'Your request does not seem to be a valid one.', 'woocart-defaults' )
				);
			}

			// Show notice after cache is flushed.
			$action = sanitize_key( $_REQUEST['wc_cache'] );

			if ( 'done' === $action ) {
				add_action( 'admin_notices', [ &$this, 'show_notices' ] );
			} elseif ( 'flush' === $action ) {
				// Check for nonce.
				check_admin_referer( 'wc_cache_nonce' );

				// Flush cache.
				$this->flush_cache();

				// Redirect after the cache is flushed.
				wp_redirect(
					esc_url_raw(
						add_query_arg(
							[ 'wc_cache' => 'done' ]
						)
					)
				);
			}
		}

		/**
		 * Flush cache (OPcache, Redis object cache, and FCGI cache)
		 *
		 * @access protected
		 */
		protected function flush_cache() {
			// OPcache.
			$this->flush_opcache();

			// Flush Redis cache.
			$this->flush_redis_cache();

			// Flush FCGI cache.
			$this->flush_fcgi_cache();
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
		 *
		 * @access protected
		 */
		protected function flush_opcache() {
			// Check & delete if file cache is enabled.
			if ( ini_get( 'opcache.file_cache' ) && is_writable( ini_get( 'opcache.file_cache' ) ) ) {
				$files = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator(
						ini_get( 'opcache.file_cache' ),
						RecursiveDirectoryIterator::SKIP_DOTS
					),
					RecursiveIteratorIterator::CHILD_FIRST
				);

				foreach ( $files as $fileinfo ) {
						$todo = ( $fileinfo->isDir() ? 'rmdir' : 'unlink' );
						$todo( $fileinfo->getRealPath() );
				}
			}

			// Flush OPcache.
			opcache_reset();
		}

		/**
		 * Flush Redis cache.
		 *
		 * @access protected
		 */
		protected function flush_redis_cache() {
			// Flush WordPress cache object.
			wp_cache_flush();

			// Purge redis keys.
			if ( function_exists( 'wp_cache_delete' ) ) {
				$key = defined( 'WP_CACHE_KEY_SALT' ) ? trim( WP_CACHE_KEY_SALT ) : null;

				if ( $key ) {
					wp_cache_delete( $key );
				}
			}
		}

		/**
		 * Flush FCGI cache.
		 *
		 * @access protected
		 */
		protected function flush_fcgi_cache() {
			// Cache location.
			$directory = '/var/www/cache/fcgi';

			// Scan directory for files.
			$files = scandir( $directory );

			// Ensure that there is no failure.
			if ( $files ) {
				if ( is_array( $files ) ) {
					foreach ( $files as $file ) {
						// Remove file from the directory.
						unlink( $directory . '/' . $file );
					}
				}
			}
		}

	}
}
