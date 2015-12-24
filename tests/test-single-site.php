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
class BetterYourlsTestSingleSite extends PHPUnit_Framework_TestCase {

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

		$this->actions = new Better_YOURLS_Actions();
		$this->admin = new Better_YOURLS_Admin();

	}

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

		\WP_Mock::setUp();

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

		\WP_Mock::tearDown();

	}
}
