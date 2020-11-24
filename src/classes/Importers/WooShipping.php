<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * WooCommerce shipping integration.
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class WooShipping implements Configuration {

		const namespace = 'wooship';

		/**
		 * Return importer specific Value instance.
		 *
		 * @param string $key Name of the kv pair.
		 * @param array  $value Value of the kv pair.
		 * @return WooShippingZone
		 */
		static function toValue( string $key, $value ): WooShippingZone {
			$val = new WooShippingZone( self::namespace );
			$val->setKey( $key );
			$val->setValue( $value );
			return $val;
		}

		/**
		 * Register the shipping zones in WCD.
		 *
		 * @access public
		 * @return mixed
		 */
		public function items(): iterable {
			global $wpdb;

			$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zones" );
			$zones = $wpdb->get_results( $query );
			foreach ( $zones as $zone ) {
				$locations = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT location_code, location_type FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE zone_id = %d",
						$zone->zone_id
					),
					'ARRAY_A'
				);

				$methods = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT method_id, method_order, is_enabled FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE zone_id = %d",
						$zone->zone_id
					),
					'ARRAY_A'
				);

				$values = array(
					'name'      => $zone->zone_name,
					'order'     => $zone->zone_order,
					'locations' => $locations,
					'methods'   => $methods,
				);

				$value = new WooShippingZone( self::namespace );
				$value->setKey( $zone->zone_id );
				$value->setZone( $values );
				yield $value;
			}

		}

		/**
		 * Import (overwrite) shipping zones into the DB
		 *
		 * @param WooShippingZone $data
		 *
		 * @access public
		 */
		public function import( $data ) {
			global $wpdb;
			$inserted = false;
			$id       = $data->getID();
			$zone     = $data->getZone();

			$inserted &= $wpdb->insert(
				"{$wpdb->prefix}woocommerce_shipping_zones",
				array(
					'zone_id'    => $id,
					'zone_name'  => $zone->name,
					'zone_order' => $zone->order,
				),
				array(
					'%d',
					'%s',
					'%d',
				)
			);

			foreach ( $data->getLocations() as $location ) {
				$inserted &= $wpdb->insert(
					"{$wpdb->prefix}woocommerce_shipping_zone_locations",
					array(
						'zone_id'       => $id,
						'location_code' => $location->location_code,
						'location_type' => $location->location_type,
					),
					array(
						'%d',
						'%s',
						'%s',
					)
				);
			}

			foreach ( $data->getMethods() as $method ) {
				$inserted &= $wpdb->insert(
					"{$wpdb->prefix}woocommerce_shipping_zone_methods",
					array(
						'zone_id'      => $id,
						'method_order' => $method->method_order,
						'is_enabled'   => $method->is_enabled,
					),
					array(
						'%d',
						'%s',
						'%d',
						'%d',
					)
				);
			}
			return $inserted;
		}
	}

}
