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

	use \WooCart\Log\Socket;

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
		 * @var bool
		 */
		public $forced_activation = false;

		/**
		 * Holds configurable array of strings.
		 * Default values are added in the constructor.
		 *
		 * @var array
		 */
		public $strings = [];

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

			// Log messages on plugin activation and de-activation.
			// Plugin activation.
			add_action( 'activated_plugin', [ &$this, 'activation' ], 10, 2 );

			// Plugin de-activation.
			add_action( 'deactivated_plugin', [ &$this, 'deactivation' ], 10, 2 );
		}

		/**
		 * Initialise the magic.
		 */
		public function init() {
			// Load class strings.
			$this->strings = [
				/* translators: 1: plugin name(s). */
				'notice_can_install_required'  => _n_noop(
					'WooCart requires the following plugin: %1$s.',
					'WooCart requires the following plugins: %1$s.',
					'woocart-defaults'
				),
				/* translators: 1: plugin name(s). */
				'notice_ask_to_update'         => _n_noop(
					'The following plugin needs to be updated to its latest version to ensure maximum compatibility with WooCart: %1$s.',
					'The following plugins need to be updated to their latest version to ensure maximum compatibility with WooCart: %1$s.',
					'woocart-defaults'
				),
				/* translators: 1: plugin name(s). */
				'notice_ask_to_update_maybe'   => _n_noop(
					'There is an update available for: %1$s.',
					'There are updates available for the following plugins: %1$s.',
					'woocart-defaults'
				),
				/* translators: 1: plugin name(s). */
				'notice_can_activate_required' => _n_noop(
					'The following required plugin is currently inactive: %1$s.',
					'The following required plugins are currently inactive: %1$s.',
					'woocart-defaults'
				),
			];

			// Check for the admin panel.
			if ( true !== ( is_admin() && ! defined( 'DOING_AJAX' ) ) ) {
				return;
			}

			// Register plugins.
			foreach ( $this->list as $plugin ) {
				// @codeCoverageIgnoreStart
				$this->register( $plugin );
				// @codeCoverageIgnoreEnd
			}

			// Proceed only if we have plugins to handle.
			if ( empty( $this->plugins ) || ! is_array( $this->plugins ) ) {
				return;
			}

			if ( true !== $this->is_complete() ) {
				// Set up the menu and notices if we still have outstanding actions.
				add_action( 'admin_notices', [ $this, 'notices' ] );
				add_action( 'admin_enqueue_scripts', [ $this, 'thickbox' ] );
			}

			// Setup the force activation hook.
			if ( true === $this->forced_activation ) {
				add_action( 'admin_init', [ $this, 'force_activation' ] );
			}
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
		 * Determine whether there are open actions for plugins.
		 *
		 * @return bool
		 */
		public function is_complete() {
			$complete = true;

			foreach ( $this->plugins as $slug => $plugin ) {
				if ( ! $this->is_plugin_active( $slug ) || false !== $this->does_plugin_have_update( $slug ) ) {
					$complete = false;
					break;
				}
			}

			return $complete;
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

			return ( ! empty( $installed_plugins[ $this->plugins[ $slug ]['file_path'] ] ) );
		}

		/**
		 * Check whether there is an update available for a plugin.
		 *
		 * @param string $slug Plugin slug.
		 * @return false|string Version number string of the available update or false if no update available.
		 */
		public function does_plugin_have_update( $slug ) {
			$updates = get_site_transient( 'update_plugins' );

			if ( isset( $this->plugins[ $slug ] ) ) {
				if ( isset( $updates->response[ $this->plugins[ $slug ]['file_path'] ]->new_version ) ) {
					return $updates->response[ $this->plugins[ $slug ]['file_path'] ]->new_version;
				}
			}

			return false;
		}

		/**
		 * Echoes required plugin notice.
		 *
		 * @global object $current_screen
		 * @return null Returns early if we're on the Install page.
		 *
		 * @codeCoverageIgnore
		 */
		public function notices() {
			// Remove nag on the install page.
			if ( ( $this->is_core_update_page() ) || ! current_user_can( 'publish_posts' ) ) {
				return;
			}

			// Store for the plugin slugs by message type.
			$message = [];

			// Initialize counters used to determine plurality of action link texts.
			$install_link_count          = 0;
			$update_link_count           = 0;
			$activate_link_count         = 0;
			$total_required_action_count = 0;

			foreach ( $this->plugins as $slug => $plugin ) {
				if ( $this->is_plugin_active( $slug ) && false === $this->does_plugin_have_update( $slug ) ) {
					continue;
				}

				if ( ! $this->is_plugin_installed( $slug ) ) {
					if ( current_user_can( 'install_plugins' ) ) {
						$install_link_count++;
						$message['notice_can_install_required'][] = $slug;
					}

					$total_required_action_count++;
				} else {
					if ( ! $this->is_plugin_active( $slug ) && $this->can_plugin_activate( $slug ) ) {
						if ( current_user_can( 'activate_plugins' ) ) {
							$activate_link_count++;
							$message['notice_can_activate_required'][] = $slug;
						}

						$total_required_action_count++;
					}

					if ( $this->does_plugin_require_update( $slug ) || false !== $this->does_plugin_have_update( $slug ) ) {
						if ( current_user_can( 'update_plugins' ) ) {
							$update_link_count++;

							if ( $this->does_plugin_require_update( $slug ) ) {
								$message['notice_ask_to_update'][] = $slug;
							} elseif ( false !== $this->does_plugin_have_update( $slug ) ) {
								$message['notice_ask_to_update_maybe'][] = $slug;
							}
						}

						if ( true === $plugin['required'] ) {
							$total_required_action_count++;
						}
					}
				}
			}

			unset( $slug, $plugin );

			// If we have notices to display, we move forward.
			if ( ! empty( $message ) || $total_required_action_count > 0 ) {
				// Sort messages.
				krsort( $message );
				$rendered = '';

				// As add_settings_error() wraps the final message in a <p> and as the final message can't be
				// filtered, using <p>'s in our html would render invalid html output.
				$line_template = '<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">%s</span>' . "\n";

				if ( ! current_user_can( 'activate_plugins' ) && ! current_user_can( 'install_plugins' ) && ! current_user_can( 'update_plugins' ) ) {
					$rendered = esc_html__( 'There are one or more required plugins to install, update or activate. Please contact the administrator of this site for help.', 'woocart-defaults' );
				} else {
					// Render the individual message lines for the notice.
					foreach ( $message as $type => $plugin_group ) {
						$linked_plugins = [];

						// Get the external info link for a plugin if one is available.
						foreach ( $plugin_group as $plugin_slug ) {
							$linked_plugins[] = $this->get_info_link( $plugin_slug );
						}

						unset( $plugin_slug );

						$count       = count( $plugin_group );
						$last_plugin = array_pop( $linked_plugins ); // Pop off last name to prep for readability.
						$imploded    = empty( $linked_plugins ) ? $last_plugin : ( implode( ', ', $linked_plugins ) . ' ' . esc_html_x( 'and', 'plugin A *and* plugin B', 'woocart-defaults' ) . ' ' . $last_plugin );

						$rendered .= sprintf(
							$line_template,
							sprintf(
								translate_nooped_plural( $this->strings[ $type ], $count, 'woocart-defaults' ),
								$imploded,
								$count
							)
						);
					}

					unset( $type, $plugin_group, $linked_plugins, $count, $last_plugin, $imploded );
				}

				// Register the nag messages and prepare them to be processed.
				add_settings_error( 'wc_plugins', 'wc_plugins', $rendered, 'notice-warning' );
			}

			// Admin options pages already output settings_errors, so this is to avoid duplication.
			if ( 'options-general' !== $GLOBALS['current_screen']->parent_base ) {
				$this->settings_errors();
			}
		}

		/**
		 * Display settings errors.
		 */
		protected function settings_errors() {
			global $wp_settings_errors;

			settings_errors( 'wc_plugins' );

			foreach ( (array) $wp_settings_errors as $key => $details ) {
				if ( 'wc_plugins' === $details['setting'] ) {
					unset( $wp_settings_errors[ $key ] );
					break;
				}
			}
		}

		/**
		 * Determine if we're on a WP Core installation/upgrade page.
		 *
		 * @return boolean True when on a WP Core installation/upgrade page, false otherwise.
		 */
		protected function is_core_update_page() {
			// Current screen is not always available, most notably on the customizer screen.
			if ( ! function_exists( 'get_current_screen' ) ) {
				return false;
			}

			$screen = get_current_screen();

			if ( 'update-core' === $screen->base ) {
				// Core update screen.
				return true;
			} elseif ( 'plugins' === $screen->base && ! empty( $_POST['action'] ) ) {
				// Plugins bulk update screen.
				return true;
			} elseif ( 'update' === $screen->base && ! empty( $_POST['action'] ) ) {
				// Individual updates (ajax call).
				return true;
			}

			return false;
		}

		/**
		 * Check if a plugin can be activated, i.e. is not currently active and meets the minimum
		 * plugin version requirements set in TGMPA (if any).
		 *
		 * @param string $slug Plugin slug.
		 * @return bool True if OK to activate, false otherwise.
		 */
		public function can_plugin_activate( $slug ) {
			return ( ! $this->is_plugin_active( $slug ) && ! $this->does_plugin_require_update( $slug ) );
		}

		/**
		 * Check whether a plugin complies with the minimum version requirements.
		 *
		 * @param string $slug Plugin slug.
		 * @return bool True when a plugin needs to be updated, otherwise false.
		 */
		public function does_plugin_require_update( $slug ) {
			$installed_version = $this->get_installed_version( $slug );
			$minimum_version   = $this->plugins[ $slug ]['version'];

			return version_compare( $minimum_version, $installed_version, '>' );
		}

		/**
		 * Retrieve the version number of an installed plugin.
		 *
		 * @param string $slug Plugin slug.
		 * @return string Version number as string or an empty string if the plugin is not installed
		 *                or version unknown (plugins which don't comply with the plugin header standard).
		 */
		public function get_installed_version( $slug ) {
			$installed_plugins = $this->get_plugins(); // Retrieve a list of all installed plugins (WP cached).

			if ( isset( $this->plugins[ $slug ] ) ) {
				if ( ! empty( $installed_plugins[ $this->plugins[ $slug ]['file_path'] ]['Version'] ) ) {
					return $installed_plugins[ $this->plugins[ $slug ]['file_path'] ]['Version'];
				}
			}

			return '';
		}

		/**
		 * Retrieve a link to a plugin information page.
		 *
		 * @param string $slug Plugin slug.
		 * @return string Fully formed html link to a plugin information page if available
		 *                or the plugin name if not.
		 */
		public function get_info_link( $slug ) {
			$url = add_query_arg(
				[
					'tab'       => 'plugin-information',
					'plugin'    => urlencode( $slug ),
					'TB_iframe' => 'true',
					'width'     => '640',
					'height'    => '500',
				],
				self_admin_url( 'plugin-install.php' )
			);

			$link = sprintf(
				'<a href="%1$s" class="thickbox">%2$s</a>',
				esc_url( $url ),
				esc_html( $this->plugins[ $slug ]['name'] )
			);

			return $link;
		}

		/**
		 * Enqueue thickbox scripts/styles for plugin info.
		 *
		 * Thickbox is not automatically included on all admin pages, so we must
		 * manually enqueue it for those pages. Thickbox is only loaded if there are
		 * any plugins left to install and activate.
		 */
		public function thickbox() {
			add_thickbox();
		}

		/**
		 * Forces plugin activation if the parameter 'force_activation' is
		 * set to true.
		 */
		public function force_activation() {
			foreach ( $this->plugins as $slug => $plugin ) {
				if ( true === $plugin['force_activation'] ) {
					if ( ! $this->is_plugin_installed( $slug ) ) {
						// Oops, plugin isn't there so iterate to next condition.
						continue;
					} elseif ( $this->can_plugin_activate( $slug ) ) {
						// There we go, activate the plugin.
						activate_plugin( $plugin['file_path'] );
					}
				}
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
				'name'             => '',      // String.
				'slug'             => '',      // String.
				'required'         => true,    // Boolean.
				'version'          => '',      // String.
				'force_activation' => false,   // Boolean.
			];

			// Prepare the received data.
			$plugin = wp_parse_args( $plugin, $defaults );

			// Standardize the received slug.
			$plugin['slug']             = sanitize_key( $plugin['slug'] );
			$plugin['required']         = $plugin['required'];
			$plugin['version']          = (string) $plugin['version'];
			$plugin['force_activation'] = $plugin['force_activation'];

			// Enrich the received data.
			$plugin['file_path'] = $this->_get_plugin_basename_from_slug( $plugin['slug'] );

			// Set the class properties.
			$this->plugins[ $plugin['slug'] ] = $plugin;

			// Should we add the force activation hook ?
			if ( true === $plugin['force_activation'] ) {
				$this->forced_activation = true;
			}
		}

		/**
		 * Log messages on plugin activation.
		 */
		public function activation( $plugin_file, $network_wide ) {
			// Get plugin data using the plugin file path.
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file, false );

			if ( $plugin_data ) {
				if ( is_array( $plugin_data ) ) {
					$emit_data = [
						'kind'    => 'plugin_change',
						'name'    => $plugin_data['Name'],
						'version' => $plugin_data['Version'],
						'action'  => 'activate',
					];

					Socket::log( $emit_data );
				}
			}
		}

		/**
		 * Log messages on plugin de-activation.
		 */
		public function deactivation( $plugin_file, $network_wide ) {
			// Get plugin data using the plugin file path.
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file, false );

			if ( $plugin_data ) {
				if ( is_array( $plugin_data ) ) {
					$emit_data = [
						'kind'    => 'plugin_change',
						'name'    => $plugin_data['Name'],
						'version' => $plugin_data['Version'],
						'action'  => 'deactivate',
					];

					Socket::log( $emit_data );
				}
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
