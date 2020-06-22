<?php
/**
 * Plugin Name: WooCart Defaults
 * Description: Manage and deploy WordPress + WooCommerce configuration changes.
 * Version:     @##VERSION##@
 * Runtime:     7.2+
 * Author:      WooCart
 * Text Domain: woocart-defaults
 * Domain Path: i18n
 * Author URI:  www.woocart.com
 */


namespace Niteo\WooCart {

	require_once __DIR__ . '/vendor/autoload.php';

	use Niteo\WooCart\Defaults\AdminDashboard;
	use Niteo\WooCart\Defaults\Dashboard;
	use Niteo\WooCart\Defaults\AutoLogin;
	use Niteo\WooCart\Defaults\CacheManager;
	use Niteo\WooCart\Defaults\DemoCleaner;
	use Niteo\WooCart\Defaults\DenyList;
	use Niteo\WooCart\Defaults\Filters;
	use Niteo\WooCart\Defaults\GDPR;
	use Niteo\WooCart\Defaults\MaintenanceMode;
	use Niteo\WooCart\Defaults\OpCacheStats\Reporter;
	use Niteo\WooCart\Defaults\Optimizations;
	use Niteo\WooCart\Defaults\PluginLogger;
	use Niteo\WooCart\Defaults\PluginManager;
	use Niteo\WooCart\Defaults\Shortcodes;
	use Niteo\WooCart\Defaults\WooCommerce;
	use Niteo\WooCart\Defaults\WordPress;

	if ( class_exists( 'WP_CLI' ) ) {
		\WP_CLI::add_command( 'wcd', __NAMESPACE__ . '\Defaults\CLI_Command' );
	} else {
		if ( function_exists( 'add_shortcode' ) ) {
			new Shortcodes();
		}

		if ( function_exists( 'do_shortcode' ) ) {
			new Filters();
		}

		/**
		 * 1. Consent notification to comply with GDPR.
		 * 2. Panel for the store in the WP admin dashboard.
		 * 3. Support auto login from url with jwt token.
		 */
		if ( function_exists( 'add_action' ) ) {
			new AdminDashboard();
			new Dashboard();
			new AutoLogin();
			new CacheManager();
			new DemoCleaner();
			new DenyList();
			new GDPR();
			new MaintenanceMode();
			new Optimizations();
			new PluginLogger();
			new PluginManager();
			new Reporter();
			new WooCommerce();
			new WordPress();
		}
	}
}
