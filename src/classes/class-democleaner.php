<?php
/**
 * Handles cleaning of demo content.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      3.9.2
 */

namespace Niteo\WooCart\Defaults {


	/**
	 * Class DemoCleaner
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class DemoCleaner {

		/**
		 * @var int
		 */
		public $products_count = 0;

		/**
		 * @var int
		 */
		public $attachments_count = 0;

		/**
		 * @var array
		 */
		public $response = [
			'code'    => '',
			'message' => '',
		];

		/**
		 * DemoCleaner constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', [ &$this, 'init' ], PHP_INT_MAX );
		}

		/**
		 * Get fired once the admin panel initializes.
		 */
		public function init() {
			// Delete content.
			$this->delete();

			// Only if demo content exists.
			if ( $this->check() ) {
				add_meta_box( 'wc-demo-cleaner', 'Remove Demo Content', [ &$this, 'widget' ], 'dashboard', 'normal', 'high' );
				add_action( 'admin_enqueue_scripts', [ &$this, 'scripts' ] );
			}

			// Add to notices.
			add_action( 'admin_notices', [ &$this, 'notices' ] );
		}

		/**
		 * Checks for the existence of demo content.
		 *
		 * @return boolean
		 */
		public function check() {
			$demo_content = get_option( 'woocart_demo_content' );

			if ( $demo_content ) {
				if ( isset( $demo_content['products'] ) || isset( $demo_content['attachments'] ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * This function handles the removal of content.
		 */
		public function delete() {
			if ( ! isset( $_GET['woo-action'] ) || ! isset( $_GET['woo-nonce'] ) || ! wp_verify_nonce( $_GET['woo-nonce'], 'woo-democleaner' ) ) {
				return;
			} else {
				$action = $_GET['woo-action'];

				// Return if $action is empty.
				if ( empty( $action ) ) {
					return;
				}

				// Proceed only if products or categories.
				if ( 'products' == $action ) {
					// Get option.
					$demo_content = get_option( 'woocart_demo_content' );

					if ( ! $demo_content ) {
						$this->response['code']    = 'error';
						$this->response['message'] = esc_html__( 'Demo content does not seem to exist for the store.', 'woocart-defaults' );
					} else {
						// Delete products & attachments.
						if ( 'products' == $action ) {
							// For products.
							if ( isset( $demo_content['products'] ) ) {
								if ( is_array( $demo_content['products'] ) ) {
									$products_count = count( $demo_content['products'] );

									foreach ( $demo_content['products'] as $product_id ) {
										$product = wc_get_product( $product_id );

										if ( ! empty( $product ) ) {
											$product->delete( true );
											$result = $product->get_id() > 0 ? false : true;

											// Increase the count on successful delete.
											if ( $result ) {
												++$this->products_count;
											}
										}
									}

									// Unset the products array if all of them were deleted successfully.
									if ( $products_count === $this->products_count ) {
										unset( $demo_content['products'] );
									}
								}
							}

							// For attachments.
							if ( isset( $demo_content['attachments'] ) ) {
								if ( is_array( $demo_content['attachments'] ) ) {
									$attachments_count = 0;

									foreach ( $demo_content['attachments'] as $attachments ) {
										$attachments_count = $attachments_count + count( $attachments );

										// Loop again for the arrays.
										foreach ( $attachments as $attachment_id ) {
											$attachment = wp_delete_attachment( $attachment_id, true );

											// Increase the count on successful delete.
											if ( false !== $attachment ) {
												++$this->attachments_count;
											}
										}
									}

									// Unset the attachments array if all of them were deleted successfully.
									if ( $attachments_count === $this->attachments_count ) {
										unset( $demo_content['attachments'] );
									}
								}
							}

							// Update the demo content option in database.
							update_option( 'woocart_demo_content', $demo_content );

							// Update the response for the screen.
							$this->response['code']    = 'success';
							$this->response['message'] = esc_html__( $this->products_count . ' products and ' . $this->attachments_count . ' related attachments were permanently removed from the store.', 'woocart-defaults' );
						}
					}
				}
			}
		}

		/**
		 * Enqueue a script for the confirmation and ajax calls.
		 *
		 * @param string $hook Hook suffix for the current admin page.
		 */
		public function scripts( $hook ) {
			if ( 'index.php' != $hook ) {
				return;
			}

			$plugin_dir = plugin_dir_url( dirname( __FILE__ ) );

			// Enqueue script and add text string for confirm box.
			wp_enqueue_script( 'woocart-demo-content-js', "{$plugin_dir}assets/js/demo-content.js", [ 'jquery' ], Release::Version, true );

			$localize_data = [
				'confirm_text' => esc_js( 'This will remove all the demo products from the store and cannot be undone. Are you sure you want to continue?', 'woocart-defaults' ),
			];
			wp_localize_script( 'woocart-demo-content-js', 'woocart_defaults', $localize_data );
		}

		/**
		 * Widget for handling the demo content removal.
		 *
		 * @codeCoverageIgnore
		 */
		public function widget() {
			$products_url = esc_url( wp_nonce_url( admin_url( 'index.php?woo-action=products' ), 'woo-democleaner', 'woo-nonce' ) );
			?>
			<div class="panel-content">
				<p class="about-description"><?php esc_html_e( 'Demo content exists for your store. You can go ahead and remove it safely from your store by using the buttons below.', 'woocart-defaults' ); ?></p>

				<p>
					<a href="javscript:;" data-url="<?php echo $products_url; ?>" class="button button-primary woocart-remove-products">Remove Demo Products</a>&nbsp;
				</p>
			</div>
			<?php
		}

		public function notices() {
			if ( ! empty( $this->response['message'] ) ) {
				?>
				<div class="notice notice-<?php echo $this->response['code']; ?> is-dismissible">
					<p><?php esc_html_e( $this->response['message'], 'woocart-defaults' ); ?></p>
				</div>
				<?php
			}
		}

	}
}
