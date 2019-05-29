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
			// Check for user and associated privacy meta.
			// If meta exists, simply return nothing.
			if ( $this->check_user() ) {
				return;
			}

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

			// Check for user and associated privacy meta.
			// If meta exists, simply return nothing.
			if ( $this->check_user() ) {
				return;
			}

			if ( ! isset( $_POST['woocart_privacy_checkbox'] ) || empty( $_POST['woocart_privacy_checkbox'] ) ) {
				wc_add_notice( esc_html__( 'Please read and accept the Privacy Policy to proceed with your order.', 'woocart-defaults' ), 'error' );
			}
		}

		/**
		 * Update order meta and include the privacy checkbox value.
		 */
		public function update_order_meta( $order_id ) {
			if ( isset( $_POST['woocart_privacy_checkbox'] ) && ! empty( isset( $_POST['woocart_privacy_checkbox'] ) ) ) {
				$user_id = get_current_user_id();

				// We have a user logged in. We update the user meta instead of post meta.
				if ( $user_id ) {
					update_user_meta( $user_id, 'woocart_privacy_checkbox', esc_attr( $_POST['woocart_privacy_checkbox'] ) );
				} else {
					update_post_meta( $order_id, 'woocart_privacy_checkbox', esc_attr( $_POST['woocart_privacy_checkbox'] ) );
				}
			}
		}

		/**
		 * Check for logged in user.
		 *
		 * @return boolean
		 */
		public function check_user() {
			$user_id = get_current_user_id();

			// The above function returns 0 if there is no user logged in.
			if ( $user_id ) {
				$user_meta = get_user_meta( $user_id, 'woocart_privacy_checkbox', true );

				if ( ! empty( $user_meta ) ) {
					return true;
				}
			}

			return false;
		}
	}
}
