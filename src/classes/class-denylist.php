<?php
/**
 * Handles content for the admin dashboard panel.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.6.1
 */

namespace Niteo\WooCart\Defaults {

	class DenyList {

		/**
		 * @var array
		 */
		private $_plugins_to_deactivate = [];

		/**
		 * @var array
		 */
		protected $blacklist = [
			'wp-clone-by-wp-academy',
			'adminer',
			'db-cache-reloaded',
			'backupwordpress',
			'backwpup',
			'contextual-related-posts',
			'ezpz-one-click-backup',
			'fuzzy-seo-booster',
			'google-sitemap-generator',
			'google-xml-sitemaps-with-multisite-support',
			'jr-referrer',
			'portable-phpmyadmin',
			'quick-cache',
			'seo-alrp',
			'similar-posts',
			'the-codetree-backup',
			'toolspack',
			'wordpress-gzip-compression',
			'wp-cache',
			'wp-engine-snapshot',
			'wp-file-cache',
			'wp-phpmyadmin',
			'wp-postviews',
			'wp-slimstat',
			'wp-super-cache',
			'wponlinebackup',
			'sgcachepress',
			'synthesis',
			'wpengine-common',
			'6scan-protection',
			'6scan-backup',
			'all-in-one-wp-security-and-firewall',
			'statpress',
			'wp-fast-cache',
			'wp-fastest-cache',
			'wp-cachecom',
			'referrer-wp',
			'adsense-click-fraud-monitoring',
			'wordpress-beta-tester',
			'wp-copysafe-web',
			'wp-copysafe-pdf',
			'wysija-newsletters',
			'wptouch',
			'custom-contact-forms',
			'wordpress-popular-posts',
			'wordfence',
			'backwpup',
			'better-wp-security',
			'backupwordpress',
			'wpclef',
			'link-juice-keeper',
			'all-404-redirect-to-homepage',
			'wp-fastest-cache',
			'wp-security-scan',
			'limit-login-attempts',
			'sucuri-scanner',
			'updraftplus',
			'duplicator',
			'wp-clone-by-wp-academy',
			'xcloner-backup-and-restore',
			'rapid-ranker',
			'fancybox-for-wordpress',
			'updraftplus',
			'backupbuddy',
			'lpdesignerx',
			'backupcreator',
			'backup-pro',
			'wp-all-import-pro',
			'zencache',
			'wp-optimize-by-xtraffic',
			'quick-cache',
			'wp-htaccess-control',
			'all-in-one-wp-security-and-firewall',
			'404-to-start',
			'remove-google-fonts-references',
			'iq-block-country',
			'wp-backup-plus',
			'automatic-updater',
			'email-subscribers',
			'backlinks-saver',
			'rvg-optimize-database',
			'multi-plugin-installer',
			'ssnuke54',
			'db-prefix-change',
			'stops-core-theme-and-plugin-updates',
			'404-to-301',
			'dessky-security',
			'404-redirected',
			'bad-behavior',
			'redirect-editor',
			'404-error-logger',
			'forty-four',
			'visitors-traffic-real-time-statistics',
			'wordfence-security-live-traffic-admin-widget',
			'file-manager',
			'easy-404-redirect',
			'404-to-301',
			'wp-clone-by-wp-academy',
			'w3-total-cache',
			'404-redirection',
			'bulletproof-security',
			'wp-stats',
			'simple-301-redirects',
			'loginizer',
			'redirect-to-404',
			'all-in-one-wp-migration',
			'wpdbspringclean',
			'seo-image',
			'eps-301-redirects',
			'easy-digital-downloads-htaccess-editor',
			'quick-pagepost-redirect-plugin',
			'wp-file-manager',
			'ewww-image-optimizer',
			'eps-301-redirects',
			'stat-counter',
			'reduce-bounce-rate',
			'ip-geo-block',
			'all-in-one-wp-migration-onedrive-extension',
			'all-in-one-wp-migration',
			'wordfence',
			'wp-cloaker',
			'wp-limit-login-attempts',
			'super-static-cache',
			'loginizer',
			'cachify',
			'dropbox-backup',
			'security-ninja',
			'coming-soon-maintenance-mode-from-acurax',
			'wp-statistics',
			'serplifywp',
			'wp-all-import',
			'recent-search-terms',
			'ari-adminer',
			'https-redirection',
			'seo-301-meta',
			'wp-db-manager',
			'file-manager-advanced',
			'force-https-littlebizzy',
			'wp-encrypt',
		];

