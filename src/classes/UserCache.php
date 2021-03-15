<?php
/**
 * Handles user cache for the store.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.28.0
 */

namespace Niteo\WooCart\Defaults;

/**
 * Class UserCache
 *
 * @package Niteo\WooCart\Defaults
 */
class UserCache {

	/**
	 * @var string
	 */
	private $_key;

	/**
	 * @var bool
	 */
	private $_is_cacheable = false;

	/**
	 * @var bool
	 */
	private $_cache_exists = false;

	/**
	 * @var string
	 */
	private $_cache_group = 'woocart-user-cache';

	/**
	 * @var string
	 */
	private $_cache;

	/**
	 * UserCache constructor.
	 */
	public function __construct() {
		add_action( 'muplugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize user cache when the mu-plugins are loaded.
	 *
	 * @return void
	 */
	public function init() : void {
		// No cache if user is in the admin panel.
		if ( \is_admin() ) {
			return;
		}

		add_action( 'init', array( $this, 'set_cache_key' ), 10 );
		add_action( 'init', array( $this, 'is_cacheable' ), 12 );
		add_action( 'init', array( $this, 'check_cache' ), 15 );
		add_action( 'init', array( $this, 'maybe_serve_cache' ), 20 );
		add_action( 'wp_loaded', array( $this, 'start_buffering' ), ~PHP_INT_MAX );
		add_action( 'shutdown', array( $this, 'register_shutdown' ), ~PHP_INT_MAX );
	}

	/**
	 * Sets cache key based on cookie token and current page.
	 *
	 * @return void
	 */
	public function set_cache_key() : void {
		$auth_cookie = \wp_parse_auth_cookie( '', 'logged_in' );

		if ( ! $auth_cookie ) {
			return;
		}

		if ( ! isset( $auth_cookie['token'] ) ) {
			return;
		}

		if ( empty( $auth_cookie['token'] ) ) {
			return;
		}

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$token = \sanitize_text_field( $auth_cookie['token'] );
		$uri   = \sanitize_text_field( $_SERVER['REQUEST_URI'] );

		$this->_key = hash( 'md5', "{$token}:{$uri}" );
	}

	/**
	 * Check if current request should be cached or not.
	 *
	 * @return void
	 */
	public function is_cacheable() {
		// Check request type.
		if ( 'GET' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		// Check for logged-in user.
		if ( ! \is_user_logged_in() ) {
			return;
		}

		// add_to_cart / add-to-cart in URL should be skipped.
		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'add_to_cart' ) ) {
			return;
		}

		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'add-to-cart' ) ) {
			return;
		}

		// add_to_wishlist / add-to-wishlist (YITH plugin) in URL should be skipped.
		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'add_to_wishlist' ) ) {
			return;
		}

		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'add-to-wishlist' ) ) {
			return;
		}

		$this->_is_cacheable = true;
	}

	/**
	 * Check for cache based on user token and page uri.
	 *
	 * @return void
	 */
	public function check_cache() : void {
		if ( ! $this->get_is_cacheable() ) {
			return;
		}

		if ( empty( $this->get_key() ) ) {
			return;
		}

		$this->_cache = \wp_cache_get( $this->get_key(), $this->get_cache_group() );

		if ( ! $this->get_cache() ) {
			return;
		}

		// If not HTML, we should probably avoid serving the content.
		if ( ! $this->is_html( $this->get_cache() ) ) {
			return;
		}

		$this->_cache_exists = true;
	}

	/**
	 * Serve cache if available.
	 *
	 * @return void
	 */
	public function maybe_serve_cache() {
		if ( ! $this->get_cache_exists() ) {
			return;
		}

		// Serve cached version.
		echo $this->get_cache();

		// Terminate page.
		$this->terminate();
	}

	/**
	 * Starts output buffering.
	 *
	 * @return void
	 */
	public function start_buffering() : void {
		if ( ! $this->get_is_cacheable() ) {
			return;
		}

		ob_start();
	}

	/**
	 * Register function to be executed when PHP has finished processing.
	 *
	 * @return void
	 */
	public function register_shutdown() : void {
		if ( ! $this->get_is_cacheable() ) {
			return;
		}

		// Get page content.
		$cache = ob_get_contents();

		// Put an end to buffer.
		$this->end_buffering();

		// Set cache
		\wp_cache_set(
			$this->get_key(),
			$cache,
			$this->get_cache_group()
		);
	}

	/**
	 * Turn off output buffer.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	public function end_buffering() : void {
		if ( ! ob_get_length() ) {
			return;
		}

		/**
		 * Flushing because we need output. In case of no output, it's better
		 * to use ob_end_clean();
		 */
		ob_end_flush();
	}

	/**
	 * Flush cache with key.
	 *
	 * @param  string $key Cache key.
	 * @return bool
	 */
	public function flush_cache( $key ) : bool {
		return \wp_cache_delete( $key, $this->_cache_group );
	}

	/**
	 * Tell if the page content is HTML.
	 *
	 * @param  string $buffer The buffer content.
	 * @return bool
	 */
	public function is_html( $buffer ) : bool {
		$found = strpos( substr( $buffer, 0, 100 ), '<html' );

		if ( $found === 0 ) {
			return true;
		}

		return (bool) $found;
	}

	/**
	 * Returns whether the page can be cached or not.
	 *
	 * @return bool
	 */
	public function get_is_cacheable() : bool {
		return $this->_is_cacheable;
	}

	/**
	 * Returns cache buffer.
	 *
	 * @return null|string
	 */
	public function get_cache() {
		return $this->_cache;
	}

	/**
	 * Returns whether cache exists or not.
	 *
	 * @return bool
	 */
	public function get_cache_exists() : bool {
		return $this->_cache_exists;
	}

	/**
	 * Returns cache key.
	 *
	 * @return null|string
	 */
	public function get_key() {
		return $this->_key;
	}

	/**
	 * Returns cache group.
	 *
	 * @return string
	 */
	public function get_cache_group() : string {
		return $this->_cache_group;
	}

	/**
	 * Wrapper around the exit() function.
	 *
	 * @codeCoverageIgnore
	 */
	protected function terminate() {
		exit;
	}

}
