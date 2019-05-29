<?php

/**
 * Extends the GDPR functionality.
 */

namespace Niteo\WooCart\Defaults\Extend {

	trait WPCF7 {

		/**
		 * Adds a privacy checkbox using contact form acceptance tag.
		 */
		public function cf_privacy_checkbox() {
			// First, we get the contact form ID's from the posts table.
			$forms = $this->get_forms();

			// Next, we add the acceptance tag to the CF template.
			$this->update_template( $forms );
		}

		/**
		 * Get contact forms from the posts table.
		 */
		public function get_forms() {
			return get_posts(
				[
					'post_type'      => 'wpcf7_contact_form',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				]
			);
		}

		/**
		 * Update cf template stored in post meta.
		 */
		public function update_template( $forms = [] ) {
			if ( count( $forms ) > 0 ) {
				foreach ( $forms as $form_id ) {
					$content = get_post_meta( $form_id, '_form', true );

					// Check if the form does not have acceptance tag already.
					$pattern = '/(\[acceptance?.*\])/';
					preg_match( $pattern, $content, $matches );

					if ( empty( $matches ) ) {
						// Add acceptance tag to the form template.
						$privacy_text   = do_shortcode(
							__( 'I\'ve read and accept the [policy-page]<a href="%s">Privacy Policy</a>[/policy-page]', 'woocart-defaults' )
						);
						$acceptance_tag = '[acceptance accept-this-1] ' . $privacy_text . ' [/acceptance]';
						$content        = str_replace( '[submit', $acceptance_tag . "\n\n[submit", $content );

						update_post_meta( $form_id, '_form', $content );
					}
				}
			}
		}
	}
}
