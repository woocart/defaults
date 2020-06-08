<?php

/**
 * Notifications on plugin search.
 */

namespace Niteo\WooCart\Defaults\Extend {

	trait Notifications {

		/**
		 * Search for keywords in the plugin search query.
		 *
		 * @param object $args Plugin API arguments
		 * @param string $action The type of information being requested from the Plugin Installation API
		 *
		 * @return void
		 */
		public function search_notification( object $args, string $action ) : void {
			if ( isset( $args->search ) ) {
				// Plugin search query
				$this->notification['term'] = strip_tags( $args->search );

				// Check for keywords
				$keywords = array(
					'backup'      => array(
						'backup',
						'backwpup',
						'duplicate',
					),
					'security'    => array(
						'restore',
						'security',
						'wordfence',
						'firewall',
					),
					'performance' => array(
						'cache',
						'smush',
						'optimize',
						'minify',
						'compress',
					),
				);

				// Match for keywords
				$this->array_match( $keywords );

				if ( $this->notification['matches'] ) {
					// Add the right notification message
					if ( 'backup' === $this->notification['matches'] ) {
						$this->notification['message'] = esc_html__( 'You are searching for backup plugins. WooCart strongly advises against installing this type of plugins because it may significantly impact staging creation. Use the Backups tab in the WooCart dashboard instead. Contact support for more information.', 'woocart-defaults' );
					}

					if ( 'security' === $this->notification['matches'] ) {
						$this->notification['message'] = esc_html__( 'You are searching for security plugins. WooCart strongly advises against installing this type of plugins because it can affect the existing security configuration. Contact support for more information.', 'woocart-defaults' );
					}

					if ( 'performance' === $this->notification['matches'] ) {
						$this->notification['message'] = esc_html__( 'You are searching for performance plugins. WooCart strongly advises against installing this type of plugins because it can affect the existing configuration and cause performance issues. Contact support for more information.', 'woocart-defaults' );
					}

					add_action( 'install_plugins_table_header', array( $this, 'add_text' ) );
				}
			}
		}

		/**
		 * Adds notification text for plugin search queries.
		 *
		 * @codeCoverageIgnore
		 */
		public function add_text() : void {
			echo '<div class="notice" style="background: #e3d9f0;
              border-radius: 8px;
              border: none;
              width: 100%;
              padding: 1em 4px;"><span style="background: orange;
              border-radius: 5px;
              color: white;
              padding: 0.3em;">WARNING</span> <span style="line-height: 22px;
              font-size: 1.07em;">' . $this->notification['message'] . '</span></div>';
		}

		/**
		 * Function to match array of keywords with the search query.
		 *
		 * @param array $keywords Keywords to match against
		 * @return bool
		 */
		private function array_match( array $keywords ) : bool {
			foreach ( $keywords as $key => $value ) {
				foreach ( $value as $keyword ) {
					if ( strpos( $this->notification['term'], $keyword ) !== false ) {
						$this->notification['matches'] = $key;

						return true;
					}
				}
			}

			return false;
		}

	}

}
