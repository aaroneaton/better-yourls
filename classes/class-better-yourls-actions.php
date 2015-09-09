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
 * @author  Chris Wiegman <chris@chriswiegman.com>
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
	 *
	 * @return Better_Yourls_Actions
	 */
	public function __construct() {

		//set default options
		$this->settings = get_option( 'better_yourls' );

		//add filters and actions if we've set API info
		if ( isset( $this->settings['domain'] ) && '' != $this->settings['domain'] && isset( $this->settings['key'] ) && '' != $this->settings['key'] ) {

			add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
			add_action( 'admin_bar_menu', array( $this, 'action_admin_bar_menu' ), 100 );
			add_action( 'save_post', array( $this, 'action_save_post' ), 11 );
			add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );

			add_filter( 'get_shortlink', array( $this, 'filter_get_shortlink' ), 10, 3 );
			add_filter( 'pre_get_shortlink', array( $this, 'filter_pre_get_shortlink' ), 11, 2 );
			add_filter( 'sharing_permalink', array( $this, 'filter_sharing_permalink' ), 10, 2 );

		}
	}

	/**
	 * Adds meta box
	 *
	 * Adds the metabox for a custom link
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function action_add_meta_boxes() {

		global $post;

		add_meta_box(
			'yourls_keyword',
			__( 'YOURLs Keyword', 'better-yourls' ),
			array( $this, 'yourls_keyword_metabox' ),
			$post->post_type,
			'side',
			'core'
		);

	}

	/**
	 * Add links to the admin bar.
	 *
	 * Adds a "Better YOURLs" menu to the admin bar to access the shortlink and stats easily from the front end.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function action_admin_bar_menu() {

		global $wp_admin_bar, $post;

		if ( ! isset( $post->ID ) ) {
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
					'title' => __( 'YOURLS', 'better-yourls' ),
				)
			);

			$wp_admin_bar->add_menu(
				array(
					'href'   => '',
					'parent' => 'better_yourls',
					'id'     => 'better_yourls-link',
					'title'  => __( 'YOURLS Link', 'better-yourls' ),
				)
			);

			$wp_admin_bar->add_menu(
				array(
					'parent' => 'better_yourls',
					'id'     => 'better_yourls-stats',
					'title'  => __( 'Link Stats', 'better-yourls' ),
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
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 *
	 * @return void
	 */
	public function action_save_post( $post_id ) {

		// Only save at normal times
		if (
			( defined( 'DOING_AUTOSAVE' ) && true === DOING_AJAX ) ||
			( defined( 'DOING_AJAX' ) && true === DOING_AUTOSAVE ) ||
			( defined( 'DOING_CRON' ) && true === DOING_CRON )
		) {
			return;
		}

		/**
		 * Filter Better YOURLs post statuses
		 *
		 * The post statuses upon which a URL should be generated.
		 *
		 * @since 2.0.0
		 *
		 * @param array Array of post statuses.
		 */
		$post_statuses = apply_filters( 'better_yourls_post_statuses', array( 'publish', 'future' ) );

		// Make sure we're not generating this for drafts or other posts that don't need them
		if ( ! in_array( get_post_status( $post_id ), $post_statuses ) ) {
			return;
		}

		$keyword = '';

		// Store custom keyword (if set)
		if ( isset( $_POST['better-yourls-keyword'] ) ) {
			$keyword = sanitize_title( trim( $_POST['better-yourls-keyword'] ) );
		}

		//Get the short URL. Note this will use the meta if it was already saved
		$link = $this->create_yourls_url( $post_id, $keyword );

		// Keyword would be a duplicate so use a standard one.
		if ( '' !== $keyword && ! $link ) {
			$link = $this->create_yourls_url( $post_id );
		}

		//Save the short URL only if it was generated correctly
		if ( $link ) {
			update_post_meta( $post_id, '_better_yourls_short_link', $link );
		}
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

			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {

				wp_register_script( 'better_yourls', BYOURLS_URL . '/assets/js/better-yourls.js', array( 'jquery' ), BYOURLS_VERSION );

			} else {

				wp_register_script( 'better_yourls', BYOURLS_URL . '/assets/js/better-yourls.min.js', array( 'jquery' ), BYOURLS_VERSION );

			}

			wp_enqueue_script( 'better_yourls' );

			wp_localize_script(
				'better_yourls',
				'better_yourls',
				array(
					'text'       => __( 'Your YOURLS short link is: ', 'better-yourls' ),
					'yourls_url' => wp_get_shortlink( $post->ID ),
				)
			);

		}
	}

	/**
	 * Creates YOURLS link.
	 *
	 * Creates YOURLS link if not in post meta and saves new link to post meta where appropriate.
	 *
	 * @since 0.0.1
	 *
	 * @param  int   $post_id the current post id
	 * @param string $keyword optional keyword for shortlink
	 * @param string $title   optional title for shortlink
	 *
	 * @return bool|string the yourls shortlink or false
	 */
	public function create_yourls_url( $post_id, $keyword = '', $title = '' ) {

		if ( is_preview() && ! is_admin() ) {
			return false;
		}

		if ( 0 != $post_id ) {

			$yourls_shortlink = get_post_meta( $post_id, '_better_yourls_short_link', true );

			if ( $yourls_shortlink ) {
				return $yourls_shortlink;
			}

			//setup call parameters
			$yourls_url = 'http://' . $this->settings['domain'] . '/yourls-api.php';
			$timestamp  = current_time( 'timestamp' );

			$request_args = array(
				'title'     => ( '' == trim( $title ) ) ? get_the_title( $post_id ) : sanitize_text_field( $title ),
				'timestamp' => $timestamp,
				'signature' => md5( $timestamp . $this->settings['key'] ),
				'action'    => 'shorturl',
				'url'       => get_permalink( $post_id ),
				'format'    => 'JSON',
			);

			//keyword and title aren't currently used but may be in the future
			if ( '' != $keyword ) {
				$request_args['keyword'] = sanitize_title( $keyword );
			}

			$request = esc_url_raw( add_query_arg( $request_args, $yourls_url ) );

			$response = wp_remote_get( $request );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$short_link = isset( $response['body'] ) ? $response['body'] : false;

			if ( false === $short_link ) {
				return false;
			}

			$url = esc_url( trim( $short_link ) );

			if ( true === $this->validate_url( $url ) ) {

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
	 * @param bool $short_link the shortlink to filter (defaults to false)
	 * @param int  $id         the post id
	 *
	 * @return bool the shortlink or false
	 */
	public function filter_get_shortlink( $short_link, $id ) {

		if ( false === is_singular() ) {
			return false;
		}

		$link = $this->create_yourls_url( $id );

		if ( false !== $link ) {
			return $link;
		}

		return $short_link;

	}

	/**
	 * Filter wp shortlink before display.
	 *
	 * Filters the default WordPress shortlink
	 *
	 * @since 0.0.1
	 *
	 * @param bool $short_link the shortlink to filter (defaults to false)
	 * @param int  $id         the post id
	 *
	 * @return bool the shortlink or false
	 */
	public function filter_pre_get_shortlink( $short_link, $id ) {

		$post = get_post( $id );

		if ( empty( $post ) ) {
			return $short_link;
		}

		//If we've already created a shortlink return it or just return the default
		$link = get_post_meta( $post->ID, '_better_yourls_short_link', true );

		if ( '' == $link ) {
			return $short_link;
		}

		return $link;

	}

	/**
	 * Adds the shortlink to Jetpack Sharing.
	 *
	 * If you're using JetPack for sharing links when publishing a post this will make sure the shared link uses your shortlink.
	 *
	 * @since 0.0.1
	 *
	 * @param string $link    the original link
	 * @param int    $post_id the post id
	 *
	 * @return string the link to share
	 */
	public function filter_sharing_permalink( $link, $post_id ) {

		$yourls_shortlink = $this->create_yourls_url( $post_id );

		if ( false !== $yourls_shortlink && '' != $yourls_shortlink ) {

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
	 * @param string $url the url to validate
	 *
	 * @return bool true if valid url else false
	 */
	private function validate_url( $url ) {

		$pattern = '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';

		return (bool) preg_match( $pattern, $url );

	}

	public function yourls_keyword_metabox() {

		global $post;

		$link     = get_post_meta( $post->ID, '_better_yourls_short_link', true );
		$readonly = '';

		if ( $link ) {
			$readonly = 'readonly="readonly" ';
		}

		echo '<input type="text" id="better-yourls-keyword" name="better-yourls-keyword" style="width: 100%;" value="' . esc_url( $link ) . '" ' . $readonly . '/>';
		echo '<p><em>' . __( 'If a short-url doesn\'t yet exist for this post you can enter a keyword above. If it already exists it will be displayed.', 'better-yourls' ) . '</em></p>';

	}
}
