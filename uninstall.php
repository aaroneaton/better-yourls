<?php
/**
 * Better YOURLs uninstaller
 *
 * Used when clicking "Delete" from inside of WordPress's plugins page.
 *
 * @package better-yourls
 *
 * @since   2.0.0
 *
 * @author  Chris Wiegman <chris@wiegman.us>
 */

/**
 * Class Better_YOURLs_Uninstaller
 */
class Better_YOURLs_Uninstaller {

	/**
	 * Initialize uninstaller
	 *
	 * Perform some checks to make sure plugin can/should be uninstalled
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// Exit if accessed directly.
		if ( ! defined( 'ABSPATH' ) ) {
			$this->exit_uninstaller();
		}

		// Not uninstalling.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			$this->exit_uninstaller();
		}

		// Not uninstalling.
		if ( ! WP_UNINSTALL_PLUGIN ) {
			$this->exit_uninstaller();
		}

		// Not uninstalling this plugin.
		if ( dirname( WP_UNINSTALL_PLUGIN ) !== dirname( plugin_basename( __FILE__ ) ) ) {
			$this->exit_uninstaller();
		}

		// Uninstall Better YOURLs.
		self::clean_data();
	}

	/**
	 * Cleanup options
	 *
	 * Deletes Better YOURLs' options and post_meta.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	protected static function clean_data() {

		delete_option( 'better_yourls' );
		delete_metadata( 'post', null, '_better_yourls_short_link', null, true );

	}

	/**
	 * Exit uninstaller
	 *
	 * Gracefully exit the uninstaller if we should not be here
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	protected function exit_uninstaller() {

		status_header( 404 );
		exit;

	}
}

new Better_YOURLs_Uninstaller();
