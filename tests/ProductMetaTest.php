<?php

namespace Niteo\WooCart\Defaults\Importers;

use PHPUnit\Framework\TestCase;

function mt_rand()
{
    return 1;
}

class ProductMetaTest extends TestCase
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
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::__construct
     * @covers Niteo\WooCart\Defaults\Importers\FromArray::fromArray
     * @covers Niteo\WooCart\Defaults\Importers\ProductMeta::getCategoryIds
     * @covers Niteo\WooCart\Defaults\Importers\ProductMeta::set_category_ids
     */
    public function testSet_category_ids()
    {
        \WP_Mock::userFunction(
            'get_term_by', array(
                'return' => (object)['term_id' => 33],
            )
        );
        $product = ProductMeta::fromArray(["category" => "test"]);
        $product->set_category_ids();
        $this->assertEquals([33], $product->getCategoryIds());

    }

    /**
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::__construct
     * @covers Niteo\WooCart\Defaults\Importers\FromArray::fromArray
     * @covers Niteo\WooCart\Defaults\Importers\ProductMeta::getCategoryIds
     * @covers Niteo\WooCart\Defaults\Importers\ProductMeta::set_category_ids
     */
    public function testSet_category_ids_missing()
    {
        \WP_Mock::userFunction(
            'get_term_by', array(
                'return' => false,
            )
        );
        \WP_Mock::userFunction(
            'wp_insert_term', array(
                'return' => ["term_id" => 33],
            )
        );
        $product = ProductMeta::fromArray(["category" => "test"]);
        $product->set_category_ids();
        $this->assertEquals([33], $product->getCategoryIds());

    }

    /**
     * @covers \Niteo\WooCart\Defaults\Importers\WooProducts::__construct
     * @covers \Niteo\WooCart\Defaults\Importers\FromArray::fromArray
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::get_image_path
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::save
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::upload_image
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::upload_images
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::set_alias
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::getAliases
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::getImageIds
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::getCategoryIds
     * @covers \Niteo\WooCart\Defaults\Importers\ProductMeta::set_category_ids
     */
    public function testSave()
    {
        $metadata = array('title' => 'image.jpg');
        \WP_Mock::userFunction(
            'wp_upload_bits', array(
                'return' => array('type' => 'jpeg', 'file' => 'image.jpg')
            )
        );
        \WP_Mock::userFunction(
            'wp_insert_attachment', array(
                'args' => array(
                    array(
                        'post_title' => 'image.jpg',
                        'post_mime_type' => 'jpeg',
                        'post_status' => 'publish',
                        'post_content' => '',
                    ),
                    'image.jpg'
                ),
                'return' => 1234
            )
        );
        \WP_Mock::userFunction(
            'wp_generate_attachment_metadata', array(
                'args' => array(1234, 'image.jpg'),
                'return' => $metadata,
            )
        );
        \WP_Mock::userFunction(
            'wp_update_attachment_metadata', array(
                'args' => array(1234, $metadata)
            )
        );
        \WP_Mock::userFunction(
            'update_post_meta', array(
                'args' => array(999, '_thumbnail_id', 1234)
            )
        );

        \WP_Mock::userFunction(
            'get_term_by', array(
                'return' => (object)['term_id' => 33],
            )
        );

        $WC_Product = \Mockery::mock('overload:WC_Product');
        $WC_Product->shouldReceive('set_props')->once()->with([
            'name' => "test",
            'description' => NULL,
            'short_description' => NULL,
            'sale_price' => NULL,
            'regular_price' => NULL,
            'category_ids' => [33],
            'image_id' => 1234,
            'gallery_image_ids' => [1234, 1234, 1234],
            'weight' => 1,
            'length' => 1,
            'width' => 1,
            'height' => 1,
            'featured' => 1,
            'shipping_class_id' => 0
        ]);
        $WC_Product->shouldReceive('save')->times(1)->andReturnTrue();
        $product = ProductMeta::fromArray(["title" => "test", "images" => [
            __DIR__ . "/fixtures/image.jpg",
            __DIR__ . "/fixtures/image.jpg",
            "common:/fixtures/image.jpg",
            "common:/fixtures/image.jpg",
            "/tmp/fixtures/image.jpg",
        ]]);
        $product->set_alias("common:", __DIR__);
        $product->set_category_ids();
        $product->upload_images();
        $product->save();

        $this->assertEquals([1234, 1234, 1234], $product->getImageIds());
        $this->count(1, $product->getAliases());
        $this->assertEquals([33], $product->getCategoryIds());
    }


}
