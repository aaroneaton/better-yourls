<?php
/**
 * YOURLS actions.
 *
 * Non admin-specific actions.
 *
 * @package better-yourls
 *
 * @since   0.0.1
 *
 * @author  Chris Wiegman <chris@wiegman.us>
 */

/**
 * Class Better_YOURLS_Actions
 */
class Better_YOURLS_Actions {

	/**
	 * The saved Better YOURLs settings
	 *
	 * @since 0.0.1
	 *
	 * @var array|bool
	 */
	protected $settings;

	/**
	 * Better YOURLS constructor.
	 *
	 * Register actions and setup local items for the plugin.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		// Set default options.
		$this->settings = get_option( 'better_yourls' );

		// Add filters and actions if we've set API info.
		if ( isset( $this->settings['domain'] ) && '' !== $this->settings['domain'] && isset( $this->settings['key'] ) && '' !== $this->settings['key'] ) {

			add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
			add_action( 'admin_bar_menu', array( $this, 'action_admin_bar_menu' ), 100 );
			add_action( 'save_post', array( $this, 'action_save_post' ), 11 );
			add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
			add_action( 'transition_post_status', array( $this, 'action_transition_post_status' ), 9, 3 );

			add_filter( 'get_shortlink', array( $this, 'filter_get_shortlink' ), 10, 3 );
			add_filter( 'pre_get_shortlink', array( $this, 'filter_pre_get_shortlink' ), 11, 2 );
			add_filter( 'sharing_permalink', array( $this, 'filter_sharing_permalink' ), 10, 2 );

			//
			//  TINYMCE EXTENSION
			//
			if ( is_admin() ) {
				add_action( 'admin_head', array( &$this, 'yourls_link_tinymce_button_init' ) );
				add_action( 'wp_ajax_yourls_get_shortlink', array( &$this, 'yourls_get_shortlink') );
			}
		}
	}

	/**
	 * Determine post validity
	 *
	 * Abstracts determining whether or not a short link should be created.
	 *
	 * @since 2.1
	 *
	 * @param int $post_id The post ID to check.
	 *
	 * @return bool True if valid post or false
	 */
	protected function _check_valid_post( $post_id ) {

		$post_type = get_post_type( $post_id );

		// Only save at normal times.
		if (
			( false === $post_type || ( isset( $this->settings['post_types'] ) && is_array( $this->settings['post_types'] ) ) && in_array( $post_type, $this->settings['post_types'], true ) ) ||
			'nav_menu_item' === $post_type ||
			( defined( 'DOING_AUTOSAVE' ) && true === DOING_AUTOSAVE ) ||
			( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) ||
			( defined( 'DOING_CRON' ) && true === DOING_CRON )
		) {
			return false;
		}

		/**
		 * Abort if there are specified posts types and the current post does not match the criteria
		 */
		$included_post_types = apply_filters( 'better_yourls_post_types', array() );
		$excluded_post_types = ( isset( $this->settings['post_types'] ) && is_array( $this->settings['post_types'] ) ) ? $this->settings['post_types'] : array();

		// Properly handle private post types and exclude if needed.
		if ( ! isset( $this->settings['private_post_types'] ) || false === $this->settings['private_post_types'] ) {

			$args = array(
				'public' => false,
			);

			$private_post_types = get_post_types( $args );

			foreach ( $private_post_types as $private_post_type ) {
				$excluded_post_types[] = $private_post_type;
			}
		}

		if ( ( ! empty( $included_post_types ) && ! in_array( $post_type, $included_post_types, true ) ) || in_array( $post_type, $excluded_post_types, true ) ) {
			return false;
		}

		/**
		 * Filter Better YOURLs post statuses
		 *
		 * The post statuses upon which a URL should be generated.
		 *
		 * @since 2.0.0
		 *
		 * @param array $post_statuses Array of post statuses.
		 */
		$post_statuses = apply_filters( 'better_yourls_post_statuses', array( 'publish', 'future' ) );

		// Make sure we're not generating this for drafts or other posts that don't need them.
		if ( ! in_array( get_post_status( $post_id ), $post_statuses, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generates the shortlink on save and transition.
	 *
	 * A common handler to use to generate a shortlink on save or transition.
	 *
	 * @since 2.1.1
	 *
	 * @param int $post_id The ID of the post that needs a shortlink.
	 *
	 * @return void
	 */
	protected function _generate_post_on_save( $post_id ) {

		// Make sure we are originating from the right place.
		if (
			true === $this->_check_valid_post( $post_id ) &&
			(
				! isset( $_POST['better_yourls_nonce'] ) || // WPCS: input var ok.
				! wp_verify_nonce( $_POST['better_yourls_nonce'], 'better_yourls_save_post' ) // WPCS: input var ok. Sanitization ok.
			)// WPCS: input var ok. Sanitization ok.
		) {
			wp_die( esc_html__( 'Security Error', 'better-yourls' ) );
		}

		$keyword = '';

		// Store custom keyword (if set).
		if ( isset( $_POST['better-yourls-keyword'] ) ) { // WPCS: input var ok.
			$keyword = sanitize_title( trim( $_POST['better-yourls-keyword'] ) ); // WPCS: input var ok. Sanitization ok.
		}

		/**
		 * Filter the keyword prior to submitting to YOURLS API.
		 * Post ID supplied to provide context to the filtered keyword
		 *
		 * @since 2.0.2
		 *
		 * @param string $keyword
		 * @param string $post_id
		 */
		$keyword = apply_filters( 'better_yourls_keyword', $keyword, $post_id );

		// Get the short URL. Note this will use the meta if it was already saved.
		$link = $this->create_yourls_url( $post_id, $keyword, '', 'save_post' );

		// Keyword would be a duplicate so use a standard one.
		if ( '' !== $keyword && ! $link ) {
			$link = $this->create_yourls_url( $post_id, '', '', 'save_post' );
		}

		// Save the short URL only if it was generated correctly.
		if ( $link ) {
			update_post_meta( $post_id, '_better_yourls_short_link', $link );
		}
	}

	/**
	 * Adds meta box
	 *
	 * Adds the metabox for a custom link.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function action_add_meta_boxes() {

		global $post;

		$post_type = get_post_type( $post->ID );

		if ( false === $post_type || ( isset( $this->settings['post_types'] ) && is_array( $this->settings['post_types'] ) ) && in_array( $post_type, $this->settings['post_types'], true ) ) {
			return;
		}

		$post_types = apply_filters( 'better_yourls_post_types', array() );

		if ( in_array( get_post_type( $post->ID ), $post_types, true ) || empty( $post_types ) ) {

			add_meta_box(
				'yourls_keyword',
				esc_html__( 'YOURLs Keyword', 'better-yourls' ),
				array( $this, 'yourls_keyword_metabox' ),
				$post->post_type,
				'side',
				'core'
			);

		}
	}

	/**
	 * Add links to the admin bar
	 *
	 * Adds a "Better YOURLs" menu to the admin bar to access the shortlink and stats easily from the front end.
	 *
	 * @since 0.0.1
	 *
	 * @global $wp_admin_bar WP_Admin_Bar An instance of the WP Admin Bar.
	 * @global $post         WP_Post The global post object
	 *
	 * @return void
	 */
	 public function action_admin_bar_menu() {

		 global $wp_admin_bar, $post;

		 if ( ! isset( $post->ID ) ) {
			 return;
		 }

		 $post_type = get_post_type( $post->ID );

		 if ( false === $post_type || ( isset( $this->settings['post_types'] ) && is_array( $this->settings['post_types'] ) ) && in_array( $post_type, $this->settings['post_types'], true ) ) {
			 return;
		 }

		 $yourls_url = wp_get_shortlink( $post->ID, 'query' );

		 if ( is_singular() && ! is_preview() && current_user_can( 'edit_post', $post->ID ) ) {

			 $stats_url = $yourls_url . '+';

			 $wp_admin_bar->remove_menu( 'get-shortlink' );

			 $wp_admin_bar->add_menu(
				 array(
					 'href'  => '',
					 'id'    => 'better_yourls',
					 'title' => esc_html__( 'YOURLS', 'better-yourls' ),
				 )
			 );

			 $wp_admin_bar->add_menu(
				 array(
					 'href'   => '',
					 'parent' => 'better_yourls',
					 'id'     => 'better_yourls-link',
					 'title'  => esc_html__( 'YOURLS Link', 'better-yourls' ),
				 )
			 );

			 $wp_admin_bar->add_menu(
				 array(
					 'parent' => 'better_yourls',
					 'id'     => 'better_yourls-stats',
					 'title'  => esc_html__( 'Link Stats', 'better-yourls' ),
					 'href'   => $stats_url,
					 'meta'   => array(
						 'target' => '_blank',
					 ),
				 )
			 );

		 }
	 }

	/**
	 * Create YOURLs link when we save a post
	 *
	 * @since 1.0.3
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function action_save_post( $post_id ) {

		if ( false === $this->_check_valid_post( $post_id ) ) {
			return;
		}

		$this->_generate_post_on_save( $post_id );

	}

	/**
	 * Create YOURLs link when we save a post
	 *
	 * @since 1.0.3
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 *
	 * @return void
	 */
	public function action_transition_post_status( $new_status, $old_status, $post ) {

		if ( false === $this->_check_valid_post( $post->ID ) || 'publish' !== $new_status ) {
			return;
		}

		$this->_generate_post_on_save( $post->ID );

	}

	/**
	 * Enqueue script with admin bar.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function action_wp_enqueue_scripts() {

		global $post;

		if ( is_admin_bar_showing() && isset( $post->ID ) && current_user_can( 'edit_post', $post->ID ) ) {

			$min = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';

			wp_register_script( 'better_yourls', BYOURLS_URL . 'assets/js/better-yourls' . $min . '.js', array( 'jquery' ), BYOURLS_VERSION );

			wp_enqueue_script( 'better_yourls' );

			wp_localize_script(
				'better_yourls',
				'better_yourls',
				array(
					'text'       => esc_html__( 'Your YOURLS short link is: ', 'better-yourls' ),
					'yourls_url' => wp_get_shortlink( $post->ID ),
				)
			);

		}
	}

	/**
	 * Creates YOURLS link
	 *
	 * Creates YOURLS link if not in post meta and saves new link to post meta where appropriate.
	 *
	 * @since 0.0.1
	 *
	 * @param int    $post_id The current post id.
	 * @param string $keyword optional keyword for shortlink.
	 * @param string $title   optional title for shortlink.
	 * @param string $hook    the hook that called this function.
	 *
	 * @return bool|string the yourls shortlink or false.
	 */
	public function create_yourls_url( $post_id, $keyword = '', $title = '', $hook = '' ) {

		if ( is_preview() && ! is_admin() ) {
			return false;
		}

		if ( 0 !== $post_id ) {

			$shortlink_integrity = ( isset( $this->settings['shortlink_integrity'] ) && true === $this->settings['shortlink_integrity'] ) ? true : false;

			$yourls_shortlink = get_post_meta( $post_id, '_better_yourls_short_link', true );

			if ( $yourls_shortlink ) {
				// There is a WP shortlink for this content

				$keyword = $this->derive_keyword($yourls_shortlink);
				if ( $keyword === false ) {
					// Keyword is malformed

					// Act as if this WP content has no existing shortlink
					delete_post_meta($post_id, '_better_yourls_short_link');
					$yourls_shortlink = false;
					$keyword = '';

				} else {
					// There is a keyword

					if ( $shortlink_integrity === true ) {
						// We need to verify this WP shortlink

						$result_keyword_exist = $this->exist_keyword( $keyword );
						if ( $result_keyword_exist === true ) {
							// The keyword exists on YOURLS server

							$shortlink_integrity_url  = ( isset( $this->settings['shortlink_integrity_url'] ) && true === $this->settings['shortlink_integrity_url'] ) ? true : false;
							if ( $shortlink_integrity_url === true ) {
								// Check that YOURLS long URL matches the expected long URL for this $keyword

                $result = $this->expand( $keyword );
                if ( $result !== false ) {
                  // There is an existing YOURLS long URL for this $keyword

                  if ( get_permalink( $post_id ) !== $result['longurl'] ) {
										// YOURLS URL does not match WP shortlink

										// Act as if this WP content has no existing shortlink
										delete_post_meta($post_id, '_better_yourls_short_link');
										$yourls_shortlink = false;
										$keyword = '';

									} else {
										// The $keyword exists and the long URL matches
										// We will NOT create a new shortlink
										return $yourls_shortlink;
									}

								} else {
									// There is no existing long URL on YOURLS server for this $keyword
									// It's ok to let this fall through.

									// We'll use $keyword to create YOURLS shortlink

									// (Actually this condition should never happen because we already checked the $keyword
									//  exists on YOURLS server, but maybe problem on that side can cause this condition)
								}

							} else {
								// The $keyword exists on the YOURLS server but we're not verifying long URL
								// We will NOT create a new shortlink
								return $yourls_shortlink;
							}

						} else {
							// There is WP shortlink but it doesn't exist on YOURLS server
							// It's ok to let this fall through

							// We'll use $keyword to create YOURLS shortlink
						}

					} else {
						// There is a WP shortlink but we're not verifying it
						// We will NOT create a new shortlink
						return $yourls_shortlink;
					}
				}
			}

			// If we get to here, there is no matching WP shortlink -> $keyword -> YOURLS long URL

			if ( $shortlink_integrity === true ) {
				// We need to continue verification of this WP content long URL

				$shortlink_url_recover = ( isset( $this->settings['shortlink_url_recover'] ) && true === $this->settings['shortlink_url_recover'] ) ? true : false;
				if ( $shortlink_url_recover === true ) {
					// Look for existing long URL on YOURLS server that matches WP content long URL

					$result = $this->get_keyword_url( get_permalink( $post_id ) );
					if ( $result !== false ) {
						// Found YOURLS long URL matching WP content URL
						// We will NOT create a new shortlink
						update_post_meta( $post_id, '_better_yourls_short_link', $result );
						return $result;

					}
				}
			}

			// If we get to here, there is no matching $keyword or long URL on YOURLS server
			// Need to create new YOURLS shortlink

			// Create new shorturl
			$title = ( '' === trim( $title ) ) ? get_the_title( $post_id ) : $title;
			$url = $this->create_yourls( get_permalink( $post_id ), $keyword, $title );

			if ( $url !== false ) {

				/**
				 * Filter the created shortlink.
				 *
				 * @since 2.0
				 *
				 * @param string $url     The shortlink or return false to short-circuit.
				 * @param int    $post_id The post id of the post for which the shortlink was created.
				 * @param string $hook    The hook which called the creation function.
				 */
				$url = apply_filters( 'better_urls_shortlink', $url, $post_id, $hook );

				if ( false === $url ) {
					return false;
				}

				$url = esc_url_raw( $url );

				update_post_meta( $post_id, '_better_yourls_short_link', $url );

				return $url;
			}
		}
		return false;
	}

	/**
	 * Filter wp shortlink before display.
	 *
	 * Filters the default WordPress shortlink
	 *
	 * @since 0.0.1
	 *
	 * @param bool $short_link the shortlink to filter (defaults to false).
	 * @param int  $id         the post id.
	 *
	 * @return bool the shortlink or false
	 */
	public function filter_get_shortlink( $short_link, $id ) {

		if ( false === $this->_check_valid_post( $id ) ) {
			return $short_link;
		}

		$link = $this->create_yourls_url( $id, '', '', 'get_shortlink' );

		if ( false !== $link ) {
			return $link;
		}

		return $short_link;
	}

	/**
	 * Filter wp shortlink before display
	 *
	 * Filters the default WordPress shortlink.
	 *
	 * @since 0.0.1
	 *
	 * @param bool $short_link the shortlink to filter (defaults to false).
	 * @param int  $id         the post id.
	 *
	 * @return bool the shortlink or false
	 */
	public function filter_pre_get_shortlink( $short_link, $id ) {

		if ( false === $this->_check_valid_post( $id ) ) {

			return $short_link;
		}

		$current_post = get_post( $id );

		// If we've already created a shortlink return it or just return the default.
		$link = get_post_meta( $current_post->ID, '_better_yourls_short_link', true );

		if ( '' === $link ) {

			return $short_link;
		}

		$link = $this->create_yourls_url( $id, '', '', '' );

		return $link;

	}

	/**
	 * Adds the shortlink to Jetpack Sharing.
	 *
	 * If you're using JetPack for sharing links when publishing a post this will make sure the shared link uses your shortlink.
	 *
	 * @since 0.0.1
	 *
	 * @param string $link    the original link.
	 * @param int    $post_id the post id.
	 *
	 * @return string the link to share.
	 */
	public function filter_sharing_permalink( $link, $post_id ) {

		if ( false === $this->_check_valid_post( $post_id ) ) {
			return $link;
		}

		$yourls_shortlink = $this->create_yourls_url( $post_id, '', '', 'sharing_permalink' );

		if ( false !== $yourls_shortlink && '' !== $yourls_shortlink ) {
			return $yourls_shortlink;
		}

		return $link;

	}

	/**
	 * Validates a URL
	 *
	 * A slightly more complex version of a URL validator. (might not need this anymore)
	 *
	 * @since 1.2
	 *
	 * @param string $url the url to validate.
	 *
	 * @return bool true if valid url else false
	 */
	private function validate_url( $url ) {

		$pattern = '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';

		return (bool) preg_match( $pattern, $url );

	}

	/**
	 * Show custom metabox
	 *
	 * Shows a metabox allowing for a custom slug on the post edit screen.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function yourls_keyword_metabox() {

		global $post;

		$link     = get_post_meta( $post->ID, '_better_yourls_short_link', true );
		$readonly = '';

		if ( $link ) {
			$readonly = 'readonly="readonly" ';
		}

		wp_nonce_field( 'better_yourls_save_post', 'better_yourls_nonce' );
		echo '<input type="text" id="better-yourls-keyword" name="better-yourls-keyword" style="width: 100%;" value="' . esc_url( $link ) . '" ' . esc_html( $readonly ) . '/>';
		echo '<p><em>' . esc_html__( 'If a short-url doesn\'t yet exist for this post you can enter a keyword above. If it already exists it will be displayed.', 'better-yourls' ) . '</em></p>';

	}

  //
  //  NEW BETTER YOURLS FUNCTIONS
  //

  	/**
  	 * Parse shortcode.
  	 *
  	 * Parse shortcode keyword from short url
  	 *
  	 * @since 0.0.2
  	 *
  	 * @param string $short_link the shortlink to filter (defaults to false).
  	 *
  	 * @return string the shortcode keyword or false
  	 */
  	private function derive_keyword( $short_link ) {

  		if ( !$short_link ) {
  			return false;
  		}

  		// Ignore protocol & www. prefix
  		$root = str_replace( array( 'https://', 'http://', 'https://www.', 'http://www.' ), '', $this->settings['domain'] );

  		if ( strpos( $short_link, $root ) ) {
  			$llink = str_replace( array( 'https://', 'http://', 'https://www.', 'http://www.' ), '', $short_link );

  			// Case insensitive comparison of the YOURLS root to match both http://Sho.rt/blah and http://sho.rt/blah
  			$request = preg_replace( "!$root/!i", '', $llink, 1 );

  			return $request;
  		}
  		return false;
  	}

	//
	//  NEW YOURLS CREATE FUNCTION
	//

	/**
	* create_yourls
	*
	* Create a YOURLS shortened url from given long url, possibly re-using existing shortlink
	*
	* @since 0.0.2
	*
	* @param array $long_url long url to be shortened, possibly a $keyword, and $title
	*
	* @return string shortlink url if successful, false if error
	*/
	private function create_yourls( $long_url, $keyword = '', $title = '' ) {

		// Setup call parameters.
		$https      = ( isset( $this->settings['https'] ) && true === $this->settings['https'] ) ? 's' : '';
		$yourls_url = esc_url_raw( 'http' . $https . '://' . $this->settings['domain'] . '/yourls-api.php' );
		$timestamp  = current_time( 'timestamp' );

		$args = array(
			'body' => array(
				'title'     => $title,
				'timestamp' => $timestamp,
				'signature' => md5( $timestamp . $this->settings['key'] ),
				'action'    => 'shorturl',
				'url'       => $long_url,
				'format'    => 'JSON',
			),
		);

		// Keyword and title aren't currently used but may be in the future.
		if ( $keyword !== '' ) {
			$args['body']['keyword'] = sanitize_title( $keyword );
		}

		// Allow the option to use a self-signed.
		if ( isset( $this->settings['https_ignore'] ) && true === $this->settings['https_ignore'] ) {
			$args['sslverify'] = false;
		}

		$response = wp_remote_post( $yourls_url, $args );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$short_link = isset( $response['body'] ) ? $response['body'] : false;
		if ( $short_link === false ) {
			return false;
		}

		$url = esc_url( trim( $short_link ) );

		if ( $this->validate_url( $url ) === true ) {
			return $url;
		}

		return false;
	}

	//
	//  TINYMCE FUNCTIONS
	//

	/**
	 * yourls_link_tinymce_button_init
	 *
	 * Initialize tinymce button and load helper code
	 *
	 * @param none
	 *
	 * @return none
	 */
	function yourls_link_tinymce_button_init() {
		global $typenow;

		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( ! in_array( $typenow, array( 'post', 'page' ) ) ) {
			return;
		}

		// Check if WYSIWYG is enabled
		if ( 'true' === get_user_option( 'rich_editing' ) ) {
			//Add a callback to regiser our TinyMCE plugin
			add_filter( 'mce_external_plugins', array( &$this, 'better_yourls_register_tinymce_plugin' ) );

			// Add a callback to add our button to the TinyMCE toolbar
			add_filter( 'mce_buttons', array( &$this, 'better_yourls_add_tinymce_button' ) );
		}
	}

	/**
	 * better_yourls_register_tinymce_plugin
	 *
	 * Declare script for button
	 *
	 * @param $plugin_array
	 *
	 * @return $plugin_array
	 */
	function better_yourls_register_tinymce_plugin( $plugin_array ) {
		$plugin_array['better_yourls_tinymce_button'] = BYOURLS_URL . 'assets/js/src/better-yourls-tinymce-button.js';

		return $plugin_array;
	}

	/**
	 * better_yourls_add_tinymce_button
	 *
	 * Add a button to tinymce editor for inserting yourls link
	 *
	 * @param $buttons
	 *
	 * @return $buttons
	 */
	function better_yourls_add_tinymce_button( $buttons ) {
		array_push( $buttons, 'better_yourls_tinymce_button' );

		return $buttons;
	}

	/**
	* yourls_get_shortlink
	*
	* API for AJAX call to create a shoturl from url on YOURLS server
	*
	* @since 0.0.2
	*
	* @param none
	*
	* @return string shortlink url if successful, original $keyword if no need to shorten, or false if error
	*/
	function yourls_get_shortlink() {

		$list = array();

		if ( !empty($_POST) ) {

			if ( isset($_POST['url']) ) {

				$url = (parse_url($_POST['url'], PHP_URL_SCHEME) ? '' : 'http://') . $_POST['url'];

				$list[] = array(
					'shortlink'	=>	'invalid'
				);

				if ( $this->validate_url( $url ) === true ) {

					$short_url = $this->create_yourls_url_tinymce( $url );
					if ( $short_url !== false ) {

            $list[0]["shortlink"] = $short_url;

            $long_url = $this->expand( $short_url );
            if ( $long_url !== false ) {
              $list[0]["title"] = $long_url['title'];
            } else {
              $list[0]["title"] = '';
            }

					}
				}
			}
		}
		echo json_encode( $list );  //always echo an array encoded in json
		die();
	}

	/**
	* create_yourls_url_tinymce
	*
	* Create a YOURLS shortened url from given long url, possibly re-using existing shortlink
	*
	* @since 0.0.2
	*
	* @param array $tinymce_url long url to be shortened
	*
	* @return string shortlink url if successful, original $tinymce_url if no need to shorten, or false if error
	*/
	private function create_yourls_url_tinymce( $tinymce_url ) {

		$shortlink_integrity = ( isset( $this->settings['shortlink_integrity'] ) && true === $this->settings['shortlink_integrity'] ) ? true : false;

		$keyword = $this->derive_keyword($tinymce_url);
		if ( $keyword !== false ) {
			// The given URL is already a shortlink from our YOURLS server

			if ( $shortlink_integrity === true ) {
				$result_keyword_exist = $this->exist_keyword( $keyword );
				if ( $result_keyword_exist !== true ) {
					//  Long URL is actually shortlink, but keyword does not exist on YOURLS server
					//  No idea how to handle this possibility other than return false
					return false;
				}
			}

			// All good with existing shortlink, just return it
			return $tinymce_url;

		} else {
			// Not already a shortlink

			if ( $shortlink_integrity === true ) {

				$shortlink_url_recover = ( isset( $this->settings['shortlink_url_recover'] ) && true === $this->settings['shortlink_url_recover'] ) ? true : false;
				if ( $shortlink_url_recover === true ) {
					// Look for existing long URL on YOURLS server that matches this long URL

					$result = $this->get_keyword_url( $tinymce_url );
					if ( $result !== false ) {
						// Found YOURLS long URL matching long URL
						// We will NOT create a new shortlink
						return $result;
					}
				}
			}

			// Create new shorturl
			$shortlink = $this->create_yourls( $tinymce_url );
			if ( $shortlink !== false ) {
				return $shortlink;
			}

		}

		return false;
	}

	//
	//  YOURLS API FUNCTONS
	//

  /**
	* expand
	*
	* Makes an API call to YOURLS service for getting long url from keyword
	*
	* @since 0.0.2
	*
	* @param array $keyword shorturl keyword
	*
	* @return string either long url if found or false if not
	*/
	private function expand( $shorturl ) {

		$args = array(
			'body' => array(
				'action'    => 'expand',
				'shorturl'   => $shorturl,
			),
		);

		$result = $this->api_call( $args );

		if ( $result !== false ) {

			$result_longurl = $result->{'longurl'};
			if ( ( $result_longurl ) && ( $result_longurl !== false ) ) {

        $retval = array(
    			'longurl' => $result_longurl
    		);

        $result_title = $result->{'title'};
			  if ( ( $result_title ) && ( $result_title !== false ) ) {

          $retval['title'] = $result_title;

        } else {
          $retval['title'] = 'not found';
        }

				return $retval;
			}
		}

		return false;
	}

	/**
	* exist_keyword
	*
	* Makes an API call to YOURLS service to check if keyword exists
	*
	* @since 0.0.2
	*
	* @param array $keyword shorturl keyword
	*
	* @return string true if found, false if not
	*/
	private function exist_keyword( $keyword ) {

		$args = array(
			'body' => array(
				'action'    => 'exist-keyword',
				'keyword'   => $keyword,
			),
		);

		$result = $this->api_call( $args );

		if ( $result !== false ) {
			$result_keyword_exist = $result->{'keyword'};

			if ( $result_keyword_exist === true ) {
				return true;
			}
		}

		return false;
	}

	/**
	* get_keyword_url
	*
	* Makes an API call to YOURLS service for getting keyword from long url
	*
	* @since 0.0.2
	*
	* @param array $keyword shorturl keyword
	*
	* @return string either keyword if found or false if not
	*/
	private function get_keyword_url( $url ) {

		$shortlink_url_recover_newest = ( isset( $this->settings['shortlink_url_recover_newest'] ) && true === $this->settings['shortlink_url_recover_newest'] ) ? true : false;

		$args = array(
			'body' => array(
				'action'    => 'get-keyword-url',
				'newest'    => ($shortlink_url_recover_newest ? 'true' : 'false'),
				'url'       => $url,
			),
		);

		$result = $this->api_call( $args );

		if ( $result !== false ) {
			$result_keyword = $result->{'keyword'};

			if ( ( $result_keyword ) && ( $result_keyword !== false ) ) {

				$https = ( isset( $this->settings['https'] ) && true === $this->settings['https'] ) ? 's' : '';
				$link = esc_url_raw( 'http' . $https . '://' . $this->settings['domain'] . '/' . $result_keyword );
				return $link;
			}
		}

		return false;
	}

	/**
	* api_call
	*
	* Makes an API call to YOURLS service
	*
	* @since 0.0.2
	*
	* @param array $args Array of arguements for yourls call.
	*
	* @return json data structure of yourls call result or false if error
	*/
	private function api_call( $args ) {

		if ( !$args ) {
			return false;
		}

		$https      = ( isset( $this->settings['https'] ) && true === $this->settings['https'] ) ? 's' : '';
		$yourls_url = esc_url_raw( 'http' . $https . '://' . $this->settings['domain'] . '/yourls-api.php' );
		$timestamp  = current_time( 'timestamp' );

		$args['body']['timestamp'] = $timestamp;
		$args['body']['signature'] = md5( $timestamp . $this->settings['key'] );
		$args['body']['format'] = 'json';

		// Allow the option to use a self-signed.
		if ( isset( $this->settings['https_ignore'] ) && true === $this->settings['https_ignore'] ) {
			$args['sslverify'] = false;
		}

		$response = wp_remote_post( $yourls_url, $args );
		if ( !$response ) {
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		if ( !$response_body ) {
			return false;
		}

		return json_decode( $response_body );
	}

}
