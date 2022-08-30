<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u104419470_DkmRt' );

/** Database username */
define( 'DB_USER', 'u104419470_OYTHL' );

/** Database password */
define( 'DB_PASSWORD', 'r8l3tFazTE' );

/** Database hostname */
define( 'DB_HOST', 'mysql' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'HaisRe$>`m&jT@<x5*l7XxDs0H(W!hVejc(i!S!(-SeW$z&<Tv+`}lSowR0cd8@r' );
define( 'SECURE_AUTH_KEY',   '#-tIZz_Qsv$Uq*^mUM5iRV4a0sq%^m+Df;wvu6uf*ywPsj x{);|f<Wa ;^-irY$' );
define( 'LOGGED_IN_KEY',     'R#(@]_t*[MO^D#a<0.z9,7l]k/bv|AP: W+Ok>:)-=bYL!m6$vm*D9XzYT.#6`6]' );
define( 'NONCE_KEY',         'o 5h1v$:fPRhgezY<n!0[L2j.]F!y>gs4%59#*&a7+1<@K$k=q~#Im2Ki2X[F)|o' );
define( 'AUTH_SALT',         '[J0tL(4sfe#.*?H8NZ99T*6Z;oC^)OU=is=D>Q9-IkCQ35x{-e5UwpE175%RTJ~M' );
define( 'SECURE_AUTH_SALT',  'x+kqjO$c+of;JD4sN-I&.q]Hhs)V2QUNeiyM+4C%=,r1~o50){U0>Q~<8lx#Zz,K' );
define( 'LOGGED_IN_SALT',    'f9:cQ8pX08.y2m>ysD;#s*]d[ZTw[$CxzdEbUui]{VjVi*QcKF)RK%2#MQ1Y^Msd' );
define( 'NONCE_SALT',        'dQZA- K%H`K]Y${tL4tVM *EHR!cRg@}ej*k|Sd&eHUG6)J/xSaI,&n0Z9Lq%)M[' );
define( 'WP_CACHE_KEY_SALT', 'U^<Q-4%cFYp,qv)La<O-PscjN<%e}*K@U&pvIU.5f2RrGSR5Wku9Fow@n97mx<nj' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );


/* Add any custom values between this line and the "stop editing" line. */



define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
