<?php


use Niteo\WooCart\Defaults\DenyList;
use PHPUnit\Framework\TestCase;

class DenyListTest extends TestCase {

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
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 */
	public function testConstructor() {
		define( '_FORCED_PLUGINS', true );
		$denylist = new DenyList();

		\WP_Mock::expectFilterAdded( 'plugin_action_links', [ $denylist, 'forced_plugins' ], 10, 4 );
		\WP_Mock::expectFilterAdded( 'plugin_install_action_links', [ $denylist, 'disable_install_link' ], 10, 2 );
		\WP_Mock::expectFilterAdded( 'plugin_action_links', [ $denylist, 'disable_activate_link' ], 10, 2 );
		\WP_Mock::expectActionAdded( 'init', [ $denylist, 'get_whitelisted_plugins' ], 10 );
		\WP_Mock::expectActionAdded( 'init', [ $denylist, 'get_denylisted_plugins' ], 10 );
		\WP_Mock::expectActionAdded( 'activate_plugin', [ $denylist, 'disable_activation' ], PHP_INT_MAX, 2 );

		$denylist->__construct();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::get_whitelisted_plugins
	 */
	public function testGetWhitelistedPlugins() {
		$denylist = new DenyList();

		\WP_Mock::userFunction(
			'get_option',
			[
				'return' => [],
			]
		);

		$denylist->get_whitelisted_plugins();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::get_denylisted_plugins
	 */
	public function testGetDenylistedPlugins() {
		$denylist = new DenyList();

		\WP_Mock::userFunction(
			'get_option',
			[
				'return' => [
					'plugin1',
					'plugin2',
				],
			]
		);

		$denylist->get_denylisted_plugins();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::force_deactivate
	 */
	public function testDisableActivationWithString() {
		$deny = new DenyList();
		\WP_Mock::userFunction(
			'deactivate_plugins',
			[
				'return' => true,
			]
		);

		\WP_Mock::userFunction(
			'get_plugins',
			[
				'return' => array(
					'astra-addon/astra-addon.php'          =>
						array(
							'Name'        => 'Astra Pro',
							'PluginURI'   => 'https://wpastra.com/',
							'Version'     => '1.8.5',
							'Description' => 'This plugin is an add-on for the Astra WordPress Theme. It offers premium features & functionalities that enhance your theming experience at next level.',
							'Author'      => 'Brainstorm Force',
							'AuthorURI'   => 'https://www.brainstormforce.com',
							'TextDomain'  => 'astra-addon',
							'DomainPath'  => '',
							'Network'     => false,
							'Title'       => 'Astra Pro',
							'AuthorName'  => 'Brainstorm Force',
						),
					'wp-dbmanager/index.php'               =>
						array(
							'Name' => 'DB Manager',
						),
					'contact-form-7/wp-contact-form-7.php' =>
						array(
							'Name'        => 'Contact Form 7',
							'PluginURI'   => 'https://contactform7.com/',
							'Version'     => '5.1.3',
							'Description' => 'Just another contact form plugin. Simple but flexible.',
							'Author'      => 'Takayuki Miyoshi',
							'AuthorURI'   => 'https://ideasilo.wordpress.com/',
							'TextDomain'  => 'contact-form-7',
							'DomainPath'  => '/languages/',
							'Network'     => false,
							'Title'       => 'Contact Form 7',
							'AuthorName'  => 'Takayuki Miyoshi',
						),
					'redis-cache/redis-cache.php'          =>
						array(
							'Name'        => 'Redis Object Cache',
							'PluginURI'   => 'https://wordpress.org/plugins/redis-cache/',
							'Version'     => '1.4.3',
							'Description' => 'A persistent object cache backend powered by Redis. Supports Predis, PhpRedis, HHVM, replication, clustering and WP-CLI.',
							'Author'      => 'Till Krüss',
							'AuthorURI'   => 'https://till.im/',
							'TextDomain'  => 'redis-cache',
							'DomainPath'  => '/languages',
							'Network'     => false,
							'Title'       => 'Redis Object Cache',
							'AuthorName'  => 'Till Krüss',
						),
					'sendgrid-email-delivery-simplified/wpsendgrid.php' =>
						array(
							'Name'        => 'SendGrid',
							'PluginURI'   => 'http://wordpress.org/plugins/sendgrid-email-delivery-simplified/',
							'Version'     => '1.11.8',
							'Description' => 'Email Delivery. Simplified. SendGrid\'s cloud-based email infrastructure relieves businesses of the cost and complexity of maintaining custom email systems. SendGrid provides reliable delivery, scalability and real-time analytics along with flexible APIs that make custom integration a breeze.',
							'Author'      => 'SendGrid',
							'AuthorURI'   => 'http://sendgrid.com',
							'TextDomain'  => 'sendgrid-email-delivery-simplified',
							'DomainPath'  => '',
							'Network'     => false,
							'Title'       => 'SendGrid',
							'AuthorName'  => 'SendGrid',
						),
					'woocommerce/woocommerce.php'          =>
						array(
							'Name'        => 'WooCommerce',
							'PluginURI'   => 'https://woocommerce.com/',
							'Version'     => '3.6.4',
							'Description' => 'An eCommerce toolkit that helps you sell anything. Beautifully.',
							'Author'      => 'Automattic',
							'AuthorURI'   => 'https://woocommerce.com',
							'TextDomain'  => 'woocommerce',
							'DomainPath'  => '/i18n/languages/',
							'Network'     => false,
							'Title'       => 'WooCommerce',
							'AuthorName'  => 'Automattic',
						),
				),
			]
		);

		$disabled_plugins = $deny->force_deactivate();
		$this->assertEquals( [ 'wp-dbmanager' => 'DB Manager' ], $disabled_plugins );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::disable_activation
	 * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
	 */
	public function test_DeactivateOnShutdown() {
		$denylist = new DenyList();

		\WP_Mock::userFunction(
			'has_action',
			[
				'args'   => [
					'shutdown',
					[
						$denylist,
						'deactivate_plugins',
					],
				],
				'return' => false,
			]
		);
		\WP_Mock::expectActionAdded( 'shutdown', [ $denylist, 'deactivate_plugins' ] );

		$denylist->disable_activation( 'wp-clone-by-wp-academy' );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::disable_activation
	 * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
	 */
	public function testDisableActivationWithArray() {
		$denylist = new DenyList();

		\WP_Mock::userFunction(
			'has_action',
			[
				'args'   => [
					'shutdown',
					[
						$denylist,
						'deactivate_plugins',
					],
				],
				'return' => false,
			]
		);
		\WP_Mock::expectActionAdded( 'shutdown', [ $denylist, 'deactivate_plugins' ] );

		$denylist->disable_activation( [ 'slug' => 'wp-clone-by-wp-academy' ] );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::deactivate_plugins
	 */
	public function testDeactivatePlugins() {
		$denylist = new DenyList();

		$refObject   = new ReflectionObject( $denylist );
		$refProperty = $refObject->getProperty( '_plugins_to_deactivate' );
		$refProperty->setAccessible( true );
		$refProperty->setValue( $denylist, [ 'adminer' ] );

		\WP_Mock::userFunction(
			'deactivate_plugins',
			[
				'args'   => [
					'adminer',
					true,
				],
				'return' => true,
			]
		);

		$denylist->deactivate_plugins();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
	 * @covers \Niteo\WooCart\Defaults\DenyList::disable_install_link
	 */
	public function testDisableInstallLinkBlacklisted() {
		$denylist = new DenyList();

		$this->assertEquals(
			$denylist->disable_install_link( [], 'adminer' ),
			[ '<a href="https://woocart.com/plugins-denylist" title="This plugin is not allowed on our system due to performance, security, or compatibility concerns. Please contact our support with any questions." target="_blank">Not available</a>' ]
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
	 * @covers \Niteo\WooCart\Defaults\DenyList::disable_install_link
	 */
	public function testDisableInstallLinkWhitelisted() {
		$denylist = new DenyList();

		$this->assertEquals(
			$denylist->disable_install_link( [], 'whitelisted-plugin' ),
			[]
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
	 * @covers \Niteo\WooCart\Defaults\DenyList::disable_activate_link
	 */
	public function testDisableActivateLinkBlacklisted() {
		$denylist = new DenyList();

		$this->assertEquals(
			$denylist->disable_activate_link( [ 'activate' => '' ], 'adminer' ),
			[ 'activate' => '<a href="https://woocart.com/plugins-denylist" data-plugin="adminer" title="This plugin is not allowed on our system due to performance, security, or compatibility concerns. Please contact our support with any questions." target="_blank">Not available</a>' ]
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DenyList::__construct
	 * @covers \Niteo\WooCart\Defaults\DenyList::is_plugin_denied
	 * @covers \Niteo\WooCart\Defaults\DenyList::disable_activate_link
	 */
	public function testDisableActivateLinkWhitelisted() {
		$denylist = new DenyList();

		$this->assertEquals(
			$denylist->disable_activate_link( [], 'whitelisted-plugin' ),
			[]
		);
	}

}
