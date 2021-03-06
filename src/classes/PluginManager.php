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

		use Extend\Notifications;

		/**
		 * List of plugins.
		 *
		 * @var array
		 */
		public $list = array();

		/**
		 * Holds arrays of plugin details.
		 *
		 * @var array
		 */
		public $plugins = array();

		/**
		 * Hold paths to plugin main file.
		 *
		 * @var array
		 */
		public $paths = array();

		/**
		 * @var array
		 */
		private $notification = array(
			'term'    => '',
			'matches' => false,
			'message' => '',
		);

		/**
		 * PluginManager constructor.
		 */
		public function __construct() {
			add_action( 'init', array( &$this, 'init' ) );

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

			// Show notification on plugin search for specific keywords
			add_filter( 'plugins_api_args', array( $this, 'search_notification' ), 10, 2 );

			// Remove redis-cache settings link for menu & plugins page
			add_action( 'admin_menu', array( $this, 'remove_redis_menu' ), PHP_INT_MAX );
			add_filter( 'plugin_action_links_redis-cache/redis-cache.php', array( &$this, 'remove_redis_plugin_links' ), PHP_INT_MAX );
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
			add_action( 'admin_init', array( &$this, 'force_activation' ) );

			// Execute other functions.
			add_action( 'current_screen', array( &$this, 'plugins_page' ) );
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
					continue;
				}

				if ( ! $this->is_plugin_active( $slug ) ) {
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

			// Only on plugins page
			if ( 'plugins' !== $screen->id ) {
				return;
			}

			// Filter out the deactivate link for required plugins.
			add_filter( 'plugin_action_links', array( &$this, 'remove_deactivation_link' ), PHP_INT_MAX, 4 );

			// Add a small text to let the user know that why the plugin cannot be de-activated.
			add_action( 'after_plugin_row', array( &$this, 'add_required_text' ), PHP_INT_MAX, 3 );
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

			$defaults = array(
				'name'      => '',      // String.
				'slug'      => '',      // String.
				'file_path' => '',           // String.
			);

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
			if ( ! in_array( $plugin_file, $this->paths ) ) {
				return;
			}

			// Check again version 5.5.0
			if ( $this->_wp_version() ) {
				echo '<tr><td colspan="4" style="background:#fcd670"><strong>' . $plugin_data['Name'] . '</strong> is a required plugin on WooCart and cannot be deactivated.</td></tr>';
				return;
			}

			echo '<tr><td colspan="3" style="background:#fcd670"><strong>' . $plugin_data['Name'] . '</strong> is a required plugin on WooCart and cannot be deactivated.</td></tr>';
		}

		/**
		 * Removes reds-cache submenu page under the settings menu.
		 */
		public function remove_redis_menu() {
			remove_submenu_page( 'options-general.php', 'redis-cache' );
		}

		/**
		 * Remove `Settings` link for the redis cache plugin.
		 *
		 * @param array  $links Array of links for the plugins
		 * @param string $file  Name of the main plugin file
		 *
		 * @return array
		 */
		public function remove_redis_plugin_links( $links ) {
			// Remove the first link (which is the `Settings` link)
			unset( $links[0] );

			return $links;
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

		/**
		 * Check for WP version.
		 *
		 * @param string $version WP version to compare against
		 * @return boolean
		 */
		private function _wp_version( string $version = '5.5.0' ) : bool {
			if ( version_compare( get_bloginfo( 'version' ), $version, '>=' ) ) {
				return true;
			}

			return false;
		}

	}
}
