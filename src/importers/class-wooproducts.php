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
		 * @var array
		 */
		protected $demo_content = [
			'products'    => [],
			'attachments' => [],
			'categories'  => [],
		];

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
				$product = ProductMeta::fromArray( $this->parse_product( $product ) );
				$product->set_alias( 'common:', $this->common_path );
				$product->set_category_ids();
				$product->upload_images();

				// Add attachments.
				// Calling this before the save() method where we remove one image from the array.
				$attachment_ids                      = $product->getImageIds();
				$this->demo_content['attachments'][] = $attachment_ids;

				// Save product.
				$product_id = $product->save();

				if ( $product_id ) {
					$this->product_count += 1;

					// Add product_id.
					$this->demo_content['products'][] = $product_id;

					// Add categories.
					$category_ids                       = $product->getCategoryIds();
					$this->demo_content['categories'][] = $category_ids;
				};
			}

			// Save demo_content in database.
			$this->mark_products( $this->demo_content );
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

		/**
		 * Save demo content to the database.
		 *
		 * @param array $demo_content
		 */
		public function mark_products( $demo_content ) {
			update_option( 'woocart_demo_content', $demo_content );
		}
	}
}
