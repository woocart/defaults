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
		public $redis_credentials = '/var/www/cache/redis.sock';

		/**
		 * @var array
		 */
		public $scripts = array(
			'scripts' => array(),
			'styles'  => array(),
		);

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

			// Runs after Customizer settings have been saved.
			add_action( 'customize_save_after', array( &$this, 'flush_fcgi_cache' ) );

			// Runs after post,page,product have benn updated
			add_action( 'save_post_page', array( &$this, 'flush_fcgi_cache_selectively_on_save' ) );
			add_action( 'save_post_post', array( &$this, 'flush_fcgi_cache_selectively_on_save' ) );
			add_action( 'save_post_product', array( &$this, 'flush_fcgi_cache_selectively_on_save' ) );

			add_action( 'delete_post', array( &$this, 'flush_fcgi_cache_selectively_on_delete' ) );

			// On product shipping (inventory decreases).
			add_action( 'woocommerce_reduce_order_stock', array( &$this, 'flush_fcgi_cache' ) );

			// On Elementor save flush caches
			add_action( 'elementor/editor/after_save', array( &$this, 'flush_fcgi_cache' ) );

			// On Beaver Builder save flush caches
			add_action( 'fl_builder_after_save_layout', array( &$this, 'flush_fcgi_cache' ) );

			// Hook to the theme & plugin editor AJAX function.
			// Priority set to -1 so that it runs before anything else.
			add_action( 'wp_ajax_edit_theme_plugin_file', array( &$this, 'flush_cache' ), PHP_INT_MAX );

			// WooCommerce attributes
			add_action( 'woocommerce_after_edit_attribute_fields', array( &$this, 'flush_fcgi_cache' ) );

			/**
			 * If FCGI_CACHE_PATH is defined in wp-config.php, use that.
			 */
			if ( defined( 'FCGI_CACHE_PATH' ) ) {
				$this->fcgi_path = FCGI_CACHE_PATH;
			}

			/**
			 * If the connection constants are defined, we use them.
			 */
			if ( defined( 'WP_REDIS_PATH' ) ) {
				$this->redis_credentials = WP_REDIS_PATH;
			}

			add_filter( 'script_loader_tag', array( $this, 'buffer_scripts' ), PHP_INT_MAX, 3 );
			add_filter( 'style_loader_tag', array( $this, 'buffer_styles' ), PHP_INT_MAX, 3 );
		}

		/**
		 * Add script tags to array.
		 *
		 * @param string $tag    The `<script>` tag for the enqueued script.
		 * @param string $handle The script's registered handle.
		 * @param string $src    The script's source URL.
		 *
		 * @return string
		 */
		public function buffer_scripts( $tag, $handle, $src ) : string {
			$this->scripts['scripts'][] = $src;

			return $tag;
		}

		/**
		 * Add style tags to array.
		 *
		 * @param string $tag    The `<script>` tag for the enqueued script.
		 * @param string $handle The script's registered handle.
		 * @param string $src    The script's source URL.
		 *
		 * @return string
		 */
		public function buffer_styles( $tag, $handle, $src ) : string {
			$this->scripts['styles'][] = $src;

			return $tag;
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
			$this->flush_fcgi_cache();

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

			try {
				// Make connection.
				$redis = new \Redis();
				$redis->connect( $this->redis_credentials );
				$redis->flushAll();
			} catch ( \Exception $e ) {

			}

		}

		/**
		 * Flush FCGI cache.
		 */
		public function flush_fcgi_cache() {

			// Check for cache folder.
			if ( ! is_dir( $this->fcgi_path ) || ! file_exists( $this->fcgi_path ) ) {
				return false;
			}

			// Cache location.
			$scan = new \RecursiveDirectoryIterator( $this->fcgi_path, \RecursiveDirectoryIterator::SKIP_DOTS );

			// Scan directory for files.
			$files = new \RecursiveIteratorIterator( $scan, \RecursiveIteratorIterator::CHILD_FIRST );

			// Check if any files deleted.
			$deleted = false;
			try {
				// Loop over the object.
				foreach ( $files as $file ) {
					// Remove file from the directory.
					if ( ! $file->isDir() ) {
						unlink( $file->getRealPath() );

						// Set it to true since we deleted a file.
						$deleted = true;
					} else {
						rmdir( $file->getRealPath() );
						$deleted = true;
					}
				}
			} catch ( \Exception $e ) {
				return false;
			}

			return $deleted;
		}

		/**
		 * Flush FCGI cache for posts pages and orders.
		 */
		public function flush_fcgi_cache_selectively_on_save( $post_id ) {

			// \WooCart\Log\Socket::log( ["kind"=>"flush_fcgi_cache_selectively_on_save", "post"=>$post_id] );
			if ( wp_is_post_revision( $post_id ) ) {
				return;
			}
			$this->flush_fcgi_cache();

		}

		/**
		 * Flush FCGI cache for posts pages and orders.
		 */
		public function flush_fcgi_cache_selectively_on_delete( $post_id ) {

			$post_type = get_post_type( $post_id );
			// \WooCart\Log\Socket::log( ["kind"=>"flush_fcgi_cache_selectively_on_delete", "post"=>$post_type] );
			if ( 'post' === $post_type ) {
				$this->flush_fcgi_cache();
			}

			if ( 'page' === $post_type ) {
				$this->flush_fcgi_cache();
			}

			if ( 'product' === $post_type ) {
				$this->flush_fcgi_cache();
			}

		}
		/**
		 * Flush cache for Beaver builder.
		 */
		public function flush_bb_cache() {
			// Clear builder cache.
			if ( class_exists( 'FLBuilderModel' ) && method_exists( 'FLBuilderModel', 'delete_asset_cache_for_all_posts' ) ) {
				\FLBuilderModel::delete_asset_cache_for_all_posts();
			}

			// Clear theme cache.
			if ( class_exists( 'FLCustomizer' ) && method_exists( 'FLCustomizer', 'clear_all_css_cache' ) ) {
				\FLCustomizer::clear_all_css_cache();
			}
		}
	}
}
