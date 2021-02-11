<?php

$root_dir = dirname( dirname( __FILE__ ) );
define( 'WP_PLUGIN_DIR', "$root_dir/tests/fixtures/" );

require_once "$root_dir/vendor/autoload.php";
require_once "$root_dir/src/index.php";

WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();

function maybe_serialize( $a ) {
	return $a;
}
