<?php


use Niteo\WooCart\Defaults\CacheManager;
use PHPUnit\Framework\TestCase;
use Predis\Client;

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

  /**
   * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
   * @covers \Niteo\WooCart\Defaults\CacheManager::admin_button
   */
  public function testAdminButtonFalse() {
    $cache      = new CacheManager();

    \WP_Mock::userFunction(
      'is_admin', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'is_admin_bar_showing', [
        'return' => false
      ]
    );

    $admin_bar  = $this->getMockBuilder( FakeMenuClass::class )
                          ->setMethods( [ 'add_menu' ] )
                          ->getMock();
    $cache->admin_button( $admin_bar );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
   * @covers \Niteo\WooCart\Defaults\CacheManager::check_cache_request
   */
  public function testCheckCacheRequestNoAdmin() {
    $cache = new CacheManager();

    $_REQUEST['wc_cache'] = true;
    \WP_Mock::userFunction(
      'is_admin', [
        'return' => false
      ]
    );
    \WP_Mock::userFunction(
      'wp_die', [
        'return' => 'true'
      ]
    );
    \WP_Mock::userFunction(
      'sanitize_key', [
        'return' => 'fake'
      ]
    );

    $cache->check_cache_request();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
   * @covers \Niteo\WooCart\Defaults\CacheManager::check_cache_request
   */
  public function testCheckCacheRequestDone() {
    $cache = new CacheManager();

    $_REQUEST['wc_cache'] = true;
    \WP_Mock::userFunction(
      'is_admin', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'sanitize_key', [
        'return' => 'done'
      ]
    );
    \WP_Mock::expectActionAdded( 'admin_notices', [ $cache, 'show_notices' ] );

    $cache->check_cache_request();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
   * @covers \Niteo\WooCart\Defaults\CacheManager::check_cache_request
   * @covers \Niteo\WooCart\Defaults\CacheManager::flush_cache
   * @covers \Niteo\WooCart\Defaults\CacheManager::flush_opcache
   * @covers \Niteo\WooCart\Defaults\CacheManager::flush_redis_cache
   * @covers \Niteo\WooCart\Defaults\CacheManager::redis_connect
   * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
   */
  public function testCheckCacheRequestFlushException() {
    $cache = new CacheManager();

    $_REQUEST['wc_cache'] = true;
    \WP_Mock::userFunction(
      'is_admin', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'sanitize_key', [
        'return' => 'flush'
      ]
    );
    \WP_Mock::userFunction(
      'check_admin_referer', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'wp_redirect', [
        'return' => true
      ]
    );
    \WP_Mock::userFunction(
      'wp_cache_flush', [
        'return' => true
      ]
    );

    // One cannot mock protected core functions, so we only patch
    // when opcache is not enabled
    if (!function_exists("opcache_reset")) {
        \WP_Mock::userFunction(
            'opcache_reset', [
              'return' => true
            ]
          );
    }


    define( 'WP_REDIS_SCHEME', 'unix' );
    define( 'WP_REDIS_PATH', '/path/to/fake/redis.sock' );

    $this->expectException(\Predis\Connection\ConnectionException::class);
    $cache->check_cache_request();
  }

}
