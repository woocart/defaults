<?php


use Niteo\WooCart\Defaults\AdminDashboard;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\AdminDashboard
 */
class AdminDashboardTest extends TestCase {

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
		$dashboard = new AdminDashboard();

		\WP_Mock::expectActionAdded( 'admin_init', array( $dashboard, 'init' ) );

		$dashboard->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::init
	 */
	public function testInitWelcomePanel() {
		$dashboard = new AdminDashboard();

		\WP_Mock::userFunction(
			'remove_action',
			array(
				'args' =>
				array(
					'welcome_panel',
					'wp_welcome_panel',
				),
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'esc_url',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'get_admin_url',
			array(
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'add_thickbox',
			array(
				'return' => true,
			)
		);

		\WP_Mock::expectActionAdded( 'welcome_panel', array( $dashboard, 'welcome_panel' ) );

		$dashboard->init();
		\WP_Mock::assertHooksAdded();
	}
}
