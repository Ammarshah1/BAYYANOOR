<?php
/**
 * Bayyanoor Frontend App-Shell Interceptor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Frontend {

    public static function init() {
        add_filter( 'template_include', array( __CLASS__, 'intercept_app_shell' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    private static $app_pages = array(
        'bayyanoor-app', 'bayyanoor-login', 'bayyanoor-ai-tutor',
        'bayyanoor-courses', 'bayyanoor-progress', 'bayyanoor-support',
    );

    /**
     * Intercept page load for app pages and use our custom shell.
     */
    public static function intercept_app_shell( $template ) {
        foreach ( self::$app_pages as $slug ) {
            if ( is_page( $slug ) ) {
                $new_template = BAYYANOOR_PLUGIN_DIR . 'templates/app-shell.php';
                if ( file_exists( $new_template ) ) {
                    return $new_template;
                }
            }
        }
        return $template;
    }

    /**
     * Enqueue the App-Shell specific assets.
     */
    public static function enqueue_assets() {
        $is_app_page = false;
        foreach ( self::$app_pages as $slug ) {
            if ( is_page( $slug ) ) {
                $is_app_page = true;
                break;
            }
        }
        if ( ! $is_app_page ) return;

        $style_path  = BAYYANOOR_PLUGIN_DIR . 'assets/css/app.css';
        $script_path = BAYYANOOR_PLUGIN_DIR . 'assets/js/app.js';
        $style_ver   = file_exists( $style_path ) ? filemtime( $style_path ) : BAYYANOOR_VERSION;
        $script_ver  = file_exists( $script_path ) ? filemtime( $script_path ) : BAYYANOOR_VERSION;

        wp_enqueue_style( 'bayyanoor-app-style', BAYYANOOR_PLUGIN_URL . 'assets/css/app.css', array(), $style_ver );
        wp_enqueue_script( 'bayyanoor-app-script', BAYYANOOR_PLUGIN_URL . 'assets/js/app.js', array( 'jquery' ), $script_ver, true );

        wp_localize_script( 'bayyanoor-app-script', 'bayyanoorAjax', array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'bayyanoor_app_nonce' ),
            'home_url'  => home_url(),
            'logged_in' => is_user_logged_in(),
        ) );
    }
}
Bayyanoor_Frontend::init();
