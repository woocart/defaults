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
		\WP_Mock::expectActionAdded( 'init', array( $wordpress, 'control_cronjobs' ), PHP_INT_MAX );

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
	 * @covers \Niteo\WooCart\Defaults\WordPress::control_cronjobs
	 */
	public function testControlCronjobsEmpty() {
		$mock = \Mockery::mock( '\Niteo\WooCart\Defaults\WordPress' )->makePartial();
		$mock->shouldReceive( 'time_now' )->andReturn( \Datetime::createFromFormat( 'H:i', '03:30', new \DateTimeZone( 'Europe/Madrid' ) ) );
		$mock->shouldReceive( 'get_store_timezone' )->andReturn( 'Europe/Madrid' );

		$mock->start_time = '03:00';
		$mock->end_time   = '04:00';

		$this->assertEmpty( $mock->control_cronjobs() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::control_cronjobs
	 * @covers \Niteo\WooCart\Defaults\WordPress::get_store_timezone
	 */
	public function testControlCronjobsNotEmpty() {
		$mock = \Mockery::mock( '\Niteo\WooCart\Defaults\WordPress' )->makePartial();
		$mock->shouldReceive( 'time_now' )->andReturn( \Datetime::createFromFormat( 'H:i', '05:30', new \DateTimeZone( 'Europe/Madrid' ) ) );
		$mock->shouldReceive( 'get_store_timezone' )->andReturn( 'Europe/Madrid' );

		$mock->start_time = '03:00';
		$mock->end_time   = '04:00';

		\WP_Mock::expectFilterAdded( 'pre_get_ready_cron_jobs', array( $mock, 'empty_cronjobs' ) );

		$mock->control_cronjobs();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::get_store_timezone
	 */
	public function testGetStoreTimezone() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => 'IN',
			)
		);

		$this->assertEquals(
			'Asia/Kolkata',
			$wordpress->get_store_timezone()
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::empty_cronjobs
	 * @covers \Niteo\WooCart\Defaults\WordPress::get_store_timezone
	 */
	public function testEmptyCronjobs() {
		$wordpress = new WordPress();

		$this->assertEquals(
			array(),
			$wordpress->empty_cronjobs()
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

}
