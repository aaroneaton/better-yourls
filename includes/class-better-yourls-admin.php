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
 * @author  Chris Wiegman <chris@wiegman.us>
 */

/**
 * Class Better_YOURLS_Admin
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
	 */
	public function __construct() {

		$this->settings = get_option( 'better_yourls' );

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) ); // Enqueue scripts for admin page.
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

		if ( 'settings_page_better_yourls' === get_current_screen()->id ) {

			$min = ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ) ? '' : '.min';

			wp_register_script( 'better_yourls_footer', BYOURLS_URL . 'assets/js/admin-footer' . $min . '.js', array( 'jquery' ), BYOURLS_VERSION, true );
			wp_register_style( 'better_yourls_admin', BYOURLS_URL . 'assets/css/better-yourls' . $min . '.css', array(), BYOURLS_VERSION ); // Add multi-select css.

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

		// Add meta boxes.
		add_meta_box(
			'better_yourls_intro',
			esc_html__( 'Better Yourls', 'better-yourls' ),
			array( $this, 'metabox_settings' ),
			'settings_page_better_yourls',
			'main'
		);

		add_meta_box(
			'better_yourls_support',
			esc_html__( 'Support This Plugin', 'better-yourls' ),
			array( $this, 'metabox_support' ),
			'settings_page_better_yourls',
			'side'
		);

		add_meta_box(
			'better_yourls_help',
			esc_html__( 'Need help?', 'better-yourls' ),
			array( $this, 'metabox_help' ),
			'settings_page_better_yourls',
			'side'
		);

		// Add Settings sections.
		add_settings_section(
			'better_yourls',
			'',
			'__return_empty_string',
			'settings_page_better_yourls'
		);

		// Add settings fields.
		add_settings_field(
			'better_yourls[domain]',
			esc_html__( 'YOURLS Domain', 'better-yourls' ),
			array( $this, 'settings_field_domain' ),
			'settings_page_better_yourls',
			'better_yourls'
		);

		add_settings_field(
			'better_yourls[https]',
			esc_html__( 'Use https', 'better-yourls' ),
			array( $this, 'settings_field_https' ),
			'settings_page_better_yourls',
			'better_yourls'
		);

		add_settings_field(
			'better_yourls[https_ignore]',
			esc_html__( 'Allow Self-signed https Certificate', 'better-yourls' ),
			array( $this, 'settings_field_https_ignore' ),
			'settings_page_better_yourls',
			'better_yourls'
		);

		add_settings_field(
			'better_yourls[key]',
			esc_html__( 'YOURLS  Token', 'better-yourls' ),
			array( $this, 'settings_field_key' ),
			'settings_page_better_yourls',
			'better_yourls'
		);

		add_settings_field(
			'better_yourls[post_types]',
			esc_html__( 'Exclude Post Types', 'better-yourls' ),
			array( $this, 'settings_field_post_types' ),
			'settings_page_better_yourls',
			'better_yourls'
		);

		add_settings_field(
			'better_yourls[private_post_types]',
			esc_html__( 'Allow Private Post Types', 'better-yourls' ),
			array( $this, 'settings_field_private_post_types' ),
			'settings_page_better_yourls',
			'better_yourls'
		);

		// Register the settings field for the entire module.
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
			esc_html__( 'Better YOURLS', 'better-yourls' ),
			esc_html__( 'Better YOURLS', 'better-yourls' ),
			'manage_options',
			'better_yourls',
			array( $this, 'render_page' )
		);

		add_action( 'load-' . $page, array( $this, 'page_actions' ) ); // Load page structure.

	}

	/**
	 * Add action link to plugin page.
	 *
	 * Adds plugin settings link to plugin page in WordPress admin area.
	 *
	 * @since 0.0.1
	 *
	 * @param object $links Array of WordPress links.
	 * @param string $file  String name of current file.
	 *
	 * @return object Array of WordPress links
	 */
	public function filter_plugin_action_links( $links, $file ) {

		static $this_plugin;

		if ( empty( $this_plugin ) ) {
			$this_plugin = 'better-yourls/better-yourls.php';
		}

		if ( $file === $this_plugin ) {
			$links[] = '<a href="options-general.php?page=better_yourls">' . esc_html__( 'Settings', 'better-yourls' ) . '</a>';
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

		printf(
			// translators: %1 is the opening of the support link markup and %2 is the closing for the markup.
			esc_html__( 'If you need help getting this plugin or have found a bug please visit the %1$ssupport forums%2$s.', 'better-yourls' ),
			'<a href="' . esc_url( $support_page ) . '" target="_blank">',
			'</a>'
		);

	}

	/**
	 * Build the settings metabox.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function metabox_settings() {

		echo '<p><strong>This plugin is up for adoption. Please <a href="http://www.chriswiegman.com/contact/">contact me</a> if you would like to continue its development.</strong></p>';

		echo '<p>', esc_html__( 'Use the settings below to configure Better YOURLs for your site.', 'better-yourls' ), '</p>';

		?>
		<form method="post" action="options.php" class="better-yourls-form" >
		<?php settings_fields( 'settings_page_better_yourls' ); ?>
		<?php do_settings_sections( 'settings_page_better_yourls' ); ?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php esc_html_e( 'Save Changes', 'better-yourls' ); ?>"/>
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

		echo '<p>' . esc_html__( 'Have you found this plugin useful? Please help support it\'s continued development with a donation of $2, $20, or even $50.', 'better-yourls' ) . '</p>';

		?>
		<form></form> <?php // Don't ask me why but WordPress filters out the form if I don't add this. ?>

		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_donations" />
			<input type="hidden" name="business" value="CFJJ7YCD72RLA" />
			<input type="hidden" name="item_name" value="Better YOURLs" />
			<input type="hidden" name="currency_code" value="USD" />
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
		</form>

		<?php
		echo '<p>' . esc_html__( 'Short on funds?', 'better-yourls' ) . '</p>';

		echo '<ul>';

		echo '<li><a href="' . esc_url( $wp_page ) . '" target="_blank">' . esc_html__( 'Rate Better YOURLS 5-stars on WordPress.org', 'better-yourls' ) . '</a></li>';

		echo '<li>' . esc_html__( 'Talk about it on your site and link back to the ', 'better-yourls' ) . '<a href="' . esc_url( $homepage ) . '" target="_blank">' . esc_html__( 'plugin page.', 'better-yourls' ) . '</a></li>';

		echo '<li><a href="http://twitter.com/home?status=' . rawurldecode( 'I use Better YOURLS for WordPress by @ChrisWiegman and you should too - ' . esc_url( $homepage ) ) . '" target="_blank">' . esc_html__( 'Tweet about it. ', 'better-yourls' ) . '</a></li>';

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

		// Set two columns for all plugins using this framework.
		$args = array(
			'max'     => 2,
			'default' => 2,
		);

		add_screen_option( 'layout_columns', $args );

		// Enqueue common scripts and try to keep it simple.
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

		$screen = get_current_screen()->id; // The current screen id.

		?>

		<div class="wrap">

			<h2><?php esc_html_e( 'Better Yourls', 'better-yourls' ); ?></h2>

			<?php
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			?>

			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-<?php echo 1 === get_current_screen()->get_columns() ? '1' : '2'; ?>">

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
	 * @param array $input array of plugin options.
	 *
	 * @return array array of plugin options
	 */
	public function sanitize_module_input( $input ) {

		// Set whether or not we're already handling private post types and act accordingly.
		$allow_private = false;

		if ( isset( $this->settings['private_post_types'] ) && true === $this->settings['private_post_types'] ) {
			$allow_private = true;
		}

		$input['domain']             = isset( $input['domain'] ) ? sanitize_text_field( $input['domain'] ) : '';
		$input['domain']             = str_replace( 'http://', '', $input['domain'] );
		$input['domain']             = str_replace( ' ', '', $input['domain'] );
		$input['domain']             = trim( $input['domain'], '/' );
		$input['key']                = isset( $input['key'] ) ? sanitize_text_field( $input['key'] ) : '';
		$input['https']              = isset( $input['https'] ) && 1 === absint( $input['https'] ) ? true : false;
		$input['https_ignore']       = isset( $input['https_ignore'] ) && 1 === absint( $input['https_ignore'] ) ? true : false;
		$input['private_post_types'] = isset( $input['private_post_types'] ) && 1 === absint( $input['private_post_types'] ) ? true : false;

		$excluded_public_post_types = array();

		// Make sure all set post types are valid.
		if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {

			$args = array();

			// Set public argument if we're not worried about private post types.
			if ( false === $allow_private ) {
				$args['public'] = true;
			}

			$public_post_types = get_post_types( $args );

			foreach ( $input['post_types'] as $post_type ) {

				if ( in_array( $post_type, $public_post_types, true ) ) {
					$excluded_public_post_types[] = sanitize_text_field( $post_type );
				}
			}

			$input['post_types'] = $excluded_public_post_types;

		}

		$args = array(
			'public' => false,
		);

		$private_post_types = get_post_types( $args );

		foreach ( $private_post_types as $post_type ) {

			if ( ! isset( $input['post_types'] ) || ! is_array( $input['post_types'] ) ) {
				$input['post_types'] = array();
			}

			// Don't automatically include all excluded private post types immediately.
			if ( ( ! isset( $this->settings['private_post_types'] ) || false === $this->settings['private_post_types'] ) && true === $input['private_post_types'] ) {

				$input['post_types'][] = $post_type;

			} else {

				$key = array_search( $post_type, $input['post_types'], true );

				if ( false !== $key ) {
					unset( $input['post_types'][ $key ] );
				}
			}
		}

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

		echo '<input class="text" name="better_yourls[domain]" id="better_yourls_domain" value="' . esc_attr( $domain ) . '" type="text">';
		echo '<label for="better_yourls_domain"><p class="description"> ' . esc_html__( 'The short domain you are using for YOURLS. Enter only the domain name.', 'better-yourls' ) . '</p></label>';

	}

	/**
	 * Echos Allow https field.
	 *
	 * @since 2.1
	 *
	 * @return void
	 */
	public function settings_field_https() {

		$https = ( isset( $this->settings['https'] ) && true === $this->settings['https'] ) ? true : false;

		echo '<input name="better_yourls[https]" id="better_yourls_https" value="1" type="checkbox" ' . checked( true, $https, false ) . '>';
		echo '<label for="better_yourls_https"><p class="description"> ' . esc_html__( 'Check this box to access your YOURLS installation over https.', 'better-yourls' ) . '</p></label>';

	}

	/**
	 * Echos Allow self-signed certificates field.
	 *
	 * @since 2.1
	 *
	 * @return void
	 */
	public function settings_field_https_ignore() {

		$https_ignore = ( isset( $this->settings['https_ignore'] ) && true === $this->settings['https_ignore'] ) ? true : false;

		echo '<input name="better_yourls[https_ignore]" id="better_yourls_https_ignore" value="1" type="checkbox" ' . checked( true, $https_ignore, false ) . '>';
		echo '<label for="better_yourls_https_ignore"><p class="description"> ' . esc_html__( 'Check this box to ignore security checks on your https certificate. Note this is not normal. Only use this if you are using a self-signed certificate to provide https to your YOURLS admin area.', 'better-yourls' ) . '</p></label>';

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

		echo '<input class="text" name="better_yourls[key]" id="better_yourls_key" value="' . esc_attr( $key ) . '" type="text">';
		echo '<label for="better_yourls_key"><p class="description"> ' . esc_html__( 'This can be found on the tools page in your YOURLS installation.', 'better-yourls' ) . '</p></label>';

	}

	/**
	 * Allow processing for private post types.
	 *
	 * @since 2.2
	 *
	 * @return void
	 */
	public function settings_field_private_post_types() {

		$private_post_types = ( isset( $this->settings['private_post_types'] ) && true === $this->settings['private_post_types'] ) ? true : false;

		echo '<input name="better_yourls[private_post_types]" id="better_yourls_private_post_types" value="1" type="checkbox" ' . checked( true, $private_post_types, false ) . '>';
		echo '<label for="better_yourls_private_post_types"><p class="description"> ' . esc_html__( 'Check this box to allow private post types to be indexed.', 'better-yourls' ) . '</p></label>';

	}

	/**
	 * Echo exclude post types field.
	 *
	 * @since 2.1
	 *
	 * @return void
	 */
	public function settings_field_post_types() {

		$args = array(
			'public' => true,
		);

		// Unset public argument if private posts are listed as true.
		if ( isset( $this->settings['private_post_types'] ) && true === $this->settings['private_post_types'] ) {
			unset( $args['public'] );
		}

		$post_types = get_post_types( $args, 'objects' );
		uasort( $post_types, array( $this, 'sort_post_types' ) );

		$excluded_post_types = array();

		// Get the list of post types we've already excluded.
		if ( isset( $this->settings['post_types'] ) ) {
			$excluded_post_types = $this->settings['post_types'];
		}

		foreach ( $post_types as $post_type ) {

			$checked = ( in_array( $post_type->name, $excluded_post_types, true ) ) ? true : false;

			echo '<input type="checkbox" name="better_yourls[post_types][' . esc_attr( $post_type->name ) . ']" value="' . esc_attr( $post_type->name ) . '" ' . checked( true, $checked, false ) . '><label for="better_yourls[post_types][' . esc_attr( $post_type->name ) . ']">' . esc_html( $post_type->labels->name ) . '</label><br />';

		}

		echo '<p class="description"> ' . esc_html__( 'Put a check mark next to any post type for which you do NOT want to generate a short link.', 'better-yourls' ) . '</p>';

	}

	/**
	 * Sort post types alphabetically by labels.
	 *
	 * @since 2.2
	 *
	 * @param \WP_Post_Type $post_type_a The first post type to sort.
	 * @param \WP_Post_Type $post_type_b The second post type to sort.
	 *
	 * @return int
	 */
	public function sort_post_types( $post_type_a, $post_type_b ) {

		if ( $post_type_a->labels->name === $post_type_b->labels->name ) {
			return 0;
		}

		return ( $post_type_a->labels->name < $post_type_b->labels->name ) ? -1 : 1;

	}
}
