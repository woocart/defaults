<?php

namespace Niteo\WooCart\Defaults {

	use Lcobucci\JWT\Configuration;
	use Lcobucci\JWT\Signer\Key;
	use Lcobucci\JWT\Signer\Hmac\Sha256;
	use Lcobucci\JWT\Signer\Key\InMemory;
	use Lcobucci\JWT\Validation\Constraint\PermittedFor;
	use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;

	/**
	 * Class AutoLogin
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class AutoLogin {


		use Extend\Dashboard;
		/**
		 * AutoLogin constructor.
		 */
		public function __construct() {
			if ( is_blog_installed() ) { // only run login functions on installed blog
				add_action( 'login_header', array( &$this, 'test_for_auto_login' ) );
				add_action( 'wp_authenticate', array( &$this, 'set_login_cookie' ) );
			}
		}

		/**
		 * Auto login as administrator without knowing username and password.
		 */
		public function auto_login(): void {
			$users = get_users(
				array(
					'role'    => 'administrator',
					'orderby' => 'ID',
				)
			);
			if ( count( $users ) > 0 ) {
				$user = $users[0];
				wp_set_auth_cookie( $user->ID, true, '' );

				// Set cookie for admin
				$this->set_cookie();

				do_action( 'wp_login', $user->get( 'user_login' ), $user );
			}
		}

		/**
		 * Validate jwt token.
		 *
		 * @param string $auth jwt token.
		 * @param string $secret shared secret to verify jwt token.
		 * @return bool
		 */
		public function validate_jwt_token( $auth, $secret ): bool {
			// $data = new ValidationData();
			// $data->setAudience( get_site_url() );
			// $data->setId( $_SERVER['STORE_ID'] );
			// $signer = new Sha256();

			$jwt = Configuration::forSymmetricSigner(
				// You may use any HMAC variations (256, 384, and 512)
				new Sha256(),
				// replace the value below with a key of your own!
				InMemory::plainText( $secret )
			);
			$jwt->setValidationConstraints( new PermittedFor( get_site_url() ) );  // aud
			$jwt->setValidationConstraints( new IdentifiedBy( $_SERVER['STORE_ID'] ) ); // jti
			try {
				$token = $jwt->parser()->parse( $auth );
			} catch ( \Exception $e ) {
				return false;
			}
			return $jwt->validator()->validate( $token, ...$jwt->validationConstraints() );
		}

		/**
		 * Auto login to WP if auth token is valid.
		 */
		public function test_for_auto_login(): void {
			if ( isset( $_GET['auth'] ) ) {
				if ( is_user_logged_in() ) {
					if ( $this->is_dashboard_active() ) {
						wp_safe_redirect( add_query_arg( array( 'reloggedin' => microtime() ), admin_url( 'index.php?page=woocart-dashboard' ) ) ); // always redirect to public page
						return;
					}
					wp_safe_redirect( add_query_arg( array( 'reloggedin' => microtime() ), admin_url() ) ); // always redirect to public page
					return;
				} elseif ( defined( 'WOOCART_LOGIN_SHARED_SECRET_PATH' ) ) {
					$auth   = $_GET['auth'];
					$secret = trim( file_get_contents( WOOCART_LOGIN_SHARED_SECRET_PATH ) );
					if ( $this->validate_jwt_token( $auth, $secret ) ) { // used by dashboard login
						$this->auto_login();
						if ( $this->is_dashboard_active() ) {
							wp_safe_redirect( add_query_arg( array( 'autologin' => microtime() ), admin_url( 'index.php?page=woocart-dashboard' ) ) ); // always redirect to admin page
							return;
						}
						wp_safe_redirect( add_query_arg( array( 'autologin' => microtime() ), admin_url() ) ); // always redirect to admin page
						return;
					} else {
						return;
					}
				}
			}
		}

		/**
		 * Set cookie with one-year validity.
		 *
		 * @codeCoverageIgnore
		 */
		protected function set_cookie() {
			setcookie(
				'woocart_wp_user',
				$_SERVER['STORE_ID'],
				array(
					'expires'  => time() + 60 * 60 * 24 * 365,
					'path'     => '/',
					'domain'   => $_SERVER['HTTP_HOST'],
					'secure'   => true,
					'samesite' => 'Lax',
				)
			);
		}

		/**
		 * Set cookie for the admin which will be used by the backend to show appropriate message
		 * to the admin if the store goes down.
		 */
		public function set_login_cookie( $username ) {
			global $wpdb;

			if ( ! username_exists( $username ) ) {
				return;
			}

			// Get user info
			$user = get_user_by( 'login', $username );
			$role = $wpdb->prefix . 'capabilities';

			// Get roles for the user and check if "administrator" role exists
			$capabilities = $user->$role;

			foreach ( $capabilities as $role ) {
				if ( 'administrator' == $role ) {
					// Set cookie with one year expiry
					$this->set_cookie();
				}
			}
		}
	}
}
