<?php
/** Disable php errors */
error_reporting(0);
ini_set('memory_limit', '-1');
ini_set('upload_max_filesize', '250M');
ini_set('post_max_size', '250M');

$config = parse_ini_file('inc/cnf/config.ini'); // Connection infos.

/** The name of the database */
define('DB_NAME', $config["db_name"]);

/** MySQL database username */
define('DB_USER', $config["db_user"]);

/** MySQL database password */
define('DB_PASSWORD', $config["db_pwd"]);

/** MySQL hostname */
define('DB_HOST', $config["db_host"]);

define('DB_PORT', $config["db_port"]);

/** Max upload size */
// define('MAX_UPLOAD_SIZE',28); // megabytes

define('MAX_UPLOAD_SIZE',1024); // megabytes

/** Results per page */
define('RESULT_PER_PAGE',12);

define('M_L', 'ar');

define('ROOT',__DIR__);

define('EXE',__DIR__ . '/exe/');

define('CRX',__DIR__ . '/crxs/');

define('PY',__DIR__ . '/py/');

/** Post type didn't had slices */
define('NO_SLICE',["quote","name","dictionary","research","book","history"]);

/** Files upload directory */
define('UPLOAD_DIR',__dir__ . "/uploads/");

/** Available social media autoh */
define("SOCIAL_LOGIN",["Facebook","Twitter"]);

/** */
// define('SITEURL', 'https://nas.4fk.org'); // Adjust for local environment
define('SITEURL', 'http://lanas.local:8888'); // Adjust for local environment

define('SITENAME', 'lanas');

// define('SITEURL', 'https://nas.4fk.org');

