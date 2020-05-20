<?php


use Niteo\WooCart\Defaults\MaintenanceMode;
use PHPUnit\Framework\TestCase;

class MaintenanceModeTest extends TestCase {

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
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 */
	public function testConstructor() {
		$maintenance = new MaintenanceMode();

		\WP_Mock::expectActionAdded( 'plugins_loaded', array( $maintenance, 'init' ) );

		$maintenance->__construct();
		\WP_Mock::assertHooksAdded();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::init
	 */
	public function testInit() {
		$maintenance = new MaintenanceMode();

		\WP_Mock::expectActionAdded( 'init', array( $maintenance, 'maintenance_mode' ) );

		$maintenance->init();
		\WP_Mock::assertHooksAdded();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testCheckReferrerFalse() {
		$_SERVER['HTTP_USER_AGENT'] = 'fake';

		$maintenance = new MaintenanceMode();
		$this->assertFalse( $maintenance->check_referrer() );
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testCheckReferrerTrue() {
		$_SERVER['HTTP_USER_AGENT'] = 'Googlebot';

		$maintenance = new MaintenanceMode();
		$this->assertTrue( $maintenance->check_referrer() );
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceModeLoginTrue() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'wp-login.php';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'wp-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceAdminTrue() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'wp-admin/';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'wp-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceAsyncTrue() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'async-upload.php';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'wp-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceUpgradeTrue() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'upgrade.php';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'wp-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenancePluginsTrue() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'plugins/';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'wp-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceXmlRpcTrue() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'xmlrpc.php';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'wp-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceCustomLoginTrue() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'something-else.php';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'something-else.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceCliTrue() {
		define( 'WP_CLI', true );

		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'some-random-post';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'wp-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceReferrerTrue() {
		$_SERVER['HTTP_USER_AGENT'] = 'Googlebot';
		$_SERVER['HTTP_HOST']       = 'woocart.com/';
		$_SERVER['REQUEST_URI']     = 'something-random-post';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'custom-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceLoggedIn() {
		$_SERVER['HTTP_USER_AGENT'] = 'fake';
		$_SERVER['HTTP_HOST']       = 'woocart.com/';
		$_SERVER['REQUEST_URI']     = 'something-random-post';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'custom-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$maintenance->maintenance_mode();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::check_referrer
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::array_to_string
	 */
	public function testMaintenanceRenderMode() {
		$_SERVER['HTTP_USER_AGENT'] = 'fake';
		$_SERVER['HTTP_HOST']       = 'woocart.com/';
		$_SERVER['REQUEST_URI']     = 'something-random-post';

		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\MaintenanceMode' )
			->makePartial();
		$mock->shouldReceive( 'render' )
			->andReturn( true );

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_login_url',
			array(
				'times'  => 1,
				'return' => 'custom-login.php',
			)
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$mock->maintenance_mode();
	}

}
