<?php
/**
 * Handles GDPR consent on the plugin frontend.
 *
 * @category   Plugins
 * @package    WordPress
 * @subpackage woocart-defaults
 * @since      1.0.0
 */

namespace Niteo\WooCart\Defaults {


	/**
	 * Class GDPR
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class GDPR {

		/**
		 * GDPR constructor.
		 */
		public function __construct() {
			add_action( 'wp_footer', [ &$this, 'show_consent' ] );
			add_action( 'wp_enqueue_scripts', [ &$this, 'scripts' ] );

			if ( is_admin() ) {
				/**
				 * Set priority one so that the menu item shows just below the default
				 * settings menu options.
				 */
				add_action( 'admin_menu', [ &$this, 'add_menu_item' ], 1 );
			}
		}

		/**
		 * @return null
		 */
		public function show_consent() {
			$consent = get_option( 'woocommerce_allow_tracking' );

			if ( 'no' === $consent ) {
				// Grab page ID's with the help of page slug.
				$privacy = absint( get_option( 'wp_page_for_privacy_policy' ) );
				$cookies = absint( get_option( 'wp_page_for_cookie_policy' ) );

				if ( $privacy && $cookies ) {
					echo '<div class="wc-defaults-gdpr">';
					echo '<p>';
					echo sprintf(
						wp_kses(
							__( 'We use cookies to improve your experience on our site. To find out more, read our <a href="%1$s" class="wcil">%2$s</a> and <a href="%3$s" class="wcil">%4$s</a>.', 'woocart-defaults' ),
							array(
								'a' => array(
									'href'  => array(),
									'class' => array(),
								),
							)
						),
						esc_url( get_permalink( $privacy ) ),
						sanitize_text_field( get_the_title( $privacy ) ),
						esc_url( get_permalink( $cookies ) ),
						sanitize_text_field( get_the_title( $cookies ) )
					);
					echo ' <a href="javascript:;" id="wc-defaults-ok">' . esc_html__( 'OK', 'woocart-defaults' ) . '</a>';
					echo '</p>';
					echo '</div><!-- .wc-defaults-gdpr -->';
				}
			}
		}

		/**
		 * Add to settings menu.
		 */
		public function add_menu_item() {
			add_options_page(
				esc_html__( 'Cookies Policy Settings', 'woocart-defaults' ),
				esc_html__( 'Cookies Policy', 'woocart-defaults' ),
				'manage_options',
				'cookie_policy_settings',
				[
					&$this,
					'options_page',
				]
			);
		}

