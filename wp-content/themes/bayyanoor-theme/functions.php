<?php
/**
 * Bayyanoor Theme Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// 1. Theme Setup
function bayyanoor_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    register_nav_menus( array(
        'primary-menu' => __( 'Primary Menu', 'bayyanoor-theme' ),
        'footer-menu'  => __( 'Footer Menu', 'bayyanoor-theme' ),
    ) );
}
add_action( 'after_setup_theme', 'bayyanoor_theme_setup' );

// 2. Enqueue Scripts and Styles
function bayyanoor_enqueue_scripts() {
    $style_path = get_stylesheet_directory() . '/style.css';
    $version    = file_exists( $style_path ) ? filemtime( $style_path ) : '2.0.0';

    wp_enqueue_style( 'bayyanoor-style', get_stylesheet_uri(), array(), $version );
}
add_action( 'wp_enqueue_scripts', 'bayyanoor_enqueue_scripts' );

function bayyanoor_primary_menu_fallback() {
    ?>
    <ul>
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'bayyanoor-theme' ); ?></a></li>
        <li><a href="<?php echo esc_url( home_url( '/programs' ) ); ?>"><?php esc_html_e( 'Programs', 'bayyanoor-theme' ); ?></a></li>
        <li><a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>"><?php esc_html_e( 'Pricing', 'bayyanoor-theme' ); ?></a></li>
        <li><a href="<?php echo esc_url( home_url( '/about' ) ); ?>"><?php esc_html_e( 'About', 'bayyanoor-theme' ); ?></a></li>
        <li><a href="<?php echo esc_url( home_url( '/contact-us' ) ); ?>"><?php esc_html_e( 'Contact', 'bayyanoor-theme' ); ?></a></li>
    </ul>
    <?php
}

// 3. Include Auto-Page Generation Logic
require get_template_directory() . '/includes/theme-setup.php';
