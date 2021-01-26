<?php

use Niteo\WooCart\Defaults\Importers\SellingLimit;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\Importers\SellingLimit
 */
class SellingLimitTest extends TestCase {

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
	 * @covers ::zoneID
	 * @covers ::countries
	 */
	public function test__construct() {
		global $wpdb;
		\WP_Mock::userFunction(
			'get_option',
			array(
				'return' => true,
			)
		);

		$wpdb         = \Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )
			->with( 'SELECT zone_id FROM wp_woocommerce_shipping_zones WHERE zone_name = %s', 'EU' )
			->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_var' )
			->with( 'query' )
			->andReturn( 3 );
		$wpdb->shouldReceive( 'prepare' )
			->with( 'SELECT location_code FROM wp_woocommerce_shipping_zone_locations WHERE zone_id = %d', 3 )
			->andReturn( 'query2' );
		$wpdb->shouldReceive( 'get_results' )
			->with( 'query2', 'ARRAY_A' )
			->andReturn( array( array( 'location_code' => 'SI' ), array( 'location_code' => 'US' ) ) );

		$s = new SellingLimit( 'EU' );

		$this->assertEquals( 3, $s->zoneID() );
		$this->assertEquals( array( 'SI', 'US' ), $s->countries( 3 ) );
	}
}
