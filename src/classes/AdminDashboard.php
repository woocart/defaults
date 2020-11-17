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


		use Extend\Proteus;

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
			if ( is_admin() ) {
				remove_action( 'welcome_panel', 'wp_welcome_panel' );

				// check if the proteus theme is active
				if ( $this->is_proteus_active() ) {
					add_action( 'welcome_panel', array( &$this, 'proteus_welcome_panel' ) );
				} else {
					add_action( 'welcome_panel', array( &$this, 'welcome_panel' ) );
				}

				// add thickbox to the dashboard page
				add_thickbox();
			}
		}


		/**
		 * Track user login to store.
		 *
		 * @codeCoverageIgnore
		 */
		public function track(): bool {
			if ( ! $this->is_proteus_active() ) {
				return false;
			}

			wp_remote_post(
				'https://app.woocart.com/api/v1/lead/track/',
				array(
					'method'      => 'POST',
					'timeout'     => 30,
					'headers'     => array(
						'Content-Type' => 'application/json',
					),
					'body'        => wp_json_encode(
						array(
							'storeId' => $_SERVER['STORE_ID'],
						)
					),
					'data_format' => 'body',
				)
			);
			return true;
		}


		/**
		 * Our customized welcome panel for the store.
		 *
		 * @codeCoverageIgnore
		 */
		public function welcome_panel() {

			include __DIR__ . '/templates/welcome-woocart.php';

		}

		/**
		 * Welcome panel for the proteus theme setup.
		 *
		 * @codeCoverageIgnore
		 */
		public function proteus_welcome_panel() {

			include __DIR__ . '/templates/welcome-proteus.php';
		}

		/**
		 * For generating the proteus purchase link.
		 */
		public function purchase_link( $utm_medium = '' ) {
			$theme_slug        = get_template();
			$landing_page_slug = preg_replace( '/-[pc]t$/', '', $theme_slug );

			return sprintf( 'https://proteusthemes.onfastspring.com/%1$s-wp?utm_source=woocart&utm_medium=%2$s&utm_campaign=woocart&utm_content=%1$s', $landing_page_slug, $utm_medium );
		}
	}
}
