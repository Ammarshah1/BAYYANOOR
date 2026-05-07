<?php
/**
 * Bayyanoor Hifz (Quran Memorization) Tracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Hifz {

    /**
     * Surah names for display.
     */
    private static $surahs = array(
        1 => 'Al-Fatiha', 2 => 'Al-Baqarah', 3 => 'Aal-e-Imran', 4 => 'An-Nisa',
        5 => 'Al-Ma\'idah', 6 => 'Al-An\'am', 7 => 'Al-A\'raf', 8 => 'Al-Anfal',
        9 => 'At-Taubah', 10 => 'Yunus', 11 => 'Hud', 12 => 'Yusuf',
        13 => 'Ar-Ra\'d', 14 => 'Ibrahim', 15 => 'Al-Hijr', 16 => 'An-Nahl',
        17 => 'Al-Isra', 18 => 'Al-Kahf', 19 => 'Maryam', 20 => 'Ta-Ha',
        21 => 'Al-Anbiya', 22 => 'Al-Hajj', 23 => 'Al-Mu\'minun', 24 => 'An-Nur',
        25 => 'Al-Furqan', 26 => 'Ash-Shu\'ara', 27 => 'An-Naml', 28 => 'Al-Qasas',
        29 => 'Al-Ankabut', 30 => 'Ar-Rum', 36 => 'Ya-Sin', 55 => 'Ar-Rahman',
        56 => 'Al-Waqi\'ah', 67 => 'Al-Mulk', 78 => 'An-Naba', 93 => 'Ad-Duha',
        94 => 'Ash-Sharh', 95 => 'At-Tin', 96 => 'Al-Alaq', 97 => 'Al-Qadr',
        98 => 'Al-Bayyinah', 99 => 'Az-Zalzalah', 100 => 'Al-Adiyat',
        101 => 'Al-Qari\'ah', 102 => 'At-Takathur', 103 => 'Al-Asr',
        104 => 'Al-Humazah', 105 => 'Al-Fil', 106 => 'Quraysh',
        107 => 'Al-Ma\'un', 108 => 'Al-Kawthar', 109 => 'Al-Kafirun',
        110 => 'An-Nasr', 111 => 'Al-Masad', 112 => 'Al-Ikhlas',
        113 => 'Al-Falaq', 114 => 'An-Nas',
    );

    /**
     * Log a memorization entry.
     *
     * @param int    $user_id
     * @param int    $surah_no
     * @param string $ayah_range  e.g., "1-5" or "1-7"
     * @param string $status_slug e.g., 'new_memorization', 'review', 'solid', 'needs_review'
     * @param int    $quality     0-100 quality score
     */
    public static function log_memorization( $user_id, $surah_no, $ayah_range, $status_slug, $quality = 0 ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'bayyanoor_hifz_logs',
            array(
                'user_id'       => $user_id,
                'surah_no'      => $surah_no,
                'ayah_range'    => $ayah_range,
                'status_slug'   => $status_slug,
                'quality_score' => $quality,
            ),
            array( '%d', '%d', '%s', '%s', '%d' )
        );

        // XP for memorization
        $xp = 0;
        switch ( $status_slug ) {
            case 'new_memorization':
                $xp = 25;
                break;
            case 'review':
                $xp = 10;
                break;
            case 'solid':
                $xp = 15;
                break;
        }

        if ( $xp > 0 ) {
            Bayyanoor_Streaks::add_xp( $user_id, $xp, 'Hifz: Surah ' . $surah_no . ' (Ayah ' . $ayah_range . ')' );
        }

        // Record activity for streak
        Bayyanoor_Streaks::record_activity( $user_id );

        // Badge: first memorization
        self::check_badges( $user_id );
    }

    /**
     * Get Hifz progress for a user — grouped by surah.
     */
    public static function get_hifz_progress( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bayyanoor_hifz_logs';

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT surah_no, ayah_range, status_slug, quality_score, MAX(logged_at) as last_review
             FROM $table
             WHERE user_id = %d
             GROUP BY surah_no, ayah_range
             ORDER BY surah_no ASC, logged_at DESC",
            $user_id
        ) );

        $progress = array();
        foreach ( $results as $row ) {
            $surah_name = isset( self::$surahs[ $row->surah_no ] ) ? self::$surahs[ $row->surah_no ] : 'Surah ' . $row->surah_no;
            if ( ! isset( $progress[ $row->surah_no ] ) ) {
                $progress[ $row->surah_no ] = array(
                    'surah_no'   => (int) $row->surah_no,
                    'surah_name' => $surah_name,
                    'sections'   => array(),
                );
            }
            $progress[ $row->surah_no ]['sections'][] = array(
                'ayah_range'    => $row->ayah_range,
                'status'        => $row->status_slug,
                'quality'       => (int) $row->quality_score,
                'last_review'   => $row->last_review,
            );
        }

        return array_values( $progress );
    }

    /**
     * Get summary stats for the dashboard widget.
     */
    public static function get_summary( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bayyanoor_hifz_logs';

        $total_entries = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(surah_no, '-', ayah_range)) FROM $table WHERE user_id = %d", $user_id
        ) );

        $surahs_started = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT surah_no) FROM $table WHERE user_id = %d", $user_id
        ) );

        $solid_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(surah_no, '-', ayah_range)) FROM $table WHERE user_id = %d AND status_slug = 'solid'", $user_id
        ) );

        return array(
            'total_sections'  => $total_entries,
            'surahs_started'  => $surahs_started,
            'solid_sections'  => $solid_count,
        );
    }

    /**
     * Get surah name by number.
     */
    public static function get_surah_name( $surah_no ) {
        return isset( self::$surahs[ $surah_no ] ) ? self::$surahs[ $surah_no ] : 'Surah ' . $surah_no;
    }

    private static function check_badges( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bayyanoor_hifz_logs';
        $badge_table = $wpdb->prefix . 'bayyanoor_badges';

        // First memorization badge
        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d", $user_id
        ) );

        if ( $count === 1 ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $badge_table WHERE user_id = %d AND badge_slug = 'hafiz_starter'", $user_id
            ) );
            if ( ! $exists ) {
                $wpdb->insert( $badge_table, array(
                    'user_id'    => $user_id,
                    'badge_slug' => 'hafiz_starter',
                    'badge_name' => 'Hafiz Starter',
                    'badge_icon' => 'HF',
                ), array( '%d', '%s', '%s', '%s' ) );
            }
        }

        // 10 sections badge
        $sections = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(surah_no, '-', ayah_range)) FROM $table WHERE user_id = %d AND status_slug = 'solid'", $user_id
        ) );
        if ( $sections >= 10 ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $badge_table WHERE user_id = %d AND badge_slug = 'solid_10'", $user_id
            ) );
            if ( ! $exists ) {
                $wpdb->insert( $badge_table, array(
                    'user_id'    => $user_id,
                    'badge_slug' => 'solid_10',
                    'badge_name' => '10 Solid Passages',
                    'badge_icon' => '10',
                ), array( '%d', '%s', '%s', '%s' ) );
            }
        }
    }
}
