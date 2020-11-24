<?php

namespace Niteo\WooCart\Defaults\Importers {

	use Niteo\WooCart\Defaults\Value;

	/**
	 * Class WooTaxesValue
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class WooTaxesValue extends Value {

		/**
		 * Return Locations array.
		 *
		 * @return iterable
		 */
		public function getLocations(): iterable {
			foreach ( $this->getTax()->locations as $location ) {
				$location              = Location::fromArray( (array) $location );
				$location->tax_rate_id = $this->getID();
				yield $location;
			}
		}

		/**
		 * Return tax like object.
		 *
		 * @return Tax
		 */
		public function getTax(): Tax {
			return Tax::fromArray( $this->value );
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
		 * Enforce Tax structure by casting in and to array.
		 *
		 * @param array $values
		 */
		public function setTax( array $values ) {
			$tax = Tax::fromArray( $values );
			$this->setValue( $tax->toArray() );
		}

		/**
		 * Sets value of WooCommerce tax option.
		 *
		 * @param array $values value to store in yaml file.
		 */
		public function setValue( array $values ) {
			$this->value = $values;
		}

		/**
		 * Gets value of WooCommerce tax option.
		 */
		public function getValue(): array {
			return $this->value;
		}
	}

}
