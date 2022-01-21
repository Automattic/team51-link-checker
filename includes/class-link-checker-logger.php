<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://automattic.com
 * @since      1.0.0
 *
 * @package    Link_Checker
 * @subpackage Link_Checker/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Link_Checker
 * @subpackage Link_Checker/includes
 */
class Link_Checker_Logger {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public static function log( $new_entry ) {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once (ABSPATH . '/wp-admin/includes/file.php');
			WP_Filesystem();
		}

		$log_file = plugin_dir_path(__DIR__) . 'link-checker.log';
		$previous_content = $wp_filesystem->get_contents( $log_file );
		$new_entry = $previous_content . "\n" . date( "Y-m-d H:i:s" ) . " - " . $new_entry;
		$wp_filesystem->put_contents( $log_file, $new_entry, 0644);

	}



}
