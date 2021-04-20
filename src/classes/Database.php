<?php
/**
 * Handles database optimizations for the store.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.30.0
 */

namespace Niteo\WooCart\Defaults;

/**
 * Class Database
 *
 * @package Niteo\WooCart\Defaults
 */
class Database {

	/**
	 * Analyze tables (posts, postmeta and options).
	 *
	 * @return void
	 */
	public function analyze_tables() : void {
		global $wpdb;

		foreach ( array( 'posts', 'postmeta', 'options' ) as $table ) {
			$analyze_output = $this->analyze_table( $wpdb->prefix . $table );

			// Output to CLI.
			$this->format_data( array( $analyze_output ), array( 'Table', 'Op', 'Msg_type', 'Msg_text' ) );

			if ( 'Error' !== $analyze_output['Msg_type'] ) {
				$this->message(
					sprintf(
						esc_html__( 'Analyzed table %1$s', 'woocart-defaults' ),
						$wpdb->prefix . $table
					),
					'success'
				);

				continue;
			}

			$this->message(
				sprintf(
					esc_html__( 'Unable to optimize table %1$s', 'woocart-defaults' ),
					$wpdb->prefix . $table
				),
				'warning'
			);
		}
	}

	/**
	 * Check for and convert to InnoDB.
	 *
	 * @return void
	 */
	public function switch_to_innodb() : void {
		$non_innodb_tables = $this->get_tables_via_engine( 'InnoDB', '!=' );

		if ( ! $non_innodb_tables ) {
			$this->message(
				esc_html__( 'All tables have InnoDB engine.', 'woocart-defaults' ),
				'success'
			);

			return;
		}

		// We have tables with engine != InnoDB.
		$counter = 0;

		// Set InnoDB engine.
		foreach ( $non_innodb_tables as $table ) {
			$set_engine = $this->set_table_engine( $table['table_name'], 'innodb' );

			if ( $set_engine ) {
				++$counter;
			}
		}

		// Display message if tables were converted.
		if ( $counter ) {
			$this->message(
				sprintf(
					esc_html__( '%1$s tables were converted to InnoDB.', 'woocart-defaults' ),
					$counter
				),
				'success'
			);

			return;
		}

		$this->message(
			esc_html__( 'Unable to convert tables to InnoDB.', 'woocart-defaults' ),
			'error'
		);
	}

	/**
	 * Add indexes to columns for different tables.
	 *
	 * @return void
	 */
	public function add_indexes() : void {
		global $wpdb;

		$indexes = array(
			'options'  => array(
				'index' => 'autoload',
				'query' => 'ADD INDEX(autoload)',
			),
			'postmeta' => array(
				'index' => 'wcd_meta',
				'query' => 'ADD INDEX `wcd_meta`(`meta_value`(10))',
			),
		);

		foreach ( $indexes as $table => $index ) {
			if ( $this->is_table_index( $wpdb->prefix . $table, $index['index'] ) ) {
				$this->message(
					sprintf(
						esc_html__( 'Index already exists for %1$s table.', 'woocart-defaults' ),
						$wpdb->prefix . $table
					),
					'success'
				);

				continue;
			}

			$add_index = $this->add_drop_column_index( $wpdb->prefix . $table, $index['query'], $index['index'] );

			if ( $add_index ) {
				$this->message(
					sprintf(
						esc_html__( 'Index added to %1$s table.', 'woocart-defaults' ),
						$wpdb->prefix . $table
					),
					'success'
				);

				continue;
			}

			$this->message(
				sprintf(
					esc_html__( 'Unable to add index to %1$s table.', 'woocart-defaults' ),
					$wpdb->prefix . $table
				),
				'error'
			);
		}
	}

	/**
	 * Analyze table for improved performance.
	 *
	 * @param string $table_name Table name to run query against.
	 *
	 * @return array
	 */
	public function analyze_table( string $table_name ) : array {
		global $wpdb;

		return (array) $wpdb->get_row( "ANALYZE TABLE {$table_name}" );
	}

	/**
	 * Add or drop index to an existing column in a table.
	 *
	 * @param $table_name   Table name without prefix.
	 * @param $query        Query to run for adding index.
	 * @param $column_name  Column name.
	 *
	 * @return bool
	 */
	public function add_drop_column_index( string $table_name, string $query, string $column_name ) : bool {
		global $wpdb;

		$wpdb->query( "ALTER TABLE {$table_name} {$query}" );

		return $this->is_table_index( $table_name, $column_name );
	}

	/**
	 * Check if table has provided index.
	 *
	 * @param string $table_name Table to check for indexes.
	 * @param string $index_name Index name to match with.
	 *
	 * @return bool
	 */
	public function is_table_index( string $table_name, string $index_name ) : bool {
		global $wpdb;

		$indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );

		foreach ( $indexes as $index ) {
			if ( ! isset( $index->Key_name ) ) {
				continue;
			}

			if ( $index->Key_name === $index_name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if table has index on specified column.
	 *
	 * @param string $table_name  Table to check for indexes.
	 * @param string $column_name Column name to match with.
	 *
	 * @return bool
	 */
	public function is_table_index_on_column( string $table_name, string $column_name ) : bool {
		global $wpdb;

		$indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );

		foreach ( $indexes as $index ) {
			if ( ! isset( $index->Column_name ) ) {
				continue;
			}

			if ( $index->Column_name === $column_name ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Verify if a table is using a given engine.
	 *
	 * @param string $table_name  Table name to check for engine.
	 * @param string $engine_name Engine name to match against.
	 *
	 * @return bool
	 */
	public function is_table_engine( string $table_name, string $engine_name ) : bool {
		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT LOWER(engine) AS engine FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = %s AND TABLE_SCHEMA = %s LIMIT 1',
			$table_name,
			$wpdb->dbname
		);

		$table_engine = $wpdb->get_var( $sql );

		return $table_engine === $engine_name;
	}

	/**
	 * Select tables for or against a specified engine.
	 *
	 * @param string $engine_name Engine name to check for or against.
	 * @param string $selector    Whether searching for or not for (= or !=).
	 *
	 * @return array
	 */
	public function get_tables_via_engine( string $engine_name, string $selector = '=' ) : array {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT engine, table_name FROM INFORMATION_SCHEMA.TABLES WHERE LOWER(engine) {$selector} %s AND TABLE_SCHEMA = '%s' AND TABLE_NAME LIKE '{$wpdb->prefix}%'",
			$engine_name,
			$wpdb->dbname
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Set engine for a table.
	 *
	 * @param string $table_name  Table name to make the change.
	 * @param string $engine_name Engine name to be set.
	 *
	 * @return bool
	 */
	public function set_table_engine( string $table_name, string $engine_name ) : bool {
		global $wpdb;

		$sql = $wpdb->prepare(
			"ALTER TABLE {$table_name} ENGINE = %s",
			$engine_name
		);
		$wpdb->query( $sql );

		return $this->is_table_engine( $table_name, $engine_name );
	}

	/**
	 * Outputs message in the CLI.
	 *
	 * @param string $message Message to output to the screen.
	 * @param string $type    Type of message to output.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	protected function message( string $message, string $type = 'line' ) : void {
		\WP_CLI::$type( $message );
	}

	/**
	 * Formats array to a specified format for display in the CLI.
	 *
	 * @param array  $data       Data to be formatted.
	 * @param array  $data_keys  Display order.
	 * @param string $format     Display format.
	 *
	 * @return void
	 * @codeCoverageIgnore
	 */
	protected function format_data( array $data, array $data_keys, string $format = 'table' ) {
		\WP_CLI\Utils\format_items( $format, $data, $data_keys );
	}

}
