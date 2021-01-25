<?php


use Niteo\WooCart\Defaults\Shortcodes;
use PHPUnit\Framework\TestCase;

class ShortcodesTest extends TestCase {

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
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 */
	public function test__construct() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		new Shortcodes();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::company_city
	 */
	public function testCompany_city() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_store_city',
				'times'  => 1,
				'return' => 'woocommerce_store_city',
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( 'woocommerce_store_city', $s->company_city( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::store_name
	 */
	public function testStore_name() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'blogname',
				'times'  => 1,
				'return' => 'blogname',
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( 'blogname', $s->store_name( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::company_postcode
	 */
	public function testCompany_postcode() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_store_postcode',
				'times'  => 1,
				'return' => 'woocommerce_store_postcode',
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( 'woocommerce_store_postcode', $s->company_postcode( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::policy_page
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testPolicy_page() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_permalink',
			array(
				'times'  => 1,
				'return' => 'slug',
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'wp_page_for_privacy_policy',
				'times'  => 1,
				'return' => 1,
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( '<a href="slug">slug</a>', $s->policy_page( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::cookie_page
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testCookie_page() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_permalink',
			array(
				'times'  => 1,
				'return' => 'slug',
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'wp_page_for_cookies_policy',
				'times'  => 1,
				'return' => 1,
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( '<a href="slug">slug</a>', $s->cookie_page( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::returns_page
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testReturns_page() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_permalink',
			array(
				'times'  => 1,
				'return' => 'slug',
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_returns_page_id',
				'times'  => 1,
				'return' => 1,
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( '<a href="slug">slug</a>', $s->returns_page( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::terms_page
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testTerms_page() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_permalink',
			array(
				'times'  => 1,
				'return' => 'slug',
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_terms_page_id',
				'times'  => 1,
				'return' => 1,
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( '<a href="slug">slug</a>', $s->terms_page( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::contact_page
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testContact_page() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_permalink',
			array(
				'times'  => 1,
				'return' => 'slug',
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'wp_page_for_contact',
				'times'  => 1,
				'return' => 1,
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( '<a href="slug">slug</a>', $s->contact_page( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testWoo_permalink() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_permalink',
			array(
				'times'  => 1,
				'return' => 'slug',
			)
		);
		$s = new Shortcodes();
		$this->assertEquals( '<a href="slug">slug</a>', $s->woo_permalink( array( 'id' => 1 ), null ) );
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testWoo_permalink_content() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_permalink',
			array(
				'times'  => 1,
				'return' => 'slug',
			)
		);
		$s = new Shortcodes();
		$this->assertEquals( '<a href="slug">slug</a>', $s->woo_permalink( array( 'id' => 1 ), '<a href="%s">slug</a>' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testWoo_permalink_empty() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		$s = new Shortcodes();
		$this->assertEquals( '[woo-permalink]', $s->woo_permalink( array(), '[woo-permalink]' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::company_name
	 */
	public function testCompany_name() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_company_name',
				'times'  => 1,
				'return' => 'woocommerce_company_name',
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( 'woocommerce_company_name', $s->company_name( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::store_url
	 */
	public function testStore_url() {
		\WP_Mock::userFunction(
			'site_url',
			array(
				'times'  => 1,
				'return' => 'foo',
			)
		);
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'blogname',
				'times'  => 1,
				'return' => 'blogname',
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( '<a href="foo">blogname</a>', $s->store_url( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::tax_id
	 */
	public function testTax_id() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_tax_id',
				'times'  => 1,
				'return' => 'woocommerce_tax_id',
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( 'woocommerce_tax_id', $s->tax_id( null, null ) );
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::page
	 */
	public function testPage_page() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);

		global $wpdb;
		$wpdb        = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->shouldReceive( 'prepare' )
			->with( "SELECT post_content from wp_posts where post_type = 'page' and post_name = %s", 'slug' )
			->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_var' )
			->with( 'query' )
			->andReturn( 'slug' );

		$s = new Shortcodes();
		$this->assertEquals( 'slug', $s->page( array( 'page' => 'slug' ), null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::page
	 */
	public function testPage_post() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);

		global $wpdb;
		$wpdb        = \Mockery::mock( '\WPDB' );
		$wpdb->posts = 'wp_posts';
		$wpdb->shouldReceive( 'prepare' )
			->with( "SELECT post_content from wp_posts where post_type = 'post' and post_name = %s", 'slug' )
			->andReturn( 'query' );
		$wpdb->shouldReceive( 'get_var' )
			->with( 'query' )
			->andReturn( 'slug' );

		$s = new Shortcodes();
		$this->assertEquals( 'slug', $s->page( array( 'post' => 'slug' ), null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::company_address
	 */
	public function testCompany_address() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => 'woocommerce_store_address',
				'times'  => 1,
				'return' => 'woocommerce_store_address',
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( 'woocommerce_store_address', $s->company_address( null, null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woocart
	 */
	public function testWoocart() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 15,
			)
		);

		$s = new Shortcodes();
		$this->assertEquals( '<a href="https://woocart.com">WooCart</a>', $s->woocart( null, null ) );
	}
}
