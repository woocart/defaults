<div class="page dashboard-page wrap">
	<h1 class="title">
		<?php esc_html_e( 'Dashboard', 'woocart' ); ?>
	</h1>
	<div class="row">
		<?php require __DIR__ . '/parts/env.php'; ?>
	</div>

	<?php
	if ( has_action( 'welcome_panel' ) && current_user_can( 'edit_theme_options' ) ) :
		$classes = 'welcome-panel';

		$option = get_user_meta( get_current_user_id(), 'show_welcome_panel', true );
		// 0 = hide, 1 = toggled to show or single site creator, 2 = multisite site owner.
		$hide = 0 == $option || ( 2 == $option && wp_get_current_user()->user_email != get_option( 'admin_email' ) );
		if ( $hide ) {
			$classes .= ' hidden';
		}
		?>

		<div id="welcome-panel" class="<?php echo esc_attr( $classes ); ?>">
			<?php wp_nonce_field( 'welcome-panel-nonce', 'welcomepanelnonce', false ); ?>
			<a class="welcome-panel-close" href="<?php echo esc_url( admin_url( '?welcome=0' ) ); ?>"
			aria-label="<?php esc_attr_e( 'Dismiss the welcome panel' ); ?>"><?php _e( 'Dismiss' ); ?></a>
			<?php
			/**
			 * Add content to the welcome panel on the admin dashboard.
			 *
			 * To remove the default welcome panel, use remove_action():
			 *
			 *     remove_action( 'welcome_panel', 'wp_welcome_panel' );
			 *
			 * @since 3.5.0
			 */
			do_action( 'welcome_panel' );
			?>
		</div>

	<?php endif; ?>

	<div class="row">
		<?php require __DIR__ . '/parts/small-actions.php'; ?>
	</div>
	<div class="row">
		<?php require __DIR__ . '/parts/big-actions.php'; ?>
	</div>
	<div class="row">
		<?php require __DIR__ . '/parts/widgets.php'; ?>
	</div>
</div>
