<?php

/**
 * Cache nav menu.
 */

namespace Niteo\WooCart\Defaults\Extend {

	trait NavCache {

		/**
		 * @var string
		 */
		private $_group = 'navmenu';

		/**
		 * @var string
		 */
		private $_keylist = 'nav_menu_key_list';

		/**
		 * @var array
		 */
		private $_whitelisted_query_strings = array(
			// https://support.google.com/searchads/answer/7342044
			'gclid',
			'gclsrc',

			// https://www.facebook.com/business/help/330994334179410
			'fbclid',

			// FB remarketing ID
			'_rmId',

			// https://en.wikipedia.org/wiki/UTM_parameters
			'utm_campaign',
			'utm_content',
			'utm_medium',
			'utm_source',
			'utm_term',
		);

		/**
		 * Initialize caching of nav menu.
		 *
		 * @return void
		 */
		public function nav_init() : void {
			add_action( 'save_post', array( $this, 'flush_nav_cache' ) );
			add_action( 'wp_create_nav_menu', array( $this, 'flush_nav_cache' ) );
			add_action( 'wp_update_nav_menu', array( $this, 'flush_nav_cache' ) );
			add_action( 'wp_delete_nav_menu', array( $this, 'flush_nav_cache' ) );
			add_action( 'split_shared_term', array( $this, 'flush_nav_cache' ) );

			if ( is_user_logged_in() ) {
				return;
			}

			if ( apply_filters( 'woocart_nav_cache_disabled', false ) ) {
				return;
			}

			if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
				return;
			}

			add_filter( 'pre_wp_nav_menu', array( $this, 'get_nav_menu' ), PHP_INT_MAX, 2 );
			add_filter( 'wp_nav_menu', array( $this, 'save_nav_menu' ), PHP_INT_MAX, 2 );
		}

		/**
		 * @param string $nav_menu_html
		 * @param object $args
		 *
		 * @return string
		 */
		public function get_nav_menu( $nav_menu_html, $args ) {
			$enabled = $this->_is_enabled();

			if ( $enabled ) {
				$cache = wp_cache_get( $this->_get_cache_key( $args ), $this->_group );

				if ( $cache ) {
					return $cache;
				}
			}

			return $nav_menu_html;
		}

		/**
		 * @param string $nav_menu_html
		 * @param object $args
		 *
		 * @return string
		 */
		public function save_nav_menu( $nav_menu_html, $args ) {
			$enabled = $this->_is_enabled();

			if ( $enabled ) {
				$key = $this->_get_cache_key( $args );
				wp_cache_set( $key, $nav_menu_html, $this->_group, DAY_IN_SECONDS );
				$this->_remember_key( $key );
			}

			return $nav_menu_html;
		}

		/**
		 * Flush nav cache.
		 *
		 * @return void
		 */
		public function flush_nav_cache() : void {
			foreach ( $this->_get_all_keys() as $key ) {
				wp_cache_delete( $key, $this->_group );
			}

			wp_cache_delete( $this->_keylist, $this->_group );
		}

		/**
		 * @param string $key
		 *
		 * @return void
		 */
		private function _remember_key( $key ) : void {
			$key_list = wp_cache_get( $this->_keylist, $this->_group );

			if ( $key_list ) {
				$key_list .= '|' . $key;
			} else {
				$key_list = $key;
			}

			wp_cache_set( $this->_keylist, $key_list, $this->_group, DAY_IN_SECONDS );
		}

		/**
		 * Get all cache keys for nav menu.
		 *
		 * @return array
		 */
		private function _get_all_keys() : array {
			$key_list = wp_cache_get( $this->_keylist, $this->_group );

			if ( ! $key_list ) {
				$key_list = '';
			}

			return explode( '|', $key_list );
		}

		/**
		 * Check for whitelisted query strings so that we don't disable cache for them.
		 *
		 * @return bool
		 */
		private function _is_enabled() : bool {
			if ( array() !== array_diff( array_keys( $_GET ), $this->_whitelisted_query_strings ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Returns a cache key name based on args and time.
		 *
		 * @param object $args
		 * @return string
		 */
		private function _get_cache_key( $args ) : string {
			return md5( wp_json_encode( $args ) . time() );
		}
	}

}
