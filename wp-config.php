<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define( 'DISALLOW_FILE_EDIT', true ); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'turwpdb' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'a=26jv|.8KX,34&y,)*!bWi#Ep_CWZrd|H~PRD8j CnX6$?2gD u^fp]#p||_}k_');
define('SECURE_AUTH_KEY',  'xUnoB.P_xKy)i]CK;+am~M!p T4/9te:tJ<O},wM}w1w<jq1+BxPRw:C+UbX`hE0');
define('LOGGED_IN_KEY',    '3OZZyj3F=/@V~@Q?V|{>{sN&9GS7I7iDi8]*h0-cFI]JI#,iYi3U24oX!cAloN0%');
define('NONCE_KEY',        'UzBF`(q8Sz:I N(`0N,lwyD}v <jesYYsj]/;laK(3($YzmmCDIozZu^{@oC$[9T');
define('AUTH_SALT',        '(oPl}5izYy?28FBX!3ff#jZe2dZ:Dl~gU?3s7H$Y)3B%-4*rMj>*AY8:D=,@7Ov ');
define('SECURE_AUTH_SALT', 'u`Gt(X,EtrAA-9* mDU9*;ABG?:s:ar?#i[qvP?^0dZp#H4Co(3>wQy-%~259KV.');
define('LOGGED_IN_SALT',   '($xZak^0:p[3!#VYYn;G!L{Qr]nU|u=u=xo8eLh%CN[KCmSgLL=8iJFK:,yHf|}i');
define('NONCE_SALT',       '|z, x6pM*S]+=)|OYLq+d%&1nPS]9,,Z^Rl9oOiRB-w6lT3fCp4}`(bky@@TICpM');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'markmyturfdb_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
