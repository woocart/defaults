<?php


use Niteo\WooCart\Defaults\Shortcodes;
use PHPUnit\Framework\TestCase;

class ShortcodesTest extends TestCase {

	function setUp() {
		\WP_Mock::setUp();
	}

	function tearDown() {
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
				'times' => 10,
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
				'times' => 10,
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
				'times' => 10,
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
				'times' => 10,
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
				'times' => 10,
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
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testWoo_permalink() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 10,
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
		$this->assertEquals( '<a href="slug">slug</a>', $s->woo_permalink( [ 'id' => 1 ], null ) );
	}


	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testWoo_permalink_content() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 10,
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
		$this->assertEquals( '<a href="slug">slug</a>', $s->woo_permalink( [ 'id' => 1 ], '<a href="%s">slug</a>' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::woo_permalink
	 */
	public function testWoo_permalink_empty() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 10,
			)
		);
		$s = new Shortcodes();
		$this->assertEquals( '[woo-permalink]', $s->woo_permalink( [], '[woo-permalink]' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::company_name
	 */
	public function testCompany_name() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 10,
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
				'times' => 10,
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
				'times' => 10,
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
				'times' => 10,
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
		$this->assertEquals( 'slug', $s->page( [ 'page' => 'slug' ], null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::page
	 */
	public function testPage_post() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 10,
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
		$this->assertEquals( 'slug', $s->page( [ 'post' => 'slug' ], null ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::__construct
	 * @covers \Niteo\WooCart\Defaults\Shortcodes::company_address
	 */
	public function testCompany_address() {
		\WP_Mock::userFunction(
			'add_shortcode',
			array(
				'times' => 10,
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
}
