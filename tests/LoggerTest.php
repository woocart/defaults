<?php


use Niteo\WooCart\Defaults\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
  function setUp()
  {
    \WP_Mock::setUp();
  }

  function tearDown()
  {
    $this->addToAssertionCount(
        \Mockery::getContainer()->mockery_getExpectationCount()
    );
    \WP_Mock::tearDown();
    \Mockery::close();
  }


  /**
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   */
  public function testConstructor()
  {
    $logger = new Logger();

    \WP_Mock::expectActionAdded( 'activated_plugin', [ $logger, 'activation' ], 10, 2 );
    \WP_Mock::expectActionAdded( 'deactivated_plugin', [ $logger, 'deactivation' ], 10, 2 );

    $logger->__construct();
  }

  /**
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   * @covers \Niteo\WooCart\Defaults\Logger::activation
   * @covers \Niteo\WooCart\Defaults\Logger::plugin_status_change
   */
  public function testActivation()
  {
    $logger = new Logger();

    \WP_Mock::userFunction(
      'get_plugin_data', [
        'return' => [
          'Name'    => 'Plugin',
          'Version' => '1.0'
        ]
      ]
    );
    \Mockery::mock( 'alias:\WooCart\Log\Socket' )
              ->shouldReceive( 'log' )
              ->andReturn( true );

    $this->assertTrue( $logger->activation( 'fake/fake.php', false ) );
  }

  /**
   * @covers \Niteo\WooCart\Defaults\Logger::__construct
   * @covers \Niteo\WooCart\Defaults\Logger::deactivation
   * @covers \Niteo\WooCart\Defaults\Logger::plugin_status_change
   */
  public function testDeactivation()
  {
    $logger = new Logger();

    \WP_Mock::userFunction(
      'get_plugin_data', [
        'return' => false
      ]
    );

    $this->assertFalse( $logger->deactivation( 'fake/fake.php', false ) );
  }

}
