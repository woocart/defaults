<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * Class ShippingLocation
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class ShippingLocation {


		use FromArray;
		use ToArray;

		/**
		 * @var int
		 */
		public $zone_id;
		/**
		 * @var string
		 */
		public $location_code;
		/**
		 * @var string
		 */
		public $location_type;
	}

}
