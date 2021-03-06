<?php

use Niteo\WooCart\Defaults\Importer;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\Importer
 */
class ImporterTest extends TestCase {

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
	 * @covers ::read_file
	 */
	public function testRead_file_non_serialized() {
		$i    = new Importer();
		$data = $i->read_file( dirname( __FILE__ ) . '/fixtures/non_serialized.yaml' );
		$this->assertEquals(
			array(
				'wp/test_name' => 'test_value',
			),
			$data
		);

	}

	/**
	 * @covers ::read_file
	 */
	public function testRead_file_serialized() {
		$i    = new Importer();
		$data = $i->read_file( dirname( __FILE__ ) . '/fixtures/serialized.yaml' );
		$this->assertEquals(
			array(
				'wp/test_name'            => 'test_value',
				'wp/test_php'             => 'i:123456;',
				'wp/test_json'            => '["abc"]',
				'wp/test_implode_newline' => "one\ntwo",
				'wp/test_implode_comma'   => 'one,two',
			),
			$data
		);

	}


	/**
	 * @covers ::read_file
	 * @covers \Niteo\WooCart\Defaults\ConfigsRegistry::get
	 * @covers ::resolve
	 * @covers \Niteo\WooCart\Defaults\Importers\WPOptions::import
	 * @covers \Niteo\WooCart\Defaults\Importers\WPOptions::toValue
	 * @covers \Niteo\WooCart\Defaults\Importers\WPOptionsValue::setValue
	 * @covers ::parse
	 * @covers \Niteo\WooCart\Defaults\Value::__construct
	 * @covers \Niteo\WooCart\Defaults\Value::getStrippedKey
	 * @covers \Niteo\WooCart\Defaults\Value::getValue
	 * @covers \Niteo\WooCart\Defaults\Value::setKey
	 * @covers ::import
	 */
	public function testImport() {
		global $wpdb;
		\WP_Mock::userFunction(
			'get_option',
			array(
				'return' => true,
			)
		);

		$wpdb          = \Mockery::mock( '\WPDB' );
		$wpdb->options = 'wp_options';
		$wpdb->shouldReceive( 'update' )
			->with( 'wp_options', array( 'option_value' => 'test_value' ), array( 'option_name' => 'test_name' ) )
			->andReturn( true );

		$wpdb->shouldReceive( 'update' )
			->with( 'wp_options', array( 'option_value' => 'i:123456;' ), array( 'option_name' => 'test_php' ) )
			->andReturn( true );

		$wpdb->shouldReceive( 'update' )
			->with( 'wp_options', array( 'option_value' => '["abc"]' ), array( 'option_name' => 'test_json' ) )
			->andReturn( true );

		$wpdb->shouldReceive( 'update' )
			->with( 'wp_options', array( 'option_value' => "one\ntwo" ), array( 'option_name' => 'test_implode_newline' ) )
			->andReturn( true );
		$wpdb->shouldReceive( 'update' )
			->with( 'wp_options', array( 'option_value' => 'one,two' ), array( 'option_name' => 'test_implode_comma' ) )
			->andReturn( true );

		$i = new Importer();
		$i->import( dirname( __FILE__ ) . '/fixtures/serialized.yaml' );
	}

	/**
	 * @covers ::resolve
	 * @covers \Niteo\WooCart\Defaults\ConfigsRegistry::get
	 */
	public function testResolve() {
		$i = new Importer();

		$this->expectException( \Exception::class );
		$i->resolve( 'foo/test' );
	}
}
