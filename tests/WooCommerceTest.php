<?php

use Niteo\WooCart\Defaults\WooCommerce;
use PHPUnit\Framework\TestCase;

class WooCommerceTest extends TestCase {


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
	 * @covers \Niteo\WooCart\Defaults\WooCommerce::__construct
	 */
	public function testConstructor() {
		$woocommerce = new WooCommerce();
		\WP_Mock::expectFilterAdded( 'woocommerce_general_settings', array( $woocommerce, 'general_settings' ) );

		$woocommerce->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\WooCommerce::__construct
	 * @covers \Niteo\WooCart\Defaults\WooCommerce::general_settings
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

}
