<?php

namespace Niteo\WooCart\Defaults {

	/**
	 * Class WooCommerce
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class WooCommerce {

		public function __construct() {
			add_filter( 'woocommerce_general_settings', [ &$this, 'general_settings' ] );
		}

		/**
		 * Return updated options for the general settings page.
		 *
		 * @return array
		 */
		public function general_settings( $settings ) {
			$updated_settings = [];

			foreach ( $settings as $section ) {
				// Check for general settings
				if ( isset( $section['id'] ) && 'general_options' == $section['id'] && isset( $section['type'] ) && 'sectionend' == $section['type'] ) {
					$option = [
						'name'     => esc_html__( 'Business Name', 'woocart-defaults' ),
						'desc_tip' => esc_html__( 'Name of the business used for operating the store.', 'woocart-defaults' ),
						'id'       => 'woocommerce_company_name',
						'type'     => 'text',
						'default'  => '',
					];

					// Add to the top of the options
					array_splice( $updated_settings, 1, 0, [ $option ] );
				}

				$updated_settings[] = $section;
			}

			return $updated_settings;
		}

	}

}