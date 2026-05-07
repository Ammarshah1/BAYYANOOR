<?php
/**
 * Bayyanoor Admin Settings
 * Provides a WP Admin page to configure navigation, quick actions, and branding.
 * All nav items, icons, and labels are editable without touching code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Admin {

    const OPT_BRANDING     = 'bayyanoor_branding';
    const OPT_SIDEBAR_NAV  = 'bayyanoor_sidebar_nav';
    const OPT_TOP_NAV      = 'bayyanoor_top_nav';
    const OPT_MOBILE_NAV   = 'bayyanoor_mobile_nav';
    const OPT_QUICK_ACTIONS = 'bayyanoor_quick_actions';

    /* =========================================================
     * DEFAULTS — match the current hardcoded values exactly
     * ========================================================= */

    public static function get_defaults_branding() {
        return array(
            'site_name'    => 'BAYYANOOR',
            'tagline'      => 'Academy',
            'logo_svg'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 7l8 4 8-4-8-4Z"></path><path d="M4 12l8 4 8-4"></path><path d="M4 17l8 4 8-4"></path></svg>',
        );
    }

    public static function get_defaults_sidebar_nav() {
        return array(
            array(
                'label'   => 'Dashboard',
                'url'     => '/bayyanoor-app',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
            array(
                'label'   => 'Courses',
                'url'     => '/bayyanoor-courses',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
            array(
                'label'   => 'AI Tutor',
                'url'     => '/bayyanoor-ai-tutor',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
            array(
                'label'   => 'Progress',
                'url'     => '/bayyanoor-progress',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
            array(
                'label'   => 'Support',
                'url'     => '/bayyanoor-support',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
        );
    }

    public static function get_defaults_top_nav() {
        return array(
            array( 'label' => 'Dashboard',  'url' => '/bayyanoor-app',      'ajax' => true,  'visible' => true ),
            array( 'label' => 'Courses',    'url' => '/bayyanoor-courses',   'ajax' => true,  'visible' => true ),
            array( 'label' => 'AI Tutor',   'url' => '/bayyanoor-ai-tutor', 'ajax' => true,  'visible' => true ),
            array( 'label' => 'Support',    'url' => '/bayyanoor-support',   'ajax' => true,  'visible' => true ),
        );
    }

    public static function get_defaults_mobile_nav() {
        return array(
            array(
                'label'   => 'Home',
                'url'     => '/bayyanoor-app',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
            array(
                'label'   => 'Courses',
                'url'     => '/bayyanoor-courses',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
            array(
                'label'   => 'AI',
                'url'     => '/bayyanoor-ai-tutor',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
            array(
                'label'   => 'Progress',
                'url'     => '/bayyanoor-progress',
                'icon'    => '<svg class="b-icon" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>',
                'ajax'    => true,
                'visible' => true,
            ),
        );
    }

    public static function get_defaults_quick_actions() {
        return array(
            array( 'label' => 'AI Tutor',        'url' => '/bayyanoor-ai-tutor', 'icon_key' => 'message',   'ajax' => true,  'visible' => true, 'id' => '' ),
            array( 'label' => 'Practice Tajweed', 'url' => '#',                   'icon_key' => 'book-open', 'ajax' => false, 'visible' => true, 'id' => 'b-practice-tajweed' ),
            array( 'label' => 'Log Hifz',         'url' => '#',                   'icon_key' => 'bookmark',  'ajax' => false, 'visible' => true, 'id' => 'b-log-hifz-btn' ),
            array( 'label' => 'Take Quiz',        'url' => '#',                   'icon_key' => 'check',     'ajax' => false, 'visible' => true, 'id' => 'b-take-quiz-btn' ),
        );
    }

    /* =========================================================
     * GETTERS — used by templates to read config
     * ========================================================= */

    public static function get_branding() {
        $saved = get_option( self::OPT_BRANDING, array() );
        return wp_parse_args( $saved, self::get_defaults_branding() );
    }

    public static function get_sidebar_nav() {
        $saved = get_option( self::OPT_SIDEBAR_NAV, array() );
        return ! empty( $saved ) ? $saved : self::get_defaults_sidebar_nav();
    }

    public static function get_top_nav() {
        $saved = get_option( self::OPT_TOP_NAV, array() );
        return ! empty( $saved ) ? $saved : self::get_defaults_top_nav();
    }

    public static function get_mobile_nav() {
        $saved = get_option( self::OPT_MOBILE_NAV, array() );
        return ! empty( $saved ) ? $saved : self::get_defaults_mobile_nav();
    }

    public static function get_quick_actions() {
        $saved = get_option( self::OPT_QUICK_ACTIONS, array() );
        return ! empty( $saved ) ? $saved : self::get_defaults_quick_actions();
    }

    /* =========================================================
     * INIT — Register admin menu and settings
     * ========================================================= */

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
    }

    public static function add_admin_menu() {
        add_menu_page(
            __( 'Bayyanoor Settings', 'bayyanoor' ),
            __( 'Bayyanoor', 'bayyanoor' ),
            'manage_options',
            'bayyanoor-settings',
            array( __CLASS__, 'render_settings_page' ),
            'dashicons-welcome-learn-more',
            3
        );
    }

    public static function register_settings() {
        register_setting( 'bayyanoor_settings_group', self::OPT_BRANDING, array(
            'type'              => 'array',
            'sanitize_callback' => array( __CLASS__, 'sanitize_branding' ),
        ) );
        register_setting( 'bayyanoor_settings_group', self::OPT_SIDEBAR_NAV, array(
            'type'              => 'array',
            'sanitize_callback' => array( __CLASS__, 'sanitize_nav_items' ),
        ) );
        register_setting( 'bayyanoor_settings_group', self::OPT_TOP_NAV, array(
            'type'              => 'array',
            'sanitize_callback' => array( __CLASS__, 'sanitize_nav_items' ),
        ) );
        register_setting( 'bayyanoor_settings_group', self::OPT_MOBILE_NAV, array(
            'type'              => 'array',
            'sanitize_callback' => array( __CLASS__, 'sanitize_nav_items' ),
        ) );
        register_setting( 'bayyanoor_settings_group', self::OPT_QUICK_ACTIONS, array(
            'type'              => 'array',
            'sanitize_callback' => array( __CLASS__, 'sanitize_nav_items' ),
        ) );
    }

    public static function sanitize_branding( $input ) {
        return array(
            'site_name' => sanitize_text_field( $input['site_name'] ?? '' ),
            'tagline'   => sanitize_text_field( $input['tagline'] ?? '' ),
            'logo_svg'  => wp_kses( $input['logo_svg'] ?? '', self::allowed_svg_tags() ),
        );
    }

    public static function sanitize_nav_items( $input ) {
        if ( ! is_array( $input ) ) return array();
        $clean = array();
        foreach ( $input as $item ) {
            if ( ! is_array( $item ) ) continue;
            $cleaned = array();
            foreach ( $item as $key => $val ) {
                if ( $key === 'icon' ) {
                    $cleaned[ $key ] = wp_kses( $val, self::allowed_svg_tags() );
                } elseif ( $key === 'visible' || $key === 'ajax' ) {
                    $cleaned[ $key ] = ! empty( $val );
                } else {
                    $cleaned[ $key ] = sanitize_text_field( $val );
                }
            }
            $clean[] = $cleaned;
        }
        return $clean;
    }

    public static function allowed_svg_tags() {
        return array(
            'svg'    => array( 'viewBox' => true, 'class' => true, 'width' => true, 'height' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'aria-hidden' => true, 'xmlns' => true ),
            'path'   => array( 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
            'circle' => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true ),
            'rect'   => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'fill' => true, 'rx' => true, 'ry' => true ),
            'line'   => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true ),
            'g'      => array( 'fill' => true, 'stroke' => true, 'transform' => true ),
        );
    }

    public static function enqueue_admin_assets( $hook ) {
        if ( $hook !== 'toplevel_page_bayyanoor-settings' ) return;
        wp_enqueue_style( 'bayyanoor-admin-css', BAYYANOOR_PLUGIN_URL . 'assets/css/admin.css', array(), BAYYANOOR_VERSION );
    }

    /* =========================================================
     * RENDER — Settings page with tabs
     * ========================================================= */

    public static function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $branding      = self::get_branding();
        $sidebar_nav   = self::get_sidebar_nav();
        $top_nav       = self::get_top_nav();
        $mobile_nav    = self::get_mobile_nav();
        $quick_actions = self::get_quick_actions();

        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'branding';
        ?>
        <div class="wrap bn-admin-wrap">
            <h1 class="bn-admin-title">
                <span class="bn-admin-logo">BN</span>
                Bayyanoor Settings
            </h1>

            <nav class="nav-tab-wrapper bn-admin-tabs">
                <a href="?page=bayyanoor-settings&tab=branding" class="nav-tab <?php echo $active_tab === 'branding' ? 'nav-tab-active' : ''; ?>">Branding</a>
                <a href="?page=bayyanoor-settings&tab=sidebar" class="nav-tab <?php echo $active_tab === 'sidebar' ? 'nav-tab-active' : ''; ?>">Sidebar Nav</a>
                <a href="?page=bayyanoor-settings&tab=topnav" class="nav-tab <?php echo $active_tab === 'topnav' ? 'nav-tab-active' : ''; ?>">Top Nav</a>
                <a href="?page=bayyanoor-settings&tab=mobilenav" class="nav-tab <?php echo $active_tab === 'mobilenav' ? 'nav-tab-active' : ''; ?>">Mobile Nav</a>
                <a href="?page=bayyanoor-settings&tab=quickactions" class="nav-tab <?php echo $active_tab === 'quickactions' ? 'nav-tab-active' : ''; ?>">Quick Actions</a>
            </nav>

            <form method="post" action="options.php" class="bn-admin-form">
                <?php settings_fields( 'bayyanoor_settings_group' ); ?>

                <?php if ( $active_tab === 'branding' ) : ?>
                    <table class="form-table bn-form-table">
                        <tr>
                            <th>Site Name</th>
                            <td><input type="text" name="<?php echo self::OPT_BRANDING; ?>[site_name]" value="<?php echo esc_attr( $branding['site_name'] ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th>Tagline</th>
                            <td><input type="text" name="<?php echo self::OPT_BRANDING; ?>[tagline]" value="<?php echo esc_attr( $branding['tagline'] ); ?>" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th>Logo SVG</th>
                            <td>
                                <textarea name="<?php echo self::OPT_BRANDING; ?>[logo_svg]" rows="5" class="large-text code"><?php echo esc_textarea( $branding['logo_svg'] ); ?></textarea>
                                <p class="description">Paste SVG markup for the logo icon. Preview appears in the app shell topbar.</p>
                                <?php if ( ! empty( $branding['logo_svg'] ) ) : ?>
                                    <div class="bn-svg-preview"><?php echo wp_kses( $branding['logo_svg'], self::allowed_svg_tags() ); ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                <?php elseif ( $active_tab === 'sidebar' ) : ?>
                    <?php self::render_nav_editor( self::OPT_SIDEBAR_NAV, $sidebar_nav, true ); ?>

                <?php elseif ( $active_tab === 'topnav' ) : ?>
                    <?php self::render_nav_editor( self::OPT_TOP_NAV, $top_nav, false ); ?>

                <?php elseif ( $active_tab === 'mobilenav' ) : ?>
                    <?php self::render_nav_editor( self::OPT_MOBILE_NAV, $mobile_nav, true ); ?>

                <?php elseif ( $active_tab === 'quickactions' ) : ?>
                    <?php self::render_nav_editor( self::OPT_QUICK_ACTIONS, $quick_actions, false, true ); ?>

                <?php endif; ?>

                <?php submit_button( 'Save Settings' ); ?>
            </form>

            <div class="bn-admin-info">
                <p><strong>Tip:</strong> To add a new nav item, add a row. To remove one, clear its label and save. Items with "Visible" unchecked will be hidden from students.</p>
                <p><strong>URLs:</strong> Use relative paths like <code>/bayyanoor-app</code>. The system will prepend your site URL automatically.</p>
            </div>
        </div>
        <?php
    }

    private static function render_nav_editor( $option_name, $items, $has_icon = true, $has_id = false ) {
        ?>
        <table class="widefat bn-nav-editor">
            <thead>
                <tr>
                    <th style="width:3%">#</th>
                    <th style="width:15%">Label</th>
                    <th style="width:20%">URL</th>
                    <?php if ( $has_icon ) : ?><th style="width:30%">Icon SVG</th><?php endif; ?>
                    <?php if ( $has_id ) : ?><th style="width:15%">Element ID</th><?php endif; ?>
                    <th style="width:8%">AJAX</th>
                    <th style="width:8%">Visible</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $i => $item ) : ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><input type="text" name="<?php echo $option_name; ?>[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $item['label'] ?? '' ); ?>" class="regular-text" /></td>
                    <td><input type="text" name="<?php echo $option_name; ?>[<?php echo $i; ?>][url]" value="<?php echo esc_attr( $item['url'] ?? '' ); ?>" class="regular-text" /></td>
                    <?php if ( $has_icon ) : ?>
                    <td><textarea name="<?php echo $option_name; ?>[<?php echo $i; ?>][icon]" rows="2" class="large-text code" style="font-size:11px"><?php echo esc_textarea( $item['icon'] ?? '' ); ?></textarea></td>
                    <?php endif; ?>
                    <?php if ( $has_id ) : ?>
                    <td><input type="text" name="<?php echo $option_name; ?>[<?php echo $i; ?>][id]" value="<?php echo esc_attr( $item['id'] ?? '' ); ?>" class="regular-text" /></td>
                    <?php endif; ?>
                    <td><input type="checkbox" name="<?php echo $option_name; ?>[<?php echo $i; ?>][ajax]" value="1" <?php checked( ! empty( $item['ajax'] ) ); ?> /></td>
                    <td><input type="checkbox" name="<?php echo $option_name; ?>[<?php echo $i; ?>][visible]" value="1" <?php checked( ! empty( $item['visible'] ) ); ?> /></td>
                </tr>
                <?php endforeach; ?>
                <!-- Extra empty row for adding new items -->
                <?php $next = count( $items ); ?>
                <tr class="bn-new-row">
                    <td><?php echo $next + 1; ?></td>
                    <td><input type="text" name="<?php echo $option_name; ?>[<?php echo $next; ?>][label]" value="" class="regular-text" placeholder="New item..." /></td>
                    <td><input type="text" name="<?php echo $option_name; ?>[<?php echo $next; ?>][url]" value="" class="regular-text" placeholder="/page-slug" /></td>
                    <?php if ( $has_icon ) : ?>
                    <td><textarea name="<?php echo $option_name; ?>[<?php echo $next; ?>][icon]" rows="2" class="large-text code" style="font-size:11px" placeholder="<svg>...</svg>"></textarea></td>
                    <?php endif; ?>
                    <?php if ( $has_id ) : ?>
                    <td><input type="text" name="<?php echo $option_name; ?>[<?php echo $next; ?>][id]" value="" class="regular-text" /></td>
                    <?php endif; ?>
                    <td><input type="checkbox" name="<?php echo $option_name; ?>[<?php echo $next; ?>][ajax]" value="1" /></td>
                    <td><input type="checkbox" name="<?php echo $option_name; ?>[<?php echo $next; ?>][visible]" value="1" /></td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}
Bayyanoor_Admin::init();
