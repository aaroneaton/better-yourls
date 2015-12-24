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
	 * @since 2.1.2
	 *
	 * @var int
	 */
	protected $actions;

	/**
	 * The file system path to the plugin.
	 *
	 * @since 2.1.2
	 *
	 * @var string
	 */
	protected $admin;

	/**
	 * BetterYourlsTestSingleSite constructor.
	 *
	 * @since 2.1.2
	 */
	public function __construct() {

		if ( ! class_exists( 'Better_YOURLS_Actions' ) ) {
			require( dirname( dirname( __FILE__ ) ) . '/classes/class-better-yourls-actions.php' );
		}

		if ( ! class_exists( 'Better_YOURLS_Admin' ) ) {
			require( dirname( dirname( __FILE__ ) ) . '/classes/class-better-yourls-admin.php' );
		}

		$this->actions = new Better_YOURLS_Actions();
		$this->admin = new Better_YOURLS_Admin();

	}

	/**
	 * Setup test
	 *
	 * Sets up the necessary data for the test. Fails if ElasicPress isn't available.@deprecated
	 *
	 * @since 2.1.2`
	 *
	 * @return void
	 */
	function setUp() {

		parent::setUp();

	}

	/**
	 * Cleanup
	 *
	 * Clean up after each test. Reset our mocks
	 *
	 * @since 2.1.2
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();

	}

	/**
	 * Test that suite is working
	 *
	 * A simple test to verify WP Mock is working.
	 *
	 * @since 2.1.2
	 *
	 * @return void
	 */
	public function testSuiteWorking() {

		$this->assertEquals( 1, 1 );

	}
}
