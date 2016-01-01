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
	 * @since 1.0.0
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
}
