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
		$wordpress = \Mockery::mock( 'Niteo\WooCart\Defaults\WordPress' )->makePartial();
		$wordpress->shouldReceive(
			array(
				'is_staging'      => true,
				'is_rewrite_urls' => true,
			)
		);

		\WP_Mock::expectActionAdded( 'admin_bar_menu', array( $wordpress, 'block_status_admin_button' ), 100 );
		\WP_Mock::expectActionAdded( 'init', array( $wordpress, 'http_block_status' ) );
		\WP_Mock::expectActionAdded( 'init', array( $wordpress, 'remove_heartbeat' ), PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'wp_footer', array( $wordpress, 'wpcf7_cache' ), PHP_INT_MAX );
		\WP_Mock::expectFilterAdded( 'file_mod_allowed', array( $wordpress, 'read_only_filesystem' ), PHP_INT_MAX, 2 );
		\WP_Mock::expectFilterAdded( 'pre_reschedule_event', array( $wordpress, 'delay_cronjobs' ), PHP_INT_MAX, 2 );
		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', array( $wordpress, 'admin_scripts' ) );
		\WP_Mock::expectActionAdded( 'admin_init', array( $wordpress, 'check_block_request' ) );

		\WP_Mock::expectActionAdded( 'wp_loaded', array( $wordpress, 'start_buffering' ), ~PHP_INT_MAX );
		\WP_Mock::expectActionAdded( 'wp_print_footer_scripts', array( $wordpress, 'end_buffering' ), PHP_INT_MAX );

		$wordpress->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::http_block_status
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testHttpBlockStatusNotActive() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $wordpress->http_block_status() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::http_block_status
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
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
	 * @covers \Niteo\WooCart\Defaults\WordPress::block_status_admin_button
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testBlockStatusButtonNoAdmin() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $wordpress->block_status_admin_button( '' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::block_status_admin_button
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testBlockStatusButtonNotBar() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'is_admin_bar_showing',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $wordpress->block_status_admin_button( '' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::block_status_admin_button
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testBlockStatusButtonNoOption() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'is_admin_bar_showing',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $wordpress->block_status_admin_button( '' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::block_status_admin_button
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testBlockStatusButtonSuccess() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'is_admin_bar_showing',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'add_query_arg',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_nonce_url',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$admin_bar = new class() {
			function add_menu( $data ) {
				// Doing something with it.
			}
		};

		$this->assertEmpty( $wordpress->block_status_admin_button( $admin_bar ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::remove_heartbeat
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testRemoveHeartbeat() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'wp_deregister_script',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $wordpress->remove_heartbeat() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::http_requests
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testHttpRequestsTrue() {
		$wordpress = new WordPress();

		$this->assertTrue( $wordpress->http_requests( false, array(), 'https://randompluginwebsite.com' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::http_requests
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
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
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
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
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
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
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
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
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testWpcf7Cache() {
		$wordpress = new WordPress();

		$this->expectOutputString( '<script>if (typeof wpcf7 !== "undefined") { wpcf7.cached = 0; }</script>', $wordpress->wpcf7_cache() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::admin_scripts
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testAdminScripts() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'wp_enqueue_style',
			array(
				'times' => 1,
			)
		);

		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'times' => 1,
			)
		);

		$this->assertEmpty( $wordpress->admin_scripts( '' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::check_block_request
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testCheckBlockRequestNoAdmin() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $wordpress->check_block_request() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::check_block_request
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testCheckBlockRequestNoRequest() {
		$wordpress = new WordPress();

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $wordpress->check_block_request() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::check_block_request
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testCheckBlockRequestNotCorrect() {
		$wordpress = new WordPress();

		$_REQUEST['wc_http_block'] = 'not_deactivate';

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'times'  => 1,
				'return' => 'not_deactivate',
			)
		);

		$this->assertEmpty( $wordpress->check_block_request() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::check_block_request
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testCheckBlockRequestFailedAdminReferer() {
		$wordpress = new WordPress();

		$_REQUEST['wc_http_block'] = 'deactivate';

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'times'  => 1,
				'return' => 'deactivate',
			)
		);

		\WP_Mock::userFunction(
			'check_admin_referer',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $wordpress->check_block_request() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::check_block_request
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testCheckBlockRequestSuccess() {
		$wordpress = new WordPress();

		$_REQUEST['wc_http_block'] = 'deactivate';

		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'sanitize_key',
			array(
				'times'  => 1,
				'return' => 'deactivate',
			)
		);

		\WP_Mock::userFunction(
			'check_admin_referer',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'update_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'wp_redirect',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'esc_url_raw',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::userFunction(
			'remove_query_arg',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $wordpress->check_block_request() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WordPress::__construct
	 * @covers \Niteo\WooCart\Defaults\WordPress::staging_url_override
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_rewrite_urls
	 * @covers \Niteo\WooCart\Defaults\WordPress::is_staging
	 */
	public function testStagingOverrideBothDomains() {
		$wordpress = new WordPress();

		$_ENV['DOMAIN']        = 'stagingdomain.com';
		$_ENV['PARENT_DOMAIN'] = 'domain.com';

		$buffer = '<div><a href="https://stagingdomain.com/relative-link">This is a test for link replacement.</a>&nbsp;&nbsp;<br><a href="https://domain.com/another-link">This is another link having parent domain value.</a>';

		$this->assertSame(
			'<div><a href="/relative-link">This is a test for link replacement.</a>&nbsp;&nbsp;<br><a href="/another-link">This is another link having parent domain value.</a>',
			$wordpress->staging_url_override( $buffer )
		);
	}

}
