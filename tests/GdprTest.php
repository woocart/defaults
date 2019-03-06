<?php


use Niteo\WooCart\Defaults\GDPR;
use PHPUnit\Framework\TestCase;

class GDPRTest extends TestCase {

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
	 * @covers \Niteo\WooCart\Defaults\GDPR::__construct
	 * @covers \Niteo\WooCart\Defaults\GDPR::scripts
	 */
	public function testConstructor() {
		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'times' => 1,
			)
		);
		\WP_Mock::userFunction(
			'wp_enqueue_style',
			array(
				'args' => [ 'woocart-gdpr', '/assets/css/front-gdpr.css', [], '@##VERSION##@' ],
			)
		);
		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'args' => [ 'woocart-gdpr', '/assets/js/front-gdpr.js', [], '@##VERSION##@', true ],
			)
		);
		\WP_Mock::userFunction(
			'is_admin',
			array(
				'times'  => 2,
				'return' => true,
			)
		);
		$gdpr = new GDPR();
		\WP_Mock::expectActionAdded( 'wp_footer', [ $gdpr, 'show_consent' ] );
		\WP_Mock::expectActionAdded( 'wp_enqueue_scripts', [ $gdpr, 'scripts' ] );
		\WP_Mock::expectActionAdded( 'admin_menu', [ $gdpr, 'add_menu_item' ], 1 );

		$gdpr->__construct();
		$gdpr->scripts();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\GDPR::__construct
	 * @covers \Niteo\WooCart\Defaults\GDPR::add_menu_item
	 */
	public function testAddMenuItem() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'add_options_page',
			array(
				'times' => 1,
			)
		);

		$gdpr->add_menu_item();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\GDPR::__construct
	 * @covers \Niteo\WooCart\Defaults\GDPR::show_consent
	 */
	public function testConsent() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'is_admin',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_allow_tracking',
				'return' => 'no',
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => [
					'wc_gdpr_notification_message',
					'We use cookies to improve your experience on our site. To find out more, read our [privacy_policy] and [cookies_policy].',
				],
				'return' => 'Test message with [privacy_policy] and [cookies_policy]',
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'wp_page_for_privacy_policy',
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'wp_page_for_cookies_policy',
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'absint',
			array(
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'get_permalink',
			array(
				'return' => 'https://woocart.com',
			)
		);
		\WP_Mock::userFunction(
			'sanitize_text_field',
			array(
				'times'  => 2,
				'return' => 'Replace Link',
			)
		);
		\WP_Mock::userFunction(
			'get_the_title',
			array(
				'times' => 2,
			)
		);

		$gdpr->show_consent();
		$this->expectOutputString(
			'<div class="wc-defaults-gdpr"><p>Test message with <a href="https://woocart.com">Replace Link</a> and <a href="https://woocart.com">Replace Link</a> <a href="javascript:;" id="wc-defaults-ok">OK</a></p></div><!-- .wc-defaults-gdpr -->'
		);
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\GDPR::__construct
	 * @covers \Niteo\WooCart\Defaults\GDPR::options_page
	 *
	public function testOptionsPage() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'current_user_can', array(
				'args'   => 'manage_privacy_options',
				'times'  => 1,
				'return' => false
			)
		);
		\WP_Mock::userFunction(
			'wp_die', array(
				'times'  => 1
			)
		);
		\WP_Mock::userFunction(
			'settings_errors', array(
				'times'  => 1
			)
		);
		\WP_Mock::userFunction(
			'get_posts', array(
				'times'  => 1
			)
		);
		\WP_Mock::userFunction(
			'wp_nonce_field', array(
				'times'  => 1
			)
		);
		\WP_Mock::userFunction(
			'submit_button', array(
				'times'  => 1
			)
		);

		$gdpr->options_page();
	} */
}
