<?php

class Electric_Scooter_Plugin {

    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $plugin_public;
    protected $plugin_admin;

    public function __construct() {
        if ( defined( 'ELECTRIC_SCOOTER_PLUGIN_VERSION' ) ) {
            $this->version = ELECTRIC_SCOOTER_PLUGIN_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'electric-scooter-plugin';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-electric-scooter-plugin-loader.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-electric-scooter-plugin-public.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-electric-scooter-plugin-admin.php';

        $this->loader = new Electric_Scooter_Plugin_Loader();
        $this->plugin_public = new Electric_Scooter_Plugin_Public( $this->get_plugin_name(), $this->get_version() );
        $this->plugin_admin = new Electric_Scooter_Plugin_Admin( $this->get_plugin_name(), $this->get_version() );
    }

    private function set_locale() {
        // Hook into the 'plugins_loaded' action hook to load the text domain.
        // The 'plugins_loaded' hook is a good place for this as it ensures all plugins are loaded.
        $this->loader->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain' );
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            $this->plugin_name,
            false, // deprecated
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' // languages directory
        );
    }

    private function define_admin_hooks() {
        // Enqueue admin styles and scripts (if any)
        $this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts' );

        // Add custom columns to Users list table
        $this->loader->add_filter( 'manage_users_columns', $this->plugin_admin, 'add_custom_user_columns' );
        $this->loader->add_action( 'manage_users_custom_column', $this->plugin_admin, 'display_custom_user_column_data', 10, 3 );
        $this->loader->add_filter( 'manage_users_sortable_columns', $this->plugin_admin, 'make_custom_user_columns_sortable' );

        // If implementing custom sorting for meta columns:
        // $this->loader->add_action( 'pre_get_users', $this->plugin_admin, 'custom_user_column_orderby' );
    }

    private function define_public_hooks() {
        // Enqueue public-facing stylesheets and JavaScript.
        $this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts' );

        // Register shortcodes
        $this->loader->add_action( 'init', $this->plugin_public, 'register_shortcode' );

        // Handle registration form submission
        // 'init' is used because it fires before headers are sent, allowing for redirects if needed.
        // It also fires early enough to process POST data before most of WordPress sets up.
        $this->loader->add_action( 'init', $this->plugin_public, 'handle_registration' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
}
