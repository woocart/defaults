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
			'adminer',
			'db-cache-reloaded',
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
			'better-wp-security',
			'backupwordpress',
			'wpclef',
			'link-juice-keeper',
			'all-404-redirect-to-homepage',
			'wp-security-scan',
			'limit-login-attempts',
			'sucuri-scanner',
			'updraftplus',
			'duplicator',
			'wp-clone-by-wp-academy',
			'xcloner-backup-and-restore',
			'rapid-ranker',
			'fancybox-for-wordpress',
			'backupbuddy',
			'lpdesignerx',
			'backupcreator',
			'backup-pro',
			'wp-all-import-pro',
			'zencache',
			'wp-optimize-by-xtraffic',
			'wp-htaccess-control',
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
			'dessky-security',
			'404-redirected',
			'bad-behavior',
			'redirect-editor',
			'404-error-logger',
			'forty-four',
			'visitors-traffic-real-time-statistics',
			'wordfence-security-live-traffic-admin-widget',
			'easy-404-redirect',
			'404-to-301',
			'w3-total-cache',
			'404-redirection',
			'bulletproof-security',
			'wp-stats',
			'simple-301-redirects',
			'redirect-to-404',
			'wpdbspringclean',
			'seo-image',
			'eps-301-redirects',
			'easy-digital-downloads-htaccess-editor',
			'quick-pagepost-redirect-plugin',
			'ewww-image-optimizer',
			'stat-counter',
			'reduce-bounce-rate',
			'ip-geo-block',
			'all-in-one-wp-migration-onedrive-extension',
			'all-in-one-wp-migration',
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
			'recent-search-terms',
			'ari-adminer',
			'https-redirection',
			'seo-301-meta',
			'wp-db-manager',
			'file-manager-advanced',
			'force-https-littlebizzy',
			'wp-encrypt',
			'wp-smushit',
			'cookie-notice',
			'wp-optimize',
			'limit-login-attempts-reloaded',
			'better-search-replace',
			'litespeed-cache',
			'sg-cachepress',
			'wps-hide-login',
			'bbpress',
			'ssl-insecure-content-fixer',
			'wp-migrate-db',
			'imsanity',
			'wp-db-backup',
			'uk-cookie-consent',
			'buddypress',
			'gotmls',
			'siteguard',
			'cloudflare',
			'tiny-compress-images',
			'login-lockdown',
			'imagify',
			'search-and-replace',
			'godaddy-email-marketing-sign-up-forms',
			'search-regex',
			'easy-theme-and-plugin-upgrades',
			'accelerated-mobile-pages',
			'amp',
			'shortpixel-image-optimiser',
			'rename-wp-login',
			'wp-dbmanager',
			'eu-cookie-law',
			'wp-gdpr-compliance',
			'auto-terms-of-service-and-privacy-policy',
			'ultimate-member',
			'cloudflare-flexible-ssl',
			'download-manager',
			'wp-cerber',
			'wordpress-https',
			'p3-profiler',
			'health-check',
			'peters-login-redirect',
			'bj-lazy-load',
			'wp-rollback',
			'login-customizer',
			'real-time-find-and-replace',
			'wp-stats-manager',
			'wps-hide-login',
			'wpe-advanced-cache-options',
			'post-views-counter',
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
