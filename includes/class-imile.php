<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.imile.com
 * @since      1.0.1
 *
 * @package    Imile
 * @subpackage Imile/includes
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
 * @since      1.0.1
 * @package    Imile
 * @subpackage Imile/includes
 * @author     iMile <imile@gmail.com>
 */
class Imile {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.1
	 * @access   protected
	 * @var      Imile_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.1
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
	 * @since    1.0.1
	 */
	public function __construct() {
		if ( defined( 'IMILE_VERSION' ) ) {
			$this->version = IMILE_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		$this->plugin_name = 'imile';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Imile_Loader. Orchestrates the hooks of the plugin.
	 * - Imile_i18n. Defines internationalization functionality.
	 * - Imile_Admin. Defines all hooks for the admin area.
	 * - Imile_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-imile-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-imile-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-imile-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-imile-public.php';

		$this->loader = new Imile_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Imile_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Imile_i18n();

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

		$plugin_admin = new Imile_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		//$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'woocommerce_saved_order_items', 10, 4 );
		$this->loader->add_action( 'woocommerce_order_status_cancelled', $plugin_admin, 'woocommerce_order_status_cancelled', 21, 1 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_meta_box' );

		$this->loader->add_action( 'wp_ajax_imile_print_invoice', $plugin_admin,  'imile_print_invoice' );

		$this->loader->add_filter( 'manage_woocommerce_page_wc-orders_columns', $plugin_admin,  'manage_edit_shop_order_columns', 20 );
		$this->loader->add_action( 'manage_edit-shop_order_columns', $plugin_admin,  'manage_edit_shop_order_columns', 20 );
		
		$this->loader->add_action( 'manage_woocommerce_page_wc-orders_custom_column', $plugin_admin,  'custom_orders_list_column_content', 20, 2 );
		$this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin,  'custom_orders_list_column_content_shop_order', 20, 2 );
		
		$this->loader->add_action( 'woocommerce_process_woocommerce_page_wc-orders_meta', $plugin_admin, 'woocommerce_saved_order_items', 99, 2 );
		$this->loader->add_action( 'woocommerce_process_shop_order_meta', $plugin_admin, 'woocommerce_saved_order_items', 99, 2 );
		
		$this->loader->add_filter( 'bulk_actions-woocommerce_page_wc-orders', $plugin_admin, 'bulk_actions_edit_shop_order', 20, 1 );
		$this->loader->add_filter( 'bulk_actions-edit-shop_order', $plugin_admin, 'bulk_actions_edit_shop_order', 20, 1 );
		
		$this->loader->add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', $plugin_admin, 'handle_bulk_actions_edit_shop_order', 10, 3 );
		$this->loader->add_action( 'handle_bulk_actions-edit-shop_order', $plugin_admin, 'handle_bulk_actions_edit_shop_order', 10, 3 );
		
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices_shipping' );

	
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Imile_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'woocommerce_thankyou' );
		$this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public, 'woocommerce_order_details_after_order_table' );
		$this->loader->add_action( 'woocommerce_api_imilecallback', $plugin_public, 'imilecallback');

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
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Imile_Loader    Orchestrates the hooks of the plugin.
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
