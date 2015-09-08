<?php

/**
 * Unit tests for single site
 *
 * Various unit tests performed against a WordPress single-site instance.
 *
 * @package better-yourls
 *
 * @since   2.0.0
 *
 * @author  Chris Wiegman <chris@chriswiegman.com>
 */
class BetterYourlsTestSingleSite extends WP_UnitTestCase {

	/**
	 * The admin ID created during setup.
	 *
	 * @since 0.1.0
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * The file system path to the plugin.
	 *
	 * @since 0.2.0
	 *
	 * @var string
	 */
	protected $plugin_path;

	/**
	 * Setup test
	 *
	 * Sets up the necessary data for the test. Fails if ElasicPress isn't available.@deprecated
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function setUp() {

		parent::setUp();

		$this->admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$this->plugin_path = dirname( dirname( __FILE__ ) );

		wp_set_current_user( $this->admin_id );

	}

	/**
	 * Cleanup
	 *
	 * Clean up after each test. Reset our mocks
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();

		$this->fired_actions = array();

	}
}
