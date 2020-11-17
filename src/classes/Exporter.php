<?php
/**
 * Handles imports from yaml.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      1.0.0
 */

namespace Niteo\WooCart\Defaults {

	use Niteo\WooCart\Defaults\Importers\WooTaxes;
	use Symfony\Component\Yaml\Yaml;


	/**
	 * Class Exporter
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class Exporter {


		/**
		 * Move the file bundle to DB.
		 *
		 * @param string $type of the bundle.
		 * @return bool|mixed
		 * @access public
		 * @throws \Exception
		 */
		public function export( $type ) {
			$importers = ConfigsRegistry::get();
			foreach ( $importers as $tx ) {
				if ( $type == $tx::namespace ) {
					foreach ( $tx->items() as $item ) {
						$key = sprintf( '%s/%s', WooTaxes::namespace, $item->getKey() );
						echo Yaml::dump( array( $key => $item->getValue() ), 2, 4 );
						echo "\n";
					}
				}
			}
		}


	}
}
