<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * Class Tax
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class Tax {

		use FromArray;
		use ToArray;

		/**
		 * @var string
		 */
		public $country;
		/**
		 * @var string
		 */
		public $state;
		/**
		 * @var int
		 */
		public $rate;
		/**
		 * @var string
		 */
		public $name;
		/**
		 * @var int
		 */
		public $priority;
		/**
		 * @var int
		 */
		public $compound;
		/**
		 * @var string
		 */
		public $shipping;
		/**
		 * @var int
		 */
		public $order;
		/**
		 * @var string
		 */
		public $class;
		/**
		 * @var array
		 */
		public $locations;
	}

}
