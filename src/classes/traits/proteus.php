<?php

/**
 * Dashboard functionality for the proteus theme setup.
 */

namespace Niteo\WooCart\Defaults\Extend {

	trait Proteus {

		/**
		 * Checks for the active theme to see if it's the proteus one or not.
		 *
		 * @return boolean
		 */
		public function check_active_theme() {
			$theme = wp_get_theme();

			// Looking for "WoonderShop" name for the theme or parent theme
			if ( 'WoonderShop' === $theme->name || 'WoonderShop' === $theme->parent_theme ) {
				return true;
			}

			return false;
		}

		/**
		 * Welcome panel for the proteus theme setup.
		 *
		 * @codeCoverageIgnore
		 */
		public function proteus_welcome_panel() {
			?>
	  <style>
				.welcome-panel-content .welcome-panel-column .welcome-panel-inner,
				.welcome-panel-content h2,
				.welcome-panel-content .about-description {
					padding: 0 10px;
				}
				.welcome-panel-content li {
					display: inline-block;
					margin-right: 13px;
				}
			</style>

			<div class="welcome-panel-content">
				<h2><?php esc_html_e( 'Welcome to your new store!', 'woocart-defaults' ); ?></h2>
				<p class="about-description"><?php esc_html_e( 'You are only a few steps away from selling.', 'woocart-defaults' ); ?></p>

				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<div class="welcome-panel-inner">
							<!-- Install theme demo content -->
							<h3><?php esc_html_e( 'Install the theme demo content', 'woocart-defaults' ); ?></h3>
							<p><?php esc_html_e( 'Woohoo! You successfully created your demo website. This quick setup process is powered by our One Click Demo Import, which is included in every theme.', 'woocart-defaults' ); ?></p>
						</div>
					</div>

					<div class="welcome-panel-column">
						<div class="welcome-panel-inner">
							<!-- Upload logo -->
							<h3><?php esc_html_e( 'Upload your logo', 'woocart-defaults' ); ?></h3>
							<p><?php echo sprintf( 'Go to <span class="sandbox-setting-path">Appearance > Customize > Theme options > <a href="%1$s">Logo</a></span>. Don\'t forget to click on <em>Save & Publish</em>.', admin_url( 'customize.php?autofocus[control]=logo_img' ) ); ?></p>
						</div>
					</div>

					<div class="welcome-panel-column welcome-panel-last">
						<div class="welcome-panel-inner">
							<!-- Pick colors -->
							<h3><?php esc_html_e( 'Pick your primary colors', 'woocart-defaults' ); ?></h3>
							<p><?php echo sprintf( 'Go to <span class="sandbox-setting-path">Appearance > Customize > Theme options > <a href="%1$s">Theme Layout & Colors</a></span>. We recommend using the same colors as your logo. Don\'t forget to click on <em>Save & Publish</em>.', admin_url( 'customize.php?autofocus[control]=primary_color' ) ); ?></p>
						</div>
					</div>
				</div>

				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<div class="welcome-panel-inner">
							<!-- Change menu -->
							<h3><?php esc_html_e( 'Change the main menu', 'woocart-defaults' ); ?></h3>
							<p><?php echo sprintf( 'Go to <span class="sandbox-setting-path">Appearance > <a href="%1$s">Menus</a></span>. Select the <em>Main Menu</em> from the dropdown at the top and click on the <em>Select</em> button. You will be able to add or remove menu items to fit your wishes. Don\'t forget to click on the <em>Save Menu</em> button. <br>(if you remove a page from the menu, you can still find it in <em>Pages > All Pages</em>)', admin_url( 'nav-menus.php' ) ); ?></p>
						</div>
					</div>

		  <div class="welcome-panel-column">
						<div class="welcome-panel-inner">
							<!-- Edit content -->
							<h3><?php esc_html_e( 'Edit website content', 'woocart-defaults' ); ?></h3>
							<p><?php echo sprintf( 'Go to <span class="sandbox-setting-path">Pages > <a href="%1$s">All Pages</a></span> and edit one of the pages (example: Home page). To edit text and images, hover the mouse over widgets and select <em>edit</em>. Don\'t forget to click on the <em>Update</em> button to save your changes. You can also copy & paste widgets by right clicking on them (works from page to page as well). To make it even easier, select the <em>Live Editor</em> in the small menu above the page.', admin_url( 'edit.php?post_type=page' ) ); ?></p>
						</div>
					</div>

					<div class="welcome-panel-column welcome-panel-last">
						<div class="welcome-panel-inner">
							<!-- Export content -->
							<h3><?php esc_html_e( 'Export your website content', 'woocart-defaults' ); ?></h3>
							<p><?php echo sprintf( 'Go to <span class="sandbox-setting-path">Tools > <a href="%1$s">Export Content</a></span>. There you can export this website content with all the changes you made, so that you will be able to import it on your own WordPress site (on your domain). All you have to do is to <a href="%2$s" target="_blank">purchase the %3$s theme</a> and follow the steps on the <a href="%1$s">Export content</a> page.', admin_url( 'tools.php?page=pt-sandbox-ocde' ), $this->purchase_link( 'wp-steps' ), explode( ' ', ( wp_get_theme() )->get( 'Name' ), 2 )[0] ); ?></p>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * For generating the proteus purchase link.
		 */
		public function purchase_link( $utm_medium = '' ) {
			$theme_slug        = get_template();
			$landing_page_slug = preg_replace( '/-[pc]t$/', '', $theme_slug );

			return sprintf( 'https://proteusthemes.onfastspring.com/%1$s-wp?utm_source=woocart&utm_medium=%2$s&utm_campaign=woocart&utm_content=%1$s', $landing_page_slug, $utm_medium );
		}

	}

}
