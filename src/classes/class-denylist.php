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
			'404-error-logger',
			'404-redirected',
			'404-redirection',
			'404-to-301',
			'404-to-start',
			'6scan-backup',
			'6scan-protection',
			'accelerated-mobile-pages',
			'adminer',
			'adsense-click-fraud-monitoring',
			'all-404-redirect-to-homepage',
			'all-in-one-wp-migration-onedrive-extension',
			'all-in-one-wp-migration',
			'all-in-one-wp-security-and-firewall',
			'amp',
			'ari-adminer',
			'auto-install-free-ssl',
			'auto-terms-of-service-and-privacy-policy',
			'automatic-updater',
			'backlinks-saver',
			'backup-pro',
			'backupbuddy',
			'backupcreator',
			'backupwordpress',
			'backwpup',
			'bad-behavior',
			'bbpress',
			'better-search-replace',
			'better-wp-security',
			'bj-lazy-load',
			'buddypress',
			'bulletproof-security',
			'cachify',
			'cloudflare-flexible-ssl',
			'cloudflare',
			'coming-soon-maintenance-mode-from-acurax',
			'contextual-related-posts',
			'cookie-notice',
			'custom-contact-forms',
			'db-cache-reloaded',
			'db-prefix-change',
			'dessky-security',
			'download-manager',
			'dropbox-backup',
			'duplicator',
			'easy-404-redirect',
			'easy-digital-downloads-htaccess-editor',
			'easy-theme-and-plugin-upgrades',
			'email-subscribers',
			'eps-301-redirects',
			'eu-cookie-law',
			'ewww-image-optimizer',
			'ezpz-one-click-backup',
			'fancybox-for-wordpress',
			'file-manager-advanced',
			'force-https-littlebizzy',
			'forty-four',
			'fuzzy-seo-booster',
			'godaddy-email-marketing-sign-up-forms',
			'google-sitemap-generator',
			'google-xml-sitemaps-with-multisite-support',
			'gotmls',
			'health-check',
			'https-redirection',
			'imagify',
			'imsanity',
			'ip-geo-block',
			'iq-block-country',
			'jetpack',
			'jr-referrer',
			'limit-login-attempts-reloaded',
			'limit-login-attempts',
			'link-juice-keeper',
			'litespeed-cache',
			'login-customizer',
			'login-lockdown',
			'loginizer',
			'lpdesignerx',
			'multi-plugin-installer',
			'one-click-ssl',
			'p3-profiler',
			'peters-login-redirect',
			'portable-phpmyadmin',
			'post-views-counter',
			'quick-cache',
			'quick-pagepost-redirect-plugin',
			'rapid-ranker',
			'real-time-find-and-replace',
			'really-simple-ssl',
			'recent-search-terms',
			'redirect-editor',
			'redirect-to-404',
			'reduce-bounce-rate',
			'referrer-wp',
			'remove-google-fonts-references',
			'rename-wp-login',
			'rvg-optimize-database',
			'search-and-replace',
			'search-regex',
			'security-ninja',
			'seo-301-meta',
			'seo-alrp',
			'seo-image',
			'serplifywp',
			'sg-cachepress',
			'sgcachepress',
			'shortpixel-image-optimiser',
			'similar-posts',
			'simple-301-redirects',
			'siteguard',
			'ssl-insecure-content-fixer',
			'ssl-zen',
			'ssnuke54',
			'stat-counter',
			'statpress',
			'stops-core-theme-and-plugin-updates',
			'sucuri-scanner',
			'super-static-cache',
			'synthesis',
			'the-codetree-backup',
			'tiny-compress-images',
			'toolspack',
			'uk-cookie-consent',
			'ultimate-member',
			'updraftplus',
			'visitors-traffic-real-time-statistics',
			'w3-total-cache',
			'wordfence-security-live-traffic-admin-widget',
			'wordfence',
			'wordpress-beta-tester',
			'wordpress-gzip-compression',
			'wordpress-https',
			'wordpress-popular-posts',
			'wp-all-import-pro',
			'wp-backup-plus',
			'wp-cache',
			'wp-cachecom',
			'wp-cerber',
			'wp-cloaker',
			'wp-clone-by-wp-academy',
			'wp-copysafe-pdf',
			'wp-copysafe-web',
			'wp-db-backup',
			'wp-db-manager',
			'wp-dbmanager',
			'wp-encrypt',
			'wp-engine-snapshot',
			'wp-fast-cache',
			'wp-fastest-cache',
			'wp-file-cache',
			'wp-force-ssl',
			'wp-gdpr-compliance',
			'wp-htaccess-control',
			'wp-limit-login-attempts',
			'wp-migrate-db',
			'wp-optimize-by-xtraffic',
			'wp-optimize',
			'wp-phpmyadmin',
			'wp-postviews',
			'wp-rollback',
			'wp-security-scan',
			'wp-slimstat',
			'wp-smushit',
			'wp-ssl-redirect',
			'wp-statistics',
			'wp-stats-manager',
			'wp-stats',
			'wp-super-cache',
			'wpclef',
			'wpdbspringclean',
			'wpe-advanced-cache-options',
			'wpengine-common',
			'wponlinebackup',
			'wps-hide-login',
			'wps-hide-login',
			'wptouch',
			'wysija-newsletters',
			'xcloner-backup-and-restore',
			'zencache',
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
		 * Disable all denied plugins if they are active.
		 *
		 * @return array of disabled plugins
		 */
		public function force_deactivate(): array {
			$deactivated_plugins = [];
			$all_plugins         = get_plugins();
			foreach ( $all_plugins as $plugin => $info ) {
				$slug = explode( '/', $plugin )[0];
				if ( in_array( $slug, $this->blacklist ) ) {
					deactivate_plugins( $slug, true );
					$deactivated_plugins[ $slug ] = $info['Name'];
				}
			}
			return $deactivated_plugins;
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
						'<a href="https://woocart.com/plugins-denylist" title="%2$s" target="_blank">%1$s</a>',
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
					'<a href="https://woocart.com/plugins-denylist" data-plugin="%3$s" title="%2$s" target="_blank">%1$s</a>',
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
