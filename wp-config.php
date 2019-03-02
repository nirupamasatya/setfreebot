<?php
define('DB_NAME', 'test-wp-P9oYig6F');
define('DB_USER', 'ApOvNm3UT9yt');
define('DB_PASSWORD', 'snfvPMPJ0cxRlQOo');

define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('AUTH_KEY',         'cukiMyzVLQGuG08g4e7eaXyEDkG5UTUgMeYlXZ9N');
define('SECURE_AUTH_KEY',  '1YPl3O6zAFrcSkwakfkuzpgVAO70ZjCdcSlmk3Z4');
define('LOGGED_IN_KEY',    '1ql25lum3jRo8bslJ1p10ngnnHXlUqfN8wQQ5NUX');
define('NONCE_KEY',        'a3C8wwry6vYspgpPtVTVAFharplYc8xKliFG60A2');
define('AUTH_SALT',        'AQNXsrM8JpmiM2U86GRgagjlD9LxpxOx9DfNQxgG');
define('SECURE_AUTH_SALT', 'ooKhQBORralZ6QHBNcRbEpvEDE9xan9rKqt2kGaA');
define('LOGGED_IN_SALT',   'ESEcEtUYqhRF4W5R4qYM6UtXfzRv695TmJBsCj7H');
define('NONCE_SALT',       'n9dpGjmkBwc0t6AGCUxrFauyqZA3u7scKY3ED1bB');

$table_prefix  = 'wp_7609d149ef_';

define('SP_REQUEST_URL', ($_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);

define('WP_SITEURL', SP_REQUEST_URL);
define('WP_HOME', SP_REQUEST_URL);

/* Change WP_MEMORY_LIMIT to increase the memory limit for public pages. */
define('WP_MEMORY_LIMIT', '256M');

/* Uncomment and change WP_MAX_MEMORY_LIMIT to increase the memory limit for admin pages. */
//define('WP_MAX_MEMORY_LIMIT', '256M');

/* That's all, stop editing! Happy blogging. */

if ( !defined('ABSPATH') )
        define('ABSPATH', dirname(__FILE__) . '/');

require_once(ABSPATH . 'wp-settings.php');
