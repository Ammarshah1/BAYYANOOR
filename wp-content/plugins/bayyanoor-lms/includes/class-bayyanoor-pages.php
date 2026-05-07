<?php
/**
 * Bayyanoor Pages — Shortcodes for in-app pages
 * Courses catalog, Progress overview, Support/FAQ
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Pages {

    public static function init() {
        add_shortcode( 'bayyanoor_courses_page', array( __CLASS__, 'render_courses_page' ) );
        add_shortcode( 'bayyanoor_progress_page', array( __CLASS__, 'render_progress_page' ) );
        add_shortcode( 'bayyanoor_support_page', array( __CLASS__, 'render_support_page' ) );
    }

    /**
     * Courses Catalog — shows all available courses with enrollment + progress.
     */
    public static function render_courses_page() {
        if ( ! is_user_logged_in() ) {
            return '<div class="bayyanoor-card"><h2>Courses</h2><p>Please <a href="' . esc_url( home_url( '/bayyanoor-login' ) ) . '">log in</a> to browse courses.</p></div>';
        }

        $user_id = get_current_user_id();

        // Learning path definition
        $learning_path = array(
            array( 'slug' => 'qaida',   'title' => 'Qaida',   'desc' => 'Learn the Arabic alphabet and basic letter forms',   'icon' => 'QA', 'color' => '#115e41' ),
            array( 'slug' => 'nazra',   'title' => 'Nazra',   'desc' => 'Read the Quran with basic fluency',                  'icon' => 'NZ', 'color' => '#1a7a56' ),
            array( 'slug' => 'tajweed', 'title' => 'Tajweed', 'desc' => 'Master the rules of proper Quranic recitation',      'icon' => 'TJ', 'color' => '#c69d67' ),
            array( 'slug' => 'hifz',    'title' => 'Hifz',    'desc' => 'Memorize the Quran with structured review',           'icon' => 'HF', 'color' => '#9f7440' ),
            array( 'slug' => 'tafseer', 'title' => 'Tafseer', 'desc' => 'Understand the meanings and context of the Quran',    'icon' => 'TF', 'color' => '#c9674b' ),
        );

        ob_start();
        ?>
        <div class="b-page-courses">
            <div class="b-page-header">
                <h1>Your Learning Journey</h1>
                <p>Follow the guided path from basics to mastery, or explore individual courses.</p>
            </div>

            <!-- Learning Path Timeline -->
            <div class="b-learning-path">
                <h2>Learning Path</h2>
                <div class="b-path-timeline">
                    <?php foreach ( $learning_path as $i => $step ) :
                        $mastery = class_exists( 'Bayyanoor_Mastery' ) ? Bayyanoor_Mastery::get_skill_mastery( $user_id, $step['slug'] ) : array( 'level' => 0, 'percentage' => 0 );
                        $status_class = 'locked';
                        if ( $mastery['level'] >= 4 ) {
                            $status_class = 'completed';
                        } elseif ( $mastery['level'] >= 1 || $i === 0 ) {
                            $status_class = 'available';
                        } elseif ( $i > 0 ) {
                            // Check if previous step has at least level 1
                            $prev = Bayyanoor_Mastery::get_skill_mastery( $user_id, $learning_path[ $i - 1 ]['slug'] );
                            if ( $prev['level'] >= 2 ) {
                                $status_class = 'available';
                            }
                        }
                    ?>
                    <div class="b-path-step <?php echo esc_attr( $status_class ); ?>">
                        <div class="b-path-node" style="--step-color: <?php echo esc_attr( $step['color'] ); ?>">
                            <span class="b-path-icon"><?php echo esc_html( $step['icon'] ); ?></span>
                            <?php if ( $status_class === 'completed' ) : ?>
                                <span class="b-path-check">✓</span>
                            <?php endif; ?>
                        </div>
                        <?php if ( $i < count( $learning_path ) - 1 ) : ?>
                            <div class="b-path-connector <?php echo $status_class === 'completed' ? 'filled' : ''; ?>"></div>
                        <?php endif; ?>
                        <div class="b-path-info">
                            <h3><?php echo esc_html( $step['title'] ); ?></h3>
                            <p><?php echo esc_html( $step['desc'] ); ?></p>
                            <?php if ( $status_class !== 'locked' ) : ?>
                                <div class="b-path-progress">
                                    <div class="b-prog"><div class="fill" style="width:<?php echo (int) $mastery['percentage']; ?>%"></div></div>
                                    <span><?php echo esc_html( $mastery['label'] ?? 'Novice' ); ?> — <?php echo (int) $mastery['percentage']; ?>%</span>
                                </div>
                            <?php else : ?>
                                <span class="b-path-locked">🔒 Complete previous stage to unlock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- All Courses Grid -->
            <div class="b-section" style="margin-top:2rem;">
                <h2>All Courses</h2>
                <div class="b-course-grid" id="b-courses-full-grid">
                    <!-- Loaded via AJAX or fallback -->
                </div>
            </div>
        </div>
        <script>
        (function($) {
            if (typeof bayyanoorAjax === 'undefined') return;
            $.post(bayyanoorAjax.ajax_url, {
                action: 'bayyanoor_get_courses',
                nonce: bayyanoorAjax.nonce
            }, function(res) {
                if (!res.success || !res.data || !res.data.length) {
                    $('#b-courses-full-grid').html('<p class="b-empty-state"><span class="b-empty-icon">📚</span><strong>No courses yet</strong><br>Courses will appear here once your teacher adds them.</p>');
                    return;
                }
                var html = '';
                res.data.forEach(function(c, i) {
                    var p = Math.max(0, Math.min(100, Math.round(parseFloat(c.progress) || 0)));
                    html += '<div class="b-course-card b-fade-in" style="animation-delay:' + (i*0.08) + 's">' +
                        '<div class="icon">' + (c.icon||'BN').replace(/[<>&"']/g,'') + '</div>' +
                        '<h3>' + (c.title||'').replace(/[<>&]/g,'') + '</h3>' +
                        '<div class="meta"><span>' + p + '%</span><span>' + (parseInt(c.lessons)||0) + ' lessons</span></div>' +
                        '<div class="b-prog"><div class="fill" style="width:'+p+'%"></div></div>' +
                        '<a href="#" class="b-btn-gold b-enroll-btn" data-course-id="'+(c.id||0)+'">' + (p > 0 ? 'Continue Learning' : 'Start Course') + '</a>' +
                    '</div>';
                });
                $('#b-courses-full-grid').html(html);
            });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Progress Page — mastery breakdown, hifz tracker, streaks, badges.
     */
    public static function render_progress_page() {
        if ( ! is_user_logged_in() ) {
            return '<div class="bayyanoor-card"><h2>Progress</h2><p>Please <a href="' . esc_url( home_url( '/bayyanoor-login' ) ) . '">log in</a> to view your progress.</p></div>';
        }

        $user_id    = get_current_user_id();
        $user       = wp_get_current_user();
        $streak     = Bayyanoor_Streaks::get_streak( $user_id );
        $mastery    = Bayyanoor_Mastery::get_user_mastery( $user_id );
        $hifz       = Bayyanoor_Hifz::get_summary( $user_id );
        $hifz_detail = Bayyanoor_Hifz::get_hifz_progress( $user_id );

        ob_start();
        ?>
        <div class="b-page-progress">
            <div class="b-page-header">
                <h1>Your Progress</h1>
                <p>Track your mastery, memorization, streaks, and achievements.</p>
            </div>

            <!-- Stats Row -->
            <div class="b-progress-stats">
                <div class="b-stat-card">
                    <span class="b-stat-number"><?php echo (int) $streak['current_streak']; ?></span>
                    <span class="b-stat-label">Day Streak</span>
                </div>
                <div class="b-stat-card">
                    <span class="b-stat-number"><?php echo (int) $streak['total_xp']; ?></span>
                    <span class="b-stat-label">Total XP</span>
                </div>
                <div class="b-stat-card">
                    <span class="b-stat-number"><?php echo (int) $streak['current_level']; ?></span>
                    <span class="b-stat-label">Level</span>
                </div>
                <div class="b-stat-card">
                    <span class="b-stat-number"><?php echo (int) $streak['longest_streak']; ?></span>
                    <span class="b-stat-label">Best Streak</span>
                </div>
            </div>

            <div class="b-progress-grid">
                <!-- Mastery Breakdown -->
                <div class="b-widget b-progress-mastery">
                    <h3 class="b-widget-title">Skill Mastery</h3>
                    <?php
                    $skills = array( 'tajweed' => 'Tajweed', 'vocabulary' => 'Vocabulary', 'memorization' => 'Memorization', 'tafseer' => 'Tafseer', 'grammar' => 'Grammar' );
                    foreach ( $skills as $slug => $label ) :
                        $m = isset( $mastery[ $slug ] ) ? $mastery[ $slug ] : array( 'level' => 0, 'percentage' => 0, 'label' => 'Novice', 'attempts' => 0 );
                    ?>
                    <div class="b-mastery-row">
                        <div class="b-mastery-info">
                            <strong><?php echo esc_html( $label ); ?></strong>
                            <span><?php echo esc_html( $m['label'] ); ?> (<?php echo (int) $m['attempts']; ?> attempts)</span>
                        </div>
                        <div class="b-prog" style="flex:1"><div class="fill" style="width:<?php echo (int) $m['percentage']; ?>%"></div></div>
                        <span class="b-mastery-pct"><?php echo (int) $m['percentage']; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Hifz Progress -->
                <div class="b-widget b-progress-hifz">
                    <h3 class="b-widget-title">Hifz Tracker</h3>
                    <div class="b-hifz-stats-row">
                        <div><strong><?php echo (int) $hifz['surahs_started']; ?></strong> surahs started</div>
                        <div><strong><?php echo (int) $hifz['total_sections']; ?></strong> sections logged</div>
                        <div><strong><?php echo (int) $hifz['solid_sections']; ?></strong> solid</div>
                    </div>
                    <?php if ( ! empty( $hifz_detail ) ) : ?>
                    <div class="b-hifz-list">
                        <?php foreach ( $hifz_detail as $surah ) : ?>
                        <div class="b-hifz-surah">
                            <strong><?php echo esc_html( $surah['surah_name'] ); ?></strong>
                            <div class="b-hifz-sections">
                                <?php foreach ( $surah['sections'] as $sec ) : ?>
                                <span class="b-hifz-section b-hifz-<?php echo esc_attr( $sec['status'] ); ?>" title="<?php echo esc_attr( $sec['status'] ); ?> — Quality: <?php echo (int) $sec['quality']; ?>%">
                                    Ayah <?php echo esc_html( $sec['ayah_range'] ); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else : ?>
                    <p class="b-empty-state"><span class="b-empty-icon">📖</span><strong>No Hifz entries yet</strong><br>Use "Log Hifz" from the dashboard to start tracking your memorization.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Badges -->
            <div class="b-widget" style="margin-top:1.5rem;">
                <h3 class="b-widget-title">Your Badges</h3>
                <div class="b-badge-grid" id="b-progress-badges">
                    <div class="b-badge-empty">Loading badges...</div>
                </div>
            </div>
        </div>
        <script>
        (function($) {
            if (typeof bayyanoorAjax === 'undefined') return;
            $.post(bayyanoorAjax.ajax_url, { action: 'bayyanoor_get_badges', nonce: bayyanoorAjax.nonce }, function(res) {
                var $g = $('#b-progress-badges');
                if (!res.success || !res.data || !res.data.length) {
                    $g.html('<p class="b-empty-state"><span class="b-empty-icon">🏅</span><strong>No badges yet</strong><br>Complete quizzes, log Hifz, and maintain streaks to earn badges.</p>');
                    return;
                }
                var h = '';
                res.data.forEach(function(b) {
                    h += '<div class="b-badge-item" title="'+((b.name||'').replace(/"/g,''))+'">' +
                        '<span class="b-badge-icon">'+((b.icon||'BN').replace(/[<>&]/g,''))+'</span>' +
                        '<span class="b-badge-name">'+((b.name||'').replace(/[<>&]/g,''))+'</span></div>';
                });
                $g.html(h);
            });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Support Page — FAQ and contact info rendered inside the app shell.
     */
    public static function render_support_page() {
        ob_start();
        ?>
        <div class="b-page-support">
            <div class="b-page-header">
                <h1>Help & Support</h1>
                <p>Find answers to common questions or reach out to our team.</p>
            </div>

            <div class="b-faq-section">
                <h2>Frequently Asked Questions</h2>
                <div class="b-faq-list">
                    <?php
                    $faqs = array(
                        array( 'q' => 'How does the mastery system work?', 'a' => 'Each skill area (Tajweed, Vocabulary, Grammar, etc.) has 5 mastery levels: Novice → Learner → Practitioner → Proficient → Master. Score above 90% on a quiz to level up. Your progress is tracked automatically.' ),
                        array( 'q' => 'How do I earn XP and level up?', 'a' => 'You earn XP by logging in daily (+10 XP), completing quizzes (+15 XP), mastering skills (+50 XP per level-up), and logging Hifz entries (+10-25 XP). Every 500 XP advances you one level.' ),
                        array( 'q' => 'What are streaks and how do they work?', 'a' => 'Streaks track consecutive days of activity. Visit your dashboard or use any feature daily to maintain your streak. Milestones at 7, 30, and 100 days award bonus XP and badges.' ),
                        array( 'q' => 'How do I track my Quran memorization?', 'a' => 'Click "Log Hifz" from the dashboard. Enter the Surah number, Ayah range, your assessment of quality, and the status (new, review, solid, needs review). Your entries are tracked on the Progress page.' ),
                        array( 'q' => 'What is the AI Tutor?', 'a' => 'The AI Tutor is a learning assistant that can help with Tajweed rules, Arabic grammar, Tafseer questions, and memorization tips. It provides educational guidance but is not a substitute for a qualified teacher.' ),
                        array( 'q' => 'Can I reset my progress?', 'a' => 'Contact your administrator to reset specific progress data. Individual quiz attempts and mastery levels can be adjusted from the admin panel.' ),
                        array( 'q' => 'How do I contact support?', 'a' => 'Email us at support@bayyanoor.com or use the contact form on our website. We typically respond within 24 hours on business days.' ),
                    );
                    foreach ( $faqs as $i => $faq ) :
                    ?>
                    <details class="b-faq-item">
                        <summary class="b-faq-question">
                            <span><?php echo esc_html( $faq['q'] ); ?></span>
                            <svg class="b-faq-chevron" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="b-faq-answer">
                            <p><?php echo esc_html( $faq['a'] ); ?></p>
                        </div>
                    </details>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="b-support-contact">
                <div class="b-widget">
                    <h3 class="b-widget-title">Need More Help?</h3>
                    <p style="color:var(--text-muted); margin-bottom:1rem;">Our team is here to help you succeed in your Quranic learning journey.</p>
                    <div class="b-support-options">
                        <div class="b-support-option">
                            <span class="b-support-icon">📧</span>
                            <div>
                                <strong>Email Support</strong>
                                <p>support@bayyanoor.com</p>
                            </div>
                        </div>
                        <div class="b-support-option">
                            <span class="b-support-icon">💬</span>
                            <div>
                                <strong>AI Tutor</strong>
                                <p><a href="<?php echo esc_url( home_url( '/bayyanoor-ai-tutor' ) ); ?>" class="ajax-link">Ask a learning question</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
Bayyanoor_Pages::init();
