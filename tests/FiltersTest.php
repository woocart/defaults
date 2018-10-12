<?php
/**
 * Created by PhpStorm.
 * User: dz0ny
 * Date: 12.10.2018
 * Time: 14:00
 */

use Niteo\WooCart\Defaults\Filters;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
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
     * @covers \Niteo\WooCart\Defaults\Filters::__construct
     */
    public function test__construct()
    {
        \WP_Mock::expectFilterAdded( 'option_woocommerce_checkout_privacy_policy_text', 'do_shortcode' );
        \WP_Mock::expectFilterAdded( 'option_woocommerce_registration_privacy_policy_text', 'do_shortcode' );
        new Filters();
    }
}
