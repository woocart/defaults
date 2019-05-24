<?php

/**
 * Extends the GDPR functionality.
 */

namespace Niteo\WooCart\Defaults\Extend {

	trait WooCommerce {

		/**
		 * Adds a custom checkbox to the woocommerce checkout for Privacy Policy.
		 */
		public function privacy_checkbox() {
			$privacy_text = do_shortcode(
				__( 'I\'ve read and accept the [policy-page]<a href="%s">Privacy Policy</a>[/policy-page]', 'woocart-defaults' )
			);

			// WooCommerce checkbox for privacy text.
			woocommerce_form_field(
				'woocart_privacy_checkbox',
				[
					'type'     => 'checkbox',
					'label'    => $privacy_text,
					'required' => true,
				]
			);
		}

		/**
		 * For showing notice if the checkbox is unchecked.
		 */
		public function show_notice() {
			global $woocommerce;

			if ( ! isset( $_POST['woocart_privacy_checkbox'] ) || empty( $_POST['woocart_privacy_checkbox'] ) ) {
				wc_add_notice( esc_html__( 'Please read and accept the Privacy Policy to proceed with your order.', 'woocart-defaults' ), 'error' );
			}
		}

		/**
		 * Update order meta and include the privacy checkbox value.
		 */
		public function update_order_meta( $order_id ) {
			if ( isset( $_POST['woocart_privacy_checkbox'] ) && ! empty( isset( $_POST['woocart_privacy_checkbox'] ) ) ) {
				update_post_meta( $order_id, 'woocart_privacy_checkbox', esc_attr( $_POST['woocart_privacy_checkbox'] ) );
			}
		}
	}
}
