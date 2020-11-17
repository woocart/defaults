<?php

namespace Niteo\WooCart\Defaults\Importers {


	/**
	 * Class ProductMeta
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class ProductMeta {



		use FromArray;

		/**
		 * @var string
		 */
		public $title;

		/**
		 * @var string
		 */
		public $description;

		/**
		 * @var string
		 */
		public $short_description;

		/**
		 * @var string
		 */
		public $price;

		/**
		 * @var string
		 */
		public $category = null;

		/**
		 * @var array
		 */
		public $images = array();

		/**
		 * @var array
		 */
		private $image_ids;

		/**
		 * @var array
		 */
		private $aliases = array();
		/**
		 * @var array
		 */
		private $category_ids = array();

		/**
		 * @return array
		 */
		public function getImageIds(): array {
			return $this->image_ids;
		}

		/**
		 * @return array
		 */
		public function getAliases(): array {
			return $this->aliases;
		}

		/**
		 * @return array
		 */
		public function getCategoryIds(): array {
			return $this->category_ids;
		}

		/**
		 * Upload images.
		 */
		public function upload_images() {
			$image_ids = array();
			if ( count( $this->images ) > 0 ) {

				foreach ( $this->images as $image ) {
					$path     = $this->get_image_path( $image );
					$image_id = $this->upload_image( $path );
					if ( $image_id ) {
						$image_ids[] = $image_id;
					}
				}
			}
			$this->image_ids = $image_ids;
		}

		/**
		 * Replace common: alias in image path with path to .common directory.
		 *
		 * @param string $image_path Image path with alias
		 * @return string
		 */
		public function get_image_path( $image_path ): string {
			foreach ( $this->aliases as $alias => $path ) {
				$image_path = str_replace( $alias, $path, $image_path );
			}
			return $image_path;
		}

		/**
		 * Upload given image.
		 *
		 * @param string $image_path
		 *
		 * @return int The attachment id of the image (0 on failure).
		 */
		private function upload_image( string $image_path ): int {

			if ( ! file_exists( $image_path ) ) {
				return 0;
			}
			// Read the image.
			$image         = file_get_contents( $image_path );
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
					include_once ABSPATH . 'wp-admin/includes/image.php';  // @codeCoverageIgnore
				}
				$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
				wp_update_attachment_metadata( $attachment_id, $metadata );
			}

			return $attachment_id;
		}


		/**
		 * Check for terms and create one if it doesn't exist.
		 */
		public function set_category_ids(): bool {

			$term = get_term_by( 'name', $this->category, 'product_cat' );
			if ( $term === false ) {
				$term               = wp_insert_term( $this->category, 'product_cat' );
				$this->category_ids = array( (int) $term['term_id'] );
				return true;
			}
			$this->category_ids = array( $term->term_id );
			return true;
		}

		/**
		 * Generate a simple product with provided data and faker and return it.
		 *
		 * @return bool
		 */
		public function save() {
			$product = new \WC_Product();
			$props   = array(
				'name'              => $this->title,
				'description'       => $this->description,
				'short_description' => $this->short_description,
				'sale_price'        => $this->price,
				'regular_price'     => $this->price,
				'category_ids'      => $this->category_ids,
				'image_id'          => array_shift( $this->image_ids ),  // use first image as cover photo
				'gallery_image_ids' => $this->image_ids,  // all other images are set as gallery
				'weight'            => mt_rand( 1, 10 ),
				'length'            => mt_rand( 1, 200 ),
				'width'             => mt_rand( 1, 200 ),
				'height'            => mt_rand( 1, 200 ),
				'featured'          => mt_rand( 0, 1 ),
				'shipping_class_id' => 0,
			);
			$product->set_props( $props );
			return $product->save();
		}

		/**
		 * @param string $string
		 * @param string $common_path
		 */
		public function set_alias( string $string, string $common_path ) {
			$this->aliases[ $string ] = $common_path;
		}

	}
}
