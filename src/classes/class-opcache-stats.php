<?php

namespace Niteo\WooCart\Defaults\OpCacheStats {

	use \WooCart\Log\Socket;

	/**
	 * Class Reporter
	 *
	 * @package Niteo\WooCart\Defaults\OpCacheStats
	 */
	class Reporter {

		const cron_hook = 'WOOCart\OpCacheStats\Reporter';


		public function __construct() {

			if ( ! wp_next_scheduled( self::cron_hook ) ) {
				wp_schedule_event( time(), 'hourly', self::cron_hook );
			}

			add_action( self::cron_hook, [ $this, 'report_stats' ] );
		}

		/**
		 * Hook function that runs as part of cron.
		 *
		 * @return bool
		 *
		 * @codeCoverageIgnore
		 */
		public function report_stats(): bool {
			$plugins = $this->parse_plugins( opcache_get_status() );
			$plugins = $this->decorate( $plugins );
			return $this->emit( $plugins );
		}

		/**
		 * Parse opcache stats and sum stats by plugin.
		 *
		 * @param array $report
		 * @return array
		 */
		public function parse_plugins( array $report ): array {
			$scripts        = $report['scripts'];
			$wp_plugin_path = wp_normalize_path( WP_PLUGIN_DIR );

			// plugins path + /
			$plugins_base_length = strlen( $wp_plugin_path ) + 1;
			$plugins             = [];
			foreach ( $scripts as $script => $stats ) {

				// script should be a plugin
				if ( ! strstr( $script, $wp_plugin_path ) ) {
					continue;
				}
				// left trim so it becomes `plugin-slug/{index.php, ...}`
				$plugin_base = substr( $script, $plugins_base_length, $plugins_base_length );

				$plugin_slug_separator = strpos( $plugin_base, '/' );
				// ignore any root files (drop-ins)
				if ( $plugin_slug_separator < 1 ) {
					continue;
				}

				// right trim so it becomes `plugin-slug`
				$slug = substr( $plugin_base, 0, $plugin_slug_separator );

				if ( ! array_key_exists( $slug, $plugins ) ) {
					$plugins[ $slug ] = 0;
				}
				$plugins[ $slug ] = $plugins[ $slug ] + $stats['memory_consumption'];
			}
			return $plugins;
		}


		/**
		 * Send data to system logger.
		 *
		 * @param array $plugins
		 * @return bool
		 */
		public function emit( array $plugins ): bool {
			$emit_data = [
				'kind'    => 'opcache_stats',
				'plugins' => $plugins,
			];

			Socket::log( $emit_data );
			return true;
		}


		/**
		 * Wrapper around the core WP get_plugins function, making sure it's actually available.
		 *
		 * @param string $plugin_folder Optional. Relative path to single plugin folder.
		 * @return array Array of installed plugins with plugin information.
		 *
		 * @codeCoverageIgnore
		 */
		public function get_plugins( $plugin_folder = '' ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			return get_plugins( $plugin_folder );
		}

		/**
		 * Decorate plugins report with WordPress metadata.
		 *
		 * @param array $plugins
		 * @return array
		 */
		public function decorate( array $plugins ): array {
			$all_plugins = $this->get_plugins();
			$report      = [];
			foreach ( $all_plugins as $base_path => $info ) {
				$slug = substr( $base_path, 0, strpos( $base_path, '/' ) );

				if ( ! array_key_exists( $slug, $plugins ) ) {
					continue;
				}

				$report[] = [
					'memory'  => $plugins[ $slug ],
					'title'   => $info['Title'],
					'version' => $info['Version'],
					'slug'    => $slug,
				];
			}
			return $report;
		}
	}
}
