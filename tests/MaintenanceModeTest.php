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

		\WP_Mock::expectActionAdded( 'template_redirect', array( $maintenance, 'maintenance_mode' ) );

		$maintenance->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::__construct
	 * @covers \Niteo\WooCart\Defaults\MaintenanceMode::maintenance_mode
	 */
	public function testMaintenanceModeWCLostPassword() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'my-account/lost-password';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
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
	 */
	public function testMaintenanceLoggedIn() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'something-random-post';

		$maintenance = new MaintenanceMode();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
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
	 */
	public function testMaintenanceStatusHeader() {
		$_SERVER['HTTP_HOST']   = 'woocart.com/';
		$_SERVER['REQUEST_URI'] = 'something-random-post';

		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\MaintenanceMode' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();
		$mock->shouldReceive( 'terminate' )
			->andReturn( true );

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		\WP_Mock::userFunction(
			'status_header',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$mock->maintenance_mode();
	}

}
