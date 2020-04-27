<?php
/**
 * Customized WP dashboard.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.7.5
 */

namespace Niteo\WooCart\Defaults {

	class Dashboard {

		use Extend\Proteus;

		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		}

		/**
		 * Setup custom dashboard.
		 */
		public function plugins_loaded() {

			$this->handle_dashboard_toggle();
			// Check if customization is disabled
			if ( 'yes' === get_option( '_hide_woocart_dashboard', 'no' ) ) {
				return;
			}

			// Check for WooCommerce
			if ( ! defined( 'WC_VERSION' ) ) {
				return;
			}

			add_action( 'admin_menu', array( $this, 'admin_menu' ), 1 );
			add_action( 'admin_menu', array( $this, 'remove_original_page' ), 999 );
			add_filter( 'custom_menu_order', array( $this, 'reorder_submenu_pages' ) );
			add_action( 'submenu_file', array( $this, 'highlight_menu_item' ) );
			add_action( 'admin_init', array( $this, 'redirect_to_dashboard' ), 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_before_admin_bar_render', array( $this, 'add_dashboard_admin_bar_menu_item' ) );
			add_action( 'wp_before_admin_bar_render', array( $this, 'reorder_admin_bar' ) );
			add_action( 'woocommerce_dashboard_status_widget_top_seller_query', array( $this, 'top_seller_query' ) );
		}


		/**
		 * Custom handler for dashboard switcher.
		 */
		public function handle_dashboard_toggle() {
			if ( isset( $_GET['woocart-dashboard'] ) ) {
				$woocart_dashboard = empty( $_GET['woocart-dashboard'] ) ? 'no' : 'yes';
				update_option( '_hide_woocart_dashboard', $woocart_dashboard );
				wp_redirect( admin_url() );
				exit;
			}
		}

		/**
		 * Optimize WooCart widget query.
		 *
		 * @param $query
		 * @return mixed
		 */
		public function top_seller_query( $query ) {
			$query['where']  = "WHERE posts.post_type IN ( '" . implode( "','", wc_get_order_types( 'order-count' ) ) . "' ) ";
			$query['where'] .= "AND posts.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "' ) ";
			$query['where'] .= "AND order_item_meta.meta_key = '_qty' ";
			$query['where'] .= "AND order_item_meta_2.meta_key = '_product_id' ";
			$query['where'] .= "AND posts.post_date >= '" . date( 'Y-m-01', current_time( 'timestamp' ) ) . "' ";
			$query['where'] .= "AND posts.post_date <= '" . date( 'Y-m-d', current_time( 'timestamp' ) ) . "' ";
			return $query;
		}

		/**
		 * Show custom actions under screen.
		 *
		 * @param $screen_settings
		 * @param $screen
		 */
		public function screen_settings( $screen_settings, $screen ) {
			if ( ! $this->is_dashboard_page() ) {
				return;
			}

			if ( isset( $_GET['welcome'] ) ) {
				$welcome_checked = empty( $_GET['welcome'] ) ? 0 : 1;
				update_user_meta( get_current_user_id(), 'show_welcome_panel', $welcome_checked );
				wp_redirect( admin_url() );
			}

			?>
			<div id="screen-options-wrap">
				<fieldset class="metabox-prefs">
					<legend>Actions</legend>
					<a class="button button-primary"
					   href="<?php echo esc_url( admin_url( '?page=woocart-dashboard&woocart-dashboard=1' ) ); ?>"
					   aria-label="<?php esc_attr_e( 'Switch to Classic Dashboard' ); ?>"><?php _e( 'Switch to Classic Dashboard' ); ?></a>
					&nbsp;<a class="button button-primary"
							 href="<?php echo esc_url( admin_url( '?page=woocart-dashboard&welcome=1' ) ); ?>"
							 aria-label="<?php esc_attr_e( 'Show the welcome panel' ); ?>"><?php _e( 'Show the welcome panel' ); ?></a>
				</fieldset>

			</div>
			<?php
		}

		/**
		 * Helper for detecting current page.
		 *
		 * @return bool
		 */
		private function is_dashboard_page() {
			$current_screen = \get_current_screen();

			if (
				'dashboard_page_woocart-dashboard' !== $current_screen->id &&
				'dashboard_page_woocart-dashboard-network' !== $current_screen->id
			) {
				return false;
			}

			return true;
		}

		/**
		 * Fake entry so that screen options can be shown.
		 */
		public function screen_options() {

			if ( ! $this->is_dashboard_page() ) {
				return;
			}

			$args = array(
				'label'   => __( 'Hide WooCart Dashboard', 'woocart-defaults' ),
				'default' => true,
				'option'  => '_hide_custom_dashboard',
			);
			add_screen_option( 'woocart_custom_dashboard', $args );

		}

		/**
		 * Redirect router.
		 */
		public function redirect_to_dashboard() {
			global $pagenow;

			// Bail if the current user is not admin.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( 'index.php' === $pagenow && empty( $_GET ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=woocart-dashboard' ) );
				exit;
			}
		}

