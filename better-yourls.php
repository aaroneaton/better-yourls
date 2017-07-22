<?php
/**
 * Plugin Name: Better YOURLS
 * Plugin URI: https://wordpress.org/plugins/better-yourls/
 * Description: Replace WordPress shortlinks with custom YOURLS domain.
 * Version: 2.2.2
 * Text Domain: better-yourls
 * Domain Path: /languages
 * Author: Chris Wiegman
 * Author URI: https://www.chriswiegman.com/
 * License: GPLv2
 * Copyright 2017 Chris Wiegman  (email: info@chriswiegman.com)
 */

define( 'BYOURLS_VERSION', '2.2.2' );
define( 'BYOURLS_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'better_yourls_loader' );

/**
 * Load Better YOURLs functionality.
 */
function better_yourls_loader() {

	// Remember the text domain.
	load_plugin_textdomain( 'better-yourls', false, dirname( dirname( __FILE__ ) ) . '/languages' );

	// Load front end items.
	if ( ! class_exists( 'Better_YOURLS_Actions' ) ) {
		require( dirname( __FILE__ ) . '/includes/class-better-yourls-actions.php' );
	}

	new Better_Yourls_Actions();

	// Load Dashboard items.
	if ( is_admin() ) {

		if ( ! class_exists( 'Better_YOURLS_Admin' ) ) {
			require( dirname( __FILE__ ) . '/includes/class-better-yourls-admin.php' );
		}

		new Better_Yourls_Admin();

	}

}
