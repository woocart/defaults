<?php

use Niteo\WooCart\Defaults\Importers\WooOptions;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\Importers\WooOptions
 */
class WOOOptionsTest extends TestCase {

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
	 * @covers ::import
	 * @covers ::toValue
	 * @covers \Niteo\WooCart\Defaults\Importers\WPOptionsValue::setValue
	 * @covers \Niteo\WooCart\Defaults\Value::__construct
	 * @covers \Niteo\WooCart\Defaults\Value::getStrippedKey
	 * @covers \Niteo\WooCart\Defaults\Value::getValue
	 * @covers \Niteo\WooCart\Defaults\Value::setKey
	 */
	public function testImport() {
		global $wpdb;

		\WP_Mock::userFunction(
			'get_option',
			array(
				'return' => true,
				'args'   => array( 'test_name' ),
			)
		);
		$wpdb          = \Mockery::mock( '\WPDB' );
		$wpdb->options = 'wp_options';
		$wpdb->shouldReceive( 'update' )
			->with( 'wp_options', array( 'option_value' => 'test_value' ), array( 'option_name' => 'test_name' ) )
			->andReturn( true );

		$value = WooOptions::toValue( 'test_name', 'test_value' );
		$o     = new WooOptions();
		$o->import( $value );
	}

	/**
	 * @covers ::import
	 * @covers ::toValue
	 * @covers \Niteo\WooCart\Defaults\Importers\WPOptionsValue::setValue
	 * @covers \Niteo\WooCart\Defaults\Value::__construct
	 * @covers \Niteo\WooCart\Defaults\Value::getStrippedKey
	 * @covers \Niteo\WooCart\Defaults\Value::getValue
	 * @covers \Niteo\WooCart\Defaults\Value::setKey
	 */
	public function testImportNew() {
		global $wpdb;

		\WP_Mock::userFunction(
			'get_option',
			array(
				'return' => false,
				'args'   => array( 'test_name' ),
			)
		);
		$wpdb          = \Mockery::mock( '\WPDB' );
		$wpdb->options = 'wp_options';
		$wpdb->shouldReceive( 'insert' )
			->with(
				'wp_options',
				array(
					'option_value' => 'test_value',
					'autoload'     => 'yes',
					'option_name'  => 'test_name',
				),
				array(
					0 => '%s',
					1 => '%s',
					2 => '%s',
				)
			)
			->andReturn( true );

		$value = WooOptions::toValue( 'test_name', 'test_value' );
		$o     = new WooOptions();
		$o->import( $value );
	}

	/**
	 * @covers ::items
	 * @covers \Niteo\WooCart\Defaults\Value::__construct
	 * @covers \Niteo\WooCart\Defaults\Importers\WPOptionsValue::setValue
	 * @covers \Niteo\WooCart\Defaults\Value::getKey
	 * @covers \Niteo\WooCart\Defaults\Value::getStrippedKey
	 * @covers \Niteo\WooCart\Defaults\Value::setKey
	 */
	public function testItems() {
		global $wpdb;

		$option               = new stdClass();
		$option->option_name  = 'test_name';
		$option->option_value = 'test_value';

		$wpdb          = \Mockery::mock( '\WPDB' );
		$wpdb->options = 'wp_options';
		$wpdb->shouldReceive( 'prepare' )->andReturn( '' );
		$wpdb->shouldReceive( 'get_results' )->andReturn( array( $option, $option, $option, $option ) );

		$o = new WooOptions();
		$this->assertCount( 4, $o->items() );
		$value = $o->items()->current();
		$this->assertEquals( 'test_name', $value->getKey() );
		$this->assertEquals( 'test_name', $value->getStrippedKey() );

	}
}
