<?php
/**
 * YOURLS admin interface.
 *
 * Admin-specific items such as settings.
 *
 * @package better-yourls
 *
 * @since 0.0.1
 *
 * @author  Chris Wiegman <chris@chriswiegman.com>
 */
class Better_YOURLS_Admin {

	/**
	 * The saved Better YOURLs settings
	 *
	 * @since 0.0.1
	 *
	 * @var array|bool
	 */
	protected $settings;

	/**
	 * Better YOURLS admin constructor.
	 *
	 * @since 0.0.1
	 *
	 * @return Better_Yourls_Admin
	 */
	public function __construct() {

		$this->settings = get_option( 'better_yourls' );

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) ); //enqueue scripts for admin page
		add_action( 'admin_init', array( $this, 'action_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );

		add_filter( 'plugin_action_links', array( $this, 'filter_plugin_action_links' ), 10, 2 );

	}

	/**
	 * Enqueue necessary admin scripts.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function action_admin_enqueue_scripts() {

		if ( get_current_screen()->id == 'settings_page_better_yourls' ) {

			if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {

				wp_register_script( 'better_yourls_footer', BYOURLS_URL . '/js/admin-footer.js', array( 'jquery' ), BYOURLS_VERSION, true );
				wp_register_style( 'better_yourls_admin', BYOURLS_URL .  '/css/better-yourls.css', array(), BYOURLS_VERSION ); //add multi-select css

			} else {

				wp_register_script( 'better_yourls_footer', BYOURLS_URL . '/js/admin-footer.min.js', array( 'jquery' ), BYOURLS_VERSION, true );
				wp_register_style( 'better_yourls_admin', BYOURLS_URL .  '/css/better-yourls.min.css', array(), BYOURLS_VERSION ); //add multi-select css

			}

			wp_enqueue_script( 'better_yourls_footer' );
			wp_enqueue_style( 'better_yourls_admin' );

		}
	}

	/**
	 * Handles admin_init functions.
	 *
	 * Builds meta boxes, sets up settings API.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function action_admin_init() {

		//add meta boxes
		add_meta_box(
			'better_yourls_intro',
			__( 'Better Yourls', 'better-yourls' ),
			array( $this, 'metabox_settings' ),
			'settings_page_better_yourls',
			'main'
		);

		add_meta_box(
			'better_yourls_support',
			__( 'Support This Plugin', 'better-yourls' ),
			array( $this, 'metabox_support' ),
			'settings_page_better_yourls',
			'side'
		);

		add_meta_box(
			'better_yourls_help',
			__( 'Need help?', 'better-yourls' ),
			array( $this, 'metabox_help' ),
			'settings_page_better_yourls',
			'side'
		);

		//Add Settings sections
		add_settings_section(
			'better_yourls',
			__( 'Configure Better YOURLS', 'better-yourls' ),
			'__return_empty_string',
			'settings_page_better_yourls'
		);

		//add settings fields
		add_settings_field(
			'better_yourls[domain]',
			__( 'YOURLS Domain', 'better-yourls' ),
			array( $this, 'settings_field_domain' ),
			'settings_page_better_yourls',
			'better_yourls'
		);

		add_settings_field(
			'better_yourls[key]',
			__( 'YOURLS  Token', 'better-yourls' ),
			array( $this, 'settings_field_key' ),
			'settings_page_better_yourls',
			'better_yourls'
		);

		//Register the settings field for the entire module
		register_setting(
			'settings_page_better_yourls',
			'better_yourls',
			array( $this, 'sanitize_module_input' )
		);

	}

	/**
	 * Handles the building of admin menus and calls required functions to render admin pages.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function action_admin_menu() {

		$page = add_options_page(
			__( 'Better YOURLS', 'better-yourls' ),
			__( 'Better YOURLS', 'better-yourls' ),
			'manage_options',
			'better_yourls',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $page, array( $this, 'page_actions' ) ); //Load page structure

	}

	/**
	 * Add action link to plugin page.
	 *
	 * Adds plugin settings link to plugin page in WordPress admin area.
	 *
	 * @since 0.0.1
	 *
	 * @param object $links Array of WordPress links
	 * @param string $file  String name of current file
	 *
	 * @return object Array of WordPress links
	 */
	public function filter_plugin_action_links( $links, $file ) {

		static $this_plugin;

		if ( empty( $this_plugin ) ) {
			$this_plugin = 'better-yourls/better-yourls.php';
		}

		if ( $file == $this_plugin ) {
			$links[] = '<a href="options-general.php?page=better_yourls">' . __( 'Settings', 'better-yourls' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Build the help metabox.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function metabox_help() {

		$support_page = 'https://wordpress.org/plugins/better-yourls/support/';

		echo '<p>' . __( 'If you need help getting this plugin or have found a bug please visit the <a href="' . $support_page . '" target="_blank">support forums</a>.', 'better-yourls' ) . '</p>';

	}

	/**
	 * Build the settings metabox.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function metabox_settings() {

		echo '<p>', __( 'Use the settings below to configure Better Yourls for your site.', 'better-yourls' ), '</p>';

		?>
		<form method="post" action="options.php" class="itsec-form" >
		<?php settings_fields( 'settings_page_better_yourls' ); ?>
		<?php do_settings_sections( 'settings_page_better_yourls' ); ?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'better-yourls' ); ?>"/>
		</p>

	<?php

	}

	/**
	 * Build support this plugin metabox.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function metabox_support() {

		$wp_page  = 'https://wordpress.org/plugins/better-yourls/';
		$homepage = 'https://wordpress.org/plugins/better-yourls/';

		echo '<p>' . __( 'Have you found this plugin useful? Please help support it\'s continued development with a donation of $20, $50, or even $100.', 'better-yourls' ) . '</p>';

		?>
		<form></form> <?php //don't ask me why but WordPress filters out the form if I don't add this ?>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="XMS5DSYBPUUNU">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"
			       name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>

		<?php
		echo '<p>' . __( 'Short on funds?', 'better-yourls' ) . '</p>';

		echo '<ul>';

		echo '<li><a href="' . $wp_page . '" target="_blank">' . __( 'Rate Better YOURLS 5-stars on WordPress.org', 'better-yourls' ) . '</a></li>';

		echo '<li>' . __( 'Talk about it on your site and link back to the ', 'better-yourls' ) . '<a href="' . $homepage . '" target="_blank">' . __( 'plugin page.', 'better-yourls' ) . '</a></li>';

		echo '<li><a href="http://twitter.com/home?status=' . urlencode( 'I use Better YOURLS for WordPress by @ChrisWiegman and you should too - ' . $homepage ) . '" target="_blank">' . __( 'Tweet about it. ', 'better-yourls' ) . '</a></li>';

		echo '</ul>';

	}

	/**
	 * Setup admin JS and column layout for postbox customization.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function page_actions() {

		//Set two columns for all plugins using this framework
		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );

		//Enqueue common scripts and try to keep it simple
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );

	}

	/**
	 * Render basic structure of the settings page.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function render_page() {

		$screen = get_current_screen()->id; //the current screen id

		?>

		<div class="wrap">

			<h2><?php _e( 'Better Yourls', 'better-yourls' ); ?></h2>

			<?php
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			?>

			<div id="poststuff">

				<div id="post-body"
				     class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

					<div id="postbox-container-2" class="postbox-container">
						<?php do_meta_boxes( $screen, 'main', null ); ?>
					</div>

					<div id="postbox-container-1" class="postbox-container">
						<?php do_meta_boxes( $screen, 'side', null ); ?>
					</div>


				</div>
				<!-- #post-body -->

			</div>
			<!-- #poststuff -->

		</div><!-- .wrap -->

	<?php

	}

	/**
	 * Sanitize plugin settings.
	 *
	 * @since 0.0.1
	 *
	 * @param array $input array of plugin options
	 *
	 * @return array array of plugin options
	 */
	public function sanitize_module_input( $input ) {

		$input['domain'] = isset( $input['domain'] ) ? sanitize_text_field( $input['domain'] ) : '';
		$input['domain'] = str_replace( 'http://', '', $input['domain'] );
		$input['domain'] = str_replace( ' ', '', $input['domain'] );
		$input['domain'] = trim( $input['domain'], '/' );
		$input['key']    = isset( $input['key'] ) ? sanitize_text_field( $input['key'] ) : '';

		return $input;

	}

	/**
	 * Echos domain field.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function settings_field_domain() {

		$domain = '';

		if ( isset( $this->settings['domain'] ) ) {
			$domain = sanitize_text_field( $this->settings['domain'] );
		}

		echo '<input class="text" name="better_yourls[domain]" id="better_yourls_domain" value="' . $domain . '" type="text">';
		echo '<label for="better_yourls_domain"><p class="description"> ' . __( 'The short domain you are using for YOURLS. Enter only the domain name.', 'better-yourls' ) . '</p></label>';

	}

	/**
	 * Echos API Key field.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function settings_field_key() {

		$key = '';

		if ( isset( $this->settings['key'] ) ) {
			$key = sanitize_text_field( $this->settings['key'] );
		}

		echo '<input class="text" name="better_yourls[key]" id="better_yourls_key" value="' . $key . '" type="text">';
		echo '<label for="better_yourls_key"><p class="description"> ' . __( 'This can be found on the tools page in your YOURLS installation..', 'better-yourls' ) . '</p></label>';

	}
}
