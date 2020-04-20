<?php
$learn_more = 'https://woocart.com/special/woondershop?store_id=' . $_SERVER['STORE_ID'];
$buy_theme  = $this->purchase_link();
$expires    = $this->date_diff();
?>
<div class="proteus">
	<div class="row">
		<div class="col-xs-12">
			<h1 class="center-xs">
				Welcome to Your WoonderShop Sandbox - Watch the Video to Learn
				How to Customize the Theme
			</h1>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-md-12">
			<h6>
				Quick instructions on customizing your store:
			</h6>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 col-md-6">

			<h5>
				Upload logo</br>
				<span>Appearance ‣ Customize ‣ Theme options ‣ <a>Logo</a></span>
			</h5>
			<h5>
				Pick your primary colors</br>
				<span>Appearance ‣ Customize ‣ Theme options ‣ <a>Theme Layout & Colors</a></span>
			</h5>
			<h5>
				Change the main menu</br>
				<span>Appearance ‣ <a>Menus</a></span>
			</h5>
			<h5>
				Edit website content</br>
				<span>Pages ‣ <a>All Pages</a></span>
			</h5>
			<h5>
				Export your WoonderShop sandbox</br>
				<span>Tools ‣ <a>Export Content</a></span>
			</h5>
		</div>
		<div class="col-xs-12 col-md-6">
			<iframe
				width="100%"
				height="315"
				src="https://www.youtube-nocookie.com/embed/tv6G4Z_RTak"
				frameborder="0"
				allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
				allowfullscreen
			></iframe>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<h6 class="center-xs">
				You can purchase the theme for a payment of $79, or subscribe to
				WooCart, the best managed hosting for WooCommerce and get the
				theme for free!
			</h6>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12 center-xs">
			<a href="<?php echo $buy_theme; ?>">
				<div class="action-left col-xs-4">
					<div class="row">
						<div class="col-xs-12 center-xs" style="font-size: x-large;line-height: normal;">Purchase
							Theme
						</div>
						<div class="col-xs-12 center-xs" style="padding-top: 2em;">Price: $79/month</div>
					</div>
				</div>
			</a>
			<a href="<?php echo $learn_more; ?>">
				<div class="action-right col-xs-4">
					<div class="row">
						<div class="col-xs-12 center-xs" style="font-size: x-large;line-height: normal;">Hosting
							Signup
						</div>
						<div class="col-xs-12 center-xs" style="padding-top: 2em;">Price: $9/month</div>
					</div>
				</div>
			</a>
		</div>
	</div>
</div>
