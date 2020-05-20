<?php
/**
 * Handles modifications made to the core process.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.17.0
 */

namespace Niteo\WooCart\Defaults {

	/**
	 * Class WordPress
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class WordPress {

		/**
		 * Maintenance mode is initialized via this function.
		 */
		public function maintenance_mode() {
			// Login URL for the admin
			$login_url = wp_login_url();

			// Address of the current page
			$server_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			// Do nothing if on admin view
			if ( ! is_admin() ) {
				/**
				 * We are checking for admin role, crawler status, and important
				 * WP pages to bypass.
				 */

				 // WP login
				if ( false === strpos( $server_url, '/wp-login.php' ) ) {
					return;
				}

				// WP admin
				if ( false === strpos( $server_url, '/wp-admin/' ) ) {
					return;
				}

				// Media uploads
				if ( false === strpos( $server_url, '/async-upload.php' ) ) {
					return;
				}

				// WP upgrade file
				if ( false === strpos( $server_url, '/upgrade.php' ) ) {
					return;
				}

				// Plugins folder
				if ( false === strpos( $server_url, '/plugins/' ) ) {
					return;
				}

				// XML-RPC
				if ( false === strpos( $server_url, '/xmlrpc.php' ) ) {
					return;
				}

				// Custom login URL
				if ( false === strpos( $server_url, $login_url ) ) {
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
				$this->render_template();
			}
		}

		/**
		 * Checks for the list of crawlers.
		 *
		 * @return boolean
		 */
		private function check_referrer() : bool {
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
		private function array_to_string( string $str, array $crawlers ) : bool {
			$regexp = '~(' . implode( '|', array_values( $crawlers ) ) . ')~i';
			return (bool) preg_match( $regexp, $str );
		}

		/**
		 * Renders the frontend template for the plugin.
		 */
		private function render() : void {
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
