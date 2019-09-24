<?php


use Niteo\WooCart\Defaults\AdminDashboard;
use PHPUnit\Framework\TestCase;

class AdminDashboardTest extends TestCase {

	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
		\Mockery::close();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 */
	public function testConstructor() {
		$dashboard = new AdminDashboard();
		\WP_Mock::expectActionAdded( 'admin_init', [ $dashboard, 'init' ] );

		$dashboard->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::init
	 */
	public function testInit() {
		$dashboard = new AdminDashboard();

		\WP_Mock::userFunction(
			'remove_action',
			[
				'args' =>
				[
					'welcome_panel',
					'wp_welcome_panel',
				],
			]
		);

		\WP_Mock::userFunction(
			'is_admin',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'esc_url',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_admin_url',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'add_thickbox',
			[
				'return' => true,
			]
		);

		\WP_Mock::expectActionAdded( 'welcome_panel', [ $dashboard, 'welcome_panel' ] );

		$dashboard->init();
		\WP_Mock::assertHooksAdded();
	}
}
