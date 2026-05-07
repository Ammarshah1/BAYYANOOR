<?php
/**
 * Bayyanoor Database Definitions and Migrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Bayyanoor_DB {

    const DB_VERSION = '2.0.0';

    /**
     * Run the database migrations (dbDelta).
     */
    public static function run_migrations() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        self::create_mastery_table( $charset_collate );
        self::create_streaks_table( $charset_collate );
        self::create_hifz_logs_table( $charset_collate );
        self::create_ai_logs_table( $charset_collate );
        self::create_badges_table( $charset_collate );
        self::create_xp_log_table( $charset_collate );

        update_option( 'bayyanoor_db_version', self::DB_VERSION );
    }

    private static function create_mastery_table( $charset_collate ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_mastery';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            skill_slug varchar(100) NOT NULL,
            mastery_level tinyint NOT NULL DEFAULT 0,
            last_tested datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            attempts int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY user_skill (user_id, skill_slug),
            KEY user_id (user_id),
            KEY skill_slug (skill_slug)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    private static function create_streaks_table( $charset_collate ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_streaks';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            current_streak int(11) NOT NULL DEFAULT 0,
            longest_streak int(11) NOT NULL DEFAULT 0,
            last_activity_date date NOT NULL,
            total_xp int(11) NOT NULL DEFAULT 0,
            current_level int(11) NOT NULL DEFAULT 1,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    private static function create_hifz_logs_table( $charset_collate ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_hifz_logs';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            surah_no int(11) NOT NULL,
            ayah_range varchar(50) NOT NULL,
            status_slug varchar(50) NOT NULL,
            quality_score tinyint DEFAULT 0,
            logged_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY surah_no (surah_no)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    private static function create_ai_logs_table( $charset_collate ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_ai_logs';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            query text NOT NULL,
            response_summary text NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    private static function create_badges_table( $charset_collate ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_badges';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            badge_slug varchar(100) NOT NULL,
            badge_name varchar(200) NOT NULL,
            badge_icon varchar(10) DEFAULT 'BN',
            awarded_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY user_badge (user_id, badge_slug),
            KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    private static function create_xp_log_table( $charset_collate ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_xp_log';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            xp_amount int(11) NOT NULL,
            reason varchar(200) NOT NULL,
            logged_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta( $sql );
    }
}
