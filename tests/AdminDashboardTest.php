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
		\WP_Mock::expectActionAdded( 'wp_login', array( $dashboard, 'track' ) );

		$dashboard->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::init
	 * @covers ::is_proteus_active
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
	 * @covers ::__construct
	 * @covers ::init
	 * @covers ::is_proteus_active
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
	 * @covers ::__construct
	 * @covers ::is_proteus_active
	 */
	public function testIsProteusActive() {
		$dashboard             = new AdminDashboard();
		$_SERVER['STORE_PLAN'] = 'lead';

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
	 * @covers ::__construct
	 * @covers ::is_proteus_active
	 * @covers ::track
	 */
	public function testTrackProteusLogin() {
		$dashboard             = new AdminDashboard();
		$_SERVER['STORE_PLAN'] = 'lead';
		$_SERVER['STORE_ID']   = '1';

		$fake           = new stdClass();
		$fake->template = 'woondershop-pt';

		\WP_Mock::userFunction(
			'wp_remote_post',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'wp_json_encode',
			array(
				'return' => 'encoded',
			)
		);

		$this->assertTrue( $dashboard->track() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_proteus_active
	 * @covers ::track
	 */
	public function testTrackProteusLoginSkip() {
		$dashboard             = new AdminDashboard();
		$_SERVER['STORE_PLAN'] = 'dev';
		$_SERVER['STORE_ID']   = '1';

		$fake           = new stdClass();
		$fake->template = 'woondershop-pt';

		$this->assertFalse( $dashboard->track() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_proteus_active
	 */
	public function testIsProteusInactive() {
		$dashboard             = new AdminDashboard();
		$_SERVER['STORE_PLAN'] = 'cart';

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
	 * @covers ::__construct
	 * @covers ::purchase_link
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
	 * @covers ::__construct
	 * @covers ::created_time
	 */
	public function testCreatedTime() {
		$dashboard           = new AdminDashboard();
		$_SERVER['STORE_ID'] = 'uuid-42';
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
	 * @covers ::__construct
	 * @covers ::expiry_time
	 */
	public function testExpiryTime() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\AdminDashboard' )->makePartial();
		$mock->shouldReceive( 'created_time' )
				 ->andReturn( 1 );

		define( 'DAY_IN_SECONDS', 100 );

		$this->assertEquals( 1001, $mock->expiry_time() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::date_diff
	 */
	public function testDateDiff() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\AdminDashboard' )->makePartial();
		$mock->shouldReceive( 'expiry_time' )
				 ->andReturn( 100 );

		$mock->date_diff();
	}
}
