<?php
/**
 * Zero-Touch Theme Setup: Auto-generating pages.
 */

function bayyanoor_auto_generate_pages() {
    $pages = array(
        'Home' => array( 'slug' => 'home', 'content' => 'Welcome to Bayyanoor.' ),
        'Programs & Curriculum' => array( 'slug' => 'programs', 'content' => '[bayyanoor_programs_overview]' ),
        'Quran Classes' => array( 'slug' => 'quran-classes', 'content' => 'Learn Quran with expert tutors.' ),
        'Arabic Language Classes' => array( 'slug' => 'arabic-language-classes', 'content' => 'Master Arabic reading and writing.' ),
        'Islamic Studies' => array( 'slug' => 'islamic-studies', 'content' => 'Comprehensive Islamic studies from Pre-K to High School.' ),
        'Pricing' => array( 'slug' => 'pricing', 'content' => 'Affordable plans for your family.' ),
        'About Us' => array( 'slug' => 'about', 'content' => 'The vision behind Bayyanoor.' ),
        'Contact Us' => array( 'slug' => 'contact-us', 'content' => 'Get in touch with our team.' ),
        'FAQ' => array( 'slug' => 'faq', 'content' => 'Frequently Asked Questions.' )
    );

    foreach ( $pages as $title => $data ) {
        $page_check = get_page_by_path( $data['slug'] );
        if ( ! isset( $page_check->ID ) ) {
            $new_page_id = wp_insert_post( array(
                'post_title'     => $title,
                'post_name'      => $data['slug'],
                'post_content'   => $data['content'],
                'post_status'    => 'publish',
                'post_type'      => 'page',
            ) );
        }
    }
    
    // Set "Home" as the front page
    $home = get_page_by_path('home');
    if ( $home ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $home->ID );
    }
    
    // Attempt to auto-create menus
    $menu_name = 'Primary Menu';
    $menu_location = 'primary-menu';
    $menu_exists = wp_get_nav_menu_object( $menu_name );
    
    if ( ! $menu_exists ) {
        $menu_id = wp_create_nav_menu($menu_name);
        
        // Add items
        wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => 'Home', 'menu-item-url' => home_url('/'), 'menu-item-status' => 'publish'));
        wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => 'Programs', 'menu-item-url' => home_url('/programs/'), 'menu-item-status' => 'publish'));
        wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => 'Pricing', 'menu-item-url' => home_url('/pricing/'), 'menu-item-status' => 'publish'));
        wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => 'About', 'menu-item-url' => home_url('/about/'), 'menu-item-status' => 'publish'));
        wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => 'Contact Us', 'menu-item-url' => home_url('/contact-us/'), 'menu-item-status' => 'publish'));
        
        $locations = get_theme_mod('nav_menu_locations');
        $locations[$menu_location] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }
}
add_action( 'after_switch_theme', 'bayyanoor_auto_generate_pages' );
