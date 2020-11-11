<?php

namespace Niteo\WooCart\Defaults {

	/**
	 * Class WooCommerce
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class WooCommerce {

		/**
		 * @var array
		 */
		private const SMALLER_PLANS = array(
			'cart',
			'shop',
			'dev',
			'cartv2',
		);

		public function __construct() {
			add_filter( 'woocommerce_general_settings', array( &$this, 'general_settings' ) );

			// Disable WooCommerce admin plugin on lower plans
			add_filter( 'woocommerce_admin_disabled', array( $this, 'maybe_disable_wc_admin' ) );
		}

		/**
		 * Return updated options for the general settings page.
		 *
		 * @return array
		 */
		public function general_settings( $settings ) {
			$updated_settings = array();

			foreach ( $settings as $section ) {
				// Check for general settings
				if ( isset( $section['id'] ) && 'general_options' == $section['id'] && isset( $section['type'] ) && 'sectionend' == $section['type'] ) {
					$option = array(
						'name'     => esc_html__( 'Business Name', 'woocart-defaults' ),
						'desc_tip' => esc_html__( 'Name of the business used for operating the store.', 'woocart-defaults' ),
						'id'       => 'woocommerce_company_name',
						'type'     => 'text',
						'default'  => '',
					);

					// Add to the top of the options
					array_splice( $updated_settings, 1, 0, array( $option ) );
				}

				$updated_settings[] = $section;
			}

			return $updated_settings;
		}

		/**
		 * Checks for store plan and disables WC admin only for smaller plans.
		 *
		 * @return @bool
		 */
		public function maybe_disable_wc_admin() : bool {
			return in_array( $_SERVER['STORE_PLAN'], self::SMALLER_PLANS );
		}

	}

}
