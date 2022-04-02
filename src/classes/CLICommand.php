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

	use Niteo\WooCart\Defaults\Database;
	use Niteo\WooCart\Defaults\Generators\Product;
	use Niteo\WooCart\Defaults\Importers\SellingLimit;
	use Niteo\WooCart\Defaults\Importers\WooPage;
	use Niteo\WooCart\Defaults\Importers\WooProducts;
	use Niteo\WooCart\Defaults\DemoCleaner;
	use Niteo\WooCart\Defaults\AutoLoginCLI;
	use WP_CLI;
	use WP_CLI_Command;


	/**
	 * WooCart Defaults Importer
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class CLICommand extends WP_CLI_Command {

		/**
		 * Exports db to bundle.
		 *
		 * ## OPTIONS
		 *
		 * <type>
		 * : The type of bundle that should be exported (wootax, woo, wp, wooship).
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd export wootax > taxes.yaml
		 *
		 * @codeCoverageIgnore
		 * @when after_wp_load
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function export( $args, $assoc_args ) {
			list($type) = $args;
			$exporter   = new Exporter();
			try {
				$exporter->export( $type );
			} catch ( \Exception $e ) {
				WP_CLI::error( "There was an error in pulling $type from the database." );
			}

		}

		/**
		 * Prints login url.
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd login
		 *
		 * @codeCoverageIgnore
		 * @when after_wp_load
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function login( $args, $assoc_args ) {
			try {
				$login = new AutoLoginCLI();
				WP_CLI::success( $login->url() );
			} catch ( \Exception $e ) {
				WP_CLI::error( 'There was an error creating login url.' );
			}

		}


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
			$sales_by_date->end_date       = strtotime( date( 'Y-m-d 00:00:00', strtotime( '-1 day' ) ) );
			$sales_by_date->chart_groupby  = 'day';
			$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

			$data = (array) $sales_by_date->get_report_data();
			echo $data[ $field ];
		}

		/**
		 * Removes demo content from the store.
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd remove_demo_content
		 *
		 * @codeCoverageIgnore
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function remove_demo_content( $args, $assoc_args ) {
			try {
				$demo_cleaner = new DemoCleaner();
				$demo_cleaner->cli();

				// Show message.
				if ( ! empty( $demo_cleaner->response['message'] ) ) {
					if ( 'error' === $demo_cleaner->response['code'] ) {
						WP_CLI::error( $demo_cleaner->response['message'] );
					} else {
						WP_CLI::success( $demo_cleaner->response['message'] );
					}
				} else {
					WP_CLI::error( 'There was an error removing demo products from the store.' );
				}
			} catch ( \Exception $e ) {
				WP_CLI::error( 'There was an error removing demo products from the store.' );
			}
		}

		/**
		 * Disables all plugins on deny list.
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd disable_denied_plugins
		 *
		 * @codeCoverageIgnore
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function disable_denied_plugins( $args, $assoc_args ) {
			try {
				$deny             = new DenyList();
				$disabled_plugins = $deny->force_deactivate();

				if ( ! empty( $disabled_plugins ) ) {
					foreach ( $disabled_plugins as $plugin => $name ) {
						WP_CLI::log( sprintf( '%s(%s) disabled', $name, $plugin ) );
					}
				}
			} catch ( \Exception $e ) {
				WP_CLI::log( 'There was an error deactivating denied plugins.' );
				WP_CLI::error( $e );
			}
		}

		/**
		 * Toggle maintenance mode for the store.
		 *
		 * ## OPTIONS
		 *
		 * <status>
		 * : Maintenance mode status.
		 * ---
		 * options:
		 *   - activate
		 *   - deactivate
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd soft_maintenance activate
		 *
		 * @codeCoverageIgnore
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function soft_maintenance( $args, $assoc_args ) {
			try {
				list($status) = $args;

				if ( 'activate' === $status ) {
					update_option( 'woocart_maintenance_mode', true );
				} elseif ( 'deactivate' === $status ) {
					update_option( 'woocart_maintenance_mode', false );
				}

				WP_CLI::log( sprintf( 'Maintenance mode has been %sd.', $status ) );
			} catch ( \Exception $e ) {
				WP_CLI::log( 'There was an error processing your request.' );
				WP_CLI::error( $e );
			}
		}

		/**
		 * Toggle blocking HTTP calls for the store.
		 *
		 * ## OPTIONS
		 *
		 * <status>
		 * : Block mode status.
		 * ---
		 * options:
		 *   - activate
		 *   - deactivate
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd block_http_calls activate
		 *
		 * @codeCoverageIgnore
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function block_http_calls( $args, $assoc_args ) {
			try {
				list($status) = $args;

				if ( 'activate' === $status ) {
					update_option( 'woocart_http_status', true );
				} elseif ( 'deactivate' === $status ) {
					update_option( 'woocart_http_status', false );
				}

				WP_CLI::log( sprintf( 'HTTP calls block mode has been %sd.', $status ) );
			} catch ( \Exception $e ) {
				WP_CLI::log( 'There was an error processing your request.' );
				WP_CLI::error( $e );
			}
		}

		/**
		 * Toggle modifying files on disk.
		 *
		 * ## OPTIONS
		 *
		 * <status>
		 * : Files mod status.
		 * ---
		 * options:
		 *   - activate
		 *   - deactivate
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd filesystem_lock activate
		 *
		 * @codeCoverageIgnore
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function filesystem_lock( $args, $assoc_args ) {
			try {
				list($status) = $args;

				if ( 'activate' === $status ) {
					update_option( 'woocart_readonly_filesystem', true );
				} elseif ( 'deactivate' === $status ) {
					update_option( 'woocart_readonly_filesystem', false );
				}

				WP_CLI::log( sprintf( 'Read-only filesystem has been %sd.', $status ) );
			} catch ( \Exception $e ) {
				WP_CLI::log( 'There was an error processing your request.' );
				WP_CLI::error( $e );
			}
		}

		/**
		 * Checks and applies the optimizations for the database.
		 *
		 * ## OPTIONS
		 *
		 * <action>
		 * : Action to be performed.
		 *
		 * [--network]
		 * : Multi-site support.
		 *
		 * ---
		 * options:
		 *   - optimize
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd db optimize
		 *
		 * @codeCoverageIgnore
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function db( $args, $assoc_args ) {
			try {
				list($action) = $args;

				if ( 'optimize' === $action ) {
					$db = new Database();

					/**
					 * Optimizations.
					 *
					 * 1. Run analyze query on 3 tables (posts, postmeta, and options)
					 * 2. Switch table engine to InnoDB
					 * 3. Add index on columns
					 */
					if ( isset( $assoc_args['network'] ) ) {
						if ( is_multisite() ) {
							$sites = get_sites(
								array(
									'fields' => 'ids',
								)
							);

							foreach ( $sites as $site ) {
								switch_to_blog( $site );

								// Fetch details for the current site.
								$site_info = get_blog_details( $site );

								WP_CLI::log( '' );
								WP_CLI::log( '--> Running optimisations for ' . $site_info->blogname . ' (ID: ' . $site . ')' );
								WP_CLI::log( '' );

								$db->analyze_tables();      // 1
								$db->switch_to_innodb();    // 2
								$db->add_indexes();             // 3

								restore_current_blog();
							}

							return;
						} else {
							WP_CLI::error( 'Multi-site is not enabled. Please try again without the --network option.' );
							return;
						}
					}

					$db->analyze_tables();          // 1
					$db->switch_to_innodb();        // 2
					$db->add_indexes();                 // 3
				}
			} catch ( \Exception $e ) {
				WP_CLI::line( 'There was an error processing your request.' );
				WP_CLI::error( $e->getMessage() );
			}
		}

		/**
		 * Convert all tables to InnoDB.
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd to_innodb
		 *
		 * @codeCoverageIgnore
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function to_innodb(  $args, $assoc_args ) {
			try {
				$db = new Database();

				$db->switch_to_innodb();
			} catch ( \Exception $e ) {
				WP_CLI::line( 'There was an error processing your request.' );
				WP_CLI::error( $e->getMessage() );
			}
		}

		/**
		 * Toggle for the restrict pagination feature.
		 *
		 * ## OPTIONS
		 *
		 * <status>
		 * : Pagination restriction status.
		 * ---
		 * options:
		 *   - activate
		 *   - deactivate
		 *
		 * ## EXAMPLES
		 *
		 *     wp wcd restrict_pagination activate
		 *
		 * @codeCoverageIgnore
		 * @param $args array list of command line arguments.
		 * @param $assoc_args array of named command line keys.
		 * @throws WP_CLI\ExitException on wrong command.
		 */
		public function restrict_pagination( $args, $assoc_args ) {
			try {
				list($status) = $args;

				if ( 'activate' === $status ) {
					update_option( 'woocart_disable_restrict_pagination', false );
				}

				if ( 'deactivate' === $status ) {
					update_option( 'woocart_disable_restrict_pagination', true );
				}

				WP_CLI::log( sprintf( 'Pagination restriction has been %sd.', $status ) );
			} catch ( \Exception $e ) {
				WP_CLI::log( 'There was an error processing your request.' );
				WP_CLI::error( $e );
			}
		}
	}
}
