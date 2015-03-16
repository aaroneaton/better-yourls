<?php

/**
 * @package better_yourls
 */

/**
 * YOURLS actions.
 *
 * Non admin-specific actions.
 *
 * @since 0.0.1
 *
 */
class Better_YOURLS_Actions {

	private
		$plugin_file,
		$settings;

	/**
	 * Better YOURLS constructor.
	 *
	 * @since 0.0.1
	 *
	 * @param string $plugin_file the main plugin file
	 *
	 * @return Better_Yourls_Actions
	 */
	public function __construct( $plugin_file ) {

		//set default options
		$this->plugin_file = $plugin_file;
		$this->settings    = get_option( 'better_yourls' );

		//add filters and actions if we've set API info
		if ( isset( $this->settings['domain'] ) && $this->settings['domain'] != '' && isset( $this->settings['key'] ) && $this->settings['key'] != '' ) {

			add_filter( 'get_shortlink', array( $this, 'get_shortlink' ), 10, 3 );
			add_filter( 'pre_get_shortlink', array( $this, 'pre_get_shortlink' ), 11, 2 );
			add_filter( 'sharing_permalink', array( $this, 'sharing_permalink' ), 10, 2 );

			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100 );
			add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		}

	}

	/**
	 * Add links to the admin bar.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	function admin_bar_menu() {

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

		if ( $post_id != 0 ) {

			$yourls_shortlink = get_post_meta( $post_id, '_better_yourls_short_link', true );

			if ( $yourls_shortlink != false ) {
				return $yourls_shortlink;
			}

			//setup call parameters
			$yourls_url   = 'http://' . $this->settings['domain'] . '/yourls-api.php';
			$timestamp    = time();
			$yours_key    = $this->settings['key'];
			$signature    = md5( $timestamp . $yours_key );
			$action       = 'shorturl';
			$format       = 'JSON';
			$original_url = get_permalink( $post_id );

			//keyword and title aren't currently used but may be in the future
			if ( $keyword != '' ) {
				$keyword = '&keyword=' . sanitize_text_field( $keyword );
			}

			$title = '&title=' . ( trim( $title ) == '' ? get_the_title( $post_id ) : sanitize_text_field( $title ) );

			$request = $yourls_url . '?timestamp=' . $timestamp . '&signature=' . $signature . '&action=' . $action . '&url=' . $original_url . '&format=' . $format . $keyword . $title;

			$response = wp_remote_get( $request );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$short_link = isset( $response['body'] ) ? $response['body'] : false;

			if ( $short_link === false ) {
				return false;
			}

			$url = esc_url( trim( $short_link ) );

			if ( $this->validate_url( $url ) === true ) {

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
	 * @param bool $short_link the shortlink to filter (defaults to false)
	 * @param int  $id         the post id
	 *
	 * @return bool the shortlink or false
	 */
	public function get_shortlink( $short_link, $id ) {

		if ( is_singular() === false ) {
			return;
		}

		$link = $this->create_yourls_url( $id );

		if ( $link !== false ) {
			return $link;
		}

		return $short_link;
	}

	/**
	 * Filter wp shortlink before display.
	 *
	 * Filters the default WordPress shortlink
	 *
	 * @param bool $short_link the shortlink to filter (defaults to false)
	 * @param int  $id         the post id
	 *
	 * @return bool the shortlink or false
	 */
	public function pre_get_shortlink( $short_link, $id ) {

		$post = get_post( $id );

		if ( empty( $post ) ) {
			return $short_link;
		}

		//If we've already created a shortlink return it or just return the default
		$link = get_post_meta( $post->ID, '_better_yourls_short_link', true );

		if ( $link == '' ) {
			return $short_link;
		}

		return $link;

	}

	/**
	 * Adds the shortlink to Jetpack Sharing.
	 *
	 * @param string $link    the original link
	 * @param int    $post_id the post id
	 *
	 * @return string the link to share
	 */
	public function sharing_permalink( $link, $post_id ) {

		$yourls_shortlink = $this->create_yourls_url( $post_id );

		if ( $yourls_shortlink !== false and $yourls_shortlink != '' ) {

			return $yourls_shortlink;

		}

		return $link;

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
	public function transition_post_status( $new_status, $old_status, $post ) {

		if ( ! current_user_can( 'edit_post', $post->ID ) || $new_status != 'publish' ) {
			return;
		}

		//Get the short URL
		$link = $this->create_yourls_url( $post->ID );

		//Save the short URL
		if ( $link !== false ) {
			update_post_meta( $post->ID, '_better_yourls_short_link', $link );
		}

	}

	/**
	 * Validates a URL
	 *
	 * @since 1.2
	 *
	 * @param string $url the url to validate
	 *
	 * @return bool true if valid url else false
	 */
	private function validate_url( $url ) {

		$pattern = "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";

		return (bool) preg_match( $pattern, $url );

	}

	/**
	 * Enqueue script with admin bar.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function wp_enqueue_scripts() {

		global $post;

		if ( is_admin_bar_showing() && isset( $post->ID ) && current_user_can( 'edit_post', $post->ID ) ) {

			wp_enqueue_script( 'better_yourls', plugins_url( '/js/better-yourls.js', $this->plugin_file ), array( 'jquery' ), '0.0.1' );
			wp_localize_script( 'better_yourls',
			                    'better_yourls',
			                    array(
				                    'text'       => __( 'Your YOURLS short link is: ', 'better-yourls' ),
				                    'yourls_url' => wp_get_shortlink( $post->ID ),
			                    )
			);

		}

	}

}
