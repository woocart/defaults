<?php

namespace Niteo\WooCart\Defaults\Importers {

	use Niteo\WooCart\Defaults\Value;

	/**
	 * Class WooTaxesValue
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class WooShippingZone extends Value {

		/**
		 * Return ShippingLocation array.
		 *
		 * @return iterable
		 */
		public function getLocations(): iterable {
			foreach ( $this->getZone()->locations as $location ) {
				$location          = ShippingLocation::fromArray( (array) $location );
				$location->zone_id = $this->getID();
				yield $location;
			}
		}

		/**
		 * Return zone like object.
		 *
		 * @return ShippingZone
		 */
		public function getZone(): ShippingZone {
			return ShippingZone::fromArray( $this->value );
		}

		/**
		 * Get tax id that was used in DB.
		 *
		 * @return int
		 */
		public function getID(): int {
			return intval( $this->getStrippedKey() );
		}

		/**
		 * Return ShippingMethod array.
		 *
		 * @return iterable
		 */
		public function getMethods(): iterable {
			foreach ( $this->getZone()->methods as $method ) {
				$method          = ShippingMethod::fromArray( (array) $method );
				$method->zone_id = $this->getID();
				yield $method;
			}
		}

		/**
		 * Enforce Zone structure by casting in and to array.
		 *
		 * @param array $values
		 */
		public function setZone( array $values ) {
			$zone = ShippingZone::fromArray( $values );
			$this->setValue( $zone->toArray() );
		}

		/**
		 * Sets value of WooCommerce tax option.
		 *
		 * @param array $values value to store in yaml file.
		 */
		public function setValue( array $values ) {
			$this->value = $values;
		}

	}

}
