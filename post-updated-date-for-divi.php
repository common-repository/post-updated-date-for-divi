<?php

/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @see               https://www.linknacional.com/
 * @since             1.0.0
 * @version           1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Post Updated Date for Divi
 * Plugin URI:        https://www.linknacional.com/wordpress/plugins/
 * Description:       Shows the post updated or modified date for divi blog posts. Will show only those posts are modified or updated.
 * Version:           1.0.5
 * Author:            Link Nacional
 * Author URI:        https://www.linknacional.com/
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       post-updated-date-for-divi
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    exit;
}

/*
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('LKN_PUDD_VERSION', '1.0.5');
define('LKN_PUDD_OPTIONS_VERSION', '1');
define('LKN_PUDD_SUPPORT_FORUM', 'https://www.linknacional.com.br/suporte');
define('LKN_PUDD_WP_VERSION', '4.0');
define('LKN_PUDD_WC_VERSION', '3.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-post-updated-date-for-divi-activator.php.
 */
function activate_lkn_post_updated_date_for_divi(): void {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-post-updated-date-for-divi-activator.php';
    Lkn_Post_Updated_Date_For_Divi_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-post-updated-date-for-divi-deactivator.php.
 */
function deactivate_lkn_post_updated_date_for_divi(): void {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-post-updated-date-for-divi-deactivator.php';
    Lkn_Post_Updated_Date_For_Divi_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_lkn_post_updated_date_for_divi' );
register_deactivation_hook( __FILE__, 'deactivate_lkn_post_updated_date_for_divi' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-post-updated-date-for-divi.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_lkn_post_updated_date_for_divi(): void {
    $plugin = new Lkn_Post_Updated_Date_For_Divi();
    $plugin->run();
}

run_lkn_post_updated_date_for_divi();
