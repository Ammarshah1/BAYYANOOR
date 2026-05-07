<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'BAYYANOOR' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '1NllFXxGd),Io{>%&8^O:^LB](xn#4m{r^W5E(t0UApT~V|aDNb0X8Z>dudFO?gN' );
define( 'SECURE_AUTH_KEY',  'Xy4KqJG@4#{T~@bmn^+X{4?z<` ;{xv[HZH&9rIfcGTW;/]AGv_n3qIM8f}I1[No' );
define( 'LOGGED_IN_KEY',    '6q_0||EK~U(t?@f]h$>lC9pS%!T@$|z>V/J!v,LLr6Bf1wwkSVlq{7k)2R8[M7If' );
define( 'NONCE_KEY',        '|z:)b98QJUnNXok})IPCs|.,_5Hq$7a$701d@4$t4l,HgJu|t dC8aw[=K+1E2![' );
define( 'AUTH_SALT',        'uWW[ yA{:%033y=/pEFZ-[L=rsuTH4w$_-D)?Cpa??~M9 @oz=NzK!1zh1no*p>f' );
define( 'SECURE_AUTH_SALT', 'mzhVsiQJcT/k=vGLwpB)Q)*_m3r0),{?d-PPRYqQO#*rk4=&9%<dO)GeC`RxjJ?}' );
define( 'LOGGED_IN_SALT',   '-3=,ory( <<<3#$+4+x_<vAhb/Z>y8O8f=*e$At&XR@]F=yF;:W.Yy>T-!|@v>us' );
define( 'NONCE_SALT',       'qw!to]Se:L&%Qf|oh:#jpJ(y$_%p3eaxq9T`^<;SY#qr%?_FVRpol[&(`t6<*yE3' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
