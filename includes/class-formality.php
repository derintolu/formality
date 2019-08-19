<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Formality
 * @subpackage Formality/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Formality
 * @subpackage Formality/includes
 * @author     Your Name <email@example.com>
 */
class Formality {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Formality_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $formality    The string used to uniquely identify this plugin.
	 */
	protected $formality;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'FORMALITY_VERSION' ) ) {
			$this->version = FORMALITY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->formality = 'formality';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->setup();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Formality_Loader. Orchestrates the hooks of the plugin.
	 * - Formality_i18n. Defines internationalization functionality.
	 * - Formality_Admin. Defines all hooks for the admin area.
	 * - Formality_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-formality-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-formality-setup.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-formality-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-formality-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-formality-public.php';

		$this->loader = new Formality_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Formality_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Formality_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Formality_Admin( $this->get_formality(), $this->get_version() );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'flush_rules');
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'formality_menu' );
		$this->loader->add_filter( 'manage_formality_form_posts_columns', $plugin_admin, 'column_results', 99 );
		$this->loader->add_action( 'manage_formality_form_posts_custom_column', $plugin_admin, 'column_results_row', 10, 2 );
		
		$plugin_results = new Formality_Results( $this->get_formality(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $plugin_results, 'metaboxes' );
		$this->loader->add_action( 'init', $plugin_results, 'unread_status');
		$this->loader->add_action( 'add_menu_classes', $plugin_results, 'unread_bubble', 99);
		$this->loader->add_action( 'admin_init', $plugin_results, 'auto_publish');
				
		$plugin_gutenberg = new Formality_Gutenberg( $this->get_formality(), $this->get_version() );
		$this->loader->add_action( 'init', $plugin_gutenberg, 'register_blocks');
		$this->loader->add_filter( 'block_categories', $plugin_gutenberg, 'block_categories', 99, 2);
		$this->loader->add_filter( 'allowed_block_types', $plugin_gutenberg, 'filter_blocks', 99, 2);
		$this->loader->add_action( 'rest_api_init', $plugin_gutenberg, 'rest_api' );
		$this->loader->add_filter( 'use_block_editor_for_post_type', $plugin_gutenberg, 'prevent_classic_editor', 99999, 2 );
		$this->loader->add_filter( 'gutenberg_can_edit_post_type', $plugin_gutenberg, 'prevent_classic_editor', 99999, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Formality_Public( $this->get_formality(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'the_content', $plugin_public, 'print_form', 99 );
		$this->loader->add_filter( 'template_include', $plugin_public, 'page_template', 99 );
		$this->loader->add_action( 'init', $plugin_public, 'shortcode' );
    $this->loader->add_filter( 'show_admin_bar', $plugin_public, 'hide_admin_bar', 99 );

		$plugin_submit = new Formality_Submit( $this->get_formality(), $this->get_version() );
		$this->loader->add_action( 'rest_api_init', $plugin_submit, 'rest_api' );
		$this->loader->add_action( 'wp_ajax_formality_token', $plugin_submit, 'token' );
		$this->loader->add_action( 'wp_ajax_nopriv_formality_token', $plugin_submit, 'token' );
		$this->loader->add_action( 'wp_ajax_formality_send', $plugin_submit, 'send' );
		$this->loader->add_action( 'wp_ajax_nopriv_formality_send', $plugin_submit, 'send' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function setup() {
  	
		$plugin_setup = new Formality_Setup( $this->get_formality(), $this->get_version() );
		$this->loader->add_action( 'init', $plugin_setup, 'post_types' );
	
	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_formality() {
		return $this->formality;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Formality_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
