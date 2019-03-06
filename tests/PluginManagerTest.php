<?php


use Niteo\WooCart\Defaults\PluginManager;
use PHPUnit\Framework\TestCase;

class PluginManagerTest extends TestCase
{
  function setUp()
  {
    \WP_Mock::setUp();
  }

  function tearDown()
  {
    $this->addToAssertionCount(
        \Mockery::getContainer()->mockery_getExpectationCount()
    );
    \WP_Mock::tearDown();
    \Mockery::close();
  }


  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testConstructor()
  {
    $plugins = new PluginManager();

    \WP_Mock::expectActionAdded( 'init', [ $plugins, 'init' ] );
    define( 'WOOCART_REQUIRED', [
      [
        'name'      => 'Autoptimize',
        'slug'      => 'autoptimize',
        'required'  => true
      ]
    ] );

    $plugins->__construct();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::init
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testInitEmpty()
  {
    $plugins = new PluginManager();

    \WP_Mock::userFunction(
      'is_admin', [
        'return' => false
      ]
    );
    \WP_Mock::userFunction(
      '_n_noop', [
        'return' => true
      ]
    );

    $this->assertEmpty( $plugins->init() );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::init
   * @covers \Niteo\WooCart\Defaults\PluginManager::register
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testInitEmptyTwo()
  {
    $mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
                        ->shouldAllowMockingProtectedMethods()
                        ->makePartial();

    \WP_Mock::userFunction(
      'is_admin', [
        'return' => true
      ]
    );

    $this->assertEmpty( $mock->init() );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::init
   * @covers \Niteo\WooCart\Defaults\PluginManager::register
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_complete
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testInit()
  {
    $mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
                        ->shouldAllowMockingProtectedMethods()
                        ->makePartial();
    $mock->forced_activation = true;
    $mock->plugins = [
      'name'              => 'Autoptimize',
      'slug'              => 'autoptimize',
      'required'          => true
    ];

    \WP_Mock::userFunction(
      'is_admin', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'wp_parse_args', [
        'return' => [
          'name'              => 'Autoptimize',
          'slug'              => 'autoptimize',
          'required'          => true,
          'version'           => '',
          'force_activation'  => true,
          'file_path'         => ''
        ]
      ]
    );
    \WP_Mock::userFunction(
      'sanitize_key', [
        'return' => 'autoptimize'
      ]
    );

    $mock->shouldReceive( '_get_plugin_basename_from_slug' )
        ->andReturn( 'autoptimize/autoptimize.php' );
    $mock->shouldReceive( 'is_complete' )
        ->andReturn( false );

    \WP_Mock::expectActionAdded( 'admin_notices', [ $mock, 'notices' ] );
    \WP_Mock::expectActionAdded( 'admin_enqueue_scripts', [ $mock, 'thickbox' ] );
    \WP_Mock::expectActionAdded( 'admin_init', [ $mock, 'force_activation' ] );

    $mock->init();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::register
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testRegister()
  {
    $plugins = new PluginManager();

    \WP_Mock::userFunction(
      'wp_parse_args', [
        'return' => [
          'name'              => 'Autoptimize',
          'slug'              => 'autoptimize',
          'required'          => true,
          'version'           => '',
          'force_activation'  => true,
          'file_path'         => ''
        ]
      ]
    );
    \WP_Mock::userFunction(
      'sanitize_key', [
        'return' => 'autoptimize'
      ]
    );

    $mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
                        ->shouldAllowMockingProtectedMethods()
                        ->makePartial();
    $mock->shouldReceive( '_get_plugin_basename_from_slug' )
        ->andReturn( [
          'autoptimize/autoptimize.php'
         ] );

    $mock->register([
      'name'              => 'Autoptimize',
      'slug'              => 'autoptimize',
      'required'          => true,
      'force_activation'  => true
    ]);
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::register
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testRegisterEmpty()
  {
    $plugins = new PluginManager();
    $this->assertEmpty( $plugins->register( [] ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_complete
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testIsComplete()
  {
    $plugins = new PluginManager();

    $this->assertTrue( $plugins->is_complete() );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_complete
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testIsCompleteFalse()
  {
    $plugins = new PluginManager();

    $mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
    $mock->plugins = [
      'name'              => 'Autoptimize',
      'slug'              => 'autoptimize',
      'required'          => true
    ];

    $mock->shouldReceive( 'does_plugin_have_update' )
        ->andReturn( true );
    $mock->shouldReceive( 'is_plugin_active' )
        ->andReturn( true );

    $this->assertFalse( $mock->is_complete() );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testIsPluginActive()
  {
    $plugins = new PluginManager();

    \WP_Mock::userFunction(
      'is_plugin_active', [
        'return' => false
      ]
    );

    $this->assertFalse( $plugins->is_plugin_active( 'autoptimize' ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::_get_plugin_basename_from_slug
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testGetPluginBasenameFromSlug()
  {
    $method   = self::getMethod( '_get_plugin_basename_from_slug' );
    $mock     = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
    $mock->shouldReceive( 'get_plugins' )
        ->andReturn( [
          'autoptimize/autoptimize.php' => []
        ] );

    $this->assertEquals( 'autoptimize/autoptimize.php', $method->invokeArgs( $mock, [ 'autoptimize' ] ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::_get_plugin_basename_from_slug
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testGetPluginBasenameFromSlugTwo()
  {
    $method   = self::getMethod( '_get_plugin_basename_from_slug' );
    $mock     = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
    $mock->shouldReceive( 'get_plugins' )
        ->andReturn( [
          'something/something.php' => []
        ] );

    $this->assertEquals( 'fakeslug', $method->invokeArgs( $mock, [ 'fakeslug' ] ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::force_activation
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_installed
   * @covers \Niteo\WooCart\Defaults\PluginManager::get_installed_version
   * @covers \Niteo\WooCart\Defaults\PluginManager::does_plugin_require_update
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_active
   * @covers \Niteo\WooCart\Defaults\PluginManager::can_plugin_activate
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testForceActivation()
  {
    $mock     = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
    $mock->plugins = [
      'autoptimize' => [
        'name'              => 'Autoptimize',
        'slug'              => 'autoptimize',
        'file_path'         => 'autoptimize/autoptimize.php',
        'required'          => true,
        'force_activation'  => true,
        'version'           => '1.0'
      ]
    ];
    $mock->shouldReceive( 'get_plugins' )
        ->andReturn( [
          'autoptimize/autoptimize.php' => [
            'Version' => '1.0'
          ]
        ] );
    \WP_Mock::userFunction(
      'activate_plugin', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'is_plugin_active', [
        'return' => false
      ]
    );

    $mock->force_activation();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::force_activation
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_installed
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testForceActivationEmpty()
  {
    $mock     = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
    $mock->plugins = [
      'autoptimize' => [
        'name'              => 'Autoptimize',
        'slug'              => 'autoptimize',
        'file_path'         => 'autoptimize/autoptimize.php',
        'required'          => true,
        'force_activation'  => true,
        'version'           => '1.0'
      ]
    ];
    $mock->shouldReceive( 'is_plugin_installed' )
        ->andReturn( false );

    $this->assertEmpty( $mock->force_activation() );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::thickbox
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testThickbox()
  {
    $plugins = new PluginManager();

    \WP_Mock::userFunction(
      'get_current_user_id', [
        'return' => 1
      ]
    );
    \WP_Mock::userFunction(
      'get_user_meta', [
        'return' => false
      ]
    );
    \WP_Mock::userFunction(
      'add_thickbox', [
        'return' => true
      ]
    );

    $plugins->thickbox();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::get_info_link
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testGetInfoLink()
  {
    $plugins = new PluginManager();
    $plugins->plugins = [
      'something' => [
        'name' => 'Something'
      ]
    ];

    \WP_Mock::userFunction(
      'self_admin_url', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'add_query_arg', [
        'return' => 'URL'
      ]
    );
    \WP_Mock::userFunction(
      'esc_url', [
        'return' => 'URL'
      ]
    );
    \WP_Mock::userFunction(
      'esc_html', [
        'return' => 'Something'
      ]
    );

    $this->assertEquals( '<a href="URL" class="thickbox">Something</a>', $plugins->get_info_link( 'something' ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::get_installed_version
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testGetInstalledVersion()
  {
    $mock     = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )->makePartial();
    $mock->shouldReceive( 'get_plugins' )
         ->andReturn( [
          'something/something.php' => []
        ] );

    $this->assertEquals( '', $mock->get_installed_version( 'fake' ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_core_update_page
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testIsCoreUpdatePageOne()
  {
    $method = self::getMethod( 'is_core_update_page' );
    $plugins = new PluginManager();
    $this->assertFalse( $method->invokeArgs( $plugins, [] ) ) ;
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_core_update_page
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testIsCoreUpdatePageTwo()
  {
    $method = self::getMethod( 'is_core_update_page' );
    $plugins = new PluginManager();

    \WP_Mock::userFunction(
      'get_current_screen', [
        'return' => (object) [
          'base' => 'update-core'
        ]
      ]
    );

    $this->assertTrue( $method->invokeArgs( $plugins, [] ) ) ;
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_core_update_page
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testIsCoreUpdatePageThree()
  {
    $method = self::getMethod( 'is_core_update_page' );
    $plugins = new PluginManager();

    $_POST['action'] = 'somevalue';
    \WP_Mock::userFunction(
      'get_current_screen', [
        'return' => (object) [
          'base' => 'plugins'
        ]
      ]
    );

    $this->assertTrue( $method->invokeArgs( $plugins, [] ) ) ;
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_core_update_page
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testIsCoreUpdatePageFour()
  {
    $method = self::getMethod( 'is_core_update_page' );
    $plugins = new PluginManager();

    $_POST['action'] = 'somevalue';
    \WP_Mock::userFunction(
      'get_current_screen', [
        'return' => (object) [
          'base' => 'update'
        ]
      ]
    );

    $this->assertTrue( $method->invokeArgs( $plugins, [] ) ) ;
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_core_update_page
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testIsCoreUpdatePageFive()
  {
    $method = self::getMethod( 'is_core_update_page' );
    $plugins = new PluginManager();

    \WP_Mock::userFunction(
      'get_current_screen', [
        'return' => (object) [
          'base' => 'random'
        ]
      ]
    );

    $this->assertFalse( $method->invokeArgs( $plugins, [] ) ) ;
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::settings_errors
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testSettingsErrors()
  {
    global $wp_settings_errors;

    $method = self::getMethod( 'settings_errors' );
    $plugins = new PluginManager();

    $wp_settings_errors = [
      'something' => [
        'setting' => 'wc_plugins'
      ]
    ];

    \WP_Mock::userFunction(
      'settings_errors', [
        'return' => true
      ]
    );

    $method->invokeArgs( $plugins, [] );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::does_plugin_have_update
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testDoesPluginHaveUpdateFalse()
  {
    $plugins = new PluginManager();

    \WP_Mock::userFunction(
      'get_site_transient', [
        'return' => false
      ]
    );

    $this->assertFalse( $plugins->does_plugin_have_update( '' ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::does_plugin_have_update
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testDoesPluginHaveUpdateVersion()
  {
    $plugins = new PluginManager();
    $plugins->plugins = [
      'autoptimize' => [
        'file_path' => 'autoptimize/autoptimize.php'
      ]
    ];

    \WP_Mock::userFunction(
      'get_site_transient', [
        'return' => (object) [
          'response' => [
            'autoptimize/autoptimize.php' => (object) [
              'new_version' => '1.0'
            ]
          ]
        ]
      ]
    );

    $this->assertEquals( '1.0', $plugins->does_plugin_have_update( 'autoptimize' ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::notices
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_core_update_page
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testNoticesEmpty()
  {
    $mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
                        ->shouldAllowMockingProtectedMethods()
                        ->makePartial();
    $mock->shouldReceive( 'is_core_update_page' )
         ->andReturn( true );

    $this->assertEmpty( $mock->notices() );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\PluginManager::__construct
   * @covers \Niteo\WooCart\Defaults\PluginManager::notices
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_core_update_page
   * @covers \Niteo\WooCart\Defaults\PluginManager::settings_errors
   * @covers \Niteo\WooCart\Defaults\PluginManager::is_plugin_installed
   * @covers \Niteo\WooCart\Defaults\PluginManager::can_plugin_activate
   * @covers \Niteo\WooCart\Defaults\PluginManager::does_plugin_require_update
   * @covers \Niteo\WooCart\Defaults\PluginManager::get_installed_version
   * @covers \Niteo\WooCart\Defaults\PluginManager::get_info_link
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testNotices()
  {
    $mock = \Mockery::mock( 'Niteo\WooCart\Defaults\PluginManager' )
                        ->shouldAllowMockingProtectedMethods()
                        ->makePartial();
    $mock->plugins = [
      'autoptimize' => [
        'name'              => 'Autoptimize',
        'slug'              => 'autoptimize',
        'required'          => true,
        'file_path'         => 'autoptimize/autoptimize.php',
        'version'           => '1.0'
      ]
    ];

    $mock->shouldReceive( 'get_plugins' )
         ->andReturn( [
          'autoptimize/autoptimize.php' => []
        ] );
    $mock->shouldReceive( 'is_core_update_page' )
         ->andReturn( false );
    $mock->shouldReceive( 'is_plugin_active' )
         ->andReturn( false );
    $mock->shouldReceive( 'does_plugin_have_update' )
         ->andReturn( true );
    $mock->shouldReceive( 'is_plugin_installed' )
         ->andReturn( true );
    $mock->shouldReceive( 'can_plugin_activate' )
         ->andReturn( true );

    \WP_Mock::userFunction(
      'get_current_user_id', [
        'return' => 1
      ]
    );
    \WP_Mock::userFunction(
      'get_user_meta', [
        'return' => false
      ]
    );
    \WP_Mock::userFunction(
      'current_user_can', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'translate_nooped_plural', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'get_current_screen', [
        'return' => (object) [
          'base' => 'fake-screen'
        ]
      ]
    );

    $GLOBALS['current_screen'] = (object) [
      'parent_base' => 'options-none'
    ];
  }

  protected static function getMethod( $name )
  {
    $class  = new ReflectionClass( 'Niteo\WooCart\Defaults\PluginManager' );
    $method = $class->getMethod( $name );
    $method->setAccessible( true );

    return $method;
  }

}
