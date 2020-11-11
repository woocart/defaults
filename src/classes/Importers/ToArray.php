<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * Trait ToArray
   *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	trait ToArray {

		/**
		 * @return array
		 */
		public function toArray(): array {
			return get_object_vars( $this );
		}
	}
}
