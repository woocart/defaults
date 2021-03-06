<?php


use Niteo\WooCart\Defaults\Importers\PageMeta;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\Importers\PageMeta
 */
class PageMetaTest extends TestCase {

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
	 * @covers ::getInsertParams
	 * @covers \Niteo\WooCart\Defaults\Importers\ToArray::toArray
	 */
	public function testGetInsertParams() {
		$p            = new PageMeta();
		$p->post_name = 'post_name';
		$this->assertEquals(
			array(
				'post_name' => 'post_name',
			),
			$p->getInsertParams()
		);
	}

	/**
	 * @covers ::getDefaultsImport
	 * @covers ::getInsertParams
	 * @covers Niteo\WooCart\Defaults\Importers\ToArray::toArray
	 */
	public function testGetDefaultsImport() {
		$p                   = new PageMeta();
		$p->post_name        = 'post_name';
		$p->woocart_defaults = array(
			'wp/key' => '$ID',
		);
		$this->assertEquals( array( 'wp/key' => 1234 ), (array) $p->getDefaultsImport( array( 'ID' => 1234 ) ) );
	}

	/**
	 * @covers ::getDefaultsImport
	 * @covers ::getInsertParams
	 * @covers Niteo\WooCart\Defaults\Importers\ToArray::toArray
	 */
	public function testGetDefaultsImportEmpty() {
		$p            = new PageMeta();
		$p->post_name = 'post_name';
		$this->assertEquals( array(), (array) $p->getDefaultsImport( array( 'ID' => 1234 ) ) );
	}
}
