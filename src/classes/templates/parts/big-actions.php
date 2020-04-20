<?php
$flush_url = wp_nonce_url( add_query_arg( array( 'wc_cache' => 'flush' ) ), 'wc_cache_nonce' );
?>
<div class="woocart-item col-xs-12 col-sm-4 col-md-4 col-lg-4">
	<div class="woocart-container woocart-box center-xs">
		<div class="woocart-big-action">
			<a href="/wp-admin/users.php">
				<svg
					width="32"
					height="36"
					viewBox="0 0 32 36"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						d="M15.9296 36C11.3363 36 7.6701 35.5478 5.00301 35.004C1.97931 34.3875 -0.264199 31.8005 0.0989897 28.7361C0.270323 27.2904 0.687658 25.6838 1.52956 24C3.92956 19.2 6.32957 18 8.72957 18C11.1296 18 14.7296 22.8 15.9296 22.8C17.1296 22.8 20.7296 18 23.1296 18C25.5296 18 27.9296 19.2 30.3296 24C31.1774 25.6956 31.5884 27.4536 31.7487 29.0261C32.0447 31.9303 29.9994 34.3388 27.1437 34.9442C24.455 35.5142 20.6902 36 15.9296 36Z"
						fill="#512089"
					/>
					<path
						d="M21.9296 8.4C21.9296 11.7137 18.3296 16.8 15.9296 16.8C13.5296 16.8 9.92963 11.7137 9.92963 8.4C9.92963 3.6 11.1296 0 15.9296 0C20.7296 0 21.9296 3.6 21.9296 8.4Z"
						fill="#512089"
					/>
				</svg>
				&nbsp;Manage Users</a
			>
		</div>
	</div>
</div>
<div class="woocart-item col-xs-12 col-sm-4 col-md-4 col-lg-4">
	<div class="woocart-container woocart-box center-xs">
		<div class="woocart-big-action">
			<a href="<?php echo $flush_url; ?>">
				<svg
					width="36"
					height="36"
					viewBox="0 0 36 36"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						d="M0 9H19V32C19 34.2091 17.2091 36 15 36H4C1.79086 36 0 34.2091 0 32V9Z"
						fill="#512089"
					/>
					<path
						d="M12 7L7 7L7 3.5C7 2.11929 8.11929 1 9.5 1C10.8807 1 12 2.11929 12 3.5L12 7Z"
						fill="#512089"
					/>
					<path
						d="M21 21H36V32C36 34.2091 34.2091 36 32 36H25C22.7909 36 21 34.2091 21 32V21Z"
						fill="#512089"
					/>
					<line
						y1="6"
						x2="19"
						y2="6"
						stroke="#512089"
						stroke-width="2"
					/>
					<path
						d="M30 24L27 24L27 1.5C27 0.671571 27.6716 -2.03558e-07 28.5 -1.31134e-07C29.3284 -5.87108e-08 30 0.671574 30 1.5L30 24Z"
						fill="#512089"
					/>
					<line
						x1="21"
						y1="26"
						x2="36"
						y2="26"
						stroke="white"
						stroke-width="2"
					/>
					<path
						d="M33 30V30C33 31.6569 31.6569 33 30 33H26"
						stroke="white"
						stroke-width="2"
					/>
					<path
						d="M4 15V15C5.10457 15 6 15.8954 6 17L6 30"
						stroke="white"
						stroke-width="2"
					/>
					<path
						d="M15 15V15C13.8954 15 13 15.8954 13 17L13 30"
						stroke="white"
						stroke-width="2"
					/>
				</svg>
				&nbsp;Flush Cache</a
			>
		</div>
	</div>
</div>
<div class="woocart-item col-xs-12 col-sm-4 col-md-4 col-lg-4">
	<div class="woocart-container woocart-box center-xs">
		<div class="woocart-big-action">
			<a href="https://app.woocart.com/stores/<?php echo $_SERVER['STORE_ID']; ?>">
				<svg
					width="36"
					height="37"
					viewBox="0 0 36 37"
					fill="none"
					xmlns="http://www.w3.org/2000/svg"
				>
					<path
						d="M14.8408 25.9192C16.46 25.9192 17.7726 24.6066 17.7726 22.9874C17.7726 21.3682 16.46 20.0555 14.8408 20.0555C13.2216 20.0555 11.9089 21.3682 11.9089 22.9874C11.9089 24.6066 13.2216 25.9192 14.8408 25.9192Z"
						fill="#512089"
					/>
					<path
						d="M26.8409 25.9192C28.4601 25.9192 29.7727 24.6066 29.7727 22.9874C29.7727 21.3682 28.4601 20.0555 26.8409 20.0555C25.2217 20.0555 23.9091 21.3682 23.9091 22.9874C23.9091 24.6066 25.2217 25.9192 26.8409 25.9192Z"
						fill="#512089"
					/>
					<path
						d="M28.8864 1.03278L25.6136 12.4873L21.3864 5.8055H19.8182L15.5909 12.4873L12.3182 1.03278C9.86364 -0.194494 6.79545 -0.126312 5.22727 0.214597L4 0.419142L4.81818 4.57823C4.81818 4.57823 5.97727 4.23732 6.59091 4.16914C7.75 3.9646 8.22727 4.30551 8.5 5.32823C8.77273 6.35096 12.5909 18.4191 12.5909 18.4191H16.8182L20.5 12.5555L24.25 18.4191H28.4773L34 1.03278H28.8864Z"
						fill="#512089"
					/>
					<path
						d="M36 31L3 31"
						stroke="#512089"
						stroke-width="3"
					/>
					<path
						d="M7 35L3 31L7 27"
						stroke="#512089"
						stroke-width="3"
					/>
				</svg>
				&nbsp;to WooCart App</a
			>
		</div>
	</div>
</div>
