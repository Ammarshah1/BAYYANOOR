<?php
/**
 * Bayyanoor Standalone App Shell v3.0
 * All navigation items, icons, and branding are admin-configurable.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$current_user = wp_get_current_user();
$initials = '';
$greeting_name = 'Guest';
if ( is_user_logged_in() ) {
    $first = $current_user->first_name ? mb_substr( $current_user->first_name, 0, 1 ) : '';
    $last  = $current_user->last_name  ? mb_substr( $current_user->last_name, 0, 1 )  : '';
    $initials = strtoupper( $first . $last );
    if ( ! $initials ) $initials = strtoupper( mb_substr( $current_user->display_name, 0, 1 ) );
    $greeting_name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
} else {
    $initials = 'BN';
}

// Load admin-configurable settings
$branding   = Bayyanoor_Admin::get_branding();
$sidebar    = Bayyanoor_Admin::get_sidebar_nav();
$top_nav    = Bayyanoor_Admin::get_top_nav();
$mobile_nav = Bayyanoor_Admin::get_mobile_nav();

// Determine current page for active states
$current_path = wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $branding['site_name'] ); ?> — Mastery Based Quranic Learning</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'bayyanoor-native-app' ); ?>>

    <div class="b-app-window">
        
        <!-- Top Navigation (admin-configurable) -->
        <header class="b-topbar">
            <div class="b-brand">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="b-brand-mark" aria-label="<?php echo esc_attr( $branding['site_name'] ); ?> home">
                    <?php echo wp_kses( $branding['logo_svg'], Bayyanoor_Admin::allowed_svg_tags() ); ?>
                </a>
                <span class="b-brand-copy">
                    <span class="en"><?php echo esc_html( $branding['site_name'] ); ?></span>
                    <span class="ar"><?php echo esc_html( $branding['tagline'] ); ?></span>
                </span>
            </div>
            
            <nav class="b-top-nav">
                <?php foreach ( $top_nav as $item ) :
                    if ( empty( $item['visible'] ) || empty( $item['label'] ) ) continue;
                    $url = esc_url( home_url( $item['url'] ) );
                    $is_active = ( strpos( $current_path, $item['url'] ) !== false );
                    $classes = ( ! empty( $item['ajax'] ) ? 'ajax-link' : '' ) . ( $is_active ? ' active' : '' );
                ?>
                    <a href="<?php echo $url; ?>" class="<?php echo esc_attr( trim( $classes ) ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
                <?php endforeach; ?>
            </nav>
            
            <div class="b-top-user">
                <div class="b-avatar"><?php echo esc_html( $initials ); ?></div>
                <div class="b-actions">
                    <button class="b-notif-btn" id="b-notif-toggle" aria-label="Notifications" title="Notifications">
                        <svg class="b-icon" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="b-notif-dot" id="b-notif-dot" style="display:none;"></span>
                    </button>
                    <div class="b-notif-dropdown" id="b-notif-dropdown" style="display:none;">
                        <div class="b-notif-header"><strong>Recent Activity</strong></div>
                        <ul class="b-notif-list" id="b-notif-list">
                            <li class="b-notif-empty">No recent notifications.</li>
                        </ul>
                    </div>
                    <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( wp_logout_url( home_url( '/bayyanoor-login' ) ) ); ?>" title="Logout" class="b-logout-link">
                        <svg class="b-icon b-icon-muted" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="b-body-wrapper">
            
            <!-- Left Sidebar (admin-configurable) -->
            <aside class="b-sidebar">
                <nav class="b-side-nav">
                    <?php foreach ( $sidebar as $item ) :
                        if ( empty( $item['visible'] ) || empty( $item['label'] ) ) continue;
                        $url = esc_url( home_url( $item['url'] ) );
                        $is_active = ( strpos( $current_path, $item['url'] ) !== false );
                        $classes = ( ! empty( $item['ajax'] ) ? 'ajax-link' : '' ) . ( $is_active ? ' active' : '' );
                    ?>
                        <a href="<?php echo $url; ?>" class="<?php echo esc_attr( trim( $classes ) ); ?>">
                            <?php echo wp_kses( $item['icon'] ?? '', Bayyanoor_Admin::allowed_svg_tags() ); ?>
                            <span><?php echo esc_html( $item['label'] ); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                
                <div class="b-side-footer">
                    <div class="greet">Salaam,<br><?php echo esc_html( $greeting_name ); ?>!</div>
                </div>
            </aside>

            <!-- Main Content Panel -->
            <main class="b-main-panel" id="bayyanoor-app-root">
                <?php
                while ( have_posts() ) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </main>
            
        </div>
    </div>

    <!-- Mobile Bottom Nav (admin-configurable) -->
    <nav class="b-mobile-nav">
        <?php foreach ( $mobile_nav as $item ) :
            if ( empty( $item['visible'] ) || empty( $item['label'] ) ) continue;
            $url = esc_url( home_url( $item['url'] ) );
            $is_active = ( strpos( $current_path, $item['url'] ) !== false );
            $classes = ( ! empty( $item['ajax'] ) ? 'ajax-link' : '' ) . ( $is_active ? ' active' : '' );
        ?>
            <a href="<?php echo $url; ?>" class="<?php echo esc_attr( trim( $classes ) ); ?>">
                <?php echo wp_kses( $item['icon'] ?? '', Bayyanoor_Admin::allowed_svg_tags() ); ?>
                <?php echo esc_html( $item['label'] ); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php wp_footer(); ?>
</body>
</html>
