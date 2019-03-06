<?php
/**
 * Handles sending messages to the logger.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.7.5
 */

namespace Niteo\WooCart\Defaults {

	use \WooCart\Log\Socket;

	/**
	 * Class Logger.
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class Logger {

		public function __construct() {
			// Plugin activation.
			add_action( 'activated_plugin', [ &$this, 'activation' ], 10, 2 );

			// Plugin de-activation.
			add_action( 'deactivated_plugin', [ &$this, 'deactivation' ], 10, 2 );
		}

		/**
		 * Log messages on plugin activation.
		 */
		public function activation( $plugin_file, $network_wide ) {
			// Pass params to the log function.
			return $this->plugin_status_change( $plugin_file, 'activate' );
		}

		/**
		 * Log messages on plugin de-activation.
		 */
		public function deactivation( $plugin_file, $network_wide ) {
			// Pass params to the log function.
			return $this->plugin_status_change( $plugin_file, 'deactivate' );
		}

		/**
		 * Send message to the logger on plugin status change.
		 *
		 * @param string $plugin_file String containing relative path to the main plugin file.
		 * @param string $status Define whether plugin is activated or de-activated.
		 */
		public function plugin_status_change( $plugin_file, $status ) {
			// Get plugin data using the plugin file path.
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file, false );

			if ( $plugin_data ) {
				if ( is_array( $plugin_data ) ) {
					$emit_data = [
						'kind'    => 'plugin_change',
						'name'    => $plugin_data['Name'],
						'version' => $plugin_data['Version'],
						'action'  => $status,
					];

					Socket::log( $emit_data );

					return true;
				}
			}

			return false;
		}

	}

}
