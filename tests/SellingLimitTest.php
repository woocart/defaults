<?php

use Niteo\WooCart\Defaults\Importers\SellingLimit;
use PHPUnit\Framework\TestCase;

class SellingLimitTest extends TestCase {

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
	 * @covers \Niteo\WooCart\Defaults\Importers\SellingLimit::__construct
	 * @covers \Niteo\WooCart\Defaults\Importers\SellingLimit::zoneID
	 * @covers \Niteo\WooCart\Defaults\Importers\SellingLimit::countries
	 */
	public function test__construct() {
		global $wpdb;
		\WP_Mock::userFunction(
			'get_option',
			[
				'return' => true,
			]
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
			->andReturn( [ [ 'location_code' => 'SI' ], [ 'location_code' => 'US' ] ] );

		$s = new SellingLimit( 'EU' );

		$this->assertEquals( 3, $s->zoneID() );
		$this->assertEquals( [ 'SI', 'US' ], $s->countries( 3 ) );
	}
}
