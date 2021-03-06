<?php


use Niteo\WooCart\Defaults\GDPR;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\GDPR
 */
class GDPRTest extends TestCase {

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
	 * @covers ::scripts
	 */
	public function testConstructor() {

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 3,
				'return' => 'yes',
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
		\WP_Mock::expectActionAdded( 'wp_footer', array( $gdpr, 'show_consent' ) );
		\WP_Mock::expectActionAdded( 'wp_enqueue_scripts', array( $gdpr, 'scripts' ) );
		\WP_Mock::expectActionAdded( 'woocommerce_checkout_after_terms_and_conditions', array( $gdpr, 'privacy_checkbox' ) );
		\WP_Mock::expectActionAdded( 'woocommerce_checkout_process', array( $gdpr, 'show_notice' ) );
		\WP_Mock::expectActionAdded( 'woocommerce_checkout_update_order_meta', array( $gdpr, 'update_order_meta' ) );
		\WP_Mock::expectActionAdded( 'admin_menu', array( $gdpr, 'add_menu_item' ), 1 );

		\WP_Mock::expectFilterAdded( 'woocommerce_get_terms_and_conditions_checkbox_text', 'do_shortcode' );

		$gdpr->__construct();
		$gdpr->scripts();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::add_menu_item
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
	public function testConsentAllowTracking() {
		$gdpr = new GDPR();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_allow_tracking',
				'return' => 'yes',
			)
		);

		$this->assertEmpty( $gdpr->show_consent() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\GDPR::__construct
	 * @covers \Niteo\WooCart\Defaults\GDPR::show_consent
	 */
	public function testConsentNoMessage() {
		$gdpr = new GDPR();

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
				'args'   => 'wc_gdpr_notification_message',
				'return' => false,
			)
		);

		$this->assertEmpty( $gdpr->show_consent() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::show_consent
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
				'args'   => array(
					'wc_gdpr_notification_message',
				),
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
	 * @covers ::__construct
	 * @covers ::privacy_checkbox
	 * @covers ::check_user
	 */
	public function testPrivacyCheckbox() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'times'  => 1,
				'return' => false,
			)
		);
		\WP_Mock::userFunction(
			'do_shortcode',
			array(
				'times'  => 1,
				'return' => 'privacy text',
			)
		);
		\WP_Mock::userFunction(
			'woocommerce_form_field',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$gdpr->privacy_checkbox();
	}

	/**
	 * @covers ::__construct
	 * @covers ::privacy_checkbox
	 * @covers ::check_user
	 */
	public function testPrivacyCheckboxEmpty() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\GDPR' )
											->makePartial();
		$mock->shouldReceive( 'check_user' )
				 ->andReturn( true );

		$this->assertEmpty( $mock->privacy_checkbox() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::show_notice
	 * @covers ::check_user
	 */
	public function testShowNotice() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'times'  => 1,
				'return' => false,
			)
		);
		\WP_Mock::userFunction(
			'wc_add_notice',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$gdpr->show_notice();
	}

	/**
	 * @covers ::__construct
	 * @covers ::show_notice
	 * @covers ::check_user
	 */
	public function testShowNoticeEmpty() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\GDPR' )
											->makePartial();
		$mock->shouldReceive( 'check_user' )
				 ->andReturn( true );

		$this->assertEmpty( $mock->show_notice() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::update_order_meta
	 */
	public function testUpdateOrderMetaGuest() {
		$_POST['woocart_privacy_checkbox'] = 'yes';

		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'times'  => 1,
				'return' => false,
			)
		);
		\WP_Mock::userFunction(
			'update_post_meta',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$gdpr->update_order_meta( 10 );
	}

	/**
	 * @covers ::__construct
	 * @covers ::update_order_meta
	 */
	public function testUpdateOrderMetaUser() {
		$_POST['woocart_privacy_checkbox'] = 'yes';

		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'times'  => 1,
				'return' => 10,
			)
		);
		\WP_Mock::userFunction(
			'update_user_meta',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$gdpr->update_order_meta( 10 );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_user
	 */
	public function testCheckUserFalse() {
		$gdpr = new GDPR();

		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertFalse( $gdpr->check_user() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::check_user
	 */
	public function testCheckUserTrue() {
		$gdpr = new GDPR();

		\WP_Mock::userFunction(
			'get_current_user_id',
			array(
				'times'  => 1,
				'return' => 10,
			)
		);
		\WP_Mock::userFunction(
			'get_user_meta',
			array(
				'times'  => 1,
				'return' => 'not empty',
			)
		);

		$this->assertTrue( $gdpr->check_user() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::cf_privacy_checkbox
	 * @covers ::get_forms
	 * @covers ::update_template
	 */
	public function testCfPrivacyCheckbox() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\GDPR' )
											->makePartial();
		$mock->shouldReceive(
			array(
				'get_forms'       => true,
				'update_template' => true,
			)
		);

		$mock->cf_privacy_checkbox();
	}

	/**
	 * @covers ::__construct
	 * @covers ::get_forms
	 */
	public function testGetForms() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'get_posts',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$gdpr->get_forms();
	}

	/**
	 * @covers ::__construct
	 * @covers ::update_template
	 */
	public function testUpdateTemplate() {
		$gdpr = new GDPR();
		\WP_Mock::userFunction(
			'get_post_meta',
			array(
				'times'  => 1,
				'return' => 'Content form template [name] [email] [submit]',
			)
		);
		\WP_Mock::userFunction(
			'do_shortcode',
			array(
				'times'  => 1,
				'return' => 'privacy policy text',
			)
		);
		\WP_Mock::userFunction(
			'update_post_meta',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$gdpr->update_template( array( 30 ) );
	}
}
