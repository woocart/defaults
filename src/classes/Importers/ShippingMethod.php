<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * Class ShippingLocation
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class ShippingMethod {


		use FromArray;
		use ToArray;

		/**
		 * @var int
		 */
		public $zone_id;
		/**
		 * @var int
		 */
		public $method_order;
		/**
		 * @var bool
		 */
		public $is_enabled;
	}

}
