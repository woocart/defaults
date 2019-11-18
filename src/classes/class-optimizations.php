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
