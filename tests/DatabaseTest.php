<?php

use Niteo\WooCart\Defaults\Database;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Niteo\WooCart\Defaults\Database
 */
class DatabaseTest extends TestCase {

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
	 * @covers ::analyze_tables
	 * @covers ::format_data
	 * @covers ::message
	 */
	public function testAnalyzeTablesSuccess() {
		global $wpdb;

		$database = Mockery::mock( Database::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$database->shouldReceive( 'analyze_table' )->andReturn(
			array(
				'Table'    => 'wp_options',
				'Op'       => 'Analyze',
				'Msg_type' => 'status',
			)
		);
		$database->shouldReceive( 'format_data' )->andReturn( true );
		$database->shouldReceive( 'message' )->andReturn( true );

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';

		$this->assertEmpty(
			$database->analyze_tables()
		);
	}

	/**
	 * @covers ::analyze_tables
	 * @covers ::format_data
	 * @covers ::message
	 */
	public function testAnalyzeTablesFail() {
		global $wpdb;

		$database = Mockery::mock( Database::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$database->shouldReceive( 'analyze_table' )->andReturn(
			array(
				'Table'    => 'wp_options',
				'Op'       => 'Analyze',
				'Msg_type' => 'Error',
			)
		);
		$database->shouldReceive( 'format_data' )->andReturn( true );
		$database->shouldReceive( 'message' )->andReturn( true );

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';

		$this->assertEmpty(
			$database->analyze_tables()
		);
	}

	/**
	 * @covers ::switch_to_innodb
	 * @covers ::get_tables_via_engine
	 * @covers ::set_table_engine
	 */
	public function testSwitchToInnodbFalse() {
		$database = Mockery::mock( Database::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$database->shouldReceive( 'get_tables_via_engine' )->andReturn( array() );
		$database->shouldReceive( 'message' )->andReturn( true );

		$this->assertEmpty(
			$database->switch_to_innodb()
		);
	}

	/**
	 * @covers ::switch_to_innodb
	 * @covers ::get_tables_via_engine
	 * @covers ::set_table_engine
	 */
	public function testSwitchToInnodbSuccess() {
		$database = Mockery::mock( Database::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$database->shouldReceive( 'get_tables_via_engine' )->andReturn(
			array(
				array(
					'engine'     => 'myisam',
					'table_name' => 'wp_options',
				),
				array(
					'engine'     => 'myisam',
					'table_name' => 'wp_posts',
				),
				array(
					'engine'     => 'myisam',
					'table_name' => 'wp_postmeta',
				),
			)
		);
		$database->shouldReceive( 'set_table_engine' )->andReturn( true );
		$database->shouldReceive( 'message' )->andReturn( true );

		$this->assertEmpty(
			$database->switch_to_innodb()
		);
	}

	/**
	 * @covers ::switch_to_innodb
	 * @covers ::get_tables_via_engine
	 * @covers ::set_table_engine
	 */
	public function testSwitchToInnodbFail() {
		$database = Mockery::mock( Database::class )->makePartial()->shouldAllowMockingProtectedMethods();
		$database->shouldReceive( 'get_tables_via_engine' )->andReturn(
			array(
				array(
					'engine'     => 'myisam',
					'table_name' => 'wp_options',
				),
				array(
					'engine'     => 'myisam',
					'table_name' => 'wp_posts',
				),
				array(
					'engine'     => 'myisam',
					'table_name' => 'wp_postmeta',
				),
			)
		);
		$database->shouldReceive( 'set_table_engine' )->andReturn( false );
		$database->shouldReceive( 'message' )->andReturn( true );

		$this->assertEmpty(
			$database->switch_to_innodb()
		);
	}

	/**
	 * @covers ::analyze_table
	 */
	public function testAnalyzeTable() {
		global $wpdb;

		$database = new Database();
		$wpdb     = Mockery::mock( '\WPDB' );
		$wpdb->shouldReceive( 'get_row' )->andReturn(
			array(
				'Table' => 'wp_options',
				'Op'    => 'Analyze',
			)
		);

		$this->assertEquals(
			array(
				'Table' => 'wp_options',
				'Op'    => 'Analyze',
			),
			$database->analyze_table( 'wp_options' )
		);
	}

	/**
	 * @covers ::add_drop_column_index
	 * @covers ::is_table_index
	 */
	public function testAddDropColumnIndexTrue() {
		global $wpdb;

		$database = Mockery::mock( Database::class )->makePartial();
		$database->shouldReceive( 'is_table_index' )->andReturn( true );

		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->shouldReceive( 'query' )->andReturn( true );

		$this->assertTrue(
			$database->add_drop_column_index( 'wp_options', 'ADD INDEX(autoload)', 'autoload' )
		);
	}

	/**
	 * @covers ::is_table_index
	 */
	public function testIsTableIndexTrue() {
		global $wpdb;

		$database = new Database();

		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->shouldReceive( 'get_results' )->andReturn(
			array(
				(object) array(
					'Diff_Key' => 'VALUE',
				),
				(object) array(
					'Key_name' => 'INDEX_ONE',
				),
				(object) array(
					'Key_name' => 'INDEX_TWO',
				),
			)
		);

		$this->assertTrue(
			$database->is_table_index( 'wp_options', 'INDEX_TWO' )
		);
	}

	/**
	 * @covers ::is_table_index
	 */
	public function testIsTableIndexFalse() {
		global $wpdb;

		$database = new Database();

		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->shouldReceive( 'get_results' )->andReturn(
			array(
				(object) array(
					'Key_name' => 'INDEX_ONE',
				),
				(object) array(
					'Key_name' => 'INDEX_TWO',
				),
			)
		);

		$this->assertFalse(
			$database->is_table_index( 'wp_options', 'INDEX_FIVE' )
		);
	}

	/**
	 * @covers ::is_table_index_on_column
	 */
	public function testIsTableIndexOnColumnTrue() {
		global $wpdb;

		$database = new Database();

		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->shouldReceive( 'get_results' )->andReturn(
			array(
				(object) array(
					'Diff_Key' => 'VALUE',
				),
				(object) array(
					'Column_name' => 'INDEX_ONE',
				),
				(object) array(
					'Column_name' => 'INDEX_TWO',
				),
			)
		);

		$this->assertTrue(
			$database->is_table_index_on_column( 'wp_options', 'INDEX_TWO' )
		);
	}

	/**
	 * @covers ::is_table_index_on_column
	 */
	public function testIsTableIndexOnColumnFalse() {
		global $wpdb;

		$database = new Database();

		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->shouldReceive( 'get_results' )->andReturn(
			array(
				(object) array(
					'Column_name' => 'INDEX_ONE',
				),
				(object) array(
					'Column_name' => 'INDEX_TWO',
				),
			)
		);

		$this->assertFalse(
			$database->is_table_index_on_column( 'wp_options', 'INDEX_FIVE' )
		);
	}

	/**
	 * @covers ::is_table_engine
	 */
	public function testIsTableEngineTrue() {
		global $wpdb;

		$database = new Database();

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->dbname = 'database';
		$wpdb->shouldReceive( 'prepare' )->andReturn( true );
		$wpdb->shouldReceive( 'get_var' )->andReturn( 'innodb' );

		$this->assertTrue(
			$database->is_table_engine( 'wp_options', 'innodb' )
		);
	}

	/**
	 * @covers ::is_table_engine
	 */
	public function testIsTableEngineFalse() {
		global $wpdb;

		$database = new Database();

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->dbname = 'database';
		$wpdb->shouldReceive( 'prepare' )->andReturn( true );
		$wpdb->shouldReceive( 'get_var' )->andReturn( 'myisam' );

		$this->assertFalse(
			$database->is_table_engine( 'wp_options', 'innodb' )
		);
	}

	/**
	 * @covers ::get_tables_via_engine
	 */
	public function testGetTablesViaEngine() {
		global $wpdb;

		$database = new Database();

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->dbname = 'database';
		$wpdb->shouldReceive( 'prepare' )->andReturn( true );
		$wpdb->shouldReceive( 'get_results' )->andReturn(
			array(
				array(
					'engine'     => 'innodb',
					'table_name' => 'wp_options',
				),
				array(
					'engine'     => 'innodb',
					'table_name' => 'wp_posts',
				),
				array(
					'engine'     => 'innodb',
					'table_name' => 'wp_postmeta',
				),
			)
		);

		$this->assertEquals(
			array(
				array(
					'engine'     => 'innodb',
					'table_name' => 'wp_options',
				),
				array(
					'engine'     => 'innodb',
					'table_name' => 'wp_posts',
				),
				array(
					'engine'     => 'innodb',
					'table_name' => 'wp_postmeta',
				),
			),
			$database->get_tables_via_engine( 'InnoDB' )
		);
	}

	/**
	 * @covers ::set_table_engine
	 * @covers ::is_table_engine
	 */
	public function testSetTableEngineTrue() {
		global $wpdb;

		$database = Mockery::mock( Database::class )->makePartial();
		$database->shouldReceive( 'is_table_engine' )->andReturn( true );

		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->shouldReceive( 'prepare' )->andReturn( true );
		$wpdb->shouldReceive( 'query' )->andReturn( true );

		$this->assertTrue(
			$database->set_table_engine( 'wp_options', 'innodb' )
		);
	}

}
