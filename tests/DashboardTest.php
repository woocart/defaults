<?php


use Niteo\WooCart\Defaults\Dashboard;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\Dashboard
 */
class DashboardTest extends TestCase {

	function setUp() : void {
		\WP_Mock::setUp();
	}

	function tearDown() : void {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
		\Mockery::close();
	}


	/**
	 * @covers ::__construct
	 */
	public function testConstructor() {
		$dashboard = new Dashboard();

		\WP_Mock::expectActionAdded( 'plugins_loaded', array( $dashboard, 'plugins_loaded' ) );

		$dashboard->__construct();
		\WP_Mock::assertHooksAdded();
	}


	/**
	 * @covers ::plugins_loaded
	 * @covers ::__construct
	 * @covers ::handle_dashboard_toggle
	 * @covers \Niteo\WooCart\Defaults\Extend\Dashboard::is_dashboard_active
	 * @covers \Niteo\WooCart\Defaults\Extend\Dashboard::is_staging
	 */
	public function testplugins_loaded() {
		$_SERVER['STORE_STAGING'] = 'no';
		$dashboard                = new Dashboard();
		define( 'WC_VERSION', 1 );
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'times'  => 1,
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => 'no',
			)
		);

		\WP_Mock::expectActionAdded( 'admin_menu', array( $dashboard, 'admin_menu' ), 1 );
		\WP_Mock::expectActionAdded( 'admin_menu', array( $dashboard, 'remove_original_page' ), 999 );
		\WP_Mock::expectActionAdded( 'submenu_file', array( $dashboard, 'highlight_menu_item' ) );
		\WP_Mock::expectActionAdded( 'admin_init', array( $dashboard, 'redirect_to_dashboard' ), 1 );
		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', array( $dashboard, 'enqueue_styles' ) );
		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', array( $dashboard, 'enqueue_scripts' ) );
		\WP_Mock::expectActionAdded( 'wp_before_admin_bar_render', array( $dashboard, 'add_dashboard_admin_bar_menu_item' ) );
		\WP_Mock::expectActionAdded( 'wp_before_admin_bar_render', array( $dashboard, 'reorder_admin_bar' ) );
		\WP_Mock::expectActionAdded( 'woocommerce_dashboard_status_widget_top_seller_query', array( $dashboard, 'top_seller_query' ) );

		$dashboard->plugins_loaded();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Dashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\Dashboard::is_rewrite_urls
	 */
	public function testIsRewriteUrlsFalse() {
		$dashboard = new Dashboard();

		$this->assertFalse( $dashboard->is_rewrite_urls() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Dashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\Dashboard::is_rewrite_urls
	 */
	public function testIsRewriteUrlsTrue() {
		$dashboard               = new Dashboard();
		$_ENV['WP_REWRITE_URLS'] = 'true';

		$this->assertTrue( $dashboard->is_rewrite_urls() );
	}

}
