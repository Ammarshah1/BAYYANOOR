<?php
/**
 * Bayyanoor Zero-Touch Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Bayyanoor_Setup {

    /**
     * Programmatically create necessary pages and assign shortcodes.
     */
    public static function run_setup() {
        self::create_page( 'Bayyanoor App', 'bayyanoor-app', '[bayyanoor_dashboard]' );
        self::create_page( 'Bayyanoor Login', 'bayyanoor-login', '[bayyanoor_login_form]' );
        self::create_page( 'Bayyanoor AI Tutor', 'bayyanoor-ai-tutor', '[bayyanoor_ai_interface]' );
        self::create_page( 'Bayyanoor Courses', 'bayyanoor-courses', '[bayyanoor_courses_page]' );
        self::create_page( 'Bayyanoor Progress', 'bayyanoor-progress', '[bayyanoor_progress_page]' );
        self::create_page( 'Bayyanoor Support', 'bayyanoor-support', '[bayyanoor_support_page]' );
    }

    private static function create_page( $title, $slug, $content ) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            $page_id = wp_insert_post( array(
                'post_title'     => $title,
                'post_name'      => $slug,
                'post_content'   => $content,
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
            ) );

            // Future: we can flag these pages using post meta to ensure they always load the app-shell template
            if ( $page_id && ! is_wp_error( $page_id ) ) {
                update_post_meta( $page_id, '_bayyanoor_app_page', 'yes' );
            }
        }
    }
}
