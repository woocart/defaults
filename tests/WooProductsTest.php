<?php

use Niteo\WooCart\Defaults\Importers\WooProducts;
use PHPUnit\Framework\TestCase;


class WooProductsTest extends TestCase
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
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::__construct
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::get_product_count
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::import
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::parse_product
     */
    public function testImport()
    {

        \Mockery::mock("alias:Niteo\WooCart\Defaults\Importers\ProductMeta")
            ->shouldReceive("set_alias")->times(3)
            ->shouldReceive("set_category_ids")->times(3)
            ->shouldReceive("upload_images")->times(3)
            ->shouldReceive("save")->andReturnTrue()
            ->shouldReceive("fromArray")->times(3)->andReturnSelf();

        $import = new WooProducts(__DIR__ . "/fixtures/products.html", __DIR__);
        $import->import();
        $this->assertEquals(3, $import->get_product_count());
    }

    /**
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::__construct
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::get_product_count
     */
    public function test__construct()
    {
        $import = new WooProducts(__DIR__ . "/fixtures/products.html", __DIR__);
        $this->assertEquals(0, $import->get_product_count());
    }

}