		/**
		 * Register custom menu.
		 */
		public function add_dashboard_admin_bar_menu_item() {

			global $wp_admin_bar;

			// Remove the initial dashboard menu item.
			$wp_admin_bar->remove_node( 'dashboard' );

			// Add our custom dashboard item.
			$id = $wp_admin_bar->add_menu(
				array(
					'id'     => 'woocart-dashboard',
					'title'  => 'Dashboard',
					'href'   => get_admin_url( null, 'admin.php?page=woocart-dashboard' ),
					'parent' => 'appearance',
				)
			);

		}

		/**
		 * Rewrite position of our dashboard.
		 */
		public function reorder_admin_bar() {
			global $wp_admin_bar;

			// The desired order of identifiers (items).
			$ids = array(
				'woocart-dashboard',
				'themes',
				'widgets',
				'menus',
			);

			// Get an array of all the toolbar items on the current page.
			$nodes = $wp_admin_bar->get_nodes();

			// Perform recognized identifiers.
			foreach ( $ids as $id ) {
				if ( ! isset( $nodes[ $id ] ) ) {
					continue;
				}

				// This will cause the identifier to act as the last menu item.
				$wp_admin_bar->remove_menu( $id );
				$wp_admin_bar->add_node( $nodes[ $id ] );

				// Remove the identifier from the list of nodes.
				unset( $nodes[ $id ] );
			}

			// Unknown identifiers will be moved to appear after known identifiers.
			foreach ( $nodes as $id => &$obj ) {
				// There is no need to organize unknown children identifiers (sub items).
				if ( ! empty( $obj->parent ) ) {
					continue;
				}

				// This will cause the identifier to act as the last menu item.
				$wp_admin_bar->remove_menu( $id );
				$wp_admin_bar->add_node( $obj );
			}

		}

		/**
		 * Configure route.
		 */
		public function remove_original_page() {
			remove_submenu_page( 'index.php', 'index.php' );
		}

		/**
		 * Configure position of our menu.
		 *
		 * @param $menu_order
		 */
		public function reorder_submenu_pages( $menu_order ) {
			// Load the global submenu.
			global $submenu;

			// Bail if for some reason the submenu is empty.
			if ( empty( $submenu ) ) {
				return;
			}

			// Try to get our custom page index.
			foreach ( $submenu['index.php'] as $key => $value ) {
				if ( 'woocart-dashboard' === $value[2] ) {
					$page_index = $key;
				}
			}

			// Bail if our custom page is missing in `$submenu` for some reason.
			if ( empty( $page_index ) ) {
				return $menu_order;
			}

			// Store the custom dashboard in variable.
			$dashboard_menu_item = $submenu['index.php'][ $page_index ];

			// Remove the original custom dashboard page.
			unset( $submenu['index.php'][ $page_index ] );

			// Add the custom dashboard page in the beginning.
			array_unshift( $submenu['index.php'], $dashboard_menu_item );

			// Finally return the menu order.
			return $menu_order;
		}

		/**
		 * Configure current menu selector.
		 *
		 * @param $parent_file
		 * @return string
		 */
		public function highlight_menu_item( $parent_file ) {
			// Get the current screen.
			$current_screen = get_current_screen();

			// Check whether is the custom dashboard page
			// and change the `parent_file` to woocart-dashboard.
			if ( 'dashboard_page_woocart-dashboard' == $current_screen->base ) {
				$parent_file = $this->get_menu_slug();
			}

			// Return the `parent_file`.
			return $parent_file;
		}


		/**
		 * Register menu handler.
		 *
		 * @return string
		 */
		public function get_menu_slug() {
			return 'woocart-dashboard';
		}

		/**
		 * Our customized welcome panel for the store.
		 *
		 * @codeCoverageIgnore
		 */
		public function render() {
			require_once ABSPATH . 'wp-admin/includes/dashboard.php';

			// Include the partial.
			include __DIR__ . '/templates/dashboard.php';
		}

		/**
		 * Configure admin.
		 *
		 * @return mixed
		 */
		public function admin_menu() {
			// Add the sub-menu page.
			$page = add_submenu_page(
				'index.php',
				__( 'Home', 'woocart' ),
				__( 'Home', 'woocart' ),
				'manage_options',
				$this->get_menu_slug(),
				array( $this, 'render' )
			);
			add_action( "load-$page", array( $this, 'screen_options' ) );
			add_filter( 'screen_settings', array( $this, 'screen_settings' ), 9999, 2 );
			return $page;
		}

		/**
		 * Register styles.
		 */
		public function enqueue_styles() {

			if ( false === $this->is_dashboard_page() ) {
				return;
			}
			$plugin_dir = plugin_dir_url( dirname( __FILE__ ) );
			wp_enqueue_style(
				'woocart-dashboard',
				"$plugin_dir/assets/css/dashboard.css",
				array(),
				Release::Version,
				'all'
			);
			wp_enqueue_style(
				'woocommerce_admin_dashboard_styles',
				\WC()->plugin_url() . '/assets/css/dashboard.css',
				array(),
				Release::Version,
				'all'
			);
		}

		/**
		 * Add styles.
		 */
		public function enqueue_scripts() {
			// Bail if we are on different page.
			if ( false === $this->is_dashboard_page() ) {
				return;
			}

			wp_enqueue_script( 'dashboard' );
		}
	}

}
