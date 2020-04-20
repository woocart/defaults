<div class="woocart-env col-xs-12 col-sm-12 col-md-12 col-lg-12">
	<?php if ( $_SERVER['STORE_PLAN'] == 'lead' ) {
		?>
		<div class="col-xs-12 woocart-sandbox">
			<span class="woocart-sandbox-sandbox">SANDBOX</span>
			<p>This is your WoonderShop sandbox store where you can test and play around. It expires in <b><?php echo $this->date_diff(); ?></b> days.</p>
		</div>
		<?php
	} else {
		if ( $_SERVER['STORE_STAGING'] == 'yes' ) {
			?>
			<div class="col-xs-12 woocart-sandbox">
				<span class="woocart-sandbox-staging">STAGING</span>
				<p>This is your staging store. All changes made to this store will not affect the live store. To publish changes go back to WooCart dashboard and click Publish to Live..</p>
			</div>
			<?php
		} else {
			?>
			<div class="col-xs-12 woocart-sandbox">
				<span class="woocart-sandbox-live">LIVE</span>
				<p>This is your live store that's accepting orders from customers. We recommend you use staging for updating the store and testing. Go to WooCart dashboard to create staging.</p>
			</div>
			<?php
		}
	};
	?>
</div>
