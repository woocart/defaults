<?php
/**
 * WP-CLI commands.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      1.0.0
 */

namespace Niteo\WooCart\Defaults {

	use Niteo\WooCart\Defaults\Generators\Product;
	use Niteo\WooCart\Defaults\Importers\SellingLimit;
	use Niteo\WooCart\Defaults\Importers\WooPage;
	use Niteo\WooCart\Defaults\Importers\WooProducts;
	use WP_CLI;
	use WP_CLI_Command;


	/**
	 * WooCart Defaults Importer
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class CLI_Command extends WP_CLI_Command {



		/**
		 * Imports bundle to database.
		 *
		 * ## OPTIONS
		 *
		 * <path>
		 * : The path to file that should be imported.
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd import /my/bundle.yaml
		 *
		 * @codeCoverageIgnore
		 * @when after_wp_load
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function import( $args, $assoc_args ) {
			list($path) = $args;

			if ( ! file_exists( $path ) ) {
				WP_CLI::error( "$path cannot be found." );
			}

			$importer = new Importer();
			try {
				$importer->import( $path );
				WP_CLI::success( "The $path has been pulled into the database." );
			} catch ( \Exception $e ) {
				WP_CLI::error( "There was an error in pushing $path to the database." );
			}

		}

		/**
		 * Imports page to database.
		 *
		 * ## OPTIONS
		 *
		 * <path>
		 * : The path to file that should be imported.
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd insert_page /my/page.html
		 *
		 * @codeCoverageIgnore
		 * @when after_wp_load
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function insert_page( $args, $assoc_args ) {
			list($path) = $args;

			if ( ! file_exists( $path ) ) {
				WP_CLI::error( "$path cannot be found." );
			}

			$page = new WooPage( $path );
			try {
				$meta = $page->getPageMeta();
				$id   = $page->insertPage( $meta );
				WP_CLI::success( "The page $path has been inserted as $id." );
			} catch ( \Exception $e ) {
				WP_CLI::error( "There was an error in pushing $path to the database." );
			}

		}

		/**
		 * Sets the woocommerce_all_except_countries based on shipping region.
		 *
		 * ## OPTIONS
		 *
		 * <region>
		 * : One of the regions in shipping table.
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd sell_only_to EU
		 *
		 * @codeCoverageIgnore
		 * @when after_wp_load
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function sell_only_to( $args, $assoc_args ) {
			list($zone) = $args;

			$limit = new SellingLimit( $zone );

			if ( ! $limit->zoneID() ) {
				WP_CLI::error( "$zone cannot be found." );
			}

			try {
				$countries = $limit->countries( $limit->zoneID() );
				update_option( 'woocommerce_allowed_countries', 'specific' );
				update_option( 'woocommerce_specific_allowed_countries', $countries );
				$list = implode( ',', $countries );
				WP_CLI::success( "The region $zone with ($list) has been inserted to woocommerce_specific_allowed_countries." );
			} catch ( \Exception $e ) {
				WP_CLI::error( "There was an error in pushing $zone to the database." );
			}

		}

		/**
		 * Import demo products.
		 *
		 * ## OPTIONS
		 *
		 * <path>
		 * : Path to file with products
		 *
		 * [--common=<common_path>]
		 * : Path to .common directory for localizations
		 * ---
		 * default: /provision/localizations/Countries/.common/
		 * ---
		 *
		 * ## EXAMPLES
		 * wp wcd demo_products /provision/localizations/Countries/.common/products-electronics.html
		 *
		 * @param array $args Arguments specified.
		 * @param array $assoc_args Associative arguments specified.
		 * @codeCoverageIgnore
		 * @throws WP_CLI\ExitException
		 */
		public function demo_products( $args, $assoc_args ) {
			list($path) = $args;

			if ( ! file_exists( $path ) ) {
				WP_CLI::error( "$path cannot be found." );
			}

			$products = new WooProducts( $path, $assoc_args['common'] );
			$products->import();

			WP_CLI::success( $products->get_product_count() . ' products added.' );
		}

		/**
		 * Dump sales stats for previous day from 00:00:00 to 23:59:59.
		 *
		 * ## OPTIONS
		 *
		 * <field>
		 * : Name of field to output.
		 * ---
		 * options:
		 *   - total_refunds
		 *   - total_tax
		 *   - total_shipping
		 *   - total_shipping_tax
		 *   - total_sales
		 *   - net_sales
		 *   - average_sales
		 *   - average_total_sales
		 *   - total_coupons
		 *   - total_refunded_orders
		 *   - total_orders
		 *   - total_items
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd sales total_sales
		 *
		 * @param array $args Arguments specified.
		 * @param array $assoc_args Associative arguments specified.
		 * @codeCoverageIgnore
		 */
		public function sales( $args, $assoc_args ) {
			list($field) = $args;

			include_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/reports/class-wc-admin-report.php';
			include_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/reports/class-wc-report-sales-by-date.php';
			$sales_by_date                 = new \WC_Report_Sales_By_Date();
			$sales_by_date->start_date     = strtotime( date( 'Y-m-d 00:00:00', strtotime( '-1 day' ) ) );
			$sales_by_date->end_date       = strtotime( date( 'Y-m-d 23:59:59', strtotime( '-1 day' ) ) );
			$sales_by_date->chart_groupby  = 'day';
			$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

			$data = (array) $sales_by_date->get_report_data();
			echo $data[ $field ];
		}

	}
}
