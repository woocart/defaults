<?php


use Niteo\WooCart\Defaults\CacheManager;
use PHPUnit\Framework\TestCase;

class CacheManagerTest extends TestCase
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
   * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
   */
  public function testConstructor()
  {
    $cache = new CacheManager();

    \WP_Mock::expectActionAdded( 'admin_bar_menu', [ $cache, 'admin_button' ], 100 );
    \WP_Mock::expectActionAdded( 'admin_init', [ $cache, 'check_cache_request' ] );
    \WP_Mock::expectActionAdded( 'activated_plugin', [ $cache, 'flush_opcache' ] );
    \WP_Mock::expectActionAdded( 'deactivated_plugin', [ $cache, 'flush_opcache' ] );
    \WP_Mock::expectActionAdded( 'upgrader_process_complete', [ $cache, 'flush_opcache' ] );
    \WP_Mock::expectActionAdded( 'check_theme_switched', [ $cache, 'flush_opcache' ] );
    \WP_Mock::expectActionAdded( 'save_post', [ $cache, 'flush_redis_cache' ] );
    \WP_Mock::expectActionAdded( 'save_post', [ $cache, 'flush_fcgi_cache' ] );
    \WP_Mock::expectActionAdded( 'after_delete_post', [ $cache, 'flush_redis_cache' ] );
    \WP_Mock::expectActionAdded( 'after_delete_post', [ $cache, 'flush_fcgi_cache' ] );
    \WP_Mock::expectActionAdded( 'customize_save_after', [ $cache, 'flush_redis_cache' ] );
    \WP_Mock::expectActionAdded( 'customize_save_after', [ $cache, 'flush_fcgi_cache' ] );
    \WP_Mock::expectActionAdded( 'woocommerce_reduce_order_stock', [ $cache, 'flush_redis_cache' ] );
    \WP_Mock::expectActionAdded( 'woocommerce_reduce_order_stock', [ $cache, 'flush_fcgi_cache' ] );

    $cache->__construct();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
   * @covers \Niteo\WooCart\Defaults\CacheManager::admin_button
   */
  public function testAdminButton() {
    $cache      = new CacheManager();
    \WP_Mock::userFunction(
      'is_admin', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'is_admin_bar_showing', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'add_query_arg', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'wp_nonce_url', [
        'return' => true
      ]
    );

    $admin_bar  = $this->getMockBuilder( FakeMenuClass::class )
                          ->setMethods( [ 'add_menu' ] )
                          ->getMock();
    $cache->admin_button( $admin_bar );
  }

}
