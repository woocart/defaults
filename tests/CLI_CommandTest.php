<?php


use Niteo\WooCart\Defaults\CLICommand;
use PHPUnit\Framework\TestCase;

// we can do this because this function is being defined in the
// namespace, and, so, this is not overwriting the built-in function,
// it's merely obscuring it
function current_time( $string ) {
	return CLICommandTest::$functions->current_time( $string );
}

class CLICommandTest extends TestCase {


	public static $functions;

	function setUp() {
		\WP_Mock::setUp();
		self::$functions = \Mockery::mock();
	}

	function tearDown() {
		$this->addToAssertionCount(
			\Mockery::getContainer()->mockery_getExpectationCount()
		);
		\WP_Mock::tearDown();
		\Mockery::close();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Filters::__construct
	 */
	public function testSales() {
		self::$functions->shouldReceive( 'current_time' )->with( 'timestamp' );
		$WC_Product = \Mockery::mock( 'overload:WC_Report_Sales_By_Date' );
		$WC_Product->shouldReceive( 'get_report_data' )->once()->with()->andReturn(
			array(
				'total_sales' => '42',
			)
		);
		( new CLICommand() )->sales( array( 'total_sales' ), array() );
		$this->expectOutputString( '42' );
	}


}
