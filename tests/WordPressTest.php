<?php

use Niteo\WooCart\Defaults\WordPress;
use PHPUnit\Framework\TestCase;

class WordPressTest extends TestCase {


	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
		\Mockery::close();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 */
	public function testConstructor() {
		$wordpress = new WordPress();
		\WP_Mock::expectActionAdded( 'init', array( $wordpress, 'http_block_status' ) );
		\WP_Mock::expectActionAdded( 'wp_footer', array( $wordpress, 'wpcf7_cache' ), PHP_INT_MAX );
		\WP_Mock::expectFilterAdded( 'file_mod_allowed', array( $wordpress, 'read_only_filesystem' ), PHP_INT_MAX, 2 );
		\WP_Mock::expectFilterAdded( 'pre_reschedule_event', array( $wordpress, 'delay_cronjobs' ), PHP_INT_MAX, 2 );

		$wordpress->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::http_block_status
	 */
	public function testHttpBlockStatus() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);
		\WP_Mock::expectFilterAdded( 'pre_http_request', array( $wordpress, 'http_requests' ), ~PHP_INT_MAX, 3 );

		$wordpress->http_block_status();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::http_requests
	 */
	public function testHttpRequestsTrue() {
		$wordpress = new WordPress();

		$this->assertTrue( $wordpress->http_requests( false, array(), 'https://randompluginwebsite.com' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::http_requests
	 */
	public function testHttpRequestsFalse() {
		$wordpress = new WordPress();

		$this->assertFalse( $wordpress->http_requests( false, array(), 'https://api.wordpress.org/plugins/woocommerce' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::delay_cronjobs
	 */
	public function testDelayCronjobsEmpty() {
		$mock = \Mockery::mock( '\Niteo\WooCart\Defaults\WordPress' )->makePartial();
		$mock->shouldReceive( 'time_now' )->andReturn( \Datetime::createFromFormat( 'H:i', '02:30', new \DateTimeZone( 'Europe/Madrid' ) ) );

		\WP_Mock::userFunction(
			'wp_timezone_string',
			array(
				'times'  => 1,
				'return' => 'Europe/Madrid',
			)
		);

		$mock->start_time = '03:00';

		$this->assertNull(
			$mock->delay_cronjobs(
				null,
				(object) array(
					'hook' => 'PLUGIN_HOOK',
				)
			)
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::delay_cronjobs
	 */
	public function testDelayCronjobsNotEmpty() {
		$mock = \Mockery::mock( '\Niteo\WooCart\Defaults\WordPress' )->makePartial();
		$mock->shouldReceive( 'time_now' )->andReturn( \Datetime::createFromFormat( 'H:i', '03:30', new \DateTimeZone( 'Europe/Madrid' ) ) );

		\WP_Mock::userFunction(
			'wp_timezone_string',
			array(
				'times'  => 1,
				'return' => 'Europe/Madrid',
			)
		);

		$mock->start_time = '03:00';
		$cron_start       = strtotime( '+1 day', \Datetime::createFromFormat( 'H:i', '03:00', new \DateTimeZone( 'Europe/Madrid' ) )->getTimestamp() );

		$this->assertEquals(
			(object) array(
				'hook'      => 'wc_facebook_generate_product_catalog_feed',
				'timestamp' => $cron_start,
			),
			$mock->delay_cronjobs(
				null,
				(object) array(
					'hook'      => 'wc_facebook_generate_product_catalog_feed',
					'timestamp' => 1000,
				)
			)
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::time_now
	 */
	public function testTimeNow() {
		$wordpress = new WordPress();

		$this->assertInstanceOf(
			'\DateTime',
			$wordpress->time_now( 'Europe/Madrid' )
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::read_only_filesystem
	 */
	public function testReadOnlyFilesystemTrue() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertTrue( $wordpress->read_only_filesystem( false, 'testing' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::read_only_filesystem
	 */
	public function testReadOnlyFilesystemFalse() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertFalse( $wordpress->read_only_filesystem( true, 'testing' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::wpcf7_cache
	 */
	public function testWpcf7Cache() {
		$wordpress = new WordPress();

		$this->expectOutputString( '<script>if (typeof wpcf7 !== "undefined") { wpcf7.cached = 0; }</script>', $wordpress->wpcf7_cache() );
	}

}
