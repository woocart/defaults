<?php

use Niteo\WooCart\Defaults\UserCache;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\UserCache
 */
class UserCacheTest extends TestCase {

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
		$usercache = new UserCache();

		\WP_Mock::expectActionAdded( 'muplugins_loaded', array( $usercache, 'init' ) );

		$usercache->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::init
	 */
	public function testInitIsAdmin() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $usercache->init() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::init
	 */
	public function testConstructorNotAdmin() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		\WP_Mock::expectActionAdded( 'init', array( $usercache, 'set_cache_key' ), 10 );
		\WP_Mock::expectActionAdded( 'init', array( $usercache, 'is_cacheable' ), 12 );
		\WP_Mock::expectActionAdded( 'init', array( $usercache, 'check_cache' ), 15 );
		\WP_Mock::expectActionAdded( 'init', array( $usercache, 'maybe_serve_cache' ), 20 );
		\WP_Mock::expectActionAdded( 'wp_loaded', array( $usercache, 'start_buffering' ), ~PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'shutdown', array( $usercache, 'register_shutdown' ), ~PHP_INT_MAX );

		$usercache->init();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_cache_key
	 */
	public function testSetCacheKeyNoAuthCookie() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'wp_parse_auth_cookie',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $usercache->set_cache_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_cache_key
	 */
	public function testSetCacheKeyInvalidCookieData() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'wp_parse_auth_cookie',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $usercache->set_cache_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_cache_key
	 */
	public function testSetCacheKeyEmptyToken() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'wp_parse_auth_cookie',
			array(
				'times'  => 1,
				'return' => array(
					'token' => '',
				),
			)
		);

		$this->assertEmpty( $usercache->set_cache_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_cache_key
	 */
	public function testSetCacheKeyEmptyRequestUri() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'wp_parse_auth_cookie',
			array(
				'times'  => 1,
				'return' => array(
					'token' => 'TOKEN_VALUE',
				),
			)
		);

		$this->assertEmpty( $usercache->set_cache_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_cache_key
	 */
	public function testSetCacheKeySuccess() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'wp_parse_auth_cookie',
			array(
				'times'  => 1,
				'return' => array(
					'token' => 'TOKEN_VALUE',
				),
			)
		);

		$_SERVER['REQUEST_URI'] = '/blog-page';

		\WP_Mock::userFunction(
			'sanitize_text_field',
			array(
				'times'  => 2,
				'return' => 'SANITIZED_VALUE',
			)
		);

		$this->assertEmpty( $usercache->set_cache_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_cacheable
	 */
	public function testIsCacheablePostMethod() {
		$usercache = new UserCache();

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->assertEmpty( $usercache->is_cacheable() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_cacheable
	 */
	public function testIsCacheableNotLoggedIn() {
		$usercache = new UserCache();

		$_SERVER['REQUEST_METHOD'] = 'GET';

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $usercache->is_cacheable() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_cacheable
	 * @covers ::check_query_strings
	 */
	public function testIsCacheableAddToCartOne() {
		$usercache = new UserCache();

		$_SERVER['REQUEST_METHOD'] = 'GET';

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$_SERVER['REQUEST_URI'] = '/blog-page?add_to_cart=100';

		\WP_Mock::userFunction(
			'wp_cache_delete',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $usercache->is_cacheable() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_cacheable
	 * @covers ::check_query_strings
	 */
	public function testIsCacheableAddToCartTwo() {
		$usercache = new UserCache();

		$_SERVER['REQUEST_METHOD'] = 'GET';

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$_SERVER['REQUEST_URI'] = '/blog-page?add-to-cart=100';

		\WP_Mock::userFunction(
			'wp_cache_delete',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $usercache->is_cacheable() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_cacheable
	 * @covers ::check_query_strings
	 */
	public function testIsCacheableAddToWishlistOne() {
		$usercache = new UserCache();

		$_SERVER['REQUEST_METHOD'] = 'GET';

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$_SERVER['REQUEST_URI'] = '/blog-page?add_to_wishlist=100';

		\WP_Mock::userFunction(
			'wp_cache_delete',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $usercache->is_cacheable() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_cacheable
	 * @covers ::check_query_strings
	 */
	public function testIsCacheableAddToWishlistTwo() {
		$usercache = new UserCache();

		$_SERVER['REQUEST_METHOD'] = 'GET';

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$_SERVER['REQUEST_URI'] = '/blog-page?add-to-wishlist=100';

		\WP_Mock::userFunction(
			'wp_cache_delete',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $usercache->is_cacheable() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_cacheable
	 * @covers ::check_query_strings
	 */
	public function testIsCacheableSuccess() {
		$usercache = new UserCache();

		$_SERVER['REQUEST_METHOD'] = 'GET';

		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$_SERVER['REQUEST_URI'] = '/blog-page';

		$this->assertEmpty( $usercache->is_cacheable() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_cache
	 */
	public function testCheckCacheNotCacheable() {
		$usercache = new UserCache();

		$this->assertEmpty( $usercache->check_cache() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_cache
	 * @covers ::get_is_cacheable
	 * @covers ::get_key
	 */
	public function testCheckCacheEmptyKey() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\UserCache' )->makePartial();
		$mock->shouldReceive( 'get_is_cacheable' )->andReturn( true );
		$mock->shouldReceive( 'get_key' )->andReturn( '' );

		$this->assertEmpty( $mock->check_cache() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_cache
	 * @covers ::get_is_cacheable
	 * @covers ::get_key
	 */
	public function testCheckCacheEmptyCache() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\UserCache' )->makePartial();
		$mock->shouldReceive( 'get_is_cacheable' )->andReturn( true );
		$mock->shouldReceive( 'get_key' )->andReturn( 'CACHE_KEY' );

		\WP_Mock::userFunction(
			'wp_cache_get',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $mock->check_cache() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_cache
	 * @covers ::get_is_cacheable
	 * @covers ::get_key
	 */
	public function testCheckCacheNotHtml() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\UserCache' )->makePartial();
		$mock->shouldReceive( 'get_is_cacheable' )->andReturn( true );
		$mock->shouldReceive( 'get_key' )->andReturn( 'CACHE_KEY' );

		\WP_Mock::userFunction(
			'wp_cache_get',
			array(
				'times'  => 1,
				'return' => 'NOT HTML CONTENT',
			)
		);

		$this->assertEmpty( $mock->check_cache() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_cache
	 * @covers ::get_is_cacheable
	 * @covers ::get_key
	 */
	public function testCheckCacheSuccess() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\UserCache' )->makePartial();
		$mock->shouldReceive( 'get_is_cacheable' )->andReturn( true );
		$mock->shouldReceive( 'get_key' )->andReturn( 'CACHE_KEY' );

		\WP_Mock::userFunction(
			'wp_cache_get',
			array(
				'times'  => 1,
				'return' => '<html>SOME CONTENT</html>',
			)
		);

		$this->assertEmpty( $mock->check_cache() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::maybe_serve_cache
	 * @covers ::get_cache_exists
	 */
	public function testMaybeServeCache() {
		$usercache = new UserCache();

		$this->assertEmpty( $usercache->maybe_serve_cache() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_cache
	 * @covers ::get_cache_exists
	 * @covers ::get_cache
	 * @covers ::terminate
	 */
	public function testMaybeServeCacheExists() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\UserCache' )->makePartial()->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'get_cache_exists' )->andReturn( true );
		$mock->shouldReceive( 'get_cache' )->andReturn( 'CACHED CONTENT' );
		$mock->shouldReceive( 'terminate' )->andReturn( true );

		$mock->maybe_serve_cache();
		$this->expectOutputString( 'CACHED CONTENT' );
	}

	/**
	 * @covers ::__construct
	 * @covers ::start_buffering
	 */
	public function testStartBufferingNotCacheable() {
		$usercache = new UserCache();

		$this->assertEmpty( $usercache->start_buffering() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::start_buffering
	 */
	public function testStartBufferingIsCacheable() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\UserCache' )->makePartial();
		$mock->shouldReceive( 'get_is_cacheable' )->andReturn( true );

		$this->assertEmpty( $mock->start_buffering() );
		ob_end_flush();
	}

	/**
	 * @covers ::__construct
	 * @covers ::get_is_cacheable
	 */
	public function testIsCacheable() {
		$usercache = new UserCache();

		$this->assertFalse( $usercache->get_is_cacheable() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::register_shutdown
	 */
	public function testRegisterShutdownNotCacheable() {
		$usercache = new UserCache();

		$this->assertEmpty( $usercache->register_shutdown() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::register_shutdown
	 * @covers ::get_is_cacheable
	 */
	public function testRegisterShutdownSuccess() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\UserCache' )->shouldAllowMockingProtectedMethods()->makePartial();
		$mock->shouldReceive( 'get_is_cacheable' )->andReturn( true );
		$mock->shouldReceive( 'terminate' )->andReturn( true );

		\WP_Mock::userFunction(
			'wp_cache_set',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $mock->register_shutdown() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::flush_cache
	 */
	public function testFlushCacheTrue() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'wp_cache_delete',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertTrue( $usercache->flush_cache( 'KEY' ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::flush_cache
	 */
	public function testFlushCacheFalse() {
		$usercache = new UserCache();

		\WP_Mock::userFunction(
			'wp_cache_delete',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertFalse( $usercache->flush_cache( 'KEY' ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_html
	 */
	public function testIsHtmlTrue() {
		$usercache = new UserCache();

		$this->assertTrue( $usercache->is_html( '<html>HTML CONTENT</html>' ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::is_html
	 */
	public function testIsHtmlFalse() {
		$usercache = new UserCache();

		$this->assertFalse( $usercache->is_html( 'NOT AN HTML CONTENT' ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::get_cache
	 */
	public function testGetCache() {
		$usercache = new UserCache();

		$this->assertEmpty( $usercache->get_cache() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::get_key
	 */
	public function testGetKey() {
		$usercache = new UserCache();

		$this->assertEmpty( $usercache->get_key() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::get_cache_group
	 */
	public function testGetCacheGroup() {
		$usercache = new UserCache();

		$this->assertEquals( 'woocart-user-cache', $usercache->get_cache_group() );
	}

}
