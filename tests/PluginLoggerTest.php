<?php


use Niteo\WooCart\Defaults\PluginLogger;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\PluginLogger
 */
class PluginLoggerTest extends TestCase {

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
		$logger = new PluginLogger();

		\WP_Mock::expectActionAdded( 'activated_plugin', array( $logger, 'activation' ), 10, 2 );
		\WP_Mock::expectActionAdded( 'deactivated_plugin', array( $logger, 'deactivation' ), 10, 2 );

		$logger->__construct();
	}

	/**
	 * @covers ::__construct
	 * @covers ::activation
	 * @covers ::plugin_status_change
	 */
	public function testActivation() {
		$logger = new PluginLogger();

		\WP_Mock::userFunction(
			'get_plugin_data',
			array(
				'return' => array(
					'Name'    => 'Plugin',
					'Version' => '1.0',
				),
			)
		);
		\Mockery::mock( 'alias:\WooCart\Log\Socket' )
			  ->shouldReceive( 'log' )
			  ->andReturn( true );

		$this->assertTrue( $logger->activation( 'fake/fake.php', false ) );
	}

	/**
	 * @covers ::__construct
	 * @covers ::deactivation
	 * @covers ::plugin_status_change
	 */
	public function testDeactivation() {
		$logger = new PluginLogger();

		\WP_Mock::userFunction(
			'get_plugin_data',
			array(
				'return' => false,
			)
		);

		$this->assertFalse( $logger->deactivation( 'fake/fake.php', false ) );
	}

}
