<?php

namespace Niteo\WooCart\Defaults\Importers {

	use Niteo\WooCart\Defaults\Value;

	/**
	 * Class WPOptionsValue
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class WPOptionsValue extends Value {

		/**
		 * Sets value of WooCommerce option.
		 *
		 * @param string $option_value value to store in yaml file.
		 */
		public function setValue( string $option_value ) {
			$this->value = $option_value;
		}

	}

}
