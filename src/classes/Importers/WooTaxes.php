<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * WooCommerce tax rates.
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class WooTaxes implements Configuration {

		/**
		 * Namespace for this importer.
		 */
		const namespace = 'wootax';

		/**
		 * Return importer specific Value instance.
		 *
		 * @param string $key Name of the kv pair.
		 * @param array  $value Value of the kv pair.
		 * @return WooTaxesValue
		 */
		static function toValue( string $key, $value ): WooTaxesValue {
			$val = new WooTaxesValue( self::namespace );
			$val->setKey( $key );
			$val->setValue( $value );
			return $val;
		}

		/**
		 * Register the tax rates in WCD.
		 *
		 * @access public
		 */
		public function items(): iterable {
			global $wpdb;

			$tax_rates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates" );

			foreach ( $tax_rates as $tax ) {

				$query = $wpdb->prepare(
					"
                SELECT location_code, location_type
                FROM {$wpdb->prefix}woocommerce_tax_rate_locations
				WHERE tax_rate_id = %d",
					$tax->tax_rate_id
				);

				$locations = $wpdb->get_results( $query, 'ARRAY_A' );

				$values = array(
					'country'   => $tax->tax_rate_country,
					'state'     => $tax->tax_rate_state,
					'rate'      => $tax->tax_rate,
					'name'      => $tax->tax_rate_name,
					'priority'  => $tax->tax_rate_priority,
					'compound'  => $tax->tax_rate_compound,
					'shipping'  => $tax->tax_rate_shipping,
					'order'     => $tax->tax_rate_order,
					'class'     => $tax->tax_rate_class,
					'locations' => $locations,
				);
				$value  = new WooTaxesValue( self::namespace );
				$value->setKey( $tax->tax_rate_id );
				$value->setTax( $values );
				yield $value;
			}
		}

		/**
		 * Import (overwrite) tax rates into the DB
		 *
		 * @param WooTaxesValue $data Value
		 *
		 * @access public
		 * @return bool
		 */
		public function import( $data ) {
			global $wpdb;
			$inserted = false;
			$id       = $data->getID();
			$tax      = $data->getTax();

			$inserted &= $wpdb->replace(
				"{$wpdb->prefix}woocommerce_tax_rates",
				array(
					'tax_rate_id'       => $id,
					'tax_rate_country'  => $tax->country,
					'tax_rate_state'    => $tax->state,
					'tax_rate'          => $tax->rate,
					'tax_rate_name'     => $tax->name,
					'tax_rate_priority' => $tax->priority,
					'tax_rate_compound' => $tax->compound,
					'tax_rate_shipping' => $tax->shipping,
					'tax_rate_order'    => $tax->order,
					'tax_rate_class'    => $tax->class,
				),
				array(
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);

			foreach ( $tax->locations as $location ) {
				$inserted &= $wpdb->replace(
					"{$wpdb->prefix}woocommerce_tax_rate_locations",
					array(
						'tax_rate_id'   => $id,
						'location_code' => $location['location_code'],
						'location_type' => $location['location_type'],
					),
					array(
						'%d',
						'%s',
						'%s',
					)
				);
			}
			return $inserted;
		}
	}

}
