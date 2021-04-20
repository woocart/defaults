<?php


namespace Niteo\WooCart\Defaults;

/**
 * Class Filters
 *
 * @package Niteo\WooCart\Defaults
 */
class Optimizations {

	/**
	 * Filters constructor.
	 */
	public function __construct() {
		add_action( 'init', array( &$this, 'disable_wp_emojicons' ) );
		// Filter to remove TinyMCE Emojis
		add_filter( 'tiny_mce_plugins', array( &$this, 'disable_emojicons_tinymce' ) );
		// Prevent action scheduler to store successful job reports
		add_filter( 'action_scheduler_retention_period', '__return_zero' );
		add_filter( 'action_scheduler_queue_runner_batch_size', array( &$this, 'ashp_increase_queue_batch_size' ) );
		add_action( 'action_scheduler_queue_runner_time_limit', array( &$this, 'ashp_increase_time_limit' ) );
		add_filter( 'action_scheduler_timeout_period', array( &$this, 'ashp_increase_timeout' ) );
		add_filter( 'action_scheduler_failure_period', array( &$this, 'ashp_increase_timeout' ) );

		// Stop woocommerce background image regeneration
		add_filter( 'woocommerce_background_image_regeneration', '__return_false' );
	}

	/**
	 * Action scheduler reset actions claimed for more than 5 minutes. Because we're increasing the batch size, we
	 * also want to increase the amount of time given to queues before resetting claimed actions.
	 */
	function ashp_increase_timeout( $timeout ) {
		return $timeout * 3;
	}

	/**
	 * Action scheduler claims a batch of actions to process in each request. It keeps the batch
	 * fairly small (by default, 25) in order to prevent errors, like memory exhaustion.
	 *
	 * This method increases it so that more actions are processed in each queue, which speeds up the
	 * overall queue processing time due to latency in requests and the minimum 1 minute between each
	 * queue being processed.
	 *
	 * For more details, see: https://actionscheduler.org/perf/#increasing-batch-size
	 */
	function ashp_increase_queue_batch_size( $batch_size ) {
		return $batch_size * 4;
	}


	/**
	 * Action Scheduler provides a default maximum of 30 seconds in which to process actions. Increase this to 120
	 * seconds for hosts like Pantheon which support such a long time limit, or if you know your PHP and Apache, Nginx
	 * or other web server configs support a longer time limit.
	 *
	 * Note, WP Engine only supports a maximum of 60 seconds - if using WP Engine, this will need to be decreased to 60.
	 */
	function ashp_increase_time_limit() {
		return 120;
	}

	/**
	 * Remove emojis.
	 */
	function disable_wp_emojicons() {
		// Remove actions and filters related to Emojis
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	}

	/**
	 * Filters for tinymce.
	 */
	function disable_emojicons_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}
}
