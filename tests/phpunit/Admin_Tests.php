<?php
/**
 * Unit Tests for the Admin class
 *
 * Various tests to run against the WordPress Admin code.
 *
 * @since   2.1.2
 *
 * @author  Chris Wiegman <chris.wiegman@10up.com>
 *
 * @package better-yourls
 */

namespace Better_Yourls\Tests;

use Better_Yourls as Base;

/**
 * Class Admin_Tests
 *
 * @package better-yourls
 */
class Admin_Tests extends Base\TestCase {

	/**
	 * Array of files needed for these tests
	 *
	 * @var array
	 */
	protected $testFiles = array(
		'class-better-yourls-admin.php',
	);

	/**
	 * Test module constructor.
	 */
	public function test_constructor() {

		// Setup.
		\WP_Mock::wpFunction( 'get_option', array(
			'args'   => 'better_yourls',
			'times'  => 2,
			'return' => array(),
		) );

		$admin = new \Better_YOURLS_Admin();

		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', array( $admin, 'action_admin_enqueue_scripts' ) );
		\WP_Mock::expectActionAdded( 'admin_init', array( $admin, 'action_admin_init' ) );
		\WP_Mock::expectActionAdded( 'admin_menu', array( $admin, 'action_admin_menu' ) );
		\WP_Mock::expectFilterAdded( 'plugin_action_links', array( $admin, 'filter_plugin_action_links' ), 10, 2 );

		// Act.
		$admin->__construct();

		// Verify.
		$this->assertConditionsMet();

	}

	/**
	 * Test function for enqueing admin scripts
	 */
	public function test_action_admin_enqueue_scripts() {

		// Setup.
		$screen     = new \stdClass;
		$screen->id = 'settings_page_better_yourls';
		define( 'BYOURLS_URL', 'http://wordpress.org' );
		define( 'BYOURLS_VERSION', '2.0' );

		\WP_Mock::wpFunction( 'get_current_screen', array(
			'times'  => 1,
			'return' => $screen,
		) );

		\WP_Mock::wpFunction( 'wp_register_script', array(
			'times' => 1,
		) );

		\WP_Mock::wpFunction( 'wp_register_style', array(
			'times' => 1,
		) );

		\WP_Mock::wpFunction( 'wp_enqueue_script', array(
			'args'  => 'better_yourls_footer',
			'times' => 1,
		) );

		\WP_Mock::wpFunction( 'wp_enqueue_style', array(
			'args'  => 'better_yourls_admin',
			'times' => 1,
		) );

		$admin = new \Better_YOURLS_Admin();

		// Act.
		$admin->action_admin_enqueue_scripts();

		// Verify.
		$this->assertConditionsMet();

	}

	/**
	 * Test admin_init functionality
	 */
	public function test_action_admin_init() {

		// Setup.
		\WP_Mock::wpPassthruFunction( '__' );

		\WP_Mock::wpFunction( 'add_meta_box', array(
			'times' => 3,
		) );

		\WP_Mock::wpFunction( 'add_settings_section', array(
			'times' => 1,
		) );

		\WP_Mock::wpFunction( 'add_settings_field', array(
			'times' => 5,
		) );

		\WP_Mock::wpFunction( 'register_setting', array(
			'times' => 1,
		) );

		$admin = new \Better_YOURLS_Admin();

		// Act.
		$admin->action_admin_init();

		// Verify.
		$this->assertConditionsMet();

	}

	/**
	 * Test action_admin_menu function.
	 */
	public function test_action_admin_menu() {

		// Setup.
		$admin = new \Better_YOURLS_Admin();

		\WP_Mock::wpPassthruFunction( '__' );

		\WP_Mock::wpFunction( 'add_options_page', array(
			'times' => 1,
		) );

		\WP_Mock::expectActionAdded( 'load-' , array( $admin, 'page_actions' ) );

		// Act.
		$admin->action_admin_menu();

		// Verify.
		$this->assertConditionsMet();

	}
}
