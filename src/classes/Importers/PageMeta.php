<?php

namespace Niteo\WooCart\Defaults\Importers {

	/**
	 * Class PageMeta
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class PageMeta {

		use FromArray;
		use ToArray;

		/**
		 * @var string
		 */
		public $post_content;
		/**
		 * @var string
		 */
		public $post_title;
		/**
		 * @var string
		 */
		public $post_excerpt;
		/**
		 * @var string
		 */
		public $post_status;
		/**
		 * @var string
		 */
		public $post_type;
		/**
		 * @var string
		 */
		public $post_name;
		/**
		 * @var string
		 */
		public $post_category;
		/**
		 * @var array
		 */
		public $meta_input;
		/**
		 * @var array
		 */
		public $woocart_defaults;


		/**
		 * wp_insert_post allowed params https://developer.wordpress.org/reference/functions/wp_insert_post/
		 *
		 * @var array
		 */
		const wp_insert_post_params = array(
			'post_content',
			'post_title',
			'post_excerpt',
			'post_status',
			'post_type',
			'post_name',
			'post_category',
			'meta_input',
		);

		/**
		 * Return only array with keys valid for wp_insert_post.
		 *
		 * @return array
		 */
		public function getInsertParams(): array {
			$allowed  = $this::wp_insert_post_params;
			$filtered = array_filter(
				self::toArray(),
				function ( $key ) use ( $allowed ) {
					return in_array( $key, $allowed );
				},
				ARRAY_FILTER_USE_KEY
			);
			$filtered = array_filter( $filtered );
			return $filtered;
		}

		/**
		 * woocart-defaults embedded in page spec.
		 *
		 * @param array $extra
		 * @return iterable
		 */
		public function getDefaultsImport( array $extra ): array {
			$out = array();

			if ( is_null( $this->woocart_defaults ) ) {
				return $out;
			}

			foreach ( $this->woocart_defaults as $key => $value ) {
				foreach ( $extra as $k => $v ) {
					if ( $value === '$' . $k ) {
						$value = $v;
					}
				}
				foreach ( $this->getInsertParams() as $k => $v ) {
					if ( $value === '$' . $k ) {
						$value = $v;
					}
				}
				$out[ $key ] = $value;
			}

			return $out;
		}
	}

}
