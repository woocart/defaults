<?php
/**
 * Created by PhpStorm.
 * User: dz0ny
 * Date: 17.9.2018
 * Time: 11:28
 */

use Niteo\WooCart\Defaults\Importers\ShippingLocation;
use Niteo\WooCart\Defaults\Importers\ShippingMethod;
use Niteo\WooCart\Defaults\Importers\ShippingZone;
use Niteo\WooCart\Defaults\Importers\WooShipping;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\Importers\WooShipping
 */
class WooShippingTest extends TestCase {


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
	 * @covers \Niteo\WooCart\Defaults\Value::__construct
	 * @covers \Niteo\WooCart\Defaults\Value::getKey
	 * @covers \Niteo\WooCart\Defaults\Value::getStrippedKey
	 * @covers \Niteo\WooCart\Defaults\Value::setKey
	 * @covers \Niteo\WooCart\Defaults\Importers\FromArray::fromArray
	 * @covers \Niteo\WooCart\Defaults\Importers\ToArray::toArray
	 * @covers ::import
	 * @covers ::toValue
	 * @covers ::toValue
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::getID
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::getLocations
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::getZone
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::setValue
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::setZone
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::getMethods
	 */
	public function testImport() {
		global $wpdb;

		$wpdb         = \Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'insert' )->with(
			'wp_woocommerce_shipping_zones',
			array(
				'zone_id'    => 0,
				'zone_name'  => 'name',
				'zone_order' => 'order',
			),
			array(
				0 => '%d',
				1 => '%s',
				2 => '%d',
			)
		);
		$wpdb->shouldReceive( 'insert' )->with(
			'wp_woocommerce_shipping_zone_locations',
			array(
				'zone_id'       => 0,
				'location_code' => null,
				'location_type' => null,
			),
			array(
				0 => '%d',
				1 => '%s',
				2 => '%s',
			)
		);
		$wpdb->shouldReceive( 'insert' )->with(
			'wp_woocommerce_shipping_zone_methods',
			array(
				'zone_id'      => 0,
				'method_order' => null,
				'is_enabled'   => null,
			),
			array(
				0 => '%d',
				1 => '%s',
				2 => '%d',
				3 => '%d',
			)
		);

		$zone            = new ShippingZone();
		$method          = new ShippingMethod();
		$loc             = new ShippingLocation();
		$method->zone_id = 1234;
		$loc->zone_id    = 1234;

		$zone->name      = 'name';
		$zone->order     = 'order';
		$zone->locations = array( $loc->toArray() );
		$zone->methods   = array( $method->toArray() );

		$value = WooShipping::toValue( 'test_name/1', $zone->toArray() );
		$o     = new WooShipping();
		$o->import( $value );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Value::__construct
	 * @covers \Niteo\WooCart\Defaults\Value::getKey
	 * @covers \Niteo\WooCart\Defaults\Value::getStrippedKey
	 * @covers \Niteo\WooCart\Defaults\Value::setKey
	 * @covers \Niteo\WooCart\Defaults\Importers\FromArray::fromArray
	 * @covers \Niteo\WooCart\Defaults\Importers\ToArray::toArray
	 * @covers ::items
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::getID
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::getLocations
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::getZone
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::setValue
	 * @covers \Niteo\WooCart\Defaults\Importers\WooShippingZone::setZone
	 */
	public function testItems() {
		global $wpdb;

		$zone             = new \stdClass();
		$zone->zone_id    = 1234;
		$zone->zone_name  = 'name';
		$zone->zone_order = 'order';

		$method               = new \stdClass();
		$method->zone_id      = 1234;
		$method->is_enabled   = true;
		$method->method_order = 1;

		$loc                = new \stdClass();
		$loc->location_type = 'location_type';
		$loc->location_code = 'location_code';
		$loc->zone_id       = 1234;

		$wpdb         = \Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' )->with( 'SELECT * FROM wp_woocommerce_shipping_zones' );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' )->with( 'SELECT location_code, location_type FROM wp_woocommerce_shipping_zone_locations WHERE zone_id = %d', 1234 );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' )->with( 'SELECT method_id, method_order, is_enabled FROM wp_woocommerce_shipping_zone_methods WHERE zone_id = %d', 1234 );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array( $zone, $zone, $zone, $zone ) );

		$o = new WooShipping();
		$this->assertCount( 4, $o->items() );

		$wpdb         = \Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' )->with( 'SELECT * FROM wp_woocommerce_shipping_zones' );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' )->with( 'SELECT location_code, location_type FROM wp_woocommerce_shipping_zone_locations WHERE zone_id = %d', 1234 );
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' )->with( 'SELECT method_id, method_order, is_enabled FROM wp_woocommerce_shipping_zone_methods WHERE zone_id = %d', 1234 );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array( $zone, $zone, $zone, $zone ), array( $loc, $loc, $loc ), array( $method, $method ) );

		$value = $o->items()->current();
		$this->assertEquals( '1234', $value->getKey() );
		$this->assertEquals( 1234, $value->getID() );
		$this->assertEquals( '1234', $value->getStrippedKey() );

		$this->assertCount( 3, $value->getLocations() );
		$value = $value->getLocations()->current();
		$this->assertEquals( '1234', $value->zone_id );
		$this->assertEquals( 'location_code', $value->location_code );
		$this->assertEquals( 'location_type', $value->location_type );

	}
}
