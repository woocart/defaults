<?php


use Niteo\WooCart\Defaults\GDPR;
use PHPUnit\Framework\TestCase;

class GDPRTest extends TestCase
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
     * @covers \Niteo\WooCart\Defaults\GDPR::__construct
     * @covers \Niteo\WooCart\Defaults\GDPR::scripts
     */
    public function testConstructor()
    {
        \WP_Mock::userFunction(
            'plugin_dir_url', array(
                'times'      => 1
            )
        );
        \WP_Mock::userFunction(
            'wp_enqueue_style', array(
                'args'      => ['woocart-gdpr', '/assets/css/front-gdpr.css', [], '@##VERSION##@']
            )
        );
        \WP_Mock::userFunction(
            'wp_enqueue_script', array(
                'args'      => ['woocart-gdpr', '/assets/js/front-gdpr.js', [], '@##VERSION##@', true]
            )
        );
        \WP_Mock::userFunction(
            'is_admin', array(
                'times'     => 2,
                'return'    => true
            )
        );
        $gdpr = new GDPR();
        \WP_Mock::expectActionAdded( 'wp_footer', [ $gdpr, 'show_consent' ] );
        \WP_Mock::expectActionAdded( 'wp_enqueue_scripts', [ $gdpr, 'scripts' ] );
        \WP_Mock::expectActionAdded( 'admin_menu', [ $gdpr, 'add_menu_item' ], 1 );

        $gdpr->__construct();
        $gdpr->scripts();
        \WP_Mock::assertHooksAdded();
    }

    /**
     * @covers \Niteo\WooCart\Defaults\GDPR::__construct
     * @covers \Niteo\WooCart\Defaults\GDPR::add_menu_item
     */
    public function testAddMenuItem() {
        $gdpr = new GDPR();
        \WP_Mock::userFunction(
            'add_options_page', array(
                'times' => 1
            )
        );

        $gdpr->add_menu_item();
    }

    /**
     * @covers \Niteo\WooCart\Defaults\GDPR::__construct
     * @covers \Niteo\WooCart\Defaults\GDPR::show_consent
     */
    public function testConsent()
    {
        $gdpr = new GDPR();
        \WP_Mock::userFunction(
            'is_admin', array(
                'return'    => true
            )
        );
        \WP_Mock::userFunction(
            'get_option', array(
                'args'      => 'woocommerce_allow_tracking',
                'return'    => 'no'
            )
        );
        \WP_Mock::userFunction(
            'absint', array(
                'return'    => true
            )
        );
        \WP_Mock::userFunction(
            'esc_url', array(
                'return'    => true
            )
        );
        \WP_Mock::userFunction(
            'sanitize_text_field', array(
                'times'     => 2,
                'return'    => ''
            )
        );
        \WP_Mock::userFunction(
            'get_the_title', array(
                'times'     => 2,
                'return'    => ''
            )
        );
        \WP_Mock::userFunction(
            'get_permalink', array(
                'times'     => 2,
                'args'      => 10,
                'return'    => 'https://woocart.com'
            )
        );
        \WP_Mock::userFunction(
            'get_option', array(
                'args'      => 'wp_page_for_privacy_policy',
                'return'    => 10
            )
        );
        \WP_Mock::userFunction(
            'get_option', array(
                'args'      => 'wp_page_for_cookie_policy',
                'return'    => 20
            )
        );
        \WP_Mock::userFunction(
            'wp_kses', array(
                'return'    => 'Standard output.'
            )
        );
        $gdpr->show_consent();
        $this->expectOutputString( '<div class="wc-defaults-gdpr"><p>Standard output. <a href="javascript:;" id="wc-defaults-ok">OK</a></p></div><!-- .wc-defaults-gdpr -->'
        );
    }

    /**
     * @covers \Niteo\WooCart\Defaults\GDPR::__construct
     * @covers \Niteo\WooCart\Defaults\GDPR::options_page
     *
    public function testOptionsPage() {
        $gdpr = new GDPR();
        \WP_Mock::userFunction(
            'current_user_can', array(
                'args'   => 'manage_privacy_options',
                'times'  => 1,
                'return' => false
            )
        );
        \WP_Mock::userFunction(
            'wp_die', array(
                'times'  => 1
            )
        );
        \WP_Mock::userFunction(
            'settings_errors', array(
                'times'  => 1
            )
        );
        \WP_Mock::userFunction(
            'get_posts', array(
                'times'  => 1
            )
        );
        \WP_Mock::userFunction(
            'wp_nonce_field', array(
                'times'  => 1
            )
        );
        \WP_Mock::userFunction(
            'submit_button', array(
                'times'  => 1
            )
        );

        $gdpr->options_page();
    } */
}
