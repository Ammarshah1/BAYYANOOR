<?php
/**
 * Bayyanoor Custom Post Types and Taxonomies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Bayyanoor_CPT {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_types' ) );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
    }

    public static function register_post_types() {
        $course_labels = array(
            'name'                  => _x( 'Subjects', 'Post Type General Name', 'bayyanoor' ),
            'singular_name'         => _x( 'Subject', 'Post Type Singular Name', 'bayyanoor' ),
            'menu_name'             => __( 'Subjects', 'bayyanoor' ),
            'name_admin_bar'        => __( 'Subject', 'bayyanoor' ),
            'archives'              => __( 'Subject Archives', 'bayyanoor' ),
            'attributes'            => __( 'Subject Attributes', 'bayyanoor' ),
            'parent_item_colon'     => __( 'Parent Subject:', 'bayyanoor' ),
            'all_items'             => __( 'All Subjects', 'bayyanoor' ),
            'add_new_item'          => __( 'Add New Subject', 'bayyanoor' ),
            'add_new'               => __( 'Add New', 'bayyanoor' ),
            'new_item'              => __( 'New Subject', 'bayyanoor' ),
            'edit_item'             => __( 'Edit Subject', 'bayyanoor' ),
            'update_item'           => __( 'Update Subject', 'bayyanoor' ),
            'view_item'             => __( 'View Subject', 'bayyanoor' ),
            'view_items'            => __( 'View Subjects', 'bayyanoor' ),
            'search_items'          => __( 'Search Subject', 'bayyanoor' ),
            'not_found'             => __( 'Not found', 'bayyanoor' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'bayyanoor' ),
        );
        $course_args = array(
            'label'                 => __( 'Subject', 'bayyanoor' ),
            'description'           => __( 'Bayyanoor Subjects/Courses', 'bayyanoor' ),
            'labels'                => $course_labels,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
            'taxonomies'            => array( 'knowledge_stream', 'difficulty_level' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-welcome-learn-more',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
        );
        register_post_type( 'bn_course', $course_args );

        $lesson_labels = array(
            'name'                  => _x( 'Lessons', 'Post Type General Name', 'bayyanoor' ),
            'singular_name'         => _x( 'Lesson', 'Post Type Singular Name', 'bayyanoor' ),
            'menu_name'             => __( 'Lessons', 'bayyanoor' ),
        );
        $lesson_args = array(
            'label'                 => __( 'Lesson', 'bayyanoor' ),
            'labels'                => $lesson_labels,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=bn_course',
            'has_archive'           => false,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        register_post_type( 'bn_lesson', $lesson_args );

        $skill_labels = array(
            'name'                  => _x( 'Skills', 'Post Type General Name', 'bayyanoor' ),
            'singular_name'         => _x( 'Skill', 'Post Type Singular Name', 'bayyanoor' ),
            'menu_name'             => __( 'Skills', 'bayyanoor' ),
        );
        $skill_args = array(
            'label'                 => __( 'Skill', 'bayyanoor' ),
            'description'           => __( 'Granular Tajweed/Grammar skills', 'bayyanoor' ),
            'labels'                => $skill_labels,
            'supports'              => array( 'title', 'editor', 'custom-fields' ),
            'taxonomies'            => array( 'knowledge_stream' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=bn_course',
            'has_archive'           => false,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        register_post_type( 'bn_skill', $skill_args );
    }

    public static function register_taxonomies() {
        $diff_labels = array(
            'name'                       => _x( 'Difficulty Levels', 'Taxonomy General Name', 'bayyanoor' ),
            'singular_name'              => _x( 'Difficulty Level', 'Taxonomy Singular Name', 'bayyanoor' ),
        );
        $diff_args = array(
            'labels'                     => $diff_labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
        );
        register_taxonomy( 'difficulty_level', array( 'bn_course', 'bn_lesson' ), $diff_args );

        $stream_labels = array(
            'name'                       => _x( 'Knowledge Streams', 'Taxonomy General Name', 'bayyanoor' ),
            'singular_name'              => _x( 'Knowledge Stream', 'Taxonomy Singular Name', 'bayyanoor' ),
        );
        $stream_args = array(
            'labels'                     => $stream_labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
        );
        register_taxonomy( 'knowledge_stream', array( 'bn_course', 'bn_skill' ), $stream_args );
    }
}
Bayyanoor_CPT::init();
