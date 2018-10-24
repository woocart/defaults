<?php

namespace Niteo\WooCart\Defaults\Importers {

	use function MongoDB\BSON\fromPHP;
	use Symfony\Component\Yaml\Yaml;



	/**
	 * Class WooProducts
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class WooProducts {



		/**
		 * @var string
		 */
		protected $file_path;

		/**
		 * @var string
		 */
		protected $common_path;

		/**
		 * @var int
		 */
		protected $product_count;

		/**
		 * @param $file_path
		 * @param $common_path
		 */
		public function __construct( $file_path, $common_path ) {
			$this->product_count = 0;
			$this->file_path     = $file_path;
			$this->common_path   = $common_path;
		}

		/**
		 * Get number of added products.
		 *
		 * @return int $product_count Number of added products.
		 */
		public function get_product_count(): int {
			return $this->product_count;
		}

		/**
		 * Read file, parse and add products.
		 */
		public function import() {

			$contents = file_get_contents( $this->file_path );
			$products = preg_split( '/^---$/m', $contents );
			foreach ( $products as $product ) {
				$time_start = microtime( true );
				$product    = ProductMeta::fromArray( $this->parse_product( $product ) );
				$product->set_alias( 'common:', $this->common_path );
				$product->set_category_ids();
				$product->upload_images();
				if ( $product->save() ) {
					$this->product_count += 1;
				};

				$time_end = microtime( true );
				$time     = $time_end - $time_start;

				fwrite( STDOUT, "import_product($product->title): $time seconds\n\n" );
			}

		}

		/**
		 * Read file and parse products.
		 *
		 * @param string $product
		 * @return array
		 */
		private function parse_product( $product ): array {
			$re = '/<!--([\s\S]+?)-->/';
			preg_match( $re, $product, $matches, PREG_OFFSET_CAPTURE, 0 );
			$meta = Yaml::parse( $matches[1][0] );

			// Rewrite props so they match the https://github.com/woocart/localizations#exporting-products-from-woocommerce
			$meta['short_description'] = $meta['description'];
			$meta['description']       = trim( preg_replace( $re, '', $product ) );
			return $meta;
		}
	}
}
