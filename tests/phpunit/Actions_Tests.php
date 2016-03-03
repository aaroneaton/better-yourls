<?php
/**
 * Unit Tests for the Actions class
 *
 * Various tests to run against the WordPress Actions code.
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
 * Class Actions_Tests
 *
 * @package better-yourls
 */
class Actions_Tests extends Base\TestCase {

	/**
	 * Array of files needed for these tests
	 *
	 * @var array
	 */
	protected $testFiles = array(
		'class-better-yourls-actions.php',
	);

	/**
	 * Test module constructor.
	 */
	public function test_constructor() {

		// Setup.
		\WP_Mock::wpFunction( 'get_option', array(
			'args'   => 'better_yourls',
			'times'  => 2,
			'return' => array(
				'domain' => 'test.domain',
				'key'    => 'test key',
			),
		) );

		$actions = new \Better_YOURLS_Actions();

		\WP_Mock::expectActionAdded( 'add_meta_boxes', array( $actions, 'action_add_meta_boxes' ) );
		\WP_Mock::expectActionAdded( 'admin_bar_menu', array( $actions, 'action_admin_bar_menu' ), 100 );
		\WP_Mock::expectActionAdded( 'save_post', array( $actions, 'action_save_post' ), 11 );
		\WP_Mock::expectActionAdded( 'wp_enqueue_scripts', array( $actions, 'action_wp_enqueue_scripts' ) );
		\WP_Mock::expectActionAdded( 'transition_post_status', array( $actions, 'action_transition_post_status' ), 9, 3 );

		\WP_Mock::expectFilterAdded( 'get_shortlink', array( $actions, 'filter_get_shortlink' ), 10, 3 );
		\WP_Mock::expectFilterAdded( 'pre_get_shortlink', array( $actions, 'filter_pre_get_shortlink' ), 11, 2 );
		\WP_Mock::expectFilterAdded( 'sharing_permalink', array( $actions, 'filter_sharing_permalink' ), 10, 2 );

		// Act.
		$actions->__construct();

		// Verify.
		$this->assertConditionsMet();

	}
}
