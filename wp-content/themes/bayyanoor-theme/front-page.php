<?php
/**
 * Bayyanoor Theme: Front Page Template
 */
get_header(); ?>

<main id="main-content">
    <section class="hero-section" aria-label="Bayyanoor Academy">
        <div class="container hero-grid">
            <div class="hero-content">
                <p class="section-kicker">Online Quran, Arabic and Islamic Studies</p>
                <h1>Bayyanoor Academy</h1>
                <p>Live teachers, structured lessons, and a beautiful learning dashboard for Muslim families who want Quran, Arabic, and Islamic studies taught with care.</p>
                <div class="hero-actions">
                    <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="btn-primary">Explore Programs</a>
                    <a href="<?php echo esc_url( home_url( '/bayyanoor-login' ) ); ?>" class="btn-secondary">Student Dashboard</a>
                </div>
            </div>

            <div class="hero-metrics" aria-label="Bayyanoor learning highlights">
                <div class="hero-metric">
                    <strong>Live</strong>
                    <span>teacher-led classes</span>
                </div>
                <div class="hero-metric">
                    <strong>3</strong>
                    <span>core programs</span>
                </div>
                <div class="hero-metric">
                    <strong>K-12</strong>
                    <span>guided pathway</span>
                </div>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <div class="section-heading">
                <p class="section-kicker">What students learn</p>
                <h2>A focused Islamic learning system</h2>
                <p>Every part of Bayyanoor uses the same structure: clear goals, regular practice, and visible progress.</p>
            </div>

            <div class="features-grid">
                <article class="feature-card">
                    <div>
                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/></svg>
                        </div>
                        <h3>Quran Mastery</h3>
                    </div>
                    <p>Students build recitation, Tajweed, memorization, and review habits with verified teachers.</p>
                </article>

                <article class="feature-card">
                    <div>
                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 2 4 4-12 12H6v-4L18 2z"/><path d="M2 22h20"/></svg>
                        </div>
                        <h3>Arabic Language</h3>
                    </div>
                    <p>Reading, writing, vocabulary, and grammar are taught step by step so the Quran becomes easier to understand.</p>
                </article>

                <article class="feature-card">
                    <div>
                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V9l7-5 7 5v12"/><path d="M9 21v-6a3 3 0 0 1 6 0v6"/></svg>
                        </div>
                        <h3>Islamic Studies</h3>
                    </div>
                    <p>Seerah, adab, fiqh, and daily Islamic identity are presented with age-appropriate structure.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="split-section">
        <div class="container split-grid">
            <div class="split-content">
                <p class="section-kicker">Designed for families</p>
                <h2 class="split-heading">Classes kids enjoy and parents can trust</h2>
                <p class="split-lead">Bayyanoor combines live instruction with a student dashboard that keeps practice, progress, and confidence visible.</p>
                <p>Instead of scattered videos or random worksheets, students follow a planned curriculum with teacher feedback and a clear next step.</p>
                <ul class="split-list">
                    <li><strong>Arabic:</strong> reading, writing, speaking, and grammar foundations.</li>
                    <li><strong>Quran:</strong> recitation, Tajweed, memorization, and review.</li>
                    <li><strong>Teachers:</strong> live guidance that follows the curriculum.</li>
                </ul>
                <a href="<?php echo esc_url( home_url( '/pricing' ) ); ?>" class="btn-primary">Enroll Now</a>
            </div>

            <div class="split-image">
                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/students.png" alt="Bayyanoor students learning online">
            </div>
        </div>
    </section>

    <section class="programs-section">
        <div class="container">
            <h2>A complete pathway for your child</h2>
            <p class="programs-subtitle">Live Quran, Arabic, and Islamic Studies taught year-round by committed teachers.</p>

            <div class="programs-grid">
                <article class="program-card">
                    <h3>Pre-K and Kindergarten</h3>
                    <p>Early Quran and Arabic exposure taught with warmth, repetition, and structure.</p>
                </article>
                <article class="program-card">
                    <h3>Elementary</h3>
                    <p>Build fluency, confidence, and strong Quran reading habits during the ideal foundation years.</p>
                </article>
                <article class="program-card">
                    <h3>Middle School</h3>
                    <p>Keep students grounded through structured studies that support identity and independence.</p>
                </article>
                <article class="program-card">
                    <h3>High School</h3>
                    <p>Advanced Quran skills, deeper understanding, and confidence to live faith with clarity.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
