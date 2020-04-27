<style>
	.welcome-panel {
		padding-bottom: 20px;
	}

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
				<!-- Logo & slider banners -->
				<h3><?php esc_html_e( 'Add Your Own Logo and Slider Banners', 'woocart-defaults' ); ?></h3>
				<p><?php echo sprintf( 'You can use something like the free tool Canva to create these graphics. Add them in the theme <a href="%1$s">Customizer</a>', esc_url( get_admin_url( null, 'customize.php' ) ) ); ?></p>
			</div>
		</div>

		<div class="welcome-panel-column">
			<div class="welcome-panel-inner">
				<!-- Add your products -->
				<h3><?php esc_html_e( 'Add Your Products', 'woocart-defaults' ); ?></h3>
				<p><?php echo sprintf( 'Add your products manually or import a CSV with the WooCommerce import. Go to <a href="%1$s">Import products</a>.', esc_url( get_admin_url( null, 'edit.php?post_type=product&page=product_importer' ) ) ); ?></p>
			</div>
		</div>

		<div class="welcome-panel-column welcome-panel-last">
			<div class="welcome-panel-inner">
				<!-- Connect a payment gateway -->
				<h3><?php esc_html_e( 'Connect a Payment Gateway', 'woocart-defaults' ); ?></h3>
				<p><?php echo sprintf( 'To start receiving payments, you\'ll need to set up a payment gateway. Go to <a href="%1$s">Plugins</a>', admin_url( 'plugins.php' ) ); ?></p>
			</div>
		</div>
	</div>

	<div class="welcome-panel-column-container">
		<div class="welcome-panel-column">
			<div class="welcome-panel-inner">
				<!-- Test checkout -->
				<h3><?php esc_html_e( 'Test The Checkout', 'woocart-defaults' ); ?></h3>
				<p><?php echo sprintf( 'Go through the buying process and review that everything is working as it should. <a href="%1$s">Visit your store</a>', esc_url( get_site_url() ) ); ?></p>
			</div>
		</div>

		<div class="welcome-panel-column welcome-panel-last">
			<div class="welcome-panel-inner">
				<!-- Set domain -->
				<h3><?php esc_html_e( 'Set the Domain in WooCart', 'woocart-defaults' ); ?></h3>
				<p><?php echo sprintf( 'Ready to publish and stop using <em>mywoocart.com</em> subdomain? <a href="%1$s" target="_blank">Go to WooCart</a> and set your domain under Settings.', 'https://woocart.com' ); ?></p>
			</div>
		</div>
		<div class="welcome-panel-column welcome-panel-last">
			<div class="welcome-panel-inner">
				<!-- Set domain -->
				<h3><?php esc_html_e( 'Use New Dashboard', 'woocart-defaults' ); ?></h3>
				<p><?php echo sprintf( 'Switch to new optimized and faster dashboard? <a href="%1$s">Use WooCart WooCommerce Dashboard</a>.', admin_url( 'index.php?woocart-dashboard=0' ) ); ?></p>
			</div>
		</div>
	</div>
</div>
