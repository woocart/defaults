<?php
/**
 * Handles content for the admin dashboard panel.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      1.0.0
 */

namespace Niteo\WooCart\Defaults {


	/**
	 * Class AdminDashboard
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class AdminDashboard {

		/**
		 * AdminDashboard constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'init' ) );
			add_action( 'wp_login', array( $this, 'track' ) );
		}

		/**
		 * Get fired once the admin panel initializes.
		 */
		public function init() {
			if ( ! is_admin() ) {
				return;
			}

			remove_action( 'welcome_panel', 'wp_welcome_panel' );
			add_action( 'welcome_panel', array( &$this, 'welcome_panel' ) );

			// add thickbox to the dashboard page
			add_thickbox();
		}

		/**
		 * Our customized welcome panel for the store.
		 *
		 * @codeCoverageIgnore
		 */
		public function welcome_panel() {

			include __DIR__ . '/templates/welcome-woocart.php';

		}

	}
}
