<?php

namespace Niteo\WooCart\Defaults {


	/**
	 * Class Shortcodes
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class Shortcodes {


		/**
		 * Shortcodes constructor.
		 */
		public function __construct() {
			add_shortcode( 'woo-include', [ &$this, 'page' ] );
			add_shortcode( 'company-name', [ &$this, 'company_name' ] );
			add_shortcode( 'company-address', [ &$this, 'company_address' ] );
			add_shortcode( 'company-city', [ &$this, 'company_city' ] );
			add_shortcode( 'company-postcode', [ &$this, 'company_postcode' ] );
			add_shortcode( 'tax-id', [ &$this, 'tax_id' ] );
			add_shortcode( 'policy-page', [ &$this, 'policy_page' ] );
			add_shortcode( 'store-url', [ &$this, 'store_url' ] );
			add_shortcode( 'store-name', [ &$this, 'store_name' ] );
			add_shortcode( 'woo-permalink', [ &$this, 'woo_permalink' ] );
			add_shortcode( 'cookie-page', [ &$this, 'cookie_page' ] );
			add_shortcode( 'returns-page', [ &$this, 'returns_page' ] );
			add_shortcode( 'terms-page', [ &$this, 'terms_page' ] );
			add_shortcode( 'contact-page', [ &$this, 'contact_page' ] );
			add_shortcode( 'woocart', [ &$this, 'woocart' ] );
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function page( $props, $content = null ) {
			global $wpdb;

			if ( array_key_exists( 'page', $props ) ) {
				$query   = $wpdb->prepare( "SELECT post_content from $wpdb->posts where post_type = 'page' and post_name = %s", $props['page'] );
				$content = $wpdb->get_var( $query );
			}

			if ( array_key_exists( 'post', $props ) ) {
				$query   = $wpdb->prepare( "SELECT post_content from $wpdb->posts where post_type = 'post' and post_name = %s", $props['post'] );
				$content = $wpdb->get_var( $query );
			}
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function store_name( $props, $content = null ) {
			$content = get_option( 'blogname' );
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function company_name( $props, $content = null ) {
			$content = get_option( 'woocommerce_company_name' );
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function company_address( $props, $content = null ) {
			$content = get_option( 'woocommerce_store_address' );
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function company_city( $props, $content = null ) {
			$content = get_option( 'woocommerce_store_city' );
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function company_postcode( $props, $content = null ) {
			$content = get_option( 'woocommerce_store_postcode' );
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function tax_id( $props, $content = null ) {
			$content = get_option( 'woocommerce_tax_id' );
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function store_url( $props, $content = null ) {
			$url     = site_url();
			$name    = get_option( 'blogname' );
			$content = sprintf( '<a href="%s">%s</a>', $url, $name );
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function policy_page( $props, $content = null ) {

			return $this->woo_permalink( [ 'option' => 'wp_page_for_privacy_policy' ], $content );
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function cookie_page( $props, $content = null ) {

			return $this->woo_permalink( [ 'option' => 'wp_page_for_cookies_policy' ], $content );
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function returns_page( $props, $content = null ) {

			return $this->woo_permalink( [ 'option' => 'woocommerce_returns_page_id' ], $content );
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function terms_page( $props, $content = null ) {

			return $this->woo_permalink( [ 'option' => 'woocommerce_terms_page_id' ], $content );
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function contact_page( $props, $content = null ) {

			return $this->woo_permalink( [ 'option' => 'wp_page_for_contact' ], $content );
		}

		/**
		 * Create a permalink based on post/page/product id.
		 *
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function woo_permalink( $props, $content = null ) {

			$a = '<a href="%s">%s</a>';
			if ( ! is_null( $content ) && strlen( $content ) > 1 ) {
				$a = $content;
			}

			if ( array_key_exists( 'option', $props ) ) {
				$url     = get_permalink( get_option( $props['option'] ) );
				$content = sprintf( $a, $url, $url );
				return $content;
			}

			if ( array_key_exists( 'id', $props ) ) {
				$url     = get_permalink( get_option( $props['id'] ) );
				$content = sprintf( $a, $url, $url );
				return $content;
			}
			return $content;
		}

		/**
		 * @param $props
		 * @param null  $content
		 * @return null
		 */
		function woocart( $props, $content = null ) {
			$url     = 'https://woocart.com';
			$name    = esc_html__( 'WooCart', 'woocart-defaults' );
			$content = sprintf( '<a href="%s">%s</a>', $url, $name );
			return $content;
		}
	}
}
