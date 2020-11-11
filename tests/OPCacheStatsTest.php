<?php


use Niteo\WooCart\Defaults\Reporter;
use PHPUnit\Framework\TestCase;

class ReporterTest extends TestCase {

	function setUp() {
		WP_Mock::setUp();
	}

	function tearDown() {
		$this->addToAssertionCount(
			Mockery::getContainer()->mockery_getExpectationCount()
		);
		WP_Mock::tearDown();
		Mockery::close();
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\Reporter::__construct
	 * @covers \Niteo\WooCart\Defaults\Reporter::parse_plugins
	 */
	public function testParse() {
		$data = array(
			'opcache_enabled'        => true,
			'cache_full'             => false,
			'restart_pending'        => false,
			'restart_in_progress'    => false,
			'memory_usage'           => array(
				'used_memory'               => 143674072,
				'free_memory'               => 124761384,
				'wasted_memory'             => 0,
				'current_wasted_percentage' => 0.0,
			),
			'interned_strings_usage' => array(
				'buffer_size'       => 6291032,
				'used_memory'       => 345320,
				'free_memory'       => 5945712,
				'number_of_strings' => 7298,
			),
			'opcache_statistics'     => array(
				'num_cached_scripts'   => 19,
				'num_cached_keys'      => 32,
				'max_cached_keys'      => 16229,
				'hits'                 => 361,
				'start_time'           => 1554918676,
				'last_restart_time'    => 0,
				'oom_restarts'         => 0,
				'hash_restarts'        => 0,
				'manual_restarts'      => 0,
				'misses'               => 19,
				'blacklist_misses'     => 0,
				'blacklist_miss_ratio' => 0.0,
				'opcache_hit_rate'     => 95.0,
			),
			'scripts'                => array(
				'/var/www/public_html/wp-content/plugins/woocommerce/autoload_static.php' => array(
					'full_path'           => '/var/www/public_html/wp-content/plugins/woocommerce/autoload_static.php',
					'hits'                => 19,
					'memory_consumption'  => 4512,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/public_html/wp-content/plugins/index.php' => array(
					'full_path'           => '/var/www/public_html/wp-content/plugins/woocommerce/autoload_static.php',
					'hits'                => 19,
					'memory_consumption'  => 4512,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Compat.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Compat.php',
					'hits'                => 19,
					'memory_consumption'  => 13696,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Serializer.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Serializer.php',
					'hits'                => 19,
					'memory_consumption'  => 10680,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/ReprSerializer.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/ReprSerializer.php',
					'hits'                => 19,
					'memory_consumption'  => 3624,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Context.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Context.php',
					'hits'                => 19,
					'memory_consumption'  => 2992,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/public_html/wp-content/plugins/woocommerce/ClassLoader.php' => array(
					'full_path'           => '/var/www/public_html/wp-content/plugins/woocommerce/ClassLoader.php',
					'hits'                => 19,
					'memory_consumption'  => 31400,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Processor/SanitizeDataProcessor.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Processor/SanitizeDataProcessor.php',
					'hits'                => 19,
					'memory_consumption'  => 11960,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/public_html/wp-content/plugins/woocommerce/autoload_real.php' => array(
					'full_path'           => '/var/www/public_html/wp-content/plugins/woocommerce/autoload_real.php',
					'hits'                => 19,
					'memory_consumption'  => 4616,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Breadcrumbs/ErrorHandler.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Breadcrumbs/ErrorHandler.php',
					'hits'                => 19,
					'memory_consumption'  => 4808,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Stacktrace.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Stacktrace.php',
					'hits'                => 19,
					'memory_consumption'  => 25056,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/TransactionStack.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/TransactionStack.php',
					'hits'                => 19,
					'memory_consumption'  => 4240,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Breadcrumbs.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Breadcrumbs.php',
					'hits'                => 19,
					'memory_consumption'  => 6048,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/autoload.php' => array(
					'full_path'           => '/var/www/bundle/vendor/autoload.php',
					'hits'                => 19,
					'memory_consumption'  => 848,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/ErrorHandler.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/ErrorHandler.php',
					'hits'                => 19,
					'memory_consumption'  => 16664,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/public_html/test.php'       => array(
					'full_path'           => '/var/www/public_html/test.php',
					'hits'                => 19,
					'memory_consumption'  => 864,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Client.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Client.php',
					'hits'                => 19,
					'memory_consumption'  => 123128,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/bundle.php'          => array(
					'full_path'           => '/var/www/bundle/bundle.php',
					'hits'                => 19,
					'memory_consumption'  => 16432,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Util.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Util.php',
					'hits'                => 19,
					'memory_consumption'  => 2328,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
				'/var/www/bundle/vendor/sentry/sentry/lib/Raven/Processor.php' => array(
					'full_path'           => '/var/www/bundle/vendor/sentry/sentry/lib/Raven/Processor.php',
					'hits'                => 19,
					'memory_consumption'  => 3528,
					'last_used'           => 'Wed Apr 10 17:52:04 2019',
					'last_used_timestamp' => 1554918724,
				),
			),
		);

		WP_Mock::userFunction(
			'wp_next_scheduled',
			array(
				'times' => 1,
			)
		);
		WP_Mock::userFunction(
			'wp_schedule_event',
			array(
				'times' => 1,
			)
		);
		WP_Mock::userFunction(
			'wp_normalize_path',
			array(
				'times'  => 1,
				'return' => '/var/www/public_html/wp-content/plugins',
			)
		);

		$stats = new Reporter();
		$this->assertEquals( array( 'woocommerce' => 40528 ), $stats->parse_plugins( $data ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Reporter::__construct
	 * @covers \Niteo\WooCart\Defaults\Reporter::decorate
	 */
	function test_decorate() {
		$data = array(
			'autoptimize/autoptimize.php'          =>
				array(
					'WC requires at least' => '',
					'WC tested up to'      => '',
					'Name'                 => 'Autoptimize',
					'PluginURI'            => 'https://autoptimize.com/',
					'Version'              => '2.4.4',
					'Description'          => 'Optimize your website\'s performance: JS, CSS, HTML, images, Google Fonts and more!',
					'Author'               => 'Frank Goossens (futtta)',
					'AuthorURI'            => 'https://autoptimize.com/',
					'TextDomain'           => 'autoptimize',
					'DomainPath'           => '',
					'Network'              => false,
					'Title'                => 'Autoptimize',
					'AuthorName'           => 'Frank Goossens (futtta)',
				),
			'contact-form-7/wp-contact-form-7.php' =>
				array(
					'WC requires at least' => '',
					'WC tested up to'      => '',
					'Name'                 => 'Contact Form 7',
					'PluginURI'            => 'https://contactform7.com/',
					'Version'              => '5.1.1',
					'Description'          => 'Just another contact form plugin. Simple but flexible.',
					'Author'               => 'Takayuki Miyoshi',
					'AuthorURI'            => 'https://ideasilo.wordpress.com/',
					'TextDomain'           => 'contact-form-7',
					'DomainPath'           => '/languages/',
					'Network'              => false,
					'Title'                => 'Contact Form 7',
					'AuthorName'           => 'Takayuki Miyoshi',
				),
			'redis-cache/redis-cache.php'          =>
				array(
					'WC requires at least' => '',
					'WC tested up to'      => '',
					'Name'                 => 'Redis Object Cache',
					'PluginURI'            => 'https://wordpress.org/plugins/redis-cache/',
					'Version'              => '1.4.1',
					'Description'          => 'A persistent object cache backend powered by Redis. Supports Predis, PhpRedis, HHVM, replication, clustering and WP-CLI.',
					'Author'               => 'Till Krüss',
					'AuthorURI'            => 'https://till.im/',
					'TextDomain'           => 'redis-cache',
					'DomainPath'           => '/languages',
					'Network'              => false,
					'Title'                => 'Redis Object Cache',
					'AuthorName'           => 'Till Krüss',
				),
			'sendgrid-email-delivery-simplified/wpsendgrid.php' =>
				array(
					'WC requires at least' => '',
					'WC tested up to'      => '',
					'Name'                 => 'SendGrid',
					'PluginURI'            => 'http://wordpress.org/plugins/sendgrid-email-delivery-simplified/',
					'Version'              => '1.11.8',
					'Description'          => 'Email Delivery. Simplified. SendGrid\'s cloud-based email infrastructure relieves businesses of the cost and complexity of maintaining custom email systems. SendGrid provides reliable delivery, scalability and real-time analytics along with flexible APIs that make custom integration a breeze.',
					'Author'               => 'SendGrid',
					'AuthorURI'            => 'http://sendgrid.com',
					'TextDomain'           => 'sendgrid-email-delivery-simplified',
					'DomainPath'           => '',
					'Network'              => false,
					'Title'                => 'SendGrid',
					'AuthorName'           => 'SendGrid',
				),
			'woocommerce/woocommerce.php'          =>
				array(
					'WC requires at least' => '',
					'WC tested up to'      => '',
					'Name'                 => 'WooCommerce',
					'PluginURI'            => 'https://woocommerce.com/',
					'Version'              => '3.5.7',
					'Description'          => 'An eCommerce toolkit that helps you sell anything. Beautifully.',
					'Author'               => 'Automattic',
					'AuthorURI'            => 'https://woocommerce.com',
					'TextDomain'           => 'woocommerce',
					'DomainPath'           => '/i18n/languages/',
					'Network'              => false,
					'Title'                => 'WooCommerce',
					'AuthorName'           => 'Automattic',
				),
		);

		WP_Mock::userFunction(
			'wp_next_scheduled',
			array(
				'times' => 1,
			)
		);
		WP_Mock::userFunction(
			'wp_schedule_event',
			array(
				'times' => 1,
			)
		);
		WP_Mock::userFunction(
			'get_plugins',
			array(
				'times'  => 1,
				'return' => $data,
			)
		);

		$stats   = new Reporter();
		$plugins = array( 'woocommerce' => 40528 );
		$info    = $stats->decorate( $plugins );
		$this->assertEquals(
			array(

				array(
					'memory'  => 40528,
					'title'   => 'WooCommerce',
					'version' => '3.5.7',
					'slug'    => 'woocommerce',
				),
			),
			$info
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Reporter::__construct
	 * @covers \Niteo\WooCart\Defaults\Reporter::emit
	 */
	function test_emit() {
		WP_Mock::userFunction(
			'wp_next_scheduled',
			array(
				'times' => 1,
			)
		);
		WP_Mock::userFunction(
			'wp_schedule_event',
			array(
				'times' => 1,
			)
		);
		$plugins = array(
			array(
				'memory'  => 40528,
				'title'   => 'WooCommerce',
				'version' => '3.5.7',
				'slug'    => 'woocommerce',
			),
		);

		Mockery::mock( 'alias:\WooCart\Log\Socket' )
			->shouldReceive( 'log' )->withArgs(
				array(
					array(
						'kind'    => 'opcache_stats',
						'plugins' => $plugins,
					),
				)
			)
			->andReturn( true );

		$stats = new Reporter();

		$this->assertTrue( $stats->emit( $plugins ) );
	}
}
