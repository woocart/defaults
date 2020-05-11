<div class="woocart-env col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<?php if ( $this->is_proteus_active() ) {
		?>
		<div class="col-xs-12 woocart-sandbox">
			<span class="woocart-sandbox-sandbox">SANDBOX</span>
			<span class="woocart-sandbox-desc">This is your WoonderShop sandbox store where you can test and play around. It expires in <b><?php echo $this->date_diff(); ?></b> days.</span>
		</div>
		<?php
	} elseif ( $this->is_staging() ) {
		?>
		<div class="col-xs-12 woocart-sandbox">
			<span class="woocart-sandbox-staging">STAGING</span>
			<span class="woocart-sandbox-desc">This is your staging store. Do not edit products, orders, customers, or comments on staging. To publish changes go back to <a href="https://app.woocart.com/stores/<?php echo $_SERVER['STORE_ID']; ?>">WooCart dashboard</a> and click Publish to Live.</span>
		</div>
		<?php
	} elseif ( $this->is_dev() ) {
		?>
		<div class="col-xs-12 woocart-sandbox">
			<span class="woocart-sandbox-staging">DEVELOPMENT</span>
			<span class="woocart-sandbox-desc">This is your store in development. Once you're ready, you can set the domain in the <a href="https://app.woocart.com/stores/<?php echo $_SERVER['STORE_ID']; ?>">WooCart dashboard</a>.</span>
		</div>
		<?php
	} else {
		?>
		<div class="col-xs-12 woocart-sandbox">
			<span class="woocart-sandbox-live">LIVE</span>
			<span class="woocart-sandbox-desc">This is your live store that's accepting orders from customers. We recommend you use staging for updating the store and testing. Go to <a href="https://app.woocart.com/stores/<?php echo $_SERVER['STORE_ID']; ?>">WooCart dashboard</a> to create staging.</span>
		</div>
		<?php
	}
	?>
</div>
