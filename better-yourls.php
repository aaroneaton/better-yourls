<?php
/*
Plugin Name: Better YOURLS
Plugin URI: https://wordpress.org/plugins/better-yourls/
Description: Replace WordPress shortlinks with custom YOURLS domain.
Version: 2.0.0
Text Domain: better-yourls
Domain Path: /lang
Author: Chris Wiegman
Author URI: https://www.chriswiegman.com/
License: GPLv2
Copyright 2015 Chris Wiegman  (email: info@chriswiegman.com)
*/

define( 'BYOURLS_VERSION', '2.0.0' );
define( 'BYOURLS_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'better_yourls_loader' );

function better_yourls_loader() {

	//remember the text domain
	load_plugin_textdomain( 'better-yourls', false, dirname( dirname( __FILE__ ) ) . '/lang' );

	//Load front end items
	if ( ! class_exists( 'Better_YOURLS_Actions' ) ) {
		require( dirname( __FILE__ ) . '/classes/class-better-yourls-actions.php' );
	}

	new Better_Yourls_Actions();

	//Load Dashboard items
	if ( is_admin() ) {

		if ( ! class_exists( 'Better_YOURLS_Admin' ) ) {
			require( dirname( __FILE__ ) . '/classes/class-better-yourls-admin.php' );
		}

		new Better_Yourls_Admin();

	}

}
