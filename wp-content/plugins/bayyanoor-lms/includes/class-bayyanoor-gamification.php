<?php
/**
 * Bayyanoor Gamification & Dynamic Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Gamification {

    public static function init() {
        add_shortcode( 'bayyanoor_dashboard', array( __CLASS__, 'render_dashboard' ) );
        add_shortcode( 'bayyanoor_programs_overview', array( __CLASS__, 'render_programs_overview' ) );
    }

    public static function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '<div class="bayyanoor-card"><h2>Dashboard</h2><p>Please <a href="' . esc_url( home_url( '/bayyanoor-login' ) ) . '">log in</a> to access your dashboard.</p></div>';
        }

        $user       = wp_get_current_user();
        $first_name = $user->first_name ? $user->first_name : $user->display_name;

        ob_start();
        ?>
        <div class="b-dashboard" id="b-dashboard" data-user-id="<?php echo esc_attr( $user->ID ); ?>">
            <div class="b-main-col">
                <div class="b-hero">
                    <h3>Salaam, <span id="b-user-name"><?php echo esc_html( $first_name ); ?></span></h3>
                    <h1>Continue your Quran learning journey</h1>

                    <div class="b-hero-stats">
                        <div class="b-hero-stat">
                            <span class="stat-icon" aria-hidden="true"><?php echo self::icon_svg( 'flame' ); ?></span>
                            <div class="stat-info">
                                <span class="stat-value" id="b-streak-count">0</span>
                                <span class="stat-label">Day Streak</span>
                            </div>
                        </div>
                        <div class="b-hero-stat">
                            <span class="stat-icon" aria-hidden="true"><?php echo self::icon_svg( 'bolt' ); ?></span>
                            <div class="stat-info">
                                <span class="stat-value" id="b-xp-count">0</span>
                                <span class="stat-label">Total XP</span>
                            </div>
                        </div>
                        <div class="b-hero-stat">
                            <span class="stat-icon" aria-hidden="true"><?php echo self::icon_svg( 'award' ); ?></span>
                            <div class="stat-info">
                                <span class="stat-value" id="b-level-count">1</span>
                                <span class="stat-label">Level</span>
                            </div>
                        </div>
                        <div class="b-hero-stat">
                            <span class="stat-icon" aria-hidden="true"><?php echo self::icon_svg( 'book' ); ?></span>
                            <div class="stat-info">
                                <span class="stat-value" id="b-hifz-count">0</span>
                                <span class="stat-label">Surahs</span>
                            </div>
                        </div>
                    </div>

                    <div class="b-xp-bar-container">
                        <div class="b-xp-bar-label">
                            <span>Level <span id="b-xp-level">1</span></span>
                            <span><span id="b-xp-current">0</span> / <span id="b-xp-next">500</span> XP</span>
                        </div>
                        <div class="b-prog b-xp-bar"><div class="fill b-xp-fill" id="b-xp-fill" style="width:0%"></div></div>
                    </div>
                </div>

                <div class="b-quick-actions">
                    <?php
                    $quick_actions = class_exists( 'Bayyanoor_Admin' ) ? Bayyanoor_Admin::get_quick_actions() : array();
                    foreach ( $quick_actions as $qa ) :
                        if ( empty( $qa['visible'] ) || empty( $qa['label'] ) ) continue;
                        $qa_url   = ( $qa['url'] && $qa['url'] !== '#' ) ? esc_url( home_url( $qa['url'] ) ) : '#';
                        $qa_class = 'b-quick-action' . ( ! empty( $qa['ajax'] ) ? ' ajax-link' : '' );
                        $qa_id    = ! empty( $qa['id'] ) ? ' id="' . esc_attr( $qa['id'] ) . '"' : '';
                    ?>
                    <a href="<?php echo $qa_url; ?>" class="<?php echo esc_attr( $qa_class ); ?>"<?php echo $qa_id; ?>>
                        <span class="qa-icon" aria-hidden="true"><?php echo self::icon_svg( $qa['icon_key'] ?? 'book' ); ?></span>
                        <span class="qa-text"><?php echo esc_html( $qa['label'] ); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>

                <div class="b-section">
                    <h2>Your Courses</h2>
                    <div class="b-course-grid" id="b-course-grid">
                        <div class="b-course-card b-skeleton"><div class="skel-icon"></div><div class="skel-line w60"></div><div class="skel-line w40"></div><div class="skel-bar"></div></div>
                        <div class="b-course-card b-skeleton"><div class="skel-icon"></div><div class="skel-line w60"></div><div class="skel-line w40"></div><div class="skel-bar"></div></div>
                        <div class="b-course-card b-skeleton"><div class="skel-icon"></div><div class="skel-line w60"></div><div class="skel-line w40"></div><div class="skel-bar"></div></div>
                        <div class="b-course-card b-skeleton"><div class="skel-icon"></div><div class="skel-line w60"></div><div class="skel-line w40"></div><div class="skel-bar"></div></div>
                    </div>
                </div>
            </div>

            <div class="b-right-col">
                <div class="b-widget">
                    <h3 class="b-widget-title">Mastery Overview</h3>
                    <div class="b-chart-wrapper">
                        <canvas id="masteryRadarChart" width="280" height="280"></canvas>
                    </div>
                </div>

                <div class="b-widget">
                    <h3 class="b-widget-title">Badges</h3>
                    <div class="b-badge-grid" id="b-badge-grid">
                        <div class="b-badge-empty">Complete activities to earn badges.</div>
                    </div>
                </div>

                <div class="b-widget">
                    <h3 class="b-widget-title">Recent Activity</h3>
                    <ul class="b-timeline" id="b-activity-feed">
                        <li class="b-activity-empty">No activity yet. Start learning.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="b-modal-overlay" id="b-hifz-modal" style="display:none;">
            <div class="b-modal">
                <div class="b-modal-header">
                    <h3>Log Hifz Progress</h3>
                    <button class="b-modal-close" data-modal="b-hifz-modal" aria-label="Close">&times;</button>
                </div>
                <form id="b-hifz-form" class="b-modal-form">
                    <div class="b-form-group">
                        <label for="hifz-surah">Surah Number</label>
                        <input type="number" id="hifz-surah" name="surah_no" min="1" max="114" placeholder="Example: 114" required />
                    </div>
                    <div class="b-form-group">
                        <label for="hifz-ayah">Ayah Range</label>
                        <input type="text" id="hifz-ayah" name="ayah_range" placeholder="Example: 1-5" required />
                    </div>
                    <div class="b-form-group">
                        <label for="hifz-status">Status</label>
                        <select id="hifz-status" name="status">
                            <option value="new_memorization">New Memorization</option>
                            <option value="review">Review</option>
                            <option value="solid">Solid</option>
                            <option value="needs_review">Needs Review</option>
                        </select>
                    </div>
                    <div class="b-form-group">
                        <label for="hifz-quality">Quality (0-100)</label>
                        <input type="number" id="hifz-quality" name="quality" min="0" max="100" value="70" />
                    </div>
                    <button type="submit" class="b-btn-gold">Log Progress</button>
                </form>
            </div>
        </div>

        <div class="b-modal-overlay" id="b-quiz-modal" style="display:none;">
            <div class="b-modal">
                <div class="b-modal-header">
                    <h3>Quick Mastery Quiz</h3>
                    <button class="b-modal-close" data-modal="b-quiz-modal" aria-label="Close">&times;</button>
                </div>
                <form id="b-quiz-form" class="b-modal-form">
                    <div class="b-form-group">
                        <label for="quiz-skill">Skill Area</label>
                        <select id="quiz-skill" name="skill_slug">
                            <option value="tajweed">Tajweed</option>
                            <option value="vocabulary">Vocabulary</option>
                            <option value="memorization">Memorization</option>
                            <option value="tafseer">Tafseer</option>
                            <option value="grammar">Grammar</option>
                        </select>
                    </div>
                    <div class="b-form-group">
                        <label for="quiz-score">Score (0-100)</label>
                        <input type="number" id="quiz-score" name="score" min="0" max="100" value="85" required />
                    </div>
                    <button type="submit" class="b-btn-gold">Submit Quiz</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render programs overview shortcode for the public Programs page.
     */
    public static function render_programs_overview() {
        $courses = new WP_Query( array(
            'post_type'      => 'bn_course',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
        ) );

        ob_start();
        ?>
        <div class="b-programs-public">
        <?php if ( $courses->have_posts() ) : ?>
            <div class="b-programs-grid">
                <?php while ( $courses->have_posts() ) : $courses->the_post(); ?>
                <div class="b-program-card-public">
                    <h3><?php the_title(); ?></h3>
                    <div class="b-program-excerpt"><?php the_excerpt(); ?></div>
                    <a href="<?php the_permalink(); ?>" class="btn-primary">Learn More</a>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <div class="b-programs-grid">
                <div class="b-program-card-public"><h3>Quran Recitation</h3><p>Master Quranic recitation with proper Tajweed under expert guidance.</p></div>
                <div class="b-program-card-public"><h3>Arabic Language</h3><p>Learn to read, write, and speak Arabic fluently.</p></div>
                <div class="b-program-card-public"><h3>Islamic Studies</h3><p>Study Seerah, fiqh, adab, and daily Islamic foundations.</p></div>
                <div class="b-program-card-public"><h3>Hifz Program</h3><p>Structured Quran memorization with tracking and review.</p></div>
            </div>
        <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function get_initials( $user ) {
        $first = $user->first_name ? mb_substr( $user->first_name, 0, 1 ) : '';
        $last  = $user->last_name  ? mb_substr( $user->last_name, 0, 1 )  : '';
        if ( $first || $last ) {
            return strtoupper( $first . $last );
        }
        return strtoupper( mb_substr( $user->display_name, 0, 1 ) );
    }

    private static function icon_svg( $name ) {
        $icons = array(
            'flame'     => '<svg viewBox="0 0 24 24"><path d="M8.5 14.5A4.5 4.5 0 0 0 17 12c0-3-2-5-3.5-7.5-.25 2.25-1.3 3.55-3 4.7C9 10.2 8.2 11.7 8.5 14.5Z"/><path d="M12 22a6 6 0 0 1-6-6c0-2.8 1.55-4.8 3.65-6.25"/></svg>',
            'bolt'      => '<svg viewBox="0 0 24 24"><path d="m13 2-9 13h7l-1 7 10-14h-7l0-6Z"/></svg>',
            'award'     => '<svg viewBox="0 0 24 24"><path d="M12 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z"/><path d="m9 14-1 7 4-2 4 2-1-7"/></svg>',
            'book'      => '<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/></svg>',
            'message'   => '<svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/></svg>',
            'book-open' => '<svg viewBox="0 0 24 24"><path d="M12 7v14"/><path d="M3 5a7 7 0 0 1 9 2 7 7 0 0 1 9-2v14a7 7 0 0 0-9 2 7 7 0 0 0-9-2V5Z"/></svg>',
            'bookmark'  => '<svg viewBox="0 0 24 24"><path d="M6 3h12v18l-6-4-6 4V3Z"/></svg>',
            'check'     => '<svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>',
        );

        return isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['book'];
    }
}
Bayyanoor_Gamification::init();