		/**
		 * Denylist constructor.
		 */
		public function __construct() {
			if ( defined( '_FORCED_PLUGINS' ) ) {
				/**
				 * This isn't used at the moment but might be useful in future.
				 * So, keeping this over here.
				 */
				add_filter( 'plugin_action_links', [ &$this, 'forced_plugins' ], 10, 4 );
			}

			add_filter( 'plugin_install_action_links', [ &$this, 'disable_install_link' ], 10, 2 );
			add_filter( 'plugin_action_links', [ &$this, 'disable_activate_link' ], 10, 2 );
			add_action( 'activate_plugin', [ &$this, 'disable_activation' ], PHP_INT_MAX, 2 );
		}

		/**
		 * Disable activation of a blacklisted plugin.
		 *
		 * @param string $plugin Plugin name to check and disable.
		 */
		public function disable_activation( $plugin ) {
			if ( $this->is_plugin_denied( $plugin ) ) {
				$this->_plugins_to_deactivate[] = $plugin;

				if ( false == has_action( 'shutdown', [ &$this, 'deactivate_plugins' ] ) ) {
					add_action( 'shutdown', [ &$this, 'deactivate_plugins' ] );
				}
			}
		}

		/**
		 * Check whether a plugin exists in the list of blacklisted plugins or not.
		 *
		 * @param string $plugin Plugin name to check from the list.
		 * @return boolean
		 * @access private
		 * @codeCoverageIgnore
		 */
		private function is_plugin_denied( $plugin ) {
			if ( is_array( $plugin ) ) {
				$info    = $plugin;
				$_plugin = $info['slug'];
			} else {
				$_plugin = $plugin;
			}

			if ( false !== strpos( $_plugin, '/' ) ) {
				$_plugin = dirname( $_plugin );
			}

			foreach ( $this->blacklist as $bad_plugin ) {
				if ( 0 === strcasecmp( $_plugin, $bad_plugin ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * De-activate all plugins which are blacklisted.
		 */
		public function deactivate_plugins() {
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php'; // @codeCoverageIgnore
			}

			foreach ( $this->_plugins_to_deactivate as $plugin ) {
				deactivate_plugins( $plugin, true );
			}
		}

		/**
		 * Disable installation link for a plugin.
		 *
		 * @param array  $links Array containing acttion links for a plugin.
		 * @param string $plugin Plugin name to check and disable.
		 *
		 * @return string|array
		 */
		public function disable_install_link( $links, $plugin ) {
			if ( $this->is_plugin_denied( $plugin ) ) {
				return [
					sprintf(
						'<a href="javascript:;" title="%2$s">%1$s</a>',
						'Not available',
						'This plugin is not allowed on our system due to performance, security, or compatibility concerns. Please contact our support with any questions.'
					),
				];
			}

			return $links;
		}

		/**
		 * Disable activation link for a plugin.
		 *
		 * @param array  $links Array containing acttion links for a plugin.
		 * @param string $plugin Plugin name to check and disable.
		 *
		 * @return string|array
		 */
		public function disable_activate_link( $links, $plugin ) {
			if (
			isset( $links['activate'] )
			&& $this->is_plugin_denied( $plugin )
			) {
				$links['activate'] = sprintf(
					'<a href="javascript:;" data-plugin="%3$s" title="%2$s">%1$s</a>',
					'Not available',
					'This plugin is not allowed on our system due to performance, security, or compatibility concerns. Please contact our support with any questions.',
					$plugin
				);
			}

			return $links;
		}

		/**
		 * @codeCoverageIgnore
		 */
		public function forced_plugins( $actions, $plugin_file, $plugin_data, $context ) {
			$forced_plugins = explode( ',', _FORCED_PLUGINS );

			foreach ( $forced_plugins as $plugin ) {
				// Deactivate.
				if (
				array_key_exists( 'deactivate', $actions )
				&& stristr( $plugin_file, $plugin ) !== false
				) {
					$actions['deactivate'] = '<span style="color:green;">Permanently de-activated</span>';
				}

				// Activate.
				if (
				array_key_exists( 'activate', $actions )
				&& stristr( $plugin_file, $plugin ) !== false
				) {
					activate_plugin( $plugin_file );
				}
			}

			return $actions;
		}

	}

}
