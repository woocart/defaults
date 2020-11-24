<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * Class Location
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class Location {

		use FromArray;
		use ToArray;

		/**
		 * @var int
		 */
		public $tax_rate_id;
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
