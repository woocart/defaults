<?php

use Niteo\WooCart\Defaults\AutoLoginCLI;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\AutoLoginCLI
 */
class AutoLoginCLITest extends TestCase {

	function setUp() : void {
		\WP_Mock::setUp();
		\WP_Mock::userFunction(
			'is_blog_installed',
			array(
				'return' => true,
			)
		);

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
	 * @covers ::url
	 * @runInSeparateProcess
	 */
	public function testConstructor() {
		$_SERVER['STORE_ID'] = 'uuid-42';

		define(
			'WOOCART_LOGIN_SHARED_SECRET_PATH',
			dirname( __FILE__ ) . '/fixtures/loginSharedSecret'
		);
		$login = new AutoLoginCLI();
		// {
		// "iss": "wp-cli",
		// "aud": "http://localhost",
		// "jti": "uuid-42",
		// "iat": 1568320837,
		// "nbf": 1568320837,
		// "exp": 1568324437
		// }
		$this->assertStringStartsWith( 'http://localhost/wp-login.php?auth=', $login->url() );

	}


}
