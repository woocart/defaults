<?php
/**
 * Handles maintenance page for the store.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.17.0
 */

namespace Niteo\WooCart\Defaults {

	/**
	 * Class MaintenanceMode
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class MaintenanceMode {

		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		/**
		 * Initializes the maintenance page.
		 */
		public function init() : void {
			add_action( 'init', array( $this, 'maintenance_mode' ) );
		}

		/**
		 * Perform checks to determine if the maintenance page needs to be
		 * rendered instead of regular store.
		 */
		public function maintenance_mode() {
			// Check option to determine the status
			if ( get_option( 'woocart_maintenance_mode', false ) ) {
				// Login URL for the admin
				$login_url = wp_login_url();

				// Address of the current page
				$url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				if ( ! is_admin() ) {
					/**
					 * We are checking for admin role, crawler status, and important
					 * WP pages to bypass.
					 */

					// WP login
					if ( false !== strpos( $url, '/wp-login.php' ) ) {
						return;
					}

					// WP admin
					if ( false !== strpos( $url, '/wp-admin/' ) ) {
						return;
					}

					// Media uploads
					if ( false !== strpos( $url, '/async-upload.php' ) ) {
						return;
					}

					// WP upgrade file
					if ( false !== strpos( $url, '/upgrade.php' ) ) {
						return;
					}

					// Plugins folder
					if ( false !== strpos( $url, '/plugins/' ) ) {
						return;
					}

					// XML-RPC
					if ( false !== strpos( $url, '/xmlrpc.php' ) ) {
						return;
					}

					// Custom login URL
					if ( false !== strpos( $url, $login_url ) ) {
						return;
					}

					// CLI
					if ( defined( 'WP_CLI' ) && WP_CLI ) {
						return;
					}

					// Checking for crawlers
					if ( $this->check_referrer() ) {
						return;
					}

					// Logged-in as admin
					if ( is_user_logged_in() ) {
						if ( current_user_can( 'manage_options' ) ) {
							return;
						}
					}

					// Render the maintenance mode template
					$this->render();
				}
			}
		}

		/**
		 * Checks for the list of crawlers.
		 *
		 * @return boolean
		 */
		public function check_referrer() : bool {
			// Crawlers
			$crawlers = array(
				'Abacho'          => 'AbachoBOT',
				'Accoona'         => 'Acoon',
				'AcoiRobot'       => 'AcoiRobot',
				'Adidxbot'        => 'adidxbot',
				'AltaVista robot' => 'Altavista',
				'Altavista robot' => 'Scooter',
				'ASPSeek'         => 'ASPSeek',
				'Atomz'           => 'Atomz',
				'Bing'            => 'bingbot',
				'BingPreview'     => 'BingPreview',
				'CrocCrawler'     => 'CrocCrawler',
				'Dumbot'          => 'Dumbot',
				'eStyle Bot'      => 'eStyle',
				'FAST-WebCrawler' => 'FAST-WebCrawler',
				'GeonaBot'        => 'GeonaBot',
				'Gigabot'         => 'Gigabot',
				'Google'          => 'Googlebot',
				'ID-Search Bot'   => 'IDBot',
				'Lycos spider'    => 'Lycos',
				'MSN'             => 'msnbot',
				'MSRBOT'          => 'MSRBOT',
				'Rambler'         => 'Rambler',
				'Scrubby robot'   => 'Scrubby',
				'Yahoo'           => 'Yahoo',
			);

			// Verify if the request is coming from a Crawler
			if ( $this->array_to_string( $_SERVER['HTTP_USER_AGENT'], $crawlers ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Match the user agent with the crawlers array.
		 *
		 * @param string $str User agent
		 * @param array  $crawlers List of crawlers to match against
		 *
		 * @return boolean
		 */
		public function array_to_string( string $str, array $crawlers ) : bool {
			$regexp = '~(' . implode( '|', array_values( $crawlers ) ) . ')~i';
			return (bool) preg_match( $regexp, $str );
		}

		/**
		 * Renders the frontend template for the plugin.
		 *
		 * @codeCoverageIgnore
		 */
		public function render() : void {
			/**
			 * Using the nocache_headers() to ensure that no browser caches the
			 * maintenance page.
			 */
			nocache_headers();
			ob_start();

			// Template
			require_once __DIR__ . '/templates/maintenance-mode.php';

			ob_flush();
			exit;
		}

	}

}
