<?php

/**
 * Extends the GDPR functionality.
 */

namespace Niteo\WooCart\Defaults\Extend;

trait ThemesDenylist {

	/**
	 * Adds notice in the admin panel for the denylisted theme.
	 *
	 * @return void
	 */
	public function add_denylist_theme_notice() : void {
		$current_theme = \wp_get_theme();

		// Check for theme.
		if ( ! in_array( $current_theme->get( 'Name' ), $this->themes_denylist ) ) {
			return;
		}

		$message = sprintf(
			esc_html__( '%1$s theme has been shown to have poor performance. We recommend switching to a different theme.', 'woocart-defaults' ),
			"<strong>{$current_theme->get( 'Name' )}</strong>"
		);

		echo '<div class="error">';
		echo '<p>' . \wp_kses(
			$message,
			array(
				'strong' => array(),
			)
		) . '</p>';
		echo '</div>';
	}

}
