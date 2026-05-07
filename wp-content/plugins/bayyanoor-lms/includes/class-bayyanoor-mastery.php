<?php
/**
 * Bayyanoor Mastery Controller
 * Handles skill mastery tracking with XP integration.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Mastery {

    /**
     * Mastery level labels.
     */
    const LEVELS = array(
        0 => 'Novice',
        1 => 'Learner',
        2 => 'Practitioner',
        3 => 'Proficient',
        4 => 'Master',
    );

    const XP_LEVEL_UP = 50;
    const XP_QUIZ     = 15;

    /**
     * Updates the mastery level for a specific skill.
     * If score > 90%, mastery increases. Otherwise attempts are recorded.
     */
    public static function update_mastery( $user_id, $skill_slug, $score ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_mastery';

        $record = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND skill_slug = %s",
            $user_id,
            $skill_slug
        ) );

        $current_level = $record ? (int) $record->mastery_level : 0;
        $attempts      = $record ? (int) $record->attempts : 0;
        $attempts++;

        $new_level = $current_level;
        $leveled_up = false;

        if ( $score > 90 && $current_level < 4 ) {
            $new_level = $current_level + 1;
            $leveled_up = true;
        }

        if ( $record ) {
            $wpdb->update(
                $table_name,
                array(
                    'mastery_level' => $new_level,
                    'last_tested'   => current_time( 'mysql', 1 ),
                    'attempts'      => $attempts,
                ),
                array( 'id' => $record->id ),
                array( '%d', '%s', '%d' ),
                array( '%d' )
            );
        } else {
            $wpdb->insert( $table_name, array(
                'user_id'       => $user_id,
                'skill_slug'    => $skill_slug,
                'mastery_level' => $new_level,
                'last_tested'   => current_time( 'mysql', 1 ),
                'attempts'      => $attempts,
            ), array( '%d', '%s', '%d', '%s', '%d' ) );
        }

        // XP awards
        if ( class_exists( 'Bayyanoor_Streaks' ) ) {
            Bayyanoor_Streaks::add_xp( $user_id, self::XP_QUIZ, 'Quiz completed: ' . $skill_slug );

            if ( $leveled_up ) {
                Bayyanoor_Streaks::add_xp( $user_id, self::XP_LEVEL_UP, 'Mastery level up: ' . $skill_slug . ' -> ' . self::get_level_label( $new_level ) );
            }

            Bayyanoor_Streaks::record_activity( $user_id );
        }

        // Check for mastery badges
        self::check_mastery_badges( $user_id );

        return array(
            'level'      => $new_level,
            'label'      => self::get_level_label( $new_level ),
            'leveled_up' => $leveled_up,
            'attempts'   => $attempts,
        );
    }

    /**
     * Get all mastery data for a user (for radar chart).
     */
    public static function get_user_mastery( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_mastery';

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT skill_slug, mastery_level, last_tested, attempts FROM $table_name WHERE user_id = %d ORDER BY skill_slug",
            $user_id
        ) );

        $mastery = array();
        foreach ( $results as $row ) {
            $mastery[ $row->skill_slug ] = array(
                'level'       => (int) $row->mastery_level,
                'label'       => self::get_level_label( (int) $row->mastery_level ),
                'last_tested' => $row->last_tested,
                'attempts'    => (int) $row->attempts,
                'percentage'  => ( (int) $row->mastery_level / 4 ) * 100,
            );
        }

        return $mastery;
    }

    /**
     * Get mastery for a specific skill.
     */
    public static function get_skill_mastery( $user_id, $skill_slug ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bayyanoor_mastery';

        $record = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND skill_slug = %s",
            $user_id,
            $skill_slug
        ) );

        if ( ! $record ) {
            return array(
                'level'      => 0,
                'label'      => 'Novice',
                'attempts'   => 0,
                'percentage' => 0,
            );
        }

        return array(
            'level'       => (int) $record->mastery_level,
            'label'       => self::get_level_label( (int) $record->mastery_level ),
            'attempts'    => (int) $record->attempts,
            'percentage'  => ( (int) $record->mastery_level / 4 ) * 100,
            'last_tested' => $record->last_tested,
        );
    }

    /**
     * Get radar chart data formatted for the dashboard canvas.
     */
    public static function get_radar_data( $user_id ) {
        $default_skills = array(
            'tajweed'      => 'Tajweed',
            'vocabulary'   => 'Vocabulary',
            'memorization' => 'Memorization',
            'tafseer'      => 'Tafseer',
            'grammar'      => 'Grammar',
        );

        $mastery = self::get_user_mastery( $user_id );

        $labels = array();
        $data   = array();

        foreach ( $default_skills as $slug => $label ) {
            $labels[] = $label;
            $data[]   = isset( $mastery[ $slug ] ) ? $mastery[ $slug ]['percentage'] : 0;
        }

        return array(
            'labels' => $labels,
            'data'   => $data,
        );
    }

    /**
     * Get level label string.
     */
    public static function get_level_label( $level ) {
        return isset( self::LEVELS[ $level ] ) ? self::LEVELS[ $level ] : 'Unknown';
    }

    /**
     * Check and award mastery-specific badges.
     */
    private static function check_mastery_badges( $user_id ) {
        global $wpdb;

        $mastery_table = $wpdb->prefix . 'bayyanoor_mastery';
        $badge_table   = $wpdb->prefix . 'bayyanoor_badges';

        // First mastery
        $total = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $mastery_table WHERE user_id = %d AND mastery_level >= 1", $user_id
        ) );

        if ( $total >= 1 ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $badge_table WHERE user_id = %d AND badge_slug = 'first_mastery'", $user_id
            ) );
            if ( ! $exists ) {
                $wpdb->insert( $badge_table, array(
                    'user_id'    => $user_id,
                    'badge_slug' => 'first_mastery',
                    'badge_name' => 'First Mastery',
                    'badge_icon' => 'M1',
                ), array( '%d', '%s', '%s', '%s' ) );
            }
        }

        // Master level (level 4)
        $masters = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $mastery_table WHERE user_id = %d AND mastery_level = 4", $user_id
        ) );

        if ( $masters >= 1 ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $badge_table WHERE user_id = %d AND badge_slug = 'true_master'", $user_id
            ) );
            if ( ! $exists ) {
                $wpdb->insert( $badge_table, array(
                    'user_id'    => $user_id,
                    'badge_slug' => 'true_master',
                    'badge_name' => 'True Master',
                    'badge_icon' => 'M4',
                ), array( '%d', '%s', '%s', '%s' ) );
            }
        }
    }
}
