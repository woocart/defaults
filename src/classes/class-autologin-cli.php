<?php

namespace Niteo\WooCart\Defaults {

	use Lcobucci\JWT\Parser;
	use Lcobucci\JWT\ValidationData;
	use Lcobucci\JWT\Signer\Hmac\Sha256;
	use Lcobucci\JWT\Builder;
	use Lcobucci\JWT\Signer\Key;

	/**
	 * Class AutoLogin
	 *
	 * @package Niteo\WooCart\Defaults
	 */
	class AutoLoginCLI {
		protected $secret;

		/**
		 * AutoLogin constructor.
		 */
		function __construct() {
			$this->secret = trim( file_get_contents( WOOCART_LOGIN_SHARED_SECRET_PATH ) );
		}


		/**
		 * Generate JWT token.
		 *
		 * @param string $auth jwt token.
		 * @param string $secret shared secret to verify jwt token.
		 * @return bool
		 */
		public function url(): string {
			$time   = time();
			$signer = new Sha256();
			$token  = ( new Builder() )->issuedBy( 'wp-cli' ) // Configures the issuer (iss claim)
									->permittedFor( get_site_url() ) // Configures the audience (aud claim)
									->identifiedBy( $_SERVER['STORE_ID'], false ) // Configures the id (jti claim), replicating as a header item
									->issuedAt( $time ) // Configures the time that the token was issue (iat claim)
									->canOnlyBeUsedAfter( $time ) // Configures the time that the token can be used (nbf claim)
									->expiresAt( $time + 3600 ) // Configures the expiration time of the token (exp claim)
									->getToken( $signer, new Key( $this->secret ) ); // Retrieves the generated token
			return sprintf( '%s/wp-login.php?auth=%s', get_site_url(), $token );
		}


	}
}
