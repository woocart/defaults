<?php


use Niteo\WooCart\Defaults\ConfigsRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\ConfigsRegistry
 */
class ImportRegistryTest extends TestCase {

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
	 * @covers ::get
	 */
	public function testGetImporters() {
		foreach ( ConfigsRegistry::get() as $importer ) {
			$this->assertArrayHasKey( 'Niteo\WooCart\Defaults\Importers\Configuration', class_implements( $importer ) );
		}
	}

}
