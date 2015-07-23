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

if ( ! class_exists( 'Better_YOURLS_Actions' ) ) {

	require( dirname( __FILE__ ) . '/classes/class-better-yourls-actions.php' );
	new Better_Yourls_Actions();

}

if ( is_admin() && ! class_exists( 'Better_YOURLS_Admin' ) ) {

	require( dirname( __FILE__ ) . '/classes/class-better-yourls-admin.php' );
	new Better_Yourls_Admin();

}
