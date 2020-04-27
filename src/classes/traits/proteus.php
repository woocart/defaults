<?php

/**
 * Dashboard functionality for the proteus theme setup.
 */

namespace Niteo\WooCart\Defaults\Extend {

	trait Proteus {

		/**
		 * Checks for the active theme to see if it's the proteus one or not.
		 *
		 * @return boolean
		 */
		public function is_proteus_active() {
			return 'lead' === $_SERVER['STORE_PLAN'];
		}

		/**
		 * Calculate difference in number of days between two dates.
		 *
		 * @return int
		 */
		public function date_diff() {
			$expiry_time = $this->expiry_time();
			$time_diff   = $expiry_time - time();

			return round( $time_diff / ( 60 * 60 * 24 ) );
		}

		/**
		 * Calculate time left before trial expires.
		 *
		 * @return timestamp
		 */
		public function expiry_time() {
			$created_time = $this->created_time();

			return $created_time + ( DAY_IN_SECONDS * 10 );
		}

		/**
		 * Return time when the instance was created.
		 *
		 * @return timestamp
		 */
		public function created_time() {
			$created_time = intval( get_option( 'wc_instance_created', -1 ) );

			if ( $created_time < 1 ) {
				$created_time = time();

				// update option in the database
				update_option( 'wc_instance_created', $created_time );
			}

			return $created_time;
		}

	}

}
