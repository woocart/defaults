<?php


use Niteo\WooCart\Defaults\PluginManager;
use PHPUnit\Framework\TestCase;

class PluginManagerTest extends TestCase {

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
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 */
	public function testConstructor() {
		$plugins = new PluginManager();

		\WP_Mock::expectActionAdded( 'init', [ $plugins, 'init' ] );
		define(
			'WOOCART_REQUIRED',
			[
				[
					'name' => 'Autoptimize',
					'slug' => 'autoptimize',
				],
			]
		);

		$plugins->__construct();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::init
	 */
	public function testInitEmpty() {
		$plugins = new PluginManager();

		\WP_Mock::userFunction(
			'is_admin',
			[
				'return' => false,
			]
		);
		\WP_Mock::userFunction(
			'_n_noop',
			[
				'return' => true,
			]
		);

		$this->assertEmpty( $plugins->init() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::init
	 * @covers \Niteo\WooCart\Defaults\PluginManager::register
	 */
	public function testInitEmptyTwo() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
						->shouldAllowMockingProtectedMethods()
						->makePartial();

		\WP_Mock::userFunction(
			'is_admin',
			[
				'return' => true,
			]
		);

		$this->assertEmpty( $mock->init() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::init
	 * @covers \Niteo\WooCart\Defaults\PluginManager::register
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
	 */
	public function testInit() {
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
						->shouldAllowMockingProtectedMethods()
						->makePartial();
		$mock->list    = [
			[
				'name' => 'Autoptimize',
				'slug' => 'autoptimize',
			],
		];
		$mock->plugins = [
			'name' => 'Autoptimize',
			'slug' => 'autoptimize',
		];

		\WP_Mock::userFunction(
			'is_admin',
			[
				'return' => true,
			]
		);
		\WP_Mock::userFunction(
			'wp_parse_args',
			[
				'return' => [
					'name'      => 'Autoptimize',
					'slug'      => 'autoptimize',
					'file_path' => '',
				],
			]
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			[
				'return' => 'autoptimize',
			]
		);

		$mock->shouldReceive( '_get_plugin_basename_from_slug' )
		->andReturn( 'autoptimize/autoptimize.php' );

		\WP_Mock::expectActionAdded( 'admin_init', [ $mock, 'force_activation' ] );
		\WP_Mock::expectActionAdded( 'current_screen', [ $mock, 'plugins_page' ] );

		$mock->init();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::init
	 * @covers \Niteo\WooCart\Defaults\PluginManager::register
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
	 */
	public function testInitTwo() {
		define( 'SENDGRID_API_KEY', true );
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
						->shouldAllowMockingProtectedMethods()
						->makePartial();
		$mock->list    = [
			[
				'name' => 'Sendgrid',
				'slug' => 'sendgrid-email-delivery-simplified',
			],
		];
		$mock->plugins = [
			'name' => 'Autoptimize',
			'slug' => 'autoptimize',
		];

		\WP_Mock::userFunction(
			'is_admin',
			[
				'return' => true,
			]
		);
		\WP_Mock::userFunction(
			'wp_parse_args',
			[
				'return' => [
					'name'      => 'Autoptimize',
					'slug'      => 'autoptimize',
					'file_path' => '',
				],
			]
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			[
				'return' => 'autoptimize',
			]
		);

		$mock->shouldReceive( '_get_plugin_basename_from_slug' )
		->andReturn( 'autoptimize/autoptimize.php' );

		\WP_Mock::expectActionAdded( 'admin_init', [ $mock, 'force_activation' ] );
		\WP_Mock::expectActionAdded( 'current_screen', [ $mock, 'plugins_page' ] );

		$mock->init();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::plugins_page
	 */
	public function testPluginsPage() {
		$plugins = new PluginManager();

		\WP_Mock::userFunction(
			'get_current_screen',
			[
				'return' => (object) [
					'id' => 'plugins',
				],
			]
		);

		\WP_Mock::expectActionAdded( 'after_plugin_row', [ $plugins, 'add_required_text' ], PHP_INT_MAX, 3 );
		\WP_Mock::expectFilterAdded( 'plugin_action_links', [ $plugins, 'remove_deactivation_link' ], PHP_INT_MAX, 4 );

		$plugins->plugins_page();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::register
	 */
	public function testRegister() {
		$plugins = new PluginManager();

		\WP_Mock::userFunction(
			'wp_parse_args',
			[
				'return' => [
					'name'      => 'Autoptimize',
					'slug'      => 'autoptimize',
					'file_path' => '',
				],
			]
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			[
				'return' => 'autoptimize',
			]
		);

		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
						->shouldAllowMockingProtectedMethods()
						->makePartial();
		$mock->shouldReceive( '_get_plugin_basename_from_slug' )
		->andReturn(
			[
				'autoptimize/autoptimize.php',
			]
		);

		$mock->register(
			[
				'name' => 'Autoptimize',
				'slug' => 'autoptimize',
			]
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::register
	 */
	public function testRegisterEmpty() {
		$plugins = new PluginManager();
		$this->assertEmpty( $plugins->register( [] ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_installed
	 * @covers \Niteo\WooCart\Defaults\PluginManager::get_plugins
	 */
	public function testIsPluginInstalled() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
											->makePartial();
		$mock->shouldReceive( 'get_plugins' )
				 ->andReturn( [ 'fake-one', 'fake-two' ] );

		$this->assertFalse( $mock->is_plugin_installed( 'fake-slug' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
	 */
	public function testIsPluginActive() {
		$plugins          = new PluginManager();
		$plugins->plugins = [
			'autoptimize' => [
				'file_path' => 'autoptimize/autoptimize.php',
			],
		];

		\WP_Mock::userFunction(
			'is_plugin_active',
			[
				'return' => true,
			]
		);

		$this->assertTrue( $plugins->is_plugin_active( 'autoptimize' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::add_required_text
	 */
	public function testAddRequiredText() {
		$plugins        = new PluginManager();
		$plugins->paths = [
			'plugin_file',
		];

		$plugins->add_required_text( 'plugin_file', [ 'Name' => 'Plugin' ] );
		$this->expectOutputString( '<tr><td colspan="3" style="background:#fcd670"><strong>Plugin</strong> is a required plugin on WooCart and cannot be deactivated.</td></tr>' );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::remove_deactivation_link
	 */
	public function testRemoveDeactivationLink() {
		$plugins        = new PluginManager();
		$plugins->paths = [
			'plugin_file',
		];

		$this->assertEquals(
			[ 'Another' => 'This will be returned' ],
			$plugins->remove_deactivation_link(
				[
					'deactivate' => 'Link',
					'Another'    => 'This will be returned',
				],
				'plugin_file'
			)
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
	 */
	public function testIsPluginActiveTwo() {
		$plugins = new PluginManager();

		$this->assertFalse( $plugins->is_plugin_active( 'fake-slug' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::_get_plugin_basename_from_slug
	 */
	public function testGetPluginBasenameFromSlug() {
		$method = self::getMethod( '_get_plugin_basename_from_slug' );
		$mock   = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->shouldReceive( 'get_plugins' )
		->andReturn(
			[
				'autoptimize/autoptimize.php' => [],
			]
		);

		$this->assertEquals( 'autoptimize/autoptimize.php', $method->invokeArgs( $mock, [ 'autoptimize' ] ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::_get_plugin_basename_from_slug
	 */
	public function testGetPluginBasenameFromSlugTwo() {
		$method = self::getMethod( '_get_plugin_basename_from_slug' );
		$mock   = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->shouldReceive( 'get_plugins' )
		->andReturn(
			[
				'something/something.php' => [],
			]
		);

		$this->assertEquals( 'fakeslug', $method->invokeArgs( $mock, [ 'fakeslug' ] ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::force_activation
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
	 * * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_installed
	 */
	public function testForceActivation() {
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->plugins = [
			'autoptimize' => [
				'name'      => 'Autoptimize',
				'slug'      => 'autoptimize',
				'file_path' => 'autoptimize/autoptimize.php',
			],
		];
		$mock->shouldReceive( 'get_plugins' )
		->andReturn(
			[
				'autoptimize/autoptimize.php',
			]
		);

		$mock->force_activation();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::force_activation
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_installed
	 */
	public function testForceActivationTwo() {
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->plugins = [
			'autoptimize' => [
				'name'      => 'Autoptimize',
				'slug'      => 'autoptimize',
				'file_path' => 'autoptimize/autoptimize.php',
			],
		];
		$mock->shouldReceive( 'get_plugins' )
		->andReturn(
			[
				'autoptimize/autoptimize.php',
			]
		);
		$mock->shouldReceive( 'is_plugin_installed' )
		->andReturn( true );
		$mock->shouldReceive( 'is_plugin_active' )
		->andReturn( false );
		\WP_Mock::userFunction(
			'activate_plugin',
			[
				'return' => true,
			]
		);

		$mock->force_activation();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
	 * @covers \Niteo\WooCart\Defaults\PluginManager::force_activation
	 * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_installed
	 */
	public function testForceActivationEmpty() {
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->plugins = [
			'autoptimize' => [
				'name'      => 'Autoptimize',
				'slug'      => 'autoptimize',
				'file_path' => 'autoptimize/autoptimize.php',
			],
		];
		$mock->shouldReceive( 'is_plugin_installed' )
		->andReturn( false );

		$this->assertEmpty( $mock->force_activation() );
	}

	protected static function getMethod( $name ) {
		$class  = new ReflectionClass( 'Niteo\WooCart\Defaults\PluginManager' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}

}