		/**
		 * Options page for the cookie policy.
		 *
		 * @codeCoverageIgnore
		 */
		public function options_page() {
			if ( ! current_user_can( 'manage_privacy_options' ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to manage Cookies Policy page on this site.', 'woocart-defaults' ) );
			}

			$action = isset( $_POST['action'] ) ? $_POST['action'] : '';

			if ( ! empty( $action ) ) {
				check_admin_referer( $action );

				if ( 'set-cookies-page' === $action ) {
					$cookies_policy_page_id = isset( $_POST['page_for_cookies_policy'] ) ? (int) $_POST['page_for_cookies_policy'] : 0;
					update_option( 'wp_page_for_cookie_policy', $cookies_policy_page_id );

					$cookies_page_updated_message = esc_html__( 'Cookies Policy page updated successfully.', 'woocart-defaults' );

					if ( $cookies_policy_page_id ) {
						/**
						 * Don't always link to the menu customizer:
						 *
						 * - Unpublished pages can't be selected by default.
						 * - `WP_Customize_Nav_Menus::__construct()` checks the user's capabilities.
						 * - Themes might not "officially" support menus.
						 */
						if (
							'publish' === get_post_status( $cookies_policy_page_id )
							&& current_user_can( 'edit_theme_options' )
							&& current_theme_supports( 'menus' )
						) {
							$cookies_page_updated_message = sprintf(
								__( 'Cookies Policy page updated successfully. Remember to <a href="%s">update your menus</a>!', 'woocart-defaults' ),
								esc_url( add_query_arg( 'autofocus[panel]', 'nav_menus', admin_url( 'customize.php' ) ) )
							);
						}
					}

					add_settings_error(
						'page_for_cookies_policy',
						'page_for_cookies_policy',
						$cookies_page_updated_message,
						'updated'
					);
				} elseif ( 'create-cookies-page' === $action ) {
					$cookies_policy_page_id = wp_insert_post(
						array(
							'post_title'   => esc_html__( 'Cookies Policy', 'woocart-defaults' ),
							'post_status'  => 'draft',
							'post_type'    => 'page',
							'post_content' => esc_html__( 'Enter content for your Cookies policy in this section.', 'woocart-defaults' ),
						),
						true
					);

					if ( is_wp_error( $cookies_policy_page_id ) ) {
						add_settings_error(
							'page_for_cookies_policy',
							'page_for_cookies_policy',
							__( 'Unable to create a Cookies Policy page.', 'woocart-defaults' ),
							'error'
						);
					} else {
						update_option( 'wp_page_for_cookie_policy', $cookies_policy_page_id );

						wp_redirect( admin_url( 'post.php?post=' . $cookies_policy_page_id . '&action=edit' ) );
						exit;
					}
				}
			}

			// If a Cookies Policy page ID is available, make sure the page actually exists. If not, display an error.
			$cookies_policy_page_exists = false;
			$cookies_policy_page_id     = (int) get_option( 'wp_page_for_cookie_policy' );

			if ( ! empty( $cookies_policy_page_id ) ) {
				$cookies_policy_page_status = get_post_status( $cookies_policy_page_id );

				if ( empty( $cookies_policy_page_status ) ) {
					add_settings_error(
						'page_for_cookies_policy',
						'page_for_cookies_policy',
						__( 'The currently selected Cookies Policy page does not exist. Please create or select a new page.', 'woocart-defaults' ),
						'error'
					);
				} else {
					if ( 'trash' === $cookies_policy_page_status ) {
						add_settings_error(
							'page_for_cookies_policy',
							'page_for_cookies_policy',
							sprintf(
								__( 'The currently selected Cookies Policy page is in the trash. Please create or select a new Cookies Policy page or <a href="%s">restore the current page</a>.', 'woocart-defaults' ),
								'edit.php?post_status=trash&post_type=page'
							),
							'error'
						);
					} else {
						$cookies_policy_page_exists = true;
					}
				}
			}

			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Cookies Policy Settings' ); ?></h1>
				<h2><?php esc_html_e( 'Manage your Cookies Policy page and notification.' ); ?></h2>
				<p>
					<?php esc_html_e( 'As a website owner, you may need to follow national or international laws. For example, you may need to create and display a Cookies Policy.' ); ?>
					<?php esc_html_e( 'If you already have a Cookies Policy page, please select it below. If not, please create one.' ); ?>
				</p>
				<?php

					// Show errors / messages.
					settings_errors();

				if ( $cookies_policy_page_exists ) {
					$edit_href = add_query_arg(
						array(
							'post'   => $cookies_policy_page_id,
							'action' => 'edit',
						),
						admin_url( 'post.php' )
					);

					$view_href = get_permalink( $cookies_policy_page_id );

					?>
					<p class="tools-cookies-edit"><strong>
					<?php

					if ( 'publish' === get_post_status( $cookies_policy_page_id ) ) {
						printf( __( '<a href="%1$s">Edit</a> or <a href="%2$s">view</a> your Cookies Policy page content.', 'woocart-defaults' ), $edit_href, $view_href );
					} else {
						printf( __( '<a href="%1$s">Edit</a> or <a href="%2$s">preview</a> your Cookies Policy page content.', 'woocart-defaults' ), $edit_href, $view_href );
					}

					?>
					</strong></p>
					<?php

				}

				?>
				<hr>
				<table class="form-table tools-privacy-policy-page">
					<tr>
						<th scope="row">
							<?php

							if ( $cookies_policy_page_exists ) {
								esc_html_e( 'Change your Cookies Policy page' );
							} else {
								esc_html_e( 'Select a Cookies Policy page' );
							}

							?>
						</th>
						<td>
							<?php

								$has_pages = (bool) get_posts(
									array(
										'post_type'      => 'page',
										'posts_per_page' => 1,
										'post_status'    => array(
											'publish',
											'draft',
										),
									)
								);

							if ( $has_pages ) :
								?>
									<form method="post" action="">
										<label for="page_for_cookies_policy">
											<?php esc_html_e( 'Select an existing page:' ); ?>
										</label>
										<input type="hidden" name="action" value="set-cookies-page">
										<?php

										wp_dropdown_pages(
											array(
												'name'     => 'page_for_cookies_policy',
												'show_option_none' => esc_html__( '&mdash; Select &mdash;', 'woocart-defaults' ),
												'option_none_value' => '0',
												'selected' => $cookies_policy_page_id,
												'post_status' => array( 'draft', 'publish' ),
											)
										);

										wp_nonce_field( 'set-cookies-page' );
										submit_button( esc_html__( 'Use This Page', 'woocart-defaults' ), 'primary', 'submit', false, array( 'id' => 'set-page' ) );

										?>
									</form>
							<?php endif; ?>

							<form class="wp-create-privacy-page" method="post" action="">
								<input type="hidden" name="action" value="create-cookies-page" />
								<span>
									<?php

									if ( $has_pages ) {
										esc_html_e( 'Or:' );
									} else {
										esc_html_e( 'There are no pages.' );
									}

									?>
								</span>
								<?php

									wp_nonce_field( 'create-cookies-page' );
									submit_button( esc_html__( 'Create New Page', 'woocart-defaults' ), 'primary', 'submit', false, array( 'id' => 'create-page' ) );

								?>
							</form>
						</td>
					</tr>
				</table>
			</div>
			<?php
		}

		/**
		 * @return null
		 */
		public function scripts() {
			$plugin_dir = plugin_dir_url( dirname( __FILE__ ) );

			wp_enqueue_style( 'woocart-gdpr', "$plugin_dir/assets/css/front-gdpr.css", [], Release::Version );
			wp_enqueue_script( 'woocart-gdpr', "$plugin_dir/assets/js/front-gdpr.js", [], Release::Version, true );
		}

	}
}
