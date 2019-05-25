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
		\WP_Mock::expectActionAdded( 'woocommerce_checkout_after_terms_and_conditions', [ $gdpr, 'privacy_checkbox' ] );
		\WP_Mock::expectActionAdded( 'woocommerce_checkout_process', [ $gdpr, 'show_notice' ] );
		\WP_Mock::expectActionAdded( 'woocommerce_checkout_update_order_meta', [ $gdpr, 'update_order_meta' ] );
		\WP_Mock::expectActionAdded( 'admin_menu', [ $gdpr, 'add_menu_item' ], 1 );

		\WP_Mock::expectFilterAdded( 'woocommerce_get_terms_and_conditions_checkbox_text', 'do_shortcode' );

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
	 * @covers \Niteo\WooCart\Defaults\GDPR::privacy_checkbox
	 */
	public function testPrivacyCheckbox() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'do_shortcode',
			[
				'times'  => 1,
				'return' => 'privacy text',
			]
		);
		\WP_Mock::userFunction(
			'woocommerce_form_field',
			[
				'times'  => 1,
				'return' => true,
			]
		);

		$gdpr->privacy_checkbox();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\GDPR::__construct
	 * @covers \Niteo\WooCart\Defaults\GDPR::show_notice
	 */
	public function testShowNotice() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'wc_add_notice',
			[
				'times'  => 1,
				'return' => true,
			]
		);

		$gdpr->show_notice();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\GDPR::__construct
	 * @covers \Niteo\WooCart\Defaults\GDPR::update_order_meta
	 */
	public function testUpdateOrderMeta() {
		$_POST['woocart_privacy_checkbox'] = 'yes';

		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'update_post_meta',
			[
				'times'  => 1,
				'return' => true,
			]
		);

		$gdpr->update_order_meta( 10 );
	}
}
