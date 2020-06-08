<?php

/**
 * Notifications on plugin search.
 */

namespace Niteo\WooCart\Defaults\Extend {

	trait Notifications {

		/**
		 * Search for keywords in the plugin search query.
		 */
		public function search_notification( $action, $args ) {
			if ( isset( $action->search ) ) {
				// Plugin search query
				$this->notification['term'] = strip_tags( $action->search );

				// Check for keywords
				$filter = array_filter(
					array(
						'backup',
						'duplicate',
						'restore',
						'security',
						'wordfence',
						'firewall',
						'cache',
						'smush',
						'optimize',
						'minify',
						'compress',
					),
					array( $this, 'array_match' )
				);

				if ( $this->notification['matches'] ) {
					  // Add the right notification message
					if ( 'backup' === $this->notification['matches'] ) {
						$this->notification['message'] = esc_html__( 'Warning! You are searching for backup plugins. WooCart strongly advises against installing this type of plugins because it may significantly impact staging creation. Use the Backups tab in the WooCart dashboard instead. Contact support for more information.', 'woocart-defaults' );
					}

					if ( 'security' === $this->notification['matches'] ) {
						  $this->notification['message'] = esc_html__( 'Warning! You are searching for security plugins. WooCart strongly advises against installing this type of plugins because it can affect the existing security configuration. Contact support for more information.', 'woocart-defaults' );
					}

					if ( 'performance' === $this->notification['matches'] ) {
							  $this->notification['message'] = esc_html__( 'Warning! You are searching for performance plugins. WooCart strongly advises against installing this type of plugins because it can affect the existing configuration and cause performance issues. Contact support for more information.', 'woocart-defaults' );
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
			echo '<div class="widefat">';
			echo '<p style="width:100%;background:rgba(241,130,141,0.70);padding:0.500rem 4px;">';
			echo $this->notification['message'];
			echo '</p>';
			echo '</div>';
		}

		/**
		 * Function to match array of keywords with the search query.
		 *
		 * @param array $keyword Keyword array items to look for in the query
		 * @return bool
		 */
		private function array_match( string $keyword ) : bool {
			if ( strpos( $this->notification['term'], $keyword ) !== false ) {
				// Backup
				if ( in_array( $keyword, array( 'backup', 'duplicate', 'restore' ) ) ) {
					$this->notification['matches'] = 'backup';
				}

				// Security
				if ( in_array( $keyword, array( 'security', 'wordfence', 'firewall' ) ) ) {
					$this->notification['matches'] = 'security';
				}

				// Performance
				if ( in_array( $keyword, array( 'cache', 'smush', 'optimize', 'minify', 'compress' ) ) ) {
					$this->notification['matches'] = 'performance';
				}

				return true;
			}

			return false;
		}

	}

}
