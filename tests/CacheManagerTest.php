<?php

use Niteo\WooCart\Defaults\CacheManager;
use PHPUnit\Framework\TestCase;

class CacheManagerTest extends TestCase {


	public function setUp() {
		\WP_Mock::setUp();
	}

	public function tearDown() {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
		\Mockery::close();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 */
	public function testConstructor() {
		$cache = new CacheManager();

		define( 'FCGI_CACHE_PATH', 'tests/cache' );

		\WP_Mock::expectActionAdded( 'admin_bar_menu', array( $cache, 'admin_button' ), 100 );
		\WP_Mock::expectActionAdded( 'admin_init', array( $cache, 'check_cache_request' ) );
		\WP_Mock::expectActionAdded( 'activated_plugin', array( $cache, 'flush_opcache' ) );
		\WP_Mock::expectActionAdded( 'deactivated_plugin', array( $cache, 'flush_opcache' ) );
		\WP_Mock::expectActionAdded( 'upgrader_process_complete', array( $cache, 'flush_opcache' ) );
		\WP_Mock::expectActionAdded( 'check_theme_switched', array( $cache, 'flush_opcache' ) );
		\WP_Mock::expectActionAdded( 'save_post', array( $cache, 'flush_redis_cache' ) );
		\WP_Mock::expectActionAdded( 'save_post', array( $cache, 'flush_fcgi_cache' ) );
		\WP_Mock::expectActionAdded( 'after_delete_post', array( $cache, 'flush_redis_cache' ) );
		\WP_Mock::expectActionAdded( 'after_delete_post', array( $cache, 'flush_fcgi_cache' ) );
		\WP_Mock::expectActionAdded( 'customize_save_after', array( $cache, 'flush_redis_cache' ) );
		\WP_Mock::expectActionAdded( 'customize_save_after', array( $cache, 'flush_fcgi_cache' ) );
		\WP_Mock::expectActionAdded( 'woocommerce_reduce_order_stock', array( $cache, 'flush_redis_cache' ) );
		\WP_Mock::expectActionAdded( 'woocommerce_reduce_order_stock', array( $cache, 'flush_fcgi_cache' ) );
		\WP_Mock::expectActionAdded( 'wp_ajax_edit_theme_plugin_file', array( $cache, 'flush_cache' ), PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'woocommerce_after_add_attribute_fields', array( $cache, 'flush_redis_cache' ) );
		\WP_Mock::expectActionAdded( 'woocommerce_after_edit_attribute_fields', array( $cache, 'flush_redis_cache' ) );
		\WP_Mock::expectActionAdded( 'updated_option', array( $cache, 'check_updated_option' ), 10, 3 );

		$cache->__construct();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::admin_button
	 */
	public function testAdminButton() {
		$cache = new CacheManager();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'is_admin_bar_showing',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'add_query_arg',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'wp_nonce_url',
			array(
				'return' => true,
			)
		);

		$admin_bar = $this->getMockBuilder( FakeMenuClass::class )
			->setMethods( array( 'add_menu' ) )
			->getMock();
		$cache->admin_button( $admin_bar );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::admin_button
	 */
	public function testAdminButtonFalse() {
		$cache = new CacheManager();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'is_admin_bar_showing',
			array(
				'return' => false,
			)
		);

		$admin_bar = $this->getMockBuilder( FakeMenuClass::class )
			->setMethods( array( 'add_menu' ) )
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
			'is_admin',
			array(
				'return' => false,
			)
		);
		\WP_Mock::userFunction(
			'wp_die',
			array(
				'return' => 'true',
			)
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'return' => 'fake',
			)
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
			'is_admin',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'return' => 'done',
			)
		);
		\WP_Mock::expectActionAdded( 'admin_notices', array( $cache, 'show_notices' ) );

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
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_bb_cache
	 */
	public function testCheckCacheRequestFlushException() {
		$cache = new CacheManager();

		$_REQUEST['wc_cache'] = true;
		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'return' => 'flush',
			)
		);
		\WP_Mock::userFunction(
			'check_admin_referer',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'wp_redirect',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'wp_cache_flush',
			array(
				'return' => true,
			)
		);

		// One cannot mock protected core functions, so we only patch
		// when opcache is not enabled
		if ( ! function_exists( 'opcache_reset' ) ) {
			\WP_Mock::userFunction(
				'opcache_reset',
				array(
					'return' => true,
				)
			);
		}

		define( 'WP_REDIS_SCHEME', 'unix' );
		define( 'WP_REDIS_PATH', '/path/to/fake/redis.sock' );

		$this->expectException( \Predis\Connection\ConnectionException::class );
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
	public function testCheckCacheRequestFlushComplete() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();
		$mock->shouldReceive( 'flush_cache' )
			->andReturn( true );

		$_REQUEST['wc_cache'] = true;
		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'return' => 'flush',
			)
		);
		\WP_Mock::userFunction(
			'check_admin_referer',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'wp_redirect',
			array(
				'return' => true,
			)
		);
		if ( ! function_exists( 'opcache_reset' ) ) {
			\WP_Mock::userFunction(
				'opcache_reset',
				array(
					'return' => true,
				)
			);
		}
		\WP_Mock::userFunction(
			'wp_cache_flush',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'wp_redirect',
			array(
				'return' => true,
			)
		);

		$mock->check_cache_request();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::redis_connect
	 */
	public function testRedisConnect() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();
		$mock->shouldReceive( 'redis_connect' )
			->andReturn( true );

		$this->assertTrue( $mock->redis_connect() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_opcache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_redis_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_bb_cache
	 */
	public function testFlushCache() {
		$method = self::getMethod( 'flush_cache' );
		$mock   = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$mock->shouldReceive( 'flush_opcache' )
			->andReturn( true );
		$mock->shouldReceive( 'flush_redis_cache' )
			->andReturn( true );
		$mock->shouldReceive( 'flush_fcgi_cache' )
			->andReturn( true );
		$mock->shouldReceive( 'flush_bb_cache' )
			->andReturn( true );

		$method->invokeArgs( $mock, array() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 */
	public function testFlushFcgiCacheTrue() {
		$method  = self::getMethod( 'flush_fcgi_cache' );
		$plugins = new CacheManager();

		// FIX: for failing test
		$fp = fopen( 'tests/cache/tmp.txt', 'wb' );
		fwrite( $fp, 'Some text..' );
		fclose( $fp );

		$this->assertTrue( $method->invokeArgs( $plugins, array( 'tests/cache' ) ) );
		$this->assertFalse( file_exists( 'tests/cache/tmp.txt' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 */
	public function testFlushFcgiCacheFalse() {
		$method  = self::getMethod( 'flush_fcgi_cache' );
		$plugins = new CacheManager();

		$this->assertFalse( $method->invokeArgs( $plugins, array( 'tests/cache' ) ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 */
	public function testFlushFcgiCacheNoDirectory() {
		$method  = self::getMethod( 'flush_fcgi_cache' );
		$plugins = new CacheManager();

		$this->assertEmpty( $method->invokeArgs( $plugins, array( 'non/existent/directory' ) ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_redis_cache
	 */
	public function testFlushRedisCache() {
		$method = self::getMethod( 'flush_redis_cache' );
		$mock   = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		\WP_Mock::userFunction(
			'wp_cache_flush',
			array(
				'return' => true,
			)
		);

		$mock->shouldReceive( 'redis_connect' )
			->andReturn( true );
		$mock->connected = true;

		// Fake class with the required method for mocking.
		$fake = new class()
		{
			public function supportsCommand( $arg ) {
				return true;
			}
		};

		$mock->redis = \Mockery::mock( 'Predis\ClientInterface' );
		$mock->redis->shouldReceive( 'getProfile' )
			->andReturn( $fake );

		$mock->redis->shouldReceive( 'scan' )
			->withArgs( array( 0, array( 'MATCH' => 'cache%3A*' ) ) )
			->andReturn( array( 0, array( 'cache%3A1st' ) ) );
		$mock->redis->shouldReceive( 'del' )
			->withArgs( array( 'cache%3A1st' ) )
			->andReturn( true );

		$method->invokeArgs( $mock, array() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_bb_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::setFlbuilder
	 * @covers \Niteo\WooCart\Defaults\CacheManager::setFlcustomizer
	 */
	public function testFlushBbCache() {
		$mock = \Mockery::mock( '\Niteo\WooCart\Defaults\CacheManager' )->makePartial();

		$fl_builder    = \Mockery::mock( 'alias:\FLBuilderModel' )->shouldReceive( 'delete_asset_cache_for_all_posts' );
		$fl_customizer = \Mockery::mock( 'alias:\FLCustomizer' )->shouldReceive( 'clear_all_css_cache' );

		$mock->shouldReceive( 'setFlbuilder' )->with( $fl_builder )->andReturn( true );
		$mock->shouldReceive( 'setFlcustomizer' )->with( $fl_customizer )->andReturn( true );

		$mock->flush_bb_cache();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::check_updated_option
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_redis_cache
	 */
	public function testCheckUpdatedOption() {
		$mock = $this->getMockBuilder( 'Niteo\WooCart\Defaults\CacheManager' )
								 ->setMethods( array( 'flush_redis_cache' ) )
								 ->getMock();
		$mock->expects( $this->once() )
					->method( 'flush_redis_cache' );

		// $mock->shouldReceive('flush_redis_cache')->andReturn(true);
		$mock->check_updated_option( 'widget_', 'old_value', 'new_value' );
	}

	protected static function getMethod( $name ) {
		$class  = new ReflectionClass( 'Niteo\WooCart\Defaults\CacheManager' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );
		return $method;
	}

}
