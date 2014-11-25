<?php
/*
Plugin Name: Better YOURLS
Plugin URI: http://chriswiegman.com
Description: Replace WordPress shortlinks with custom YOURLS domain.
Version: 1.0.4
Text Domain: better-yourls
Domain Path: /languages
Author: Chris Wiegman
Author URI: http://chriswiegman.com
License: GPLv2
Copyright 2014 Chris Wiegman  (email: info@chriswiegman.com)
*/

if ( ! class_exists( 'Better_YOURLS_Actions' ) ) {
	require( dirname( __FILE__ ) . '/inc/class-better-yourls-actions.php' );
	new Better_Yourls_Actions( __FILE__ );
}

if ( is_admin() && ! class_exists( 'Better_YOURLS_Admin' ) ) {
	require( dirname( __FILE__ ) . '/inc/class-better-yourls-admin.php' );
	new Better_Yourls_Admin( __FILE__ );
}