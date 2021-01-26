<?php


use Niteo\WooCart\Defaults\PluginManager;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\PluginManager
 */
class PluginManagerTest extends TestCase {

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
		$plugins = new PluginManager();

		\WP_Mock::expectActionAdded( 'init', array( $plugins, 'init' ) );
		\WP_Mock::expectFilterAdded( 'plugins_api_args', array( $plugins, 'search_notification' ), 10, 2 );
		\WP_Mock::expectActionAdded( 'admin_menu', array( $plugins, 'remove_redis_menu' ), PHP_INT_MAX );
		\WP_Mock::expectFilterAdded( 'plugin_action_links_redis-cache/redis-cache.php', array( $plugins, 'remove_redis_plugin_links' ), PHP_INT_MAX );
		define(
			'WOOCART_REQUIRED',
			array(
				array(
					'name' => 'Autoptimize',
					'slug' => 'autoptimize',
				),
			)
		);

		$plugins->__construct();
	}

	/**
	 * @covers ::__construct
	 * @covers ::init
	 */
	public function testInitEmpty() {
		$plugins = new PluginManager();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => false,
			)
		);
		\WP_Mock::userFunction(
			'_n_noop',
			array(
				'return' => true,
			)
		);

		$this->assertEmpty( $plugins->init() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::init
	 * @covers ::register
	 */
	public function testInitEmptyTwo() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
						->shouldAllowMockingProtectedMethods()
						->makePartial();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);

		$this->assertEmpty( $mock->init() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::init
	 * @covers ::register
	 * @covers ::is_plugin_active
	 */
	public function testInit() {
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
						->shouldAllowMockingProtectedMethods()
						->makePartial();
		$mock->list    = array(
			array(
				'name' => 'Autoptimize',
				'slug' => 'autoptimize',
			),
		);
		$mock->plugins = array(
			'name' => 'Autoptimize',
			'slug' => 'autoptimize',
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'wp_parse_args',
			array(
				'return' => array(
					'name'      => 'Autoptimize',
					'slug'      => 'autoptimize',
					'file_path' => '',
				),
			)
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'return' => 'autoptimize',
			)
		);

		$mock->shouldReceive( '_get_plugin_basename_from_slug' )
		->andReturn( 'autoptimize/autoptimize.php' );

		\WP_Mock::expectActionAdded( 'admin_init', array( $mock, 'force_activation' ) );
		\WP_Mock::expectActionAdded( 'current_screen', array( $mock, 'plugins_page' ) );

		$mock->init();
	}

	/**
	 * @covers ::__construct
	 * @covers ::init
	 * @covers ::register
	 * @covers ::is_plugin_active
	 */
	public function testInitTwo() {
		define( 'SENDGRID_API_KEY', true );
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
						->shouldAllowMockingProtectedMethods()
						->makePartial();
		$mock->list    = array(
			array(
				'name' => 'Sendgrid',
				'slug' => 'sendgrid-email-delivery-simplified',
			),
		);
		$mock->plugins = array(
			'name' => 'Autoptimize',
			'slug' => 'autoptimize',
		);

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'wp_parse_args',
			array(
				'return' => array(
					'name'      => 'Autoptimize',
					'slug'      => 'autoptimize',
					'file_path' => '',
				),
			)
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'return' => 'autoptimize',
			)
		);

		$mock->shouldReceive( '_get_plugin_basename_from_slug' )
		->andReturn( 'autoptimize/autoptimize.php' );

		\WP_Mock::expectActionAdded( 'admin_init', array( $mock, 'force_activation' ) );
		\WP_Mock::expectActionAdded( 'current_screen', array( $mock, 'plugins_page' ) );

		$mock->init();
	}

	/**
	 * @covers ::__construct
	 * @covers ::plugins_page
	 */
	public function testPluginsPageNotPluginsPage() {
		$plugins = new PluginManager();

		\WP_Mock::userFunction(
			'get_current_screen',
			array(
				'return' => (object) array(
					'id' => 'themes',
				),
			)
		);

		$this->assertEmpty( $plugins->plugins_page() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::plugins_page
	 */
	public function testPluginsPage() {
		$plugins = new PluginManager();

		\WP_Mock::userFunction(
			'get_current_screen',
			array(
				'return' => (object) array(
					'id' => 'plugins',
				),
			)
		);

		\WP_Mock::expectActionAdded( 'after_plugin_row', array( $plugins, 'add_required_text' ), PHP_INT_MAX, 3 );
		\WP_Mock::expectFilterAdded( 'plugin_action_links', array( $plugins, 'remove_deactivation_link' ), PHP_INT_MAX, 4 );

		$plugins->plugins_page();
	}

	/**
	 * @covers ::__construct
	 * @covers ::register
	 */
	public function testRegister() {
		$plugins = new PluginManager();

		\WP_Mock::userFunction(
			'wp_parse_args',
			array(
				'return' => array(
					'name'      => 'Autoptimize',
					'slug'      => 'autoptimize',
					'file_path' => '',
				),
			)
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'return' => 'autoptimize',
			)
		);

		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
						->shouldAllowMockingProtectedMethods()
						->makePartial();
		$mock->shouldReceive( '_get_plugin_basename_from_slug' )
		->andReturn(
			array(
				'autoptimize/autoptimize.php',
			)
		);

		$mock->register(
			array(
				'name' => 'Autoptimize',
				'slug' => 'autoptimize',
			)
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::register
	 */
	public function testRegisterEmpty() {
		$plugins = new PluginManager();
		$this->assertEmpty( $plugins->register( array() ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_plugin_installed
	 * @covers ::get_plugins
	 */
	public function testIsPluginInstalled() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
											->makePartial();
		$mock->shouldReceive( 'get_plugins' )
				 ->andReturn( array( 'fake-one', 'fake-two' ) );

		$this->assertFalse( $mock->is_plugin_installed( 'fake-slug' ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_plugin_active
	 */
	public function testIsPluginActive() {
		$plugins          = new PluginManager();
		$plugins->plugins = array(
			'autoptimize' => array(
				'file_path' => 'autoptimize/autoptimize.php',
			),
		);

		\WP_Mock::userFunction(
			'is_plugin_active',
			array(
				'return' => true,
			)
		);

		$this->assertTrue( $plugins->is_plugin_active( 'autoptimize' ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::add_required_text
	 * @covers ::_wp_version
	 */
	public function testAddRequiredTextNoPlugin() {
		$plugins        = new PluginManager();
		$plugins->paths = array(
			'plugin_file',
		);

		$this->assertEmpty(
			$plugins->add_required_text( 'another_plugin', array( 'Name' => 'Another Plugin' ) )
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::add_required_text
	 * @covers ::_wp_version
	 */
	public function testAddRequiredTextLowerWPVersion() {
		$plugins        = new PluginManager();
		$plugins->paths = array(
			'plugin_file',
		);

		\WP_Mock::userFunction(
			'get_bloginfo',
			array(
				'times'  => 1,
				'return' => '5.4.0',
			)
		);

		$plugins->add_required_text( 'plugin_file', array( 'Name' => 'Plugin' ) );
		$this->expectOutputString( '<tr><td colspan="3" style="background:#fcd670"><strong>Plugin</strong> is a required plugin on WooCart and cannot be deactivated.</td></tr>' );
	}

	/**
	 * @covers ::__construct
	 * @covers ::add_required_text
	 * @covers ::_wp_version
	 */
	public function testAddRequiredText() {
		$plugins        = new PluginManager();
		$plugins->paths = array(
			'plugin_file',
		);

		\WP_Mock::userFunction(
			'get_bloginfo',
			array(
				'times'  => 1,
				'return' => '5.5.0',
			)
		);

		$plugins->add_required_text( 'plugin_file', array( 'Name' => 'Plugin' ) );
		$this->expectOutputString( '<tr><td colspan="4" style="background:#fcd670"><strong>Plugin</strong> is a required plugin on WooCart and cannot be deactivated.</td></tr>' );
	}

	/**
	 * @covers ::__construct
	 * @covers ::remove_deactivation_link
	 */
	public function testRemoveDeactivationLink() {
		$plugins        = new PluginManager();
		$plugins->paths = array(
			'plugin_file',
		);

		$this->assertEquals(
			array( 'Another' => 'This will be returned' ),
			$plugins->remove_deactivation_link(
				array(
					'deactivate' => 'Link',
					'Another'    => 'This will be returned',
				),
				'plugin_file'
			)
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_plugin_active
	 */
	public function testIsPluginActiveTwo() {
		$plugins = new PluginManager();

		$this->assertFalse( $plugins->is_plugin_active( 'fake-slug' ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::_get_plugin_basename_from_slug
	 */
	public function testGetPluginBasenameFromSlug() {
		$method = self::getMethod( '_get_plugin_basename_from_slug' );
		$mock   = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->shouldReceive( 'get_plugins' )
		->andReturn(
			array(
				'autoptimize/autoptimize.php' => array(),
			)
		);

		$this->assertEquals( 'autoptimize/autoptimize.php', $method->invokeArgs( $mock, array( 'autoptimize' ) ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::_get_plugin_basename_from_slug
	 */
	public function testGetPluginBasenameFromSlugTwo() {
		$method = self::getMethod( '_get_plugin_basename_from_slug' );
		$mock   = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->shouldReceive( 'get_plugins' )
		->andReturn(
			array(
				'something/something.php' => array(),
			)
		);

		$this->assertEquals( 'fakeslug', $method->invokeArgs( $mock, array( 'fakeslug' ) ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::force_activation
	 * @covers ::is_plugin_active
	 * * @covers ::is_plugin_installed
	 */
	public function testForceActivation() {
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->plugins = array(
			'autoptimize' => array(
				'name'      => 'Autoptimize',
				'slug'      => 'autoptimize',
				'file_path' => 'autoptimize/autoptimize.php',
			),
		);
		$mock->shouldReceive( 'get_plugins' )
		->andReturn(
			array(
				'autoptimize/autoptimize.php',
			)
		);

		$mock->force_activation();
	}

	/**
	 * @covers ::__construct
	 * @covers ::force_activation
	 * @covers ::is_plugin_active
	 * @covers ::is_plugin_installed
	 */
	public function testForceActivationTwo() {
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->plugins = array(
			'autoptimize' => array(
				'name'      => 'Autoptimize',
				'slug'      => 'autoptimize',
				'file_path' => 'autoptimize/autoptimize.php',
			),
		);
		$mock->shouldReceive( 'get_plugins' )
		->andReturn(
			array(
				'autoptimize/autoptimize.php',
			)
		);
		$mock->shouldReceive( 'is_plugin_installed' )
		->andReturn( true );
		$mock->shouldReceive( 'is_plugin_active' )
		->andReturn( false );
		\WP_Mock::userFunction(
			'activate_plugin',
			array(
				'return' => true,
			)
		);

		$mock->force_activation();
	}

	/**
	 * @covers ::__construct
	 * @covers ::force_activation
	 * @covers ::is_plugin_installed
	 */
	public function testForceActivationEmpty() {
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
		$mock->plugins = array(
			'autoptimize' => array(
				'name'      => 'Autoptimize',
				'slug'      => 'autoptimize',
				'file_path' => 'autoptimize/autoptimize.php',
			),
		);
		$mock->shouldReceive( 'is_plugin_installed' )
		->andReturn( false );

		$this->assertEmpty( $mock->force_activation() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::search_notification
	 * @covers ::array_match
	 */
	public function testSeachNotificationBackup() {
		$plugins = new PluginManager();
		$object  = (object) array(
			'search' => 'backup',
		);

		\WP_Mock::expectActionAdded( 'install_plugins_table_header', array( $plugins, 'add_text' ) );
		$plugins->search_notification( $object, 'query_api' );
	}

	/**
	 * @covers ::__construct
	 * @covers ::search_notification
	 * @covers ::array_match
	 */
	public function testSeachNotificationSecurity() {
		$plugins = new PluginManager();
		$object  = (object) array(
			'search' => 'wordfence',
		);

		\WP_Mock::expectActionAdded( 'install_plugins_table_header', array( $plugins, 'add_text' ) );
		$plugins->search_notification( $object, 'query_api' );
	}

	/**
	 * @covers ::__construct
	 * @covers ::search_notification
	 * @covers ::array_match
	 */
	public function testSeachNotificationPerformance() {
		$plugins = new PluginManager();
		$object  = (object) array(
			'search' => 'smush',
		);

		\WP_Mock::expectActionAdded( 'install_plugins_table_header', array( $plugins, 'add_text' ) );
		$plugins->search_notification( $object, 'query_api' );
	}

	/**
	 * @covers ::__construct
	 * @covers ::search_notification
	 * @covers ::array_match
	 */
	public function testSeachNotificationNoMatch() {
		$plugins = new PluginManager();
		$object  = (object) array(
			'search' => 'random',
		);

		\WP_Mock::expectActionNotAdded( 'install_plugins_table_header', array( $plugins, 'add_text' ) );
		$plugins->search_notification( $object, 'query_api' );
	}

	/**
	 * @covers ::__construct
	 * @covers ::remove_redis_menu
	 */
	public function testRemoveRedisMenu() {
		$plugins = new PluginManager();

		\WP_Mock::userFunction(
			'remove_submenu_page',
			array(
				'args'   => array(
					'options-general.php',
					'redis-cache',
				),
				'times'  => 1,
				'return' => true,
			)
		);

		$plugins->remove_redis_menu();
	}

	/**
	 * @covers ::__construct
	 * @covers ::remove_redis_plugin_links
	 */
	public function testRemoveRedisPluginLinks() {
		$plugins = new PluginManager();

		$this->assertEquals(
			array( 1 => 'deactivate.php' ),
			$plugins->remove_redis_plugin_links( array( 'settings.php', 'deactivate.php' ) )
		);
	}

	protected static function getMethod( $name ) {
		$class  = new ReflectionClass( 'Niteo\WooCart\Defaults\PluginManager' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );

		return $method;
	}



}
