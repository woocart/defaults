<?php

namespace Niteo\WooCart\Defaults\Importers {

	use Niteo\WooCart\Defaults\Importer;
	use Symfony\Component\Yaml\Yaml;

	/**
	 * Class WooPage
	 *
	 * @package Niteo\WooCart\Defaults\Importers
	 */
	class WooPage {

		/**
		 * @var string
		 */
		protected $file_path;

		/**
		 * @param string $file_path
		 */
		public function __construct( $file_path ) {
			$this->file_path = $file_path;
		}

		/**
		 * @return PageMeta
		 */
		public function getPageMeta(): PageMeta {
			$contents = file_get_contents( $this->file_path );

			$re = '/<!--([\s\S]+?)-->/';
			preg_match( $re, $contents, $matches, PREG_OFFSET_CAPTURE, 0 );
			$meta                 = Yaml::parse( $matches[1][0] );
			$meta['post_content'] = trim( preg_replace( $re, '', $contents, 1 ) );

			return PageMeta::fromArray( $meta );

		}

		/**
		 * @param PageMeta $page
		 * @return int
		 * @throws \Exception
		 */
		public function insertPage( PageMeta $page ): int {

			$post_id = wp_insert_post( $page->getInsertParams() );

			$import = new Importer();
			$import->parse( (array) $page->getDefaultsImport( array( 'ID' => $post_id ) ) );

			return $post_id;
		}

	}

}
