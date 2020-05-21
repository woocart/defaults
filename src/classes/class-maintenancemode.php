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
				// Address of the current page
				$url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

				if ( ! is_admin() ) {
					/**
					 * We are checking for admin role and WP pages to bypass.
					 */

					// WooCommerce Forgot Password
					if ( false !== strpos( $url, '/my-account/lost-password' ) ) {
						return;
					}

					// Logged-in as admin
					if ( is_user_logged_in() ) {
						if ( current_user_can( 'manage_options' ) ) {
							return;
						}
					}

					/**
					 * Throw 503 error code which is handled by `woocart-default-backend`
					 *
					 * @see https://github.com/niteoweb/woocart-default-backend/blob/master/html/index.html
					 */
					status_header( 503 );
					$this->terminate();
				}
			}
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
