<?php
/**
 * Handles plugin management for the store.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.7.4
 */

namespace Niteo\WooCart\Defaults {


	/**
	 * Class PluginManager.
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class PluginManager {

		/**
		 * List of plugins.
		 *
		 * @var array
		 */
		public $list = [];

		/**
		 * Holds arrays of plugin details.
		 *
		 * @var array
		 */
		public $plugins = [];

		/**
		 * Hold paths to plugin main file.
		 *
		 * @var array
		 */
		public $paths = [];

		/**
		 * PluginManager constructor.
		 */
		public function __construct() {
			add_action( 'init', [ &$this, 'init' ] );

			/**
			 * Check for the plugins list.
			 * WOOCART_REQUIRED is defined in the wp-config.php file.
			 * Multi-dimensional array with each array having name & slug of the plugin.
			 *
			 * Example:
			 * [
			 *      [
			 *          "name" => "Plugin Name",
			 *          "slug" => "plugin-slug"
			 *      ]
			 * ]
			 *
			 * @see https://github.com/niteoweb/woocart-docker-web/blob/master/bin/runtime/phases/phase_21-wp-config
			 */
			if ( defined( 'WOOCART_REQUIRED' ) ) {
				$this->list = WOOCART_REQUIRED;
			}
		}

		/**
		 * Initialise the magic.
		 */
		public function init() {
			// Check for the admin panel.
			if ( true !== ( is_admin() && ! defined( 'DOING_AJAX' ) ) ) {
				return;
			}

			// Register plugins.
			foreach ( $this->list as $plugin ) {
				$this->register( $plugin );
			}

			// Proceed only if we have plugins to handle.
			if ( empty( $this->plugins ) || ! is_array( $this->plugins ) ) {
				return;
			}

			// Force activate required plugins.
			add_action( 'admin_init', [ &$this, 'force_activation' ] );

			// Execute other functions.
			add_action( 'current_screen', [ &$this, 'plugins_page' ] );
		}

		/**
		 * Wrapper around the core WP get_plugins function, making sure it's actually available.
		 *
		 * @param string $plugin_folder Optional. Relative path to single plugin folder.
		 * @return array Array of installed plugins with plugin information.
		 *
		 * @codeCoverageIgnore
		 */
		public function get_plugins( $plugin_folder = '' ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return get_plugins( $plugin_folder );
		}

		/**
		 * Check if a plugin is active.
		 *
		 * @param string $slug Plugin slug.
		 * @return bool True if active, false otherwise.
		 */
		public function is_plugin_active( $slug ) {
			if ( isset( $this->plugins[ $slug ] ) ) {
				return is_plugin_active( $this->plugins[ $slug ]['file_path'] );
			}

			return false;
		}

		/**
		 * Check if a plugin is installed. Does not take must-use plugins into account.
		 *
		 * @param string $slug Plugin slug.
		 * @return bool True if installed, false otherwise.
		 */
		public function is_plugin_installed( $slug ) {
			// Retrieve a list of all installed plugins (WP cached).
			$installed_plugins = $this->get_plugins();

			// Ensure we have plugin in the array.
			if ( isset( $this->plugins[ $slug ] ) ) {
				return ( ! empty( $installed_plugins[ $this->plugins[ $slug ]['file_path'] ] ) );
			}

			return false;
		}

		/**
		 * Forces plugin activation.
		 */
		public function force_activation() {
			foreach ( $this->plugins as $slug => $plugin ) {
				if ( ! $this->is_plugin_installed( $slug ) ) {
					// Oops, plugin isn't there so iterate to next condition.
					continue;
				} elseif ( ! $this->is_plugin_active( $slug ) ) {
					// There we go, activate the plugin.
					activate_plugin( $plugin['file_path'] );
				}
			}
		}

		/**
		 * Execute other functions on the plugins.php page
		 */
		public function plugins_page() {
			// Get the current screen to ensure that the functions only get executed
			// on the plugins.php page
			$screen = get_current_screen();

			// Only on plugins page.
			if ( 'plugins' === $screen->id ) {
				// Filter out the deactivate link for required plugins.
				add_filter( 'plugin_action_links', [ &$this, 'remove_deactivation_link' ], PHP_INT_MAX, 4 );

				// Add a small text to let the user know that why the plugin cannot be de-activated.
				add_action( 'after_plugin_row', [ &$this, 'add_required_text' ], PHP_INT_MAX, 3 );
			}
		}

		/**
		 * Add individual plugin to the collection of plugins.
		 *
		 * If the required keys are not set or the plugin has already
		 * been registered, the plugin is not added.
		 *
		 * @param array|null $plugin Array of plugin arguments or null if invalid argument.
		 * @return null Return early if incorrect argument.
		 */
		public function register( $plugin ) {
			if ( empty( $plugin['slug'] ) || empty( $plugin['name'] ) ) {
				return;
			}

			$defaults = [
				'name'      => '',      // String.
				'slug'      => '',      // String.
				'file_path' => '',           // String.
			];

			// Prepare the received data.
			$plugin = wp_parse_args( $plugin, $defaults );

			// Standardize the received slug.
			$plugin['slug'] = sanitize_key( $plugin['slug'] );

			// Enrich the received data.
			$plugin['file_path'] = $this->_get_plugin_basename_from_slug( $plugin['slug'] );

			// Add to $paths if not empty.
			if ( ! empty( $plugin['file_path'] ) ) {
				$this->paths[] = $plugin['file_path'];
			}

			// Set the class properties.
			$this->plugins[ $plugin['slug'] ] = $plugin;
		}

		/**
		 * Filter function to remove deactivation links from the required plugins
		 * on the plugins.php page.
		 *
		 * @param array  $actions Plugin actions such as deactivate, edit.
		 * @param string $plugin_file Path to the plugin main file relative to the plugins directory.
		 *
		 * @return array
		 */
		public function remove_deactivation_link( $actions, $plugin_file ) {
			// Remove deactivate link for important plugins.
			if ( array_key_exists( 'deactivate', $actions ) && in_array( $plugin_file, $this->paths ) ) {
				unset( $actions['deactivate'] );
			}

			return $actions;
		}

		/**
		 * Add required text below the plugin row on the page.
		 *
		 * @param string $plugin_file Path to the plugin main file relative to the plugins directory.
		 * @param array  $plugin_data Plugin data such as name, description, etc.
		 *
		 * @return void
		 */
		public function add_required_text( $plugin_file, $plugin_data ) {
			if ( in_array( $plugin_file, $this->paths ) ) {
				echo '<tr><td colspan="3" style="background:#fcd670"><strong>' . $plugin_data['Name'] . '</strong> is a required plugin on WooCart and cannot be deactivated.</td></tr>';
			}
		}

		/**
		 * Helper function to extract the file path of the plugin file from the
		 * plugin slug, if the plugin is installed.
		 *
		 * @param string $slug Plugin slug (typically folder name) as provided by the developer.
		 * @return string Either file path for plugin if installed, or just the plugin slug.
		 */
		protected function _get_plugin_basename_from_slug( $slug ) {
			$keys = array_keys( $this->get_plugins() );

			foreach ( $keys as $key ) {
				if ( preg_match( '|^' . $slug . '/|', $key ) ) {
					return $key;
				}
			}

			return $slug;
		}

	}
}
