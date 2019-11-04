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
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::is_proteus_active
	 */
	public function testInitProteusInactive() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\AdminDashboard' )->makePartial();
		$mock->shouldReceive( 'is_proteus_active' )
				 ->andReturn( false );

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

		\WP_Mock::expectActionAdded( 'welcome_panel', [ $mock, 'welcome_panel' ] );

		$mock->init();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::init
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::is_proteus_active
	 */
	public function testInitProteusActive() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\AdminDashboard' )->makePartial();
		$mock->shouldReceive( 'is_proteus_active' )
				 ->andReturn( true );

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

		\WP_Mock::expectActionAdded( 'welcome_panel', [ $mock, 'proteus_welcome_panel' ] );

		$mock->init();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::is_proteus_active
	 */
	public function testIsProteusActive() {
		$dashboard = new AdminDashboard();

		$fake               = new stdClass();
		$fake->name         = 'WoonderShop';
		$fake->parent_theme = 'WoonderShop';

		\WP_Mock::userFunction(
			'wp_get_theme',
			[
				'return' => $fake,
			]
		);

		$this->assertTrue( $dashboard->is_proteus_active() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::is_proteus_active
	 */
	public function testIsProteusInactive() {
		$dashboard = new AdminDashboard();

		$fake               = new stdClass();
		$fake->name         = 'Astra';
		$fake->parent_theme = 'Astra';

		\WP_Mock::userFunction(
			'wp_get_theme',
			[
				'return' => $fake,
			]
		);

		$this->assertFalse( $dashboard->is_proteus_active() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::purchase_link
	 */
	public function testPurchaseLink() {
		$dashboard = new AdminDashboard();

		\WP_Mock::userFunction(
			'get_template',
			[
				'return' => 'woondershop',
			]
		);

		$this->assertEquals( 'https://proteusthemes.onfastspring.com/woondershop-wp?utm_source=woocart&utm_medium=&utm_campaign=woocart&utm_content=woondershop', $dashboard->purchase_link() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::created_time
	 */
	public function testCreatedTime() {
		$dashboard = new AdminDashboard();

		\WP_Mock::userFunction(
			'get_option',
			[
				'return' => '-1',
			]
		);

		\WP_Mock::userFunction(
			'update_option',
			[
				'return' => true,
			]
		);

		$dashboard->created_time();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::expiry_time
	 */
	public function testExpiryTime() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\AdminDashboard' )->makePartial();
		$mock->shouldReceive( 'created_time' )
				 ->andReturn( 1 );

		define( 'DAY_IN_SECONDS', 100 );

		$this->assertEquals( 701, $mock->expiry_time() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::date_diff
	 */
	public function testDateDiff() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\AdminDashboard' )->makePartial();
		$mock->shouldReceive( 'expiry_time' )
				 ->andReturn( 100 );

		$mock->date_diff();
	}
}
