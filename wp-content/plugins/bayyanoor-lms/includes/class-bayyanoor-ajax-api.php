<?php
/**
 * Bayyanoor Central AJAX API Controller
 * All frontend AJAX endpoints are registered here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Ajax_API {

    public static function init() {
        // Dashboard data
        add_action( 'wp_ajax_bayyanoor_get_dashboard_data', array( __CLASS__, 'get_dashboard_data' ) );

        // Mastery
        add_action( 'wp_ajax_bayyanoor_submit_quiz', array( __CLASS__, 'submit_quiz' ) );
        add_action( 'wp_ajax_bayyanoor_get_mastery_data', array( __CLASS__, 'get_mastery_data' ) );

        // Courses
        add_action( 'wp_ajax_bayyanoor_get_courses', array( __CLASS__, 'get_courses' ) );

        // Activity
        add_action( 'wp_ajax_bayyanoor_get_activity', array( __CLASS__, 'get_activity' ) );

        // Hifz
        add_action( 'wp_ajax_bayyanoor_log_hifz', array( __CLASS__, 'log_hifz' ) );
        add_action( 'wp_ajax_bayyanoor_get_hifz_progress', array( __CLASS__, 'get_hifz_progress' ) );

        // Badges
        add_action( 'wp_ajax_bayyanoor_get_badges', array( __CLASS__, 'get_badges' ) );

        // Streak
        add_action( 'wp_ajax_bayyanoor_get_streak', array( __CLASS__, 'get_streak' ) );
    }

    /**
     * GET DASHBOARD DATA — Aggregates all widgets into one response.
     */
    public static function get_dashboard_data() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'Not authenticated.' );
        }

        $user = wp_get_current_user();

        // Record daily activity
        Bayyanoor_Streaks::record_activity( $user_id );

        // Gather data
        $streak_data  = Bayyanoor_Streaks::get_streak( $user_id );
        $radar_data   = Bayyanoor_Mastery::get_radar_data( $user_id );
        $hifz_summary = Bayyanoor_Hifz::get_summary( $user_id );
        $badges       = self::get_user_badges( $user_id );
        $activity     = self::get_recent_activity( $user_id );
        $courses      = self::get_enrolled_courses( $user_id );

        wp_send_json_success( array(
            'user' => array(
                'display_name' => $user->display_name,
                'first_name'   => $user->first_name ? $user->first_name : $user->display_name,
                'initials'     => self::get_initials( $user ),
                'email'        => $user->user_email,
            ),
            'streak'  => $streak_data,
            'mastery' => $radar_data,
            'hifz'    => $hifz_summary,
            'badges'  => $badges,
            'activity' => $activity,
            'courses' => $courses,
        ) );
    }

    /**
     * SUBMIT QUIZ — Processes quiz results and updates mastery.
     */
    public static function submit_quiz() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id    = get_current_user_id();
        $skill_slug = isset( $_POST['skill_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['skill_slug'] ) ) : '';
        $score      = isset( $_POST['score'] ) ? floatval( $_POST['score'] ) : 0;

        if ( ! $user_id || empty( $skill_slug ) ) {
            wp_send_json_error( 'Invalid request.' );
        }

        $result = Bayyanoor_Mastery::update_mastery( $user_id, $skill_slug, $score );

        wp_send_json_success( array(
            'mastery' => $result,
            'streak'  => Bayyanoor_Streaks::get_streak( $user_id ),
        ) );
    }

    /**
     * GET MASTERY DATA — Returns radar chart data.
     */
    public static function get_mastery_data() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'Not authenticated.' );
        }

        wp_send_json_success( Bayyanoor_Mastery::get_radar_data( $user_id ) );
    }

    /**
     * GET COURSES — Returns enrolled courses with progress.
     */
    public static function get_courses() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'Not authenticated.' );
        }

        wp_send_json_success( self::get_enrolled_courses( $user_id ) );
    }

    /**
     * GET ACTIVITY — Returns recent activity.
     */
    public static function get_activity() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'Not authenticated.' );
        }

        wp_send_json_success( self::get_recent_activity( $user_id ) );
    }

    /**
     * LOG HIFZ — Records memorization progress.
     */
    public static function log_hifz() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id    = get_current_user_id();
        $surah_no   = isset( $_POST['surah_no'] )   ? intval( $_POST['surah_no'] )   : 0;
        $ayah_range = isset( $_POST['ayah_range'] )  ? sanitize_text_field( wp_unslash( $_POST['ayah_range'] ) ) : '';
        $status     = isset( $_POST['status'] )      ? sanitize_text_field( wp_unslash( $_POST['status'] ) )     : 'new_memorization';
        $quality    = isset( $_POST['quality'] )     ? intval( $_POST['quality'] )    : 0;

        if ( ! $user_id || ! $surah_no || empty( $ayah_range ) ) {
            wp_send_json_error( 'Invalid request.' );
        }

        Bayyanoor_Hifz::log_memorization( $user_id, $surah_no, $ayah_range, $status, $quality );

        wp_send_json_success( array(
            'message' => 'Hifz entry logged successfully!',
            'hifz'    => Bayyanoor_Hifz::get_summary( $user_id ),
            'streak'  => Bayyanoor_Streaks::get_streak( $user_id ),
        ) );
    }

    /**
     * GET HIFZ PROGRESS — Returns detailed surah-by-surah progress.
     */
    public static function get_hifz_progress() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'Not authenticated.' );
        }

        wp_send_json_success( Bayyanoor_Hifz::get_hifz_progress( $user_id ) );
    }

    /**
     * GET BADGES — Returns all user badges.
     */
    public static function get_badges() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'Not authenticated.' );
        }

        wp_send_json_success( self::get_user_badges( $user_id ) );
    }

    /**
     * GET STREAK — Returns streak data.
     */
    public static function get_streak() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'Not authenticated.' );
        }

        wp_send_json_success( Bayyanoor_Streaks::get_streak( $user_id ) );
    }

    /* =============================================
     * PRIVATE HELPERS
     * ============================================= */

    private static function get_initials( $user ) {
        $first = $user->first_name ? mb_substr( $user->first_name, 0, 1 ) : '';
        $last  = $user->last_name  ? mb_substr( $user->last_name, 0, 1 )  : '';
        if ( $first || $last ) {
            return strtoupper( $first . $last );
        }
        return strtoupper( mb_substr( $user->display_name, 0, 1 ) );
    }

    private static function get_user_badges( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bayyanoor_badges';

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT badge_slug, badge_name, badge_icon, awarded_at FROM $table WHERE user_id = %d ORDER BY awarded_at DESC",
            $user_id
        ) );

        $badges = array();
        foreach ( $results as $row ) {
            $badges[] = array(
                'slug'       => $row->badge_slug,
                'name'       => $row->badge_name,
                'icon'       => $row->badge_icon,
                'awarded_at' => $row->awarded_at,
            );
        }

        return $badges;
    }

    private static function get_recent_activity( $user_id ) {
        global $wpdb;
        $activities = array();

        // XP log as activity feed
        $xp_logs = $wpdb->get_results( $wpdb->prepare(
            "SELECT xp_amount, reason, logged_at FROM {$wpdb->prefix}bayyanoor_xp_log WHERE user_id = %d ORDER BY logged_at DESC LIMIT 10",
            $user_id
        ) );

        foreach ( $xp_logs as $log ) {
            $activities[] = array(
                'type'   => 'xp',
                'text'   => $log->reason,
                'xp'     => '+' . $log->xp_amount . ' XP',
                'time'   => human_time_diff( strtotime( $log->logged_at ), current_time( 'timestamp' ) ) . ' ago',
                'raw_time' => $log->logged_at,
            );
        }

        // Badges
        $badges = $wpdb->get_results( $wpdb->prepare(
            "SELECT badge_name, badge_icon, awarded_at FROM {$wpdb->prefix}bayyanoor_badges WHERE user_id = %d ORDER BY awarded_at DESC LIMIT 5",
            $user_id
        ) );

        foreach ( $badges as $badge ) {
            $activities[] = array(
                'type'   => 'badge',
                'text'   => 'Earned badge: ' . $badge->badge_name,
                'xp'     => '',
                'time'   => human_time_diff( strtotime( $badge->awarded_at ), current_time( 'timestamp' ) ) . ' ago',
                'raw_time' => $badge->awarded_at,
            );
        }

        // Sort by time descending
        usort( $activities, function( $a, $b ) {
            return strtotime( $b['raw_time'] ) - strtotime( $a['raw_time'] );
        } );

        // Remove raw_time before returning
        foreach ( $activities as &$act ) {
            unset( $act['raw_time'] );
        }

        return array_slice( $activities, 0, 10 );
    }

    private static function get_enrolled_courses( $user_id ) {
        // Get courses from CPT
        $courses_query = new WP_Query( array(
            'post_type'      => 'bn_course',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        $courses = array();
        $icons = array( 'QR', 'AR', 'IS', 'HF', 'BK', 'GD', 'RV', 'ST' );
        $i = 0;

        if ( $courses_query->have_posts() ) {
            while ( $courses_query->have_posts() ) {
                $courses_query->the_post();
                $post_id = get_the_ID();

                // Calculate progress from lessons (simplified)
                $lesson_count = (int) get_post_meta( $post_id, '_bayyanoor_lesson_count', true );
                $completed    = (int) get_user_meta( $user_id, '_bayyanoor_completed_' . $post_id, true );
                $lesson_count = $lesson_count > 0 ? $lesson_count : 20; // Default
                $progress     = $lesson_count > 0 ? min( 100, round( ( $completed / $lesson_count ) * 100 ) ) : 0;

                $courses[] = array(
                    'id'       => $post_id,
                    'title'    => get_the_title(),
                    'icon'     => $icons[ $i % count( $icons ) ],
                    'progress' => $progress,
                    'lessons'  => $lesson_count,
                    'link'     => get_permalink(),
                );
                $i++;
            }
            wp_reset_postdata();
        }

        // If no courses exist in CPT, return demo courses
        if ( empty( $courses ) ) {
            $courses = array(
                array( 'id' => 0, 'title' => 'Tajweed Essentials', 'icon' => 'QR', 'progress' => 0, 'lessons' => 45, 'link' => '#' ),
                array( 'id' => 0, 'title' => 'Arabic Grammar L1', 'icon' => 'AR', 'progress' => 0, 'lessons' => 38, 'link' => '#' ),
                array( 'id' => 0, 'title' => 'Islamic History: Seerah', 'icon' => 'IS', 'progress' => 0, 'lessons' => 52, 'link' => '#' ),
                array( 'id' => 0, 'title' => 'Stories of the Prophets', 'icon' => 'ST', 'progress' => 0, 'lessons' => 60, 'link' => '#' ),
            );
        }

        return $courses;
    }
}
Bayyanoor_Ajax_API::init();
