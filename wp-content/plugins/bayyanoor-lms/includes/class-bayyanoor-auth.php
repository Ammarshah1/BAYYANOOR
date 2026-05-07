<?php
/**
 * Bayyanoor Authentication System
 * Handles login, registration, logout, and role management.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bayyanoor_Auth {

    public static function init() {
        // Shortcodes
        add_shortcode( 'bayyanoor_login_form', array( __CLASS__, 'render_login_form' ) );

        // AJAX actions (logged-out users)
        add_action( 'wp_ajax_nopriv_bayyanoor_login', array( __CLASS__, 'handle_login' ) );
        add_action( 'wp_ajax_nopriv_bayyanoor_register', array( __CLASS__, 'handle_register' ) );
        add_action( 'wp_ajax_bayyanoor_logout', array( __CLASS__, 'handle_logout' ) );

        // Register custom roles on init
        add_action( 'init', array( __CLASS__, 'register_roles' ) );

        // Redirect logged-in users away from login page
        add_action( 'template_redirect', array( __CLASS__, 'redirect_logged_in_users' ) );
    }

    /**
     * Register custom WP roles for the LMS.
     */
    public static function register_roles() {
        if ( get_option( 'bayyanoor_roles_created' ) ) {
            return;
        }
        add_role( 'bayyanoor_student', __( 'Bayyanoor Student', 'bayyanoor' ), array(
            'read' => true,
        ) );
        add_role( 'bayyanoor_teacher', __( 'Bayyanoor Teacher', 'bayyanoor' ), array(
            'read'         => true,
            'edit_posts'   => true,
            'upload_files' => true,
        ) );
        add_role( 'bayyanoor_parent', __( 'Bayyanoor Parent', 'bayyanoor' ), array(
            'read' => true,
        ) );
        update_option( 'bayyanoor_roles_created', true );
    }

    /**
     * Redirect logged-in users from the login page to the dashboard.
     */
    public static function redirect_logged_in_users() {
        if ( is_page( 'bayyanoor-login' ) && is_user_logged_in() ) {
            wp_safe_redirect( home_url( '/bayyanoor-app' ) );
            exit;
        }
    }

    /**
     * AJAX Login Handler.
     */
    public static function handle_login() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
        $password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '';
        $remember = isset( $_POST['remember'] ) && $_POST['remember'] === 'true';

        if ( empty( $username ) || empty( $password ) ) {
            wp_send_json_error( 'Please fill in all fields.' );
        }

        $user = wp_signon( array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember,
        ), is_ssl() );

        if ( is_wp_error( $user ) ) {
            wp_send_json_error( 'Invalid username or password. Please try again.' );
        }

        wp_send_json_success( array(
            'redirect' => home_url( '/bayyanoor-app' ),
            'message'  => 'Login successful! Redirecting...',
        ) );
    }

    /**
     * AJAX Registration Handler.
     */
    public static function handle_register() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );

        $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
        $last_name  = isset( $_POST['last_name'] )  ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) )  : '';
        $email      = isset( $_POST['email'] )       ? sanitize_email( wp_unslash( $_POST['email'] ) )          : '';
        $username   = isset( $_POST['username'] )    ? sanitize_user( wp_unslash( $_POST['username'] ) )        : '';
        $password   = isset( $_POST['password'] )    ? wp_unslash( $_POST['password'] )                         : '';
        $confirm    = isset( $_POST['confirm_password'] ) ? wp_unslash( $_POST['confirm_password'] )            : '';

        // Validation
        if ( empty( $first_name ) || empty( $email ) || empty( $username ) || empty( $password ) ) {
            wp_send_json_error( 'Please fill in all required fields.' );
        }
        if ( ! is_email( $email ) ) {
            wp_send_json_error( 'Please enter a valid email address.' );
        }
        if ( strlen( $password ) < 8 ) {
            wp_send_json_error( 'Password must be at least 8 characters.' );
        }
        if ( $password !== $confirm ) {
            wp_send_json_error( 'Passwords do not match.' );
        }
        if ( username_exists( $username ) ) {
            wp_send_json_error( 'This username is already taken.' );
        }
        if ( email_exists( $email ) ) {
            wp_send_json_error( 'An account with this email already exists.' );
        }

        // Create user
        $user_id = wp_insert_user( array(
            'user_login'   => $username,
            'user_email'   => $email,
            'user_pass'    => $password,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $first_name . ( $last_name ? ' ' . $last_name : '' ),
            'role'         => 'bayyanoor_student',
        ) );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( 'Registration failed. Please try again.' );
        }

        // Initialize streak record
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'bayyanoor_streaks',
            array(
                'user_id'            => $user_id,
                'current_streak'     => 0,
                'longest_streak'     => 0,
                'last_activity_date' => current_time( 'Y-m-d' ),
                'total_xp'           => 0,
                'current_level'      => 1,
            ),
            array( '%d', '%d', '%d', '%s', '%d', '%d' )
        );

        // Award welcome badge
        $wpdb->insert(
            $wpdb->prefix . 'bayyanoor_badges',
            array(
                'user_id'    => $user_id,
                'badge_slug' => 'welcome',
                'badge_name' => 'Welcome to Bayyanoor',
                'badge_icon' => 'BN',
            ),
            array( '%d', '%s', '%s', '%s' )
        );

        // Auto-login after registration
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        wp_send_json_success( array(
            'redirect' => home_url( '/bayyanoor-app' ),
            'message'  => 'Welcome to Bayyanoor! Setting up your account...',
        ) );
    }

    /**
     * AJAX Logout Handler.
     */
    public static function handle_logout() {
        check_ajax_referer( 'bayyanoor_app_nonce', 'nonce' );
        wp_logout();
        wp_send_json_success( array(
            'redirect' => home_url( '/bayyanoor-login' ),
        ) );
    }

    /**
     * Render the login / registration form shortcode.
     */
    public static function render_login_form() {
        if ( is_user_logged_in() ) {
            return '<div class="bayyanoor-card"><p>You are already logged in. <a href="' . esc_url( home_url( '/bayyanoor-app' ) ) . '">Go to Dashboard</a></p></div>';
        }

        ob_start();
        ?>
        <div class="b-auth-container">
            <!-- Tab Switcher -->
            <div class="b-auth-tabs">
                <button class="b-auth-tab active" data-tab="login">Sign In</button>
                <button class="b-auth-tab" data-tab="register">Create Account</button>
            </div>

            <!-- Login Form -->
            <div class="b-auth-panel active" id="b-login-panel">
                <div class="b-auth-header">
                    <div class="b-auth-logo">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#115e41" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    </div>
                    <h2>Welcome Back</h2>
                    <p>Sign in to continue your learning journey</p>
                </div>
                <form id="bayyanoor-login-form" class="b-auth-form">
                    <div class="b-form-group">
                        <label for="login-username">Username or Email</label>
                        <input type="text" id="login-username" name="username" placeholder="Enter your username" required autocomplete="username" />
                    </div>
                    <div class="b-form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" placeholder="Enter your password" required autocomplete="current-password" />
                    </div>
                    <div class="b-form-row">
                        <label class="b-checkbox">
                            <input type="checkbox" name="remember" value="true" />
                            <span>Remember me</span>
                        </label>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="b-forgot-link">Forgot password?</a>
                    </div>
                    <div class="b-form-error" id="login-error" style="display:none;"></div>
                    <button type="submit" class="b-btn-submit" id="login-submit-btn">
                        <span class="btn-text">Sign In</span>
                        <span class="btn-loader" style="display:none;">
                            <svg class="spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </span>
                    </button>
                </form>
            </div>

            <!-- Registration Form -->
            <div class="b-auth-panel" id="b-register-panel">
                <div class="b-auth-header">
                    <div class="b-auth-logo">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#115e41" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    </div>
                    <h2>Join Bayyanoor</h2>
                    <p>Start your Quranic mastery journey today</p>
                </div>
                <form id="bayyanoor-register-form" class="b-auth-form">
                    <div class="b-form-row-2col">
                        <div class="b-form-group">
                            <label for="reg-first-name">First Name *</label>
                            <input type="text" id="reg-first-name" name="first_name" placeholder="Ahmed" required />
                        </div>
                        <div class="b-form-group">
                            <label for="reg-last-name">Last Name</label>
                            <input type="text" id="reg-last-name" name="last_name" placeholder="Khan" />
                        </div>
                    </div>
                    <div class="b-form-group">
                        <label for="reg-email">Email Address *</label>
                        <input type="email" id="reg-email" name="email" placeholder="you@example.com" required autocomplete="email" />
                    </div>
                    <div class="b-form-group">
                        <label for="reg-username">Username *</label>
                        <input type="text" id="reg-username" name="username" placeholder="Choose a username" required autocomplete="username" />
                    </div>
                    <div class="b-form-row-2col">
                        <div class="b-form-group">
                            <label for="reg-password">Password *</label>
                            <input type="password" id="reg-password" name="password" placeholder="Min 8 characters" required autocomplete="new-password" />
                        </div>
                        <div class="b-form-group">
                            <label for="reg-confirm-password">Confirm *</label>
                            <input type="password" id="reg-confirm-password" name="confirm_password" placeholder="Repeat password" required autocomplete="new-password" />
                        </div>
                    </div>
                    <div class="b-form-error" id="register-error" style="display:none;"></div>
                    <button type="submit" class="b-btn-submit" id="register-submit-btn">
                        <span class="btn-text">Create Account</span>
                        <span class="btn-loader" style="display:none;">
                            <svg class="spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </span>
                    </button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
Bayyanoor_Auth::init();
