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
			add_action( 'template_redirect', array( $this, 'maintenance_mode' ) );
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

					/**
					 * Throw 418 error code which is handled by `woocart-default-backend`
					 *
					 * @see https://github.com/niteoweb/woocart-default-backend/blob/master/html/index.html
					 */
					status_header( 418 );
					$this->terminate();
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
		 * Wrapper around the exit() function.
		 *
		 * @codeCoverageIgnore
		 */
		protected function terminate() {
			exit;
		}

	}

}
