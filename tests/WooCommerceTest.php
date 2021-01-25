<?php

use Niteo\WooCart\Defaults\WooCommerce;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\WooCommerce
 */
class WooCommerceTest extends TestCase {


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
	 */
	public function testConstructor() {
		$woocommerce = new WooCommerce();
		\WP_Mock::expectFilterAdded( 'woocommerce_general_settings', array( $woocommerce, 'general_settings' ) );
		\WP_Mock::expectFilterAdded( 'woocommerce_admin_disabled', array( $woocommerce, 'maybe_disable_wc_admin' ) );

		$woocommerce->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers ::__construct
	 * @covers ::general_settings
	 */
	public function testGeneralSettings() {
		$woocommerce = new WooCommerce();

		$this->assertEquals(
			$woocommerce->general_settings(
				array(
					array(
						'id'   => 'general_options',
						'type' => 'sectionend',
					),
				)
			),
			array(
				array(
					'name'     => 'Business Name',
					'desc_tip' => 'Name of the business used for operating the store.',
					'id'       => 'woocommerce_company_name',
					'type'     => 'text',
					'default'  => '',
				),
				array(
					'id'   => 'general_options',
					'type' => 'sectionend',
				),
			)
		);
	}

	/**
	 * @covers ::__construct
	 * @covers ::maybe_disable_wc_admin
	 */
	public function testDisableWCAdminTrue() {
		$woocommerce = new WooCommerce();

		// Set to cart plan
		$_SERVER['STORE_PLAN'] = 'cart';

		$this->assertTrue( $woocommerce->maybe_disable_wc_admin() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::maybe_disable_wc_admin
	 */
	public function testDisableWCAdminFalse() {
		$woocommerce = new WooCommerce();

		// Set to market plan
		$_SERVER['STORE_PLAN'] = 'market';

		$this->assertFalse( $woocommerce->maybe_disable_wc_admin() );
	}

}
