<?php

namespace Niteo\WooCart\Defaults;

use Niteo\WooCart\Defaults\AutoLogin;
use PHPUnit\Framework\TestCase;

function setcookie( $name, $value, $options ) {
	return AutoLoginTest::$functions->setcookie( $name, $value, $options );
}
function time() {
	return 100;
}

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\AutoLogin
 */
class AutoLoginTest extends TestCase {

	public static $functions;

	function setUp() : void {
		\WP_Mock::setUp();
		self::$functions = \Mockery::mock();

		\WP_Mock::userFunction(
			'is_blog_installed',
			array(
				'return' => true,
			)
		);
		$_SERVER['STORE_ID'] = 'uuid-42';
		\WP_Mock::userFunction(
			'get_site_url',
			array(
				'return' => 'http://localhost',
			)
		);
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
		$login = new AutoLogin();
		\WP_Mock::expectActionAdded( 'login_header', array( $login, 'test_for_auto_login' ) );

		$login->__construct();

	}

	/**
	 * @covers ::__construct
	 * @covers ::test_for_auto_login
	 * @covers \Niteo\WooCart\Defaults\Extend\Dashboard::is_dashboard_active
	 * @covers \Niteo\WooCart\Defaults\Extend\Dashboard::is_staging
	 */
	public function testTest_for_auto_login() {
		$_SERVER['STORE_STAGING'] = 'no';
		\WP_Mock::userFunction(
			'is_user_logged_in',
			array(
				'return_in_order' => array( true, false, false, false ),
				'times'           => 4,
			)
		);
		\WP_Mock::userFunction(
			'wp_safe_redirect',
			array(
				'times' => 2,
			)
		);
		\WP_Mock::userFunction(
			'add_query_arg',
			array(
				'times'  => 2,
				'return' => 'store.com/wp-admin',
			)
		);
		\WP_Mock::userFunction(
			'admin_url',
			array(
				'return' => 'store.com/wp-admin',
				'times'  => 2,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'times' => 2,
			)
		);

		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\AutoLogin' )->makePartial();
		$mock->shouldReceive( 'validate_jwt_token' )
			->with( 'foo_jwt_auth_token', 'sharedSecret' )
			->once()
			->andReturn( true );
		$mock->shouldReceive( 'validate_jwt_token' )
			->with( 'foo_invalid_jwt_auth_token', 'sharedSecret' )
			->once()
			->andReturn( false );
		$mock->shouldReceive( 'auto_login' )->once();

		$_GET['auth'] = 'foo_jwt_auth_token';

		// user is logged in, redirect to admin
		$mock->test_for_auto_login();

		// user is not logged in but WOOCART_LOGIN_SHARED_SECRET_PATH is not
		// defined
		$mock->test_for_auto_login();

		define(
			'WOOCART_LOGIN_SHARED_SECRET_PATH',
			dirname( __FILE__ ) . '/fixtures/loginSharedSecret'
		);
		// everything is ok, login user and redirect to admin
		$mock->test_for_auto_login();

		$_GET['auth'] = 'foo_invalid_jwt_auth_token';
		// invalid jwt token
		$mock->test_for_auto_login();
	}

	/**
	 * @covers ::__construct
	 * @covers ::auto_login
	 * @covers ::set_cookie
	 */
	public function testAuto_login() {
		$user     = \Mockery::mock();
		$user->ID = 1;
		$user->shouldReceive( 'get' )
			->with( 'user_login' )
			->once()
			->andReturn( 'user' );
		\WP_Mock::userFunction(
			'get_users',
			array(
				'args'   => array(
					array(
						'role'    => 'administrator',
						'orderby' => 'ID',
					),
				),
				'return' => array( $user ),
				'times'  => 1,
			)
		);
		\WP_Mock::userFunction(
			'wp_set_auth_cookie',
			array(
				'args'  => array( 1, true, '' ),
				'times' => 1,
			)
		);
		\WP_Mock::expectAction( 'wp_login', 'user', $user );

		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\AutoLogin' )->makePartial();
		$mock->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'set_cookie' )
				 ->andReturn( true );
		$mock->auto_login();
	}

	/**
	 * @covers ::__construct
	 * @covers ::validate_jwt_token
	 */
	public function testValidate_jwt_token() {
		$login = new AutoLogin();

		// valid token without time limit
		$token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE1MTYyMzkwMjJ9.3-LrEOL2cAHF0j1pmTKdb2852Uptw0B9a8hUyqNS260';
		$result = $login->validate_jwt_token( $token, 'sharedSecret' );
		$this->assertTrue( $result );
		// valid token with expired time limit
		$token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE1MTYyMzkwMjJ9.pMTj03W8rgsg97tJ298dDQLxnjikp8rOeP2J8m6dC3A';
		$result = $login->validate_jwt_token( $token, 'sharedSecret' );
		$this->assertFalse( $result );
		// valid token with wrong secret
		$token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE1MTYyMzkwMjJ9.UTLxY2zcznIFA42CIYV4iVWLEQhIrJyQ8I5eyZ_VpT8';
		$result = $login->validate_jwt_token( $token, 'sharedSecret' );
		$this->assertFalse( $result );
		// invalid token
		$token  = '2.2.2';
		$result = $login->validate_jwt_token( $token, 'sharedSecret' );
		$this->assertFalse( $result );
		// invalid token
		$token  = '2';
		$result = $login->validate_jwt_token( $token, 'sharedSecret' );
		$this->assertFalse( $result );
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_login_cookie
	 * @covers ::set_cookie
	 */
	public function testSetLoginCookie() {
		global $wpdb;

		$_SERVER['HTTP_HOST'] = 'HOST';
		$_SERVER['STORE_ID']  = 'STORE_ID';

		$login = new AutoLogin();
		$wpdb  = new Class() {
			public $prefix = 'wp_';
		};

		$user = new Class() {
			public $wp_capabilities = array(
				'administrator',
				'editor',
			);
		};

		\WP_Mock::userFunction(
			'username_exists',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'get_user_by',
			array(
				'args'   => array(
					'login',
					'USERNAME',
				),
				'times'  => 1,
				'return' => $user,
			)
		);

		self::$functions->shouldReceive( 'setcookie' )->with(
			'woocart_wp_user',
			'STORE_ID',
			array(
				'expires'  => 31536100,
				'path'     => '/',
				'domain'   => 'HOST',
				'secure'   => true,
				'samesite' => 'Lax',
			)
		);
		$login->set_login_cookie( 'USERNAME' );
	}

	/**
	 * @covers ::__construct
	 * @covers ::set_login_cookie
	 * @covers ::set_cookie
	 */
	public function testSetLoginCookieWrongUsername() {
		$login = new AutoLogin();

		\WP_Mock::userFunction(
			'username_exists',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$login->set_login_cookie( 'USERNAME' );
	}
}
