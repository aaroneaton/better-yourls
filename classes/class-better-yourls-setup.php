<?php

/**
 * @package better_yourls
 */

/**
 * YOURLS setup.
 *
 * @since 0.0.1
 *
 */
class Better_YOURLS_Setup {

	/**
	 * Better YOURLS constructor.
	 *
	 * @since 0.0.1
	 *
	 * @param string $case The case to execute
	 *
	 * @return Better_YOURLS_Setup
	 */
	function __construct( $case = false ) {

		if ( ! $case ) {
			die( 'error' );
		}

		switch ( $case ) {
			case 'activate': //active plugin
				$this->activate_execute();
				break;

			case 'deactivate': //deactivate plugin
				$this->deactivate_execute();
				break;

			case 'uninstall': //uninstall plugin
				$this->uninstall_execute();
				break;
		}
	}

	/**
	 * Entry point for activation
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public static function on_activate() {

		new Better_YOURLS_Setup( 'activate' );
	}

	/**
	 * Entry point for deactivation
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public static function on_deactivate() {

		if ( defined( 'BETTER_YOURLS_DEVELOPMENT' ) && BETTER_YOURLS_DEVELOPMENT == true ) {
			$case = 'uninstall';
		} else {
			$case = 'deactivate';
		}

		new Better_YOURLS_Setup( $case );
	}

	/**
	 * Entry point for uninstall
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public static function on_uninstall() {

		if ( __FILE__ != WP_UNINSTALL_PLUGIN ) { //verify they actually clicked uninstall
			return;
		}

		new Better_YOURLS_Setup( 'uninstall' );
	}

	/**
	 * Execute Activation functions
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	function activate_execute() {

	}

	/**
	 * Execute Update functions
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	function update_execute() {

	}

	/**
	 * Execute Deactivation functions
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	function deactivate_execute() {
	}

	/**
	 * Execute Uninstall functions
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	function uninstall_execute() {

		delete_option( 'better_yourls' );
		delete_metadata( 'post', null, '_better_yourls_short_link', null, true );

	}

}