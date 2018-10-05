<?php

namespace Niteo\WooCart\Defaults\Importers {

	use Faker\Factory;
	use Niteo\WooCart\Defaults\Importer;
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
		protected $base_path;


		/**
		 * @var int
		 */
		protected $product_count;


		/**
		 *
		 */
		public function __construct() {
			$this->product_count = 0;
		}


		/**
		 * Get number of added products.
		 *
		 * @return int $product_count Number of added products.
		 */
		public function get_product_count() {
			return $this->product_count;
		}


		/**
		 * Read file, parse and add products.
		 *
		 * @param string $file_path
		 */
		public function add_products( $file_path ) {
			$this->file_path = $file_path;
			$this->base_path = dirname( $file_path );

			$contents = file_get_contents( $this->file_path );
			$products = explode('---', $contents);

			foreach( $products as $product ) {
				$data = $this->parse_product( trim( $product ) );
				$images = $this->upload_images( $data );
				if ( $images ) {
					$data['image_id'] = array_shift($images);
					$data['gallery'] = $images;
				}
				$product = $this->create_simple_product( $data );
				if ( $product ) {
					$product->save();
					$this->product_count += 1;
				}
			}
		}


		/**
		 * Read file and parse products.
		 * @param array $product
		 * @return array
		 */
		private function parse_product( $product ) {
			list($attributes, $details) = explode( '-->', $product );
			$attributes = trim( $attributes, '<!--' );
			$details = trim( $details, '---' );
			$attributes = Yaml::parse( $attributes );
			$attributes['details'] = $details;
			$attributes['image_id'] = null;
			$attributes['gallery'] = null;
			return $attributes;
		}


		/**
		 * Upload images.
		 * @param array $product
		 * @return array
		 */
		public function upload_images( $data ) {
			$images = [];
			foreach( $data['images'] as $image ) {
				$path = $this->absolute_image_path( $image );
				$image_id = $this->upload_image( $path );
				if ( $image_id ) {
					$images[] = $image_id;
				}
			}
			return $images;
		}


		/**
		 * Transform image path from relative to absolute.
		 * @param string $image
		 * @param string $replacement
		 * @return array
		 */
		private function absolute_image_path( $image_path, $replacement='common:' ) {
			return str_replace( $replacement, $this->base_path . '/', $image_path );
		}


		/**
		 * Upload given image.
		 *
		 * @param int $parent Parent ID.
		 *
		 * @return int The attachment id of the image (0 on failure).
		 */
		public function upload_image( string $image_path, int $parent = 0 ) {
			if ( ! file_exists( $image_path ) ) {
				return 0;
			}
			// Read the image.
			$image 		   = file_get_contents( $image_path );
			$name          = basename( $image_path );
			$attachment_id = 0;
			// Upload the image.
			$upload = wp_upload_bits( $name, '', $image );
			if ( empty( $upload['error'] ) ) {
				$attachment_id = (int) wp_insert_attachment(
					array(
						'post_title'     => $name,
						'post_mime_type' => $upload['type'],
						'post_status'    => 'publish',
						'post_content'   => '',
					),
					$upload['file']
				);
			}
			if ( $attachment_id ) {
				if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
					include_once ABSPATH . 'wp-admin/includes/image.php';
				}
				$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
				wp_update_attachment_metadata( $attachment_id, $metadata );
				if ( $parent ) {
					update_post_meta( $parent, '_thumbnail_id', $attachment_id );
				}
			}
			return $attachment_id;
		}


		/**
		 * Generate a simple product with provided data and faker and return it.
		 * @param array $data
		 * @return \WC_Product
		 */
		public static function create_simple_product( $data ) {
			$faker             = Factory::create();
			$name              = $data['title'];
			$will_manage_stock = $faker->boolean();
			$price             = $data['price'];
			$is_on_sale        = $faker->boolean( 30 );
			$sale_price        = $is_on_sale ? $faker->randomFloat( 2, 0, $price ) : '';
			$image_id          = $data['image_id'];
			$gallery           = $data['gallery'];
			$product           = new \WC_Product();
			$product->set_props(
				array(
					'name'               => $name,
					'featured'           => $faker->boolean(),
					'catalog_visibility' => 'visible',
					'description'        => $data['description'] . $data['details'],
					'short_description'  => null,
					'sku'                => sanitize_title( $name ) . '-' . $faker->ean8,
					'regular_price'      => $price,
					'sale_price'         => $sale_price,
					'date_on_sale_from'  => '',
					'date_on_sale_to'    => $faker->iso8601( date( 'c', strtotime( '+1 month' ) ) ),
					'total_sales'        => $faker->numberBetween( 0, 10000 ),
					'tax_status'         => 'taxable',
					'tax_class'          => '',
					'manage_stock'       => $will_manage_stock,
					'stock_quantity'     => $will_manage_stock ? $faker->numberBetween( -100, 100 ) : null,
					'stock_status'       => 'instock',
					'backorders'         => $faker->randomElement( array( 'yes', 'no', 'notify' ) ),
					'sold_individually'  => $faker->boolean( 20 ),
					'weight'             => $faker->numberBetween( 1, 200 ),
					'length'             => $faker->numberBetween( 1, 200 ),
					'width'              => $faker->numberBetween( 1, 200 ),
					'height'             => $faker->numberBetween( 1, 200 ),
					'parent_id'          => 0,
					'reviews_allowed'    => $faker->boolean(),
					'purchase_note'      => $faker->boolean() ? $faker->text() : '',
					'menu_order'         => $faker->numberBetween( 0, 10000 ),
					'virtual'            => false,
					'downloadable'       => false,
					'category_ids'       => null,
					'tag_ids'            => null,
					'shipping_class_id'  => 0,
					'image_id'           => $image_id,
					'gallery_image_ids'  => $gallery,
				)
			);
			return $product;
		}
	}
}
