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
					// Get URL's for the page ID's.
					$privacy_page = esc_url( get_permalink( $privacy ) );
					$cookies_page = esc_url( get_permalink( $cookies ) );

					if ( $privacy_page && $cookies_page ) {
						echo '<div class="wc-defaults-gdpr">';
						echo '<p>';
						echo sprintf(
							wp_kses(
								__( 'We use cookies to improve your experience on our site. To find out more, read our <a href="%1$s" class="wcil">Privacy Policy</a> and <a href="%2$s" class="wcil">Cookie Policy</a>.', 'woocart-defaults' ),
								array(
									'a' => array(
										'href'  => array(),
										'class' => array(),
									),
								)
							),
							$privacy_page,
							$cookies_page
						);
						echo ' <a href="javascript:;" id="wc-defaults-ok">' . esc_html__( 'OK', 'woocart-defaults' ) . '</a>';
						echo '</p>';
						echo '</div><!-- .wc-defaults-gdpr -->';
					}
				}
			}
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
