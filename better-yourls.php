<?php
/*
Plugin Name: Better YOURLS
Plugin URI: https://wordpress.org/plugins/better-yourls/
Description: Replace WordPress shortlinks with custom YOURLS domain.
Version: 1.0.5
Text Domain: better-yourls
Domain Path: /lang
Author: Chris Wiegman
Author URI: https://www,chriswiegman.com/
License: GPLv2
Copyright 2015 Chris Wiegman  (email: info@chriswiegman.com)
*/

define( 'BYOURLS_VERSION', '1.0.5' );
define( 'BYOURLS_URL', plugin_dir_url( __FILE__ ) );

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

//Handle activation and deactivation
if ( ! class_exists( 'Better_YOURLS_Setup' ) ) {
	require( dirname( __FILE__ ) . '/classes/class-better-yourls-setup.php' );
}

register_activation_hook( __FILE__, array( 'Better_YOURLS_Setup', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'Better_YOURLS_Setup', 'on_deactivate' ) );
