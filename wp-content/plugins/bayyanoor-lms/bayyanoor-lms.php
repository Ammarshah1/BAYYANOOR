<?php
/**
 * Plugin Name: Bayyanoor LMS
 * Plugin URI: https://bayyanoor.com
 * Description: Standalone Mastery-Based Quranic LMS (Native Tech Stack).
 * Version: 2.0.0
 * Author: Sikandar Hayat Baba
 * License: GPLv2 or later
 * Text Domain: bayyanoor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'BAYYANOOR_VERSION', '2.0.0' );
define( 'BAYYANOOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BAYYANOOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Initialize the plugin classes.
 */
function bayyanoor_lms_init() {
	// Core
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-db.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-cpt.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-setup.php';

	// Engines
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-streaks.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-mastery.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-hifz.php';

	// Frontend & Features
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-frontend.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-pages.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-auth.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-ai.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-gamification.php';
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-ajax-api.php';

	// Admin
	require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-admin.php';
}
add_action( 'plugins_loaded', 'bayyanoor_lms_init' );

/**
 * Activation hook: Run DB migration, CPT flush, and zero-touch setup.
 */
function bayyanoor_lms_activate() {
    require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-db.php';
    Bayyanoor_DB::run_migrations();
    
    // Register CPTs before flush so rewrite rules are captured
    require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-cpt.php';
    Bayyanoor_CPT::register_post_types();
    Bayyanoor_CPT::register_taxonomies();
    flush_rewrite_rules();

    // Run zero-touch setup
    require_once BAYYANOOR_PLUGIN_DIR . 'includes/class-bayyanoor-setup.php';
    Bayyanoor_Setup::run_setup();
}
register_activation_hook( __FILE__, 'bayyanoor_lms_activate' );

/**
 * Deactivation hook.
 */
function bayyanoor_lms_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'bayyanoor_lms_deactivate' );
