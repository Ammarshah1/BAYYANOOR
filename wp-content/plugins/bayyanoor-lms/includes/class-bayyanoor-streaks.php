<?php
/**
 * Bayyanoor Streak Tracking Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Streaks {

    const XP_DAILY_LOGIN      = 10;
    const XP_STREAK_7_BONUS   = 50;
    const XP_STREAK_30_BONUS  = 200;
    const XP_STREAK_100_BONUS = 1000;
    const XP_PER_LEVEL        = 500;

    /**
     * Record a learning activity and update streak.
     */
    public static function record_activity( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bayyanoor_streaks';
        $today = current_time( 'Y-m-d' );

        $record = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d", $user_id
        ) );

        if ( ! $record ) {
            // First activity ever
            $wpdb->insert( $table, array(
                'user_id'            => $user_id,
                'current_streak'     => 1,
                'longest_streak'     => 1,
                'last_activity_date' => $today,
                'total_xp'           => self::XP_DAILY_LOGIN,
                'current_level'      => 1,
            ), array( '%d', '%d', '%d', '%s', '%d', '%d' ) );

            self::log_xp( $user_id, self::XP_DAILY_LOGIN, 'First activity' );
            return;
        }

        $last_date = $record->last_activity_date;

        // Already recorded today
        if ( $last_date === $today ) {
            return;
        }

        $yesterday = wp_date( 'Y-m-d', strtotime( '-1 day' ) );
        $xp_earned = self::XP_DAILY_LOGIN;

        if ( $last_date === $yesterday ) {
            // Continue streak
            $new_streak = (int) $record->current_streak + 1;
        } else {
            // Streak broken — reset
            $new_streak = 1;
        }

        $longest = max( (int) $record->longest_streak, $new_streak );
        $total_xp = (int) $record->total_xp + $xp_earned;

        // Milestone bonuses
        if ( $new_streak === 7 ) {
            $total_xp += self::XP_STREAK_7_BONUS;
            self::log_xp( $user_id, self::XP_STREAK_7_BONUS, '7-day streak bonus' );
            self::award_badge_if_new( $user_id, 'streak_7', '7-Day Streak', '7D' );
        }
        if ( $new_streak === 30 ) {
            $total_xp += self::XP_STREAK_30_BONUS;
            self::log_xp( $user_id, self::XP_STREAK_30_BONUS, '30-day streak bonus' );
            self::award_badge_if_new( $user_id, 'streak_30', '30-Day Streak', '30D' );
        }
        if ( $new_streak === 100 ) {
            $total_xp += self::XP_STREAK_100_BONUS;
            self::log_xp( $user_id, self::XP_STREAK_100_BONUS, '100-day streak bonus' );
            self::award_badge_if_new( $user_id, 'streak_100', '100-Day Streak', '100D' );
        }

        $new_level = max( 1, (int) floor( $total_xp / self::XP_PER_LEVEL ) + 1 );

        $wpdb->update(
            $table,
            array(
                'current_streak'     => $new_streak,
                'longest_streak'     => $longest,
                'last_activity_date' => $today,
                'total_xp'           => $total_xp,
                'current_level'      => $new_level,
            ),
            array( 'user_id' => $user_id ),
            array( '%d', '%d', '%s', '%d', '%d' ),
            array( '%d' )
        );

        self::log_xp( $user_id, self::XP_DAILY_LOGIN, 'Daily activity' );
    }

    /**
     * Add XP to the user and update level.
     */
    public static function add_xp( $user_id, $amount, $reason = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bayyanoor_streaks';

        $record = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d", $user_id
        ) );

        if ( ! $record ) {
            return;
        }

        $total_xp = (int) $record->total_xp + $amount;
        $new_level = max( 1, (int) floor( $total_xp / self::XP_PER_LEVEL ) + 1 );

        $wpdb->update(
            $table,
            array( 'total_xp' => $total_xp, 'current_level' => $new_level ),
            array( 'user_id' => $user_id ),
            array( '%d', '%d' ),
            array( '%d' )
        );

        self::log_xp( $user_id, $amount, $reason );
    }

    /**
     * Get streak data for a user.
     */
    public static function get_streak( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bayyanoor_streaks';

        $record = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d", $user_id
        ) );

        if ( ! $record ) {
            return array(
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_xp'       => 0,
                'current_level'  => 1,
                'xp_for_next'    => self::XP_PER_LEVEL,
                'xp_progress'    => 0,
            );
        }

        $total_xp    = (int) $record->total_xp;
        $level       = (int) $record->current_level;
        $xp_for_next = $level * self::XP_PER_LEVEL;
        $xp_prev     = ( $level - 1 ) * self::XP_PER_LEVEL;
        $xp_progress = $xp_for_next > $xp_prev ? round( ( ( $total_xp - $xp_prev ) / ( $xp_for_next - $xp_prev ) ) * 100 ) : 0;

        // Check if streak is still active (last activity was today or yesterday)
        $today     = current_time( 'Y-m-d' );
        $yesterday = wp_date( 'Y-m-d', strtotime( '-1 day' ) );
        $current_streak = ( $record->last_activity_date === $today || $record->last_activity_date === $yesterday )
            ? (int) $record->current_streak
            : 0;

        return array(
            'current_streak' => $current_streak,
            'longest_streak' => (int) $record->longest_streak,
            'total_xp'       => $total_xp,
            'current_level'  => $level,
            'xp_for_next'    => $xp_for_next,
            'xp_progress'    => min( 100, $xp_progress ),
        );
    }

    private static function log_xp( $user_id, $amount, $reason ) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'bayyanoor_xp_log',
            array(
                'user_id'   => $user_id,
                'xp_amount' => $amount,
                'reason'    => $reason,
            ),
            array( '%d', '%d', '%s' )
        );
    }

    private static function award_badge_if_new( $user_id, $slug, $name, $icon ) {
        global $wpdb;
        $table = $wpdb->prefix . 'bayyanoor_badges';
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND badge_slug = %s", $user_id, $slug
        ) );
        if ( ! $exists ) {
            $wpdb->insert( $table, array(
                'user_id'    => $user_id,
                'badge_slug' => $slug,
                'badge_name' => $name,
                'badge_icon' => $icon,
            ), array( '%d', '%s', '%s', '%s' ) );
        }
    }
}
