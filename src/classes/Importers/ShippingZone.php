<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * Class ShippingZone
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class ShippingZone {


		use FromArray;
		use ToArray;

		/**
		 * @var int
		 */
		public $zone_id;
		/**
		 * @var string
		 */
		public $name;
		/**
		 * @var int
		 */
		public $order;
		/**
		 * @var array
		 */
		public $locations;
		/**
		 * @var array
		 */
		public $methods;
	}

}
