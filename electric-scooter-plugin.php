<?php
/**
 * Plugin Name: Electric Scooter Plugin
 * Plugin URI: https://example.com/electric-scooter-plugin
 * Description: A plugin to manage electric scooter rentals.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: electric-scooter-plugin
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Current plugin version.
 */
define( 'ELECTRIC_SCOOTER_PLUGIN_VERSION', '1.0.0' );
define( 'ELECTRIC_SCOOTER_DAILY_BINARY_CAP', 50000 ); // Example: ₹50,000 cap

/**
 * The code that runs during plugin activation.
 * This function will add a custom WordPress role 'member'.
 */
function activate_electric_scooter_plugin() {
    // Ensure the main plugin class is loaded if it contains any activation logic,
    // though for add_role it's a core WordPress function.
    // require_once plugin_dir_path( __FILE__ ) . 'includes/class-electric-scooter-plugin.php';

    add_role(
        'member', // Role slug
        __( 'Member', 'electric-scooter-plugin' ), // Display name
        array( // Capabilities
            'read' => true, // Basic capability for all users
            // Add other capabilities specific to 'member' role as needed later
            // e.g., 'upload_files' => true, if members need to upload KYC docs themselves
        )
    );
}
register_activation_hook( __FILE__, 'activate_electric_scooter_plugin' );

/**
 * The code that runs during plugin deactivation.
 * This function will remove the custom WordPress role 'member'.
 */
function deactivate_electric_scooter_plugin() {
    // Ensure the main plugin class is loaded if it contains any deactivation logic.
    // require_once plugin_dir_path( __FILE__ ) . 'includes/class-electric-scooter-plugin.php';

    remove_role( 'member' );
}
register_deactivation_hook( __FILE__, 'deactivate_electric_scooter_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-electric-scooter-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_electric_scooter_plugin() {
    $plugin = new Electric_Scooter_Plugin();
    $plugin->run();
}
run_electric_scooter_plugin();
