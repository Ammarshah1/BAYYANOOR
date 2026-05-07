<?php
/**
 * Bayyanoor Theme: Header
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if ( function_exists( 'wp_body_open' ) ) { wp_body_open(); } ?>

<header class="site-header">
    <div class="container header-inner">
        <div class="site-logo">
            <span class="site-logo-mark" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M12 3 4 7l8 4 8-4-8-4Z"/><path d="M4 12l8 4 8-4"/><path d="M4 17l8 4 8-4"/></svg>
            </span>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <span>BAYYANOOR</span>
            </a>
        </div>

        <nav class="main-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'bayyanoor-theme' ); ?>">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary-menu',
                'container'      => false,
                'fallback_cb'    => 'bayyanoor_primary_menu_fallback',
            ) );
            ?>
        </nav>

        <div class="header-actions">
            <a href="<?php echo esc_url( home_url( '/bayyanoor-login' ) ); ?>" class="btn-secondary">Student Login</a>
            <a href="<?php echo esc_url( home_url( '/contact-us' ) ); ?>" class="btn-primary">Get Started</a>
        </div>
    </div>
</header>
