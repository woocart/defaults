<?php


use Niteo\WooCart\Defaults\DenyList;
use PHPUnit\Framework\TestCase;

class DenyListTest extends TestCase
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
   * @covers \Niteo\WooCart\Defaults\DenyList::__construct
   */
  public function testConstructor()
  {
    define( '_FORCED_PLUGINS', true );
    $denylist = new DenyList();

    \WP_Mock::expectFilterAdded( 'plugin_action_links', [ $denylist, 'forced_plugins' ], 10, 4 );
    \WP_Mock::expectFilterAdded( 'plugin_install_action_links', [ $denylist, 'disable_install_link' ], 10, 2 );
    \WP_Mock::expectFilterAdded( 'plugin_action_links', [ $denylist, 'disable_activate_link' ], 10, 2 );

    \WP_Mock::expectActionAdded( 'activate_plugin', [ $denylist, 'disable_activation' ], PHP_INT_MAX, 2 );

    $denylist->__construct();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\DenyList::__construct
   * @covers \Niteo\WooCart\Defaults\DenyList::disable_activation
   * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
   */
  public function testDisableActivationWithString()
  {
    $denylist = new DenyList();

    \WP_Mock::userFunction(
      'has_action', [
        'args'   => [
          'shutdown',
          [
            $denylist,
            'deactivate_plugins'
          ]
        ],
        'return' => false
      ]
    );
    \WP_Mock::expectActionAdded( 'shutdown', [ $denylist, 'deactivate_plugins' ] );

    $denylist->disable_activation( 'wp-clone-by-wp-academy' );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\DenyList::__construct
   * @covers \Niteo\WooCart\Defaults\DenyList::disable_activation
   * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
   */
  public function testDisableActivationWithArray()
  {
    $denylist = new DenyList();

    \WP_Mock::userFunction(
      'has_action', [
        'args'   => [
          'shutdown',
          [
            $denylist,
            'deactivate_plugins'
          ]
        ],
        'return' => false
      ]
    );
    \WP_Mock::expectActionAdded( 'shutdown', [ $denylist, 'deactivate_plugins' ] );

    $denylist->disable_activation( [ 'slug' => 'wp-clone-by-wp-academy' ] );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\DenyList::__construct
   * @covers \Niteo\WooCart\Defaults\DenyList::deactivate_plugins
   */
  public function testDeactivatePlugins()
  {
    $denylist    = new DenyList();

    $refObject   = new ReflectionObject( $denylist );
    $refProperty = $refObject->getProperty( '_plugins_to_deactivate' );
    $refProperty->setAccessible( true );
    $refProperty->setValue( $denylist, [ 'adminer' ] );

    \WP_Mock::userFunction(
      'deactivate_plugins', [
        'args'   => [
          'adminer',
          true
        ],
        'return' => true
      ]
    );

    $denylist->deactivate_plugins();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\DenyList::__construct
   * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
   * @covers \Niteo\WooCart\Defaults\DenyList::disable_install_link
   */
  public function testDisableInstallLinkBlacklisted()
  {
    $denylist = new DenyList();

    $this->assertEquals(
      $denylist->disable_install_link( [], 'adminer' ),
      [ '<a href="javascript:;" title="This plugin is not allowed on our system due to performance, security, or compatibility concerns. Please contact our support with any questions.">Not available</a>' ]
    );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\DenyList::__construct
   * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
   * @covers \Niteo\WooCart\Defaults\DenyList::disable_install_link
   */
  public function testDisableInstallLinkWhitelisted()
  {
    $denylist = new DenyList();

    $this->assertEquals(
      $denylist->disable_install_link( [], 'whitelisted-plugin' ),
      []
    );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\DenyList::__construct
   * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
   * @covers \Niteo\WooCart\Defaults\DenyList::disable_activate_link
   */
  public function testDisableActivateLinkBlacklisted()
  {
    $denylist = new DenyList();

    $this->assertEquals(
      $denylist->disable_activate_link( [ 'activate' => '' ], 'adminer' ),
      [ 'activate' => '<a href="javascript:;" data-plugin="adminer" title="This plugin is not allowed on our system due to performance, security, or compatibility concerns. Please contact our support with any questions.">Not available</a>' ]
    );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\DenyList::__construct
   * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
   * @covers \Niteo\WooCart\Defaults\DenyList::disable_activate_link
   */
  public function testDisableActivateLinkWhitelisted()
  {
    $denylist = new DenyList();

    $this->assertEquals(
      $denylist->disable_activate_link( [], 'whitelisted-plugin' ),
      []
    );
  }

}
