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
		private $list = [
			[
				'name'     => 'Autoptimize',
				'slug'     => 'autoptimize',
				'required' => true,
			],
		];

		/**
		 * Holds arrays of plugin details.
		 *
		 * @var array
		 */
		private $plugins = [];

		/**
		 * @var bool
		 */
		private $forced_activation = false;

		/**
		 * Flag to determine if the user can dismiss the notice nag.
		 *
		 * @var boolean
		 */
		public $dismissable = true;

		/**
		 * Message to be output above nag notice if dismissable is false.
		 *
		 * @var string
		 */
		public $dismiss_msg = '';

		/**
		 * PluginManager constructor.
		 */
		public function __construct() {
			add_action( 'init', [ &$this, 'init' ] );
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

			// Set up the menu and notices if we still have outstanding actions.
			add_action( 'admin_notices', array( $this, 'notices' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'thickbox' ) );
			if ( true !== $this->is_complete() ) {
			}

			// Setup the force activation hook.
			if ( true === $this->forced_activation ) {
				add_action( 'admin_init', array( $this, 'force_activation' ) );
			}
		}

		/**
		 * Wrapper around the core WP get_plugins function, making sure it's actually available.
		 *
		 * @param string $plugin_folder Optional. Relative path to single plugin folder.
		 * @return array Array of installed plugins with plugin information.
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
			return ( ( ! empty( $this->plugins[ $slug ]['is_callable'] ) && is_callable( $this->plugins[ $slug ]['is_callable'] ) ) );
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

			if ( isset( $updates->response[ $this->plugins[ $slug ]['file_path'] ]->new_version ) ) {
				return $updates->response[ $this->plugins[ $slug ]['file_path'] ]->new_version;
			}

			return false;
		}

		/**
		 * Echoes required plugin notice.
		 *
		 * @global object $current_screen
		 * @return null Returns early if we're on the Install page.
		 */
		public function notices() {
			// Remove nag on the install page / Return early if the nag message has been dismissed or user < author.
			if ( ( $this->is_core_update_page() ) || get_user_meta( get_current_user_id(), 'wc_dismissed_notice_plugins', true ) || ! current_user_can( 'publish_posts' ) ) {
				return;
			}

			// Store for the plugin slugs by message type.
			$message = array();

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

						if ( true === $plugin['required'] ) {
							$message['notice_can_install_required'][] = $slug;
						} else {
							$message['notice_can_install_recommended'][] = $slug;
						}
					}

					if ( true === $plugin['required'] ) {
						$total_required_action_count++;
					}
				} else {
					if ( ! $this->is_plugin_active( $slug ) && $this->can_plugin_activate( $slug ) ) {
						if ( current_user_can( 'activate_plugins' ) ) {
							$activate_link_count++;

							if ( true === $plugin['required'] ) {
								$message['notice_can_activate_required'][] = $slug;
							} else {
								$message['notice_can_activate_recommended'][] = $slug;
							}
						}

						if ( true === $plugin['required'] ) {
							$total_required_action_count++;
						}
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
					$rendered  = esc_html__( 'There are one or more required or recommended plugins to install, update or activate. Please contact the administrator of this site for help.', 'woocart-defaults' );
					$rendered .= $this->create_user_action_links_for_notice( 0, 0, 0, $line_template );
				} else {

					// If dismissable is false and a message is set, output it now.
					if ( ! $this->dismissable && ! empty( $this->dismiss_msg ) ) {
						$rendered .= sprintf( $line_template, wp_kses_post( $this->dismiss_msg ) );
					}

					// Render the individual message lines for the notice.
					foreach ( $message as $type => $plugin_group ) {
						$linked_plugins = array();

						// Get the external info link for a plugin if one is available.
						foreach ( $plugin_group as $plugin_slug ) {
							$linked_plugins[] = $this->get_info_link( $plugin_slug );
						}
						unset( $plugin_slug );

						$count = count( $plugin_group );

						$last_plugin = array_pop( $linked_plugins ); // Pop off last name to prep for readability.
						$imploded    = empty( $linked_plugins ) ? $last_plugin : ( implode( ', ', $linked_plugins ) . ' ' . esc_html_x( 'and', 'plugin A *and* plugin B', 'woocart-defaults' ) . ' ' . $last_plugin );

						switch ( $type ) {
							case 'notice_can_install_required':
								$rendered .= sprintf(
									$line_template,
									sprintf(
										translate_nooped_plural(
											_n_noop(
												'WooCart requires the following plugin: %1$s.',
												'WooCart requires the following plugins: %1$s.',
												'woocart-defaults'
											),
											$count,
											'woocart-defaults'
										),
										$imploded,
										$count
									)
								);
								break;
							case 'notice_can_install_recommended':
								$rendered .= sprintf(
									$line_template,
									sprintf(
										translate_nooped_plural(
											_n_noop(
												'WooCart recommends the following plugin: %1$s.',
												'WooCart recommends the following plugins: %1$s.',
												'woocart-defaults'
											),
											$count,
											'woocart-defaults'
										),
										$imploded,
										$count
									)
								);
								break;
							case 'notice_can_activate_required':
								$rendered .= sprintf(
									$line_template,
									sprintf(
										translate_nooped_plural(
											_n_noop(
												'The following required plugin is currently inactive: %1$s.',
												'The following required plugins are currently inactive: %1$s.',
												'woocart-defaults'
											),
											$count,
											'woocart-defaults'
										),
										$imploded,
										$count
									)
								);
								break;
							case 'notice_can_activate_recommended':
								$rendered .= sprintf(
									$line_template,
									sprintf(
										translate_nooped_plural(
											_n_noop(
												'The following recommended plugin is currently inactive: %1$s.',
												'The following recommended plugins are currently inactive: %1$s.',
												'woocart-defaults'
											),
											$count,
											'woocart-defaults'
										),
										$imploded,
										$count
									)
								);
								break;
							case 'notice_ask_to_update':
								$rendered .= sprintf(
									$line_template,
									sprintf(
										translate_nooped_plural(
											_n_noop(
												'The following plugin needs to be updated to its latest version to ensure maximum compatibility with WooCart: %1$s.',
												'The following plugins need to be updated to their latest version to ensure maximum compatibility with WooCart: %1$s.',
												'woocart-defaults'
											),
											$count,
											'woocart-defaults'
										),
										$imploded,
										$count
									)
								);
								break;
							case 'notice_ask_to_update_maybe':
								$rendered .= sprintf(
									$line_template,
									sprintf(
										translate_nooped_plural(
											_n_noop(
												'There is an update available for: %1$s.',
												'There are updates available for the following plugins: %1$s.',
												'woocart-defaults'
											),
											$count,
											'woocart-defaults'
										),
										$imploded,
										$count
									)
								);
								break;
						}
					}

					unset( $type, $plugin_group, $linked_plugins, $count, $last_plugin, $imploded );
					$rendered .= $this->create_user_action_links_for_notice( $install_link_count, $update_link_count, $activate_link_count, $line_template );
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

			if ( ! empty( $installed_plugins[ $this->plugins[ $slug ]['file_path'] ]['Version'] ) ) {
				return $installed_plugins[ $this->plugins[ $slug ]['file_path'] ]['Version'];
			}

			return '';
		}

		/**
		 * Generate the user action links for the admin notice.
		 *
		 * @param int $install_count  Number of plugins to install.
		 * @param int $update_count   Number of plugins to update.
		 * @param int $activate_count Number of plugins to activate.
		 * @param int $line_template  Template for the HTML tag to output a line.
		 * @return string Action links.
		 */
		protected function create_user_action_links_for_notice( $install_count, $update_count, $activate_count, $line_template ) {
			// Setup action links.
			$action_links = array(
				'install'  => '',
				'update'   => '',
				'activate' => '',
				'dismiss'  => $this->dismissable ? '<a href="' . esc_url( wp_nonce_url( add_query_arg( 'wc-plugins-dismiss', 'dismiss_admin_notices' ), 'wc-plugins-dismiss-' . get_current_user_id() ) ) . '" class="dismiss-notice" target="_parent">' . esc_html__( 'Dismiss this notice', 'woocart-defaults' ) . '</a>' : '',
			);

			$link_template = '<a href="%2$s">%1$s</a>';

			if ( current_user_can( 'install_plugins' ) ) {
				if ( $install_count > 0 ) {
					$action_links['install'] = sprintf(
						$link_template,
						translate_nooped_plural(
							_n_noop(
								'Begin installing plugin',
								'Begin installing plugins',
								'woocart-defaults'
							),
							$install_count,
							'woocart-defaults'
						),
						esc_url( $this->get_install_status_url( 'install' ) )
					);
				}

				if ( $update_count > 0 ) {
					$action_links['update'] = sprintf(
						$link_template,
						translate_nooped_plural(
							_n_noop(
								'Begin updating plugin',
								'Begin updating plugins',
								'woocart-defaults'
							),
							$update_count,
							'woocart-defaults'
						),
						esc_url( $this->get_install_status_url( 'update' ) )
					);
				}
			}

			if ( current_user_can( 'activate_plugins' ) && $activate_count > 0 ) {
				$action_links['activate'] = sprintf(
					$link_template,
					translate_nooped_plural(
						_n_noop(
							'Begin activating plugin',
							'Begin activating plugins',
							'woocart-defaults'
						),
						$activate_count,
						'woocart-defaults'
					),
					esc_url( $this->get_install_status_url( 'activate' ) )
				);
			}

			$action_links = apply_filters( 'wc_plugins_notice_action_links', $action_links );

			// Remove any empty array items.
			$action_links = array_filter( (array) $action_links );

			if ( ! empty( $action_links ) ) {
				$action_links = sprintf( $line_template, implode( ' | ', $action_links ) );
				return $action_links;
			}

			return '';
		}

		/**
		 * Retrieve the URL to the Install page for a specific plugin status (view).
		 *
		 * @param string $status Plugin status - either 'install', 'update' or 'activate'.
		 * @return string Properly encoded URL (not escaped).
		 */
		public function get_install_status_url( $status ) {
			return add_query_arg(
				array(
					'plugin_status' => urlencode( $status ),
				),
				$this->get_install_url()
			);
		}

		/**
		 * Retrieve the URL to the Install page.
		 *
		 * @return string Properly encoded URL (not escaped).
		 */
		public function get_install_url() {
			static $url;

			if ( ! isset( $url ) ) {
				$parent = 'themes.php';

				if ( false === strpos( $parent, '.php' ) ) {
					$parent = 'admin.php';
				}
				$url = add_query_arg(
					array(
						'page' => urlencode( 'woocart-install-plugins' ),
					),
					self_admin_url( $parent )
				);
			}

			return $url;
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
				array(
					'tab'       => 'plugin-information',
					'plugin'    => urlencode( $slug ),
					'TB_iframe' => 'true',
					'width'     => '640',
					'height'    => '500',
				),
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
		 * Register dismissal of admin notices.
		 */
		public function dismiss() {
			if ( isset( $_GET['wc-plugins-dismiss'] ) && check_admin_referer( 'wc-plugins-dismiss-' . get_current_user_id() ) ) {
				update_user_meta( get_current_user_id(), 'wc_dismissed_notice_plugins', 1 );
			}
		}

		/**
		 * Enqueue thickbox scripts/styles for plugin info.
		 *
		 * Thickbox is not automatically included on all admin pages, so we must
		 * manually enqueue it for those pages.
		 *
		 * Thickbox is only loaded if the user has not dismissed the admin
		 * notice or if there are any plugins left to install and activate.
		 */
		public function thickbox() {
			if ( ! get_user_meta( get_current_user_id(), 'wc_dismissed_notice_plugins', true ) ) {
				add_thickbox();
			}
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

			$defaults = array(
				'name'             => '',      // String.
				'slug'             => '',      // String.
				'required'         => false,   // Boolean.
				'version'          => '',      // String.
				'force_activation' => false,    // Boolean.
			);

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
				$this->has_forced_activation = true;
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
