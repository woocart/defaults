<?php

namespace Niteo\WooCart\Defaults {

	use Lcobucci\JWT\Configuration;
	use Lcobucci\JWT\Signer\Key;
	use Lcobucci\JWT\Signer\Hmac\Sha256;
	use Lcobucci\JWT\Signer\Key\InMemory;
	use DateTimeImmutable;
	use Lcobucci\JWT\Token;

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

			$now    = new DateTimeImmutable();
			$config = Configuration::forSymmetricSigner(
				// You may use any HMAC variations (256, 384, and 512)
				new Sha256(),
				// replace the value below with a key of your own!
				InMemory::plainText( $this->secret )
			);
			$token = $config->builder()->
			// Configures the issuer (iss claim)
			issuedBy( 'wp-cli' )->
			// Configures the audience (aud claim)
			permittedFor( get_site_url() )->
			// Configures the id (jti claim)
			identifiedBy( $_SERVER['STORE_ID'], false )->
			// Configures the time that the token was issue (iat claim)
			issuedAt( $now )->
			// Configures the time that the token can be used (nbf claim)
			canOnlyBeUsedAfter( $now )->
			// Configures the expiration time of the token (exp claim)
			expiresAt( $now->modify( '+1 hour' ) )->
			// Builds a new token
			getToken( $config->signer(), $config->signingKey() )->toString();

			return sprintf( '%s/wp-login.php?auth=%s', get_site_url(), $token );
		}


	}
}
