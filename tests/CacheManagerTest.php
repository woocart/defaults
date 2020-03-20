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
		\WP_Mock::expectActionAdded( 'customize_save_after', array( $cache, 'flush_fcgi_cache' ) );
		\WP_Mock::expectActionAdded( 'woocommerce_reduce_order_stock', array( $cache, 'flush_fcgi_cache' ) );
		\WP_Mock::expectActionAdded( 'elementor/editor/after_save', array( $cache, 'flush_fcgi_cache' ) );
		\WP_Mock::expectActionAdded( 'fl_builder_after_save_layout', array( $cache, 'flush_fcgi_cache' ) );
		\WP_Mock::expectActionAdded( 'wp_ajax_edit_theme_plugin_file', array( $cache, 'flush_cache' ), PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'woocommerce_after_edit_attribute_fields', array( $cache, 'flush_fcgi_cache' ) );

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

		\WP_Mock::userFunction(
			'add_query_arg',
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

		define( 'WP_REDIS_PATH', '/path/to/fake/redis.sock' );

		$cache->check_cache_request();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::check_cache_request
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_opcache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_redis_cache
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
		\WP_Mock::userFunction(
			'add_query_arg',
			array(
				'return' => true,
			)
		);

		$mock->check_cache_request();
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
		$plugins = new CacheManager();

		// FIX: for failing test
		@mkdir( 'tests/cache' );
		@mkdir( 'tests/cache/a' );
		@mkdir( 'tests/cache/f' );
		@mkdir( 'tests/cache/d' );
		file_put_contents( 'tests/cache/a/aaa', 'data' );
		file_put_contents( 'tests/cache/f/aaa', 'data' );
		file_put_contents( 'tests/cache/d/aaa', 'data' );
		$plugins->fcgi_path = 'tests/cache';

		$this->assertTrue( $plugins->flush_fcgi_cache() );
		$this->assertFalse( file_exists( 'tests/cache/a/aaa' ) );
		$this->assertFalse( file_exists( 'tests/cache/f/aaa' ) );
		$this->assertFalse( file_exists( 'tests/cache/d/aaa' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 */
	public function testFlushFcgiCacheFalse() {
		$plugins = new CacheManager();
		$this->assertFalse( $plugins->flush_fcgi_cache() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 */
	public function testFlushFcgiCacheNoDirectory() {
		$plugins            = new CacheManager();
		$plugins->fcgi_path = 'foo/tests/cache';

		$this->assertFalse( $plugins->flush_fcgi_cache() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_redis_cache
	 */
	public function testFlushRedisCache() {

		$redis = \Mockery::mock( '\Redis' );
		$redis->shouldReceive( 'flushAll' )
			->andReturn( true );
		$cache = new CacheManager();
		$cache->flush_redis_cache();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_bb_cache
	 */
	public function testFlushBbCache() {
		$cache = new CacheManager();

		$builder = \Mockery::mock( 'alias:\FLBuilderModel' )
							->shouldReceive( 'delete_asset_cache_for_all_posts' );

		$customizer = \Mockery::mock( 'alias:\FLCustomizer' )
							->shouldReceive( 'clear_all_css_cache' );

		$cache->flush_bb_cache();
	}

	protected static function getMethod( $name ) {
		$class  = new ReflectionClass( 'Niteo\WooCart\Defaults\CacheManager' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );
		return $method;
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache_selectively_on_save
	 */
	public function testFlushOnSaveSelectively() {

		\WP_Mock::userFunction(
			'wp_is_post_revision',
			array(
				'return' => false,
			)
		);
		$post = new Class() {
			public $post_type = 'post';
		};

		$page = new Class() {
			public $post_type = 'page';
		};

		$product = new Class() {
			public $post_type = 'product';
		};

		$postmethod    = self::getMethod( 'flush_fcgi_cache_selectively_on_save' );
		$pagemethod    = self::getMethod( 'flush_fcgi_cache_selectively_on_save' );
		$productmethod = self::getMethod( 'flush_fcgi_cache_selectively_on_save' );
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$mock->shouldReceive( 'flush_fcgi_cache' )
			->andReturn( true );

		$postmethod->invokeArgs( $mock, array( 1, $post, false ) );
		$pagemethod->invokeArgs( $mock, array( 1, $page, false ) );
		$productmethod->invokeArgs( $mock, array( 1, $product, false ) );
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache_selectively_on_save
	 */
	public function testFlushOnSaveSelectivelySkipRevision() {

		\WP_Mock::userFunction(
			'wp_is_post_revision',
			array(
				'return' => true,
			)
		);
		$post = new Class() {
			public $post_type = 'post';
		};

		$page = new Class() {
			public $post_type = 'page';
		};

		$product = new Class() {
			public $post_type = 'product';
		};

		$postmethod    = self::getMethod( 'flush_fcgi_cache_selectively_on_save' );
		$pagemethod    = self::getMethod( 'flush_fcgi_cache_selectively_on_save' );
		$productmethod = self::getMethod( 'flush_fcgi_cache_selectively_on_save' );
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$mock->shouldNotReceive( 'flush_fcgi_cache' );

		$postmethod->invokeArgs( $mock, array( 1, $post, false ) );
		$pagemethod->invokeArgs( $mock, array( 1, $page, false ) );
		$productmethod->invokeArgs( $mock, array( 1, $product, false ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache_selectively_on_delete
	 */
	public function testFlushOnDeleteSelectivelyPost() {

		$post = new Class() {
			public $post_type = 'post';
		};

		\WP_Mock::userFunction(
			'get_post_type',
			array(
				'return' => array( $post ),
			)
		);

		$postmethod = self::getMethod( 'flush_fcgi_cache_selectively_on_delete' );

		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$mock->shouldReceive( 'flush_fcgi_cache' )
			->andReturn( true );

		$postmethod->invokeArgs( $mock, array( 1 ) );

	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache_selectively_on_delete
	 */
	public function testFlushOnDeleteSelectivelyPage() {

		$page = new Class() {
			public $post_type = 'page';
		};

		\WP_Mock::userFunction(
			'get_post_type',
			array(
				'return' => array( $page ),
			)
		);

		$pagemethod = self::getMethod( 'flush_fcgi_cache_selectively_on_delete' );
		$mock       = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$mock->shouldReceive( 'flush_fcgi_cache' )
			->andReturn( true );

		$pagemethod->invokeArgs( $mock, array( 1 ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\CacheManager::__construct
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache
	 * @covers \Niteo\WooCart\Defaults\CacheManager::flush_fcgi_cache_selectively_on_delete
	 */
	public function testFlushOnDeleteSelectivelyProduct() {

		$product = new Class() {
			public $post_type = 'product';
		};

		\WP_Mock::userFunction(
			'get_post_type',
			array(
				'return' => array( $product ),
			)
		);

		$productmethod = self::getMethod( 'flush_fcgi_cache_selectively_on_delete' );
		$mock          = \Mockery::mock( 'Niteo\WooCart\Defaults\CacheManager' )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$mock->shouldReceive( 'flush_fcgi_cache' )
			->andReturn( true );

		$productmethod->invokeArgs( $mock, array( 1 ) );
	}
}
