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

		$message = sprintf(
			esc_html__( '%1$s theme has been denylisted on WooCart. Kindly switch to a different theme or %2$scontact support%3$s.', 'kafkai' ),
			"<strong>{$current_theme->get( 'Name' )}</strong>",
			'<a href="https://help.woocart.com/" target="_blank">',
			'</a>'
		);

		echo '<div class="error">';
		echo '<p>' . \wp_kses(
			$message,
			array(
				'a'      => array(
					'href'   => array(),
					'target' => array(),
				),
				'strong' => array(),
			)
		) . '</p>';
		echo '</div>';
	}

}
