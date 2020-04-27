<?php $wc_admin_dashboard = require_once ABSPATH . '/wp-content/plugins/woocommerce/includes/admin/class-wc-admin-dashboard.php'; ?>

<div class="woocart-item col-xs-12 col-lg-4">
	<div class="woocart-box-widget">
		<div class="col-xs-12">
			<h2 class="box-title">
				WooCommerce Status
			</h2>
		</div>
		<div class="col-xs-12">
			<div id="woocommerce_dashboard_status">
				<?php $wc_admin_dashboard->status_widget(); ?>
			</div>
		</div>
	</div>
</div>
<div class="woocart-item col-xs-12 col-lg-4">
	<div class="woocart-box-widget">
		<div class="col-xs-12">
			<h2 class="box-title">
				WooCommerce Recent Reviews
			</h2>
		</div>
		<div class="col-xs-12">
			<div id="woocommerce_dashboard_status">
				<?php $wc_admin_dashboard->recent_reviews(); ?>
			</div>
		</div>
	</div>
</div>
<div class="woocart-item col-xs-12 col-lg-4">
	<div class="woocart-box-widget">
		<div class="col-xs-12">
			<h2 class="box-title">
				WordPress News
			</h2>
		</div>

		<div id="dashboard_primary" class="col-xs-12">
			<div class="hide-if-no-js">
				<div class="inside">
					<?php wp_dashboard_primary(); ?>
				</div>
			</div>

			<p class="community-events-footer">
				<?php
				printf(
					'<a href="%1$s" target="_blank">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
					'https://make.wordpress.org/community/meetups-landing-page',
					__( 'Meetups' ),
					/* translators: Accessibility text. */
					__( '(opens in a new tab)' )
				);
				?>

				|

				<?php
				printf(
					'<a href="%1$s" target="_blank">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
					'https://central.wordcamp.org/schedule/',
					__( 'WordCamps' ),
					/* translators: Accessibility text. */
					__( '(opens in a new tab)' )
				);
				?>

				|

				<?php
				printf(
					'<a href="%1$s" target="_blank">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
					/* translators: If a Rosetta site exists (e.g. https://es.wordpress.org/news/), then use that. Otherwise, leave untranslated. */
					esc_url( _x( 'https://wordpress.org/news/', 'Events and News dashboard widget' ) ),
					__( 'News' ),
					/* translators: Accessibility text. */
					__( '(opens in a new tab)' )
				);
				?>
			</p>
		</div>
	</div>
</div>
