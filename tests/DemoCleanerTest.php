<?php


use Niteo\WooCart\Defaults\DemoCleaner;
use PHPUnit\Framework\TestCase;

class DemoCleanerTest extends TestCase {

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
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 */
	public function testConstructor() {
		$demo_cleaner = new DemoCleaner();
		\WP_Mock::expectActionAdded( 'admin_init', array( $demo_cleaner, 'init' ), PHP_INT_MAX );

		$demo_cleaner->__construct();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::init
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::delete
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::check
	 */
	public function testInit() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\DemoCleaner' )
					  ->makePartial();
		$mock->shouldReceive( 'delete' )
		 ->andReturn( true );

		\WP_Mock::wpFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => array(
					'products'    => array(),
					'attachments' => array(),
				),
			)
		);
		\WP_Mock::wpFunction(
			'add_meta_box',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', array( $mock, 'scripts' ) );
		\WP_Mock::expectActionAdded( 'admin_notices', array( $mock, 'notices' ) );

		$mock->init();
		\WP_Mock::assertHooksAdded();
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::check
	 */
	public function testCheckNotTrue() {
		$demo_cleaner = new DemoCleaner();

		\WP_Mock::wpFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertFalse( $demo_cleaner->check() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::scripts
	 */
	public function testScriptsCorrectHook() {
		$demo_cleaner = new DemoCleaner();

		\WP_Mock::wpFunction(
			'plugin_dir_url',
			array(
				'times'  => 1,
				'return' => 'https://localhost',
			)
		);
		\WP_Mock::wpFunction(
			'wp_enqueue_script',
			array(
				'times'  => 1,
				'return' => true,
			)
		);
		\WP_Mock::wpFunction(
			'wp_localize_script',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$demo_cleaner->scripts( 'index.php' );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::scripts
	 */
	public function testScriptsWrongHook() {
		$demo_cleaner = new DemoCleaner();
		$this->assertEmpty( $demo_cleaner->scripts( 'plugins.php' ) );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::delete
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::process
	 */
	public function testDeleteNoAction() {
		$demo_cleaner = new DemoCleaner();
		$this->assertEmpty( $demo_cleaner->delete() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::delete
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::process
	 */
	public function testDeleteEmptyAction() {
		$demo_cleaner = new DemoCleaner();

		$_GET['woo-action'] = '';
		$_GET['woo-nonce']  = '__nonce';

		\WP_Mock::userFunction(
			'wp_verify_nonce',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $demo_cleaner->delete() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::delete
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::process
	 */
	public function testDeleteNoOption() {
		$demo_cleaner = new DemoCleaner();

		$_GET['woo-action'] = 'products';
		$_GET['woo-nonce']  = '__nonce';

		\WP_Mock::userFunction(
			'wp_verify_nonce',
			array(
				'times'  => 1,
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$this->assertEmpty( $demo_cleaner->delete() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::delete
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::process
	 */
	public function testDeleteProducts() {
		$demo_cleaner = new DemoCleaner();

		$_GET['woo-action'] = 'products';
		$_GET['woo-nonce']  = '__nonce';

		// Fake anonymous class.
		$fake_class = new class() {
			public function delete( $force = false ) {
				return true;
			}

			public function get_id() {
				return -1;
			}
		};

		\WP_Mock::userFunction(
			'wp_verify_nonce',
			array(
				'times'  => 1,
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => array(
					'products' => array(
						30,
						40,
						50,
						60,
					),
				),
			)
		);
		\WP_Mock::userFunction(
			'wc_get_product',
			array(
				'times'  => 4,
				'return' => $fake_class,
			)
		);
		\WP_Mock::userFunction(
			'update_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $demo_cleaner->delete() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::delete
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::process
	 */
	public function testDeleteAttachments() {
		$demo_cleaner = new DemoCleaner();

		$_GET['woo-action'] = 'products';
		$_GET['woo-nonce']  = '__nonce';

		\WP_Mock::userFunction(
			'wp_verify_nonce',
			array(
				'times'  => 1,
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'return' => array(
					'attachments' => array(
						array( 30, 40 ),
						array( 50, 60 ),
						array( 70, 80 ),
					),
				),
			)
		);
		\WP_Mock::userFunction(
			'wp_delete_attachment',
			array(
				'times'  => 6,
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'update_option',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$this->assertEmpty( $demo_cleaner->delete() );
	}

	/**
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::__construct
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::cli
	 * @covers \Niteo\WooCart\Defaults\DemoCleaner::process
	 */
	public function testCli() {
		$mock = \Mockery::mock( 'Niteo\WooCart\Defaults\DemoCleaner' )
					  ->makePartial();
		$mock->shouldReceive( 'process' )
		 ->andReturn( true );

		$this->assertEmpty( $mock->cli() );
	}
}
