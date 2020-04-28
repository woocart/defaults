<?php

/**
 * Dashboard functionality for the proteus theme setup.
 */

namespace Niteo\WooCart\Defaults\Extend {

	trait Dashboard {


		/**
		 * Checks if custom dashboard is enabled and we should redirect to it.
		 *
		 * @return boolean
		 */
		public function is_dashboard_active(): bool {

			// Check if customization is disabled
			if ( 'yes' === get_option( '_hide_woocart_dashboard', 'no' ) ) {
				return false;
			}

			// Check for WooCommerce
			if ( ! defined( 'WC_VERSION' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Checks if this is staging env.
		 *
		 * @return boolean
		 */
		public function is_staging(): bool {
			// Check if customization is disabled
			return $_SERVER['STORE_STAGING'] === 'yes';
		}

		/**
		 * Checks if this is live env.
		 *
		 * @return boolean
		 */
		public function is_live(): bool {
			// Check if customization is disabled
			return $_SERVER['STORE_STAGING'] === 'no';
		}


	}

}
