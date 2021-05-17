<?php

use Niteo\WooCart\Defaults\CacheManager;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\CacheManager
 */
class ApiCacheTest extends TestCase {

	public function setUp() : void {
		\WP_Mock::setUp();
	}

	public function tearDown() : void {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
		\Mockery::close();
	}

	/**
	 * @covers ::__construct
	 * @covers ::api_init
	 */
	public function testApiInitNoCacheKey() {
		$cache = \Mockery::mock( CacheManager::class )->makePartial();
		$cache->shouldReceive( 'create_cache_key' )->andReturn( false );

		$this->assertEmpty( $cache->api_init() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::api_init
	 * @covers ::create_cache_key
	 */
	public function testApiInitWithCacheKey() {
		$cache                  = new CacheManager();
		$_SERVER['REQUEST_URI'] = 'https://api.test/wp-json/wc/v3/reports?period=week';

		\WP_Mock::userFunction(
			'sanitize_text_field',
			array(
				'times'  => 1,
				'return' => 'https://api.test/wp-json/wc/v3/reports?period=week',
			)
		);

		\WP_Mock::userFunction(
			'wp_parse_url',
			array(
				'times'  => 1,
				'return' => array(
					'path'  => 'https://api.test/wp-json/wc/v3/reports',
					'query' => 'period=week',
				),
			)
		);

		\WP_Mock::userFunction(
			'wp_parse_args',
			array(
				'times'  => 1,
				'return' => array(
					'period' => 'week',
				),
			)
		);

		\WP_Mock::userFunction(
			'wp_json_encode',
			array(
				'times'  => 1,
				'return' => 'JSON_ENCODED_STRING',
			)
		);

		\WP_Mock::expectFilterAdded( 'rest_pre_dispatch', array( $cache, 'serve_cache' ), PHP_INT_MAX, 3 );
		\WP_Mock::expectFilterAdded( 'rest_pre_echo_response', array( $cache, 'set_cache' ), PHP_INT_MAX, 3 );

		$cache->api_init();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::create_cache_key
	 */
	public function testCreateCacheKeyNoUrl() {
		$cache                  = new CacheManager();
		$_SERVER['REQUEST_URI'] = '';

		$this->assertEmpty( $cache->create_cache_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::create_cache_key
	 */
	public function testCreateCacheKeyNoPathSet() {
		$cache                  = new CacheManager();
		$_SERVER['REQUEST_URI'] = 'https://api.test/wp-json/wc/v3/orders';

		\WP_Mock::userFunction(
			'sanitize_text_field',
			array(
				'times'  => 1,
				'return' => 'https://api.test/wp-json/wc/v3/orders',
			)
		);

		\WP_Mock::userFunction(
			'wp_parse_url',
			array(
				'times'  => 1,
				'return' => array(),
			)
		);

		$this->assertEmpty( $cache->create_cache_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::create_cache_key
	 */
	public function testCreateCacheKeyDiffUrl() {
		$cache                  = new CacheManager();
		$_SERVER['REQUEST_URI'] = 'https://api.test/wp-json/wc/v3/orders';

		\WP_Mock::userFunction(
			'sanitize_text_field',
			array(
				'times'  => 1,
				'return' => 'https://api.test/wp-json/wc/v3/orders',
			)
		);

		\WP_Mock::userFunction(
			'wp_parse_url',
			array(
				'times'  => 1,
				'return' => array(
					'path' => 'https://api.test/wp-json/wc/v3/orders',
				),
			)
		);

		$this->assertEmpty( $cache->create_cache_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::serve_cache
	 */
	public function testServeCacheNoTransient() {
		$cache = new CacheManager();

		\WP_Mock::userFunction(
			'get_transient',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEquals(
			array( 'NOT_CACHED_RESULT' ),
			$cache->serve_cache(
				array( 'NOT_CACHED_RESULT' ),
				\Mockery::mock( \WP_REST_Server::class ),
				\Mockery::mock( \WP_REST_Request::class )
			)
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::serve_cache
	 */
	public function testServeCacheWithTransient() {
		$cache = \Mockery::mock( CacheManager::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$cache->shouldReceive( 'set_header' )->andReturn( true );
		$cache->shouldReceive( 'exit' )->andReturn( true );

		\WP_Mock::userFunction(
			'get_transient',
			array(
				'times'  => 1,
				'return' => array(
					'content' => 'FROM CACHE',
				),
			)
		);

		\WP_Mock::userFunction(
			'wp_json_encode',
			array(
				'times'  => 1,
				'return' => '{"content":"FROM CACHE"}',
			)
		);

		$this->expectOutputString( '{"content":"FROM CACHE"}' );
		$cache->serve_cache(
			array( 'NOT_CACHED_RESULT' ),
			\Mockery::mock( \WP_REST_Server::class ),
			\Mockery::mock( \WP_REST_Request::class )
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_cache
	 */
	public function testSetCacheEmptyResult() {
		$cache = new CacheManager();

		$this->assertEquals(
			array(),
			$cache->set_cache(
				array(),
				\Mockery::mock( \WP_REST_Server::class ),
				\Mockery::mock( \WP_REST_Request::class )
			)
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_cache
	 */
	public function testSetCacheDiffResponse() {
		$cache = new CacheManager();

		$this->assertEquals(
			array(
				'data' => array(
					'status' => 400,
				),
			),
			$cache->set_cache(
				array(
					'data' => array(
						'status' => 400,
					),
				),
				\Mockery::mock( \WP_REST_Server::class ),
				\Mockery::mock( \WP_REST_Request::class )
			)
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_cache
	 */
	public function testSetCacheTrue() {
		$cache = new CacheManager();

		define( 'HOUR_IN_SECONDS', 3600 );

		\WP_Mock::userFunction(
			'set_transient',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEquals(
			array(
				'data' => array(
					'status'   => 200,
					'response' => 'API RESPONSE TO CACHE',
				),
			),
			$cache->set_cache(
				array(
					'data' => array(
						'status'   => 200,
						'response' => 'API RESPONSE TO CACHE',
					),
				),
				\Mockery::mock( \WP_REST_Server::class ),
				\Mockery::mock( \WP_REST_Request::class )
			)
		);
	}

}
