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
        $flush_url  = add_query_arg( [ 'wc_cache' => 'flush' ] );
        $nonce_url  = wp_nonce_url( $flush_url, 'wc_cache_nonce' );

        // Add button to the bar.
        $admin_bar->add_menu(
          [
            'parent'  => '',
            'id'      => 'wc_cache_button',
            'title'   => esc_html__( 'Flush Cache', 'woocart-defaults' ),
            'meta'    => [
              'title' => esc_html__( 'Flush Cache', 'woocart-defaults' )
            ],
            'href'    => $nonce_url
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
        add_action( 'admin_notices', 'show_notices' );
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
      if ( function_exists( 'opcache_reset' ) ) {
        // Check & delete if file cache is enabled.
        if (
          ini_get( 'opcache.file_cache' )
          && is_writable( ini_get( 'opcache.file_cache' ) )
        ) {
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

      // Flush Redis cache.
      wp_cache_flush();
    }

    /**
     * Displays notice after the cache is flushed.
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

	}
}
