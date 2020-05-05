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
