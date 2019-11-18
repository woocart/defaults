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

		\WP_Mock::expectActionAdded( 'admin_init', array( $dashboard, 'init' ) );

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

		\WP_Mock::expectActionAdded( 'welcome_panel', array( $mock, 'welcome_panel' ) );

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

		\WP_Mock::expectActionAdded( 'welcome_panel', array( $mock, 'proteus_welcome_panel' ) );

		$mock->init();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::is_proteus_active
	 */
	public function testIsProteusActive() {
		$dashboard = new AdminDashboard();

		$fake           = new stdClass();
		$fake->template = 'woondershop-pt';

		\WP_Mock::userFunction(
			'wp_get_theme',
			array(
				'return' => $fake,
			)
		);

		$this->assertTrue( $dashboard->is_proteus_active() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::__construct
	 * @covers \Niteo\WooCart\Defaults\AdminDashboard::is_proteus_active
	 */
	public function testIsProteusInactive() {
		$dashboard = new AdminDashboard();

		$fake           = new stdClass();
		$fake->template = 'astra';

		\WP_Mock::userFunction(
			'wp_get_theme',
			array(
				'return' => $fake,
			)
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
			array(
				'return' => 'woondershop',
			)
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
			array(
				'return' => '-1',
			)
		);

		\WP_Mock::userFunction(
			'update_option',
			array(
				'return' => true,
			)
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
