<?php
// config.php

if (!defined('ABSPATH')) define('ABSPATH', dirname(__FILE__) . '/');

define('DEBUG', '1');                                   // Debug mode

define('SYS_DIR', 'sys');
define('TPL_DIR', 'sys/templates');
define('CONTENT_DIR', ABSPATH . 'content');

require_once(ABSPATH . SYS_DIR . '/functions.php');

define('DB_NAME', 'doc_system');
define('DB_USER', 'root');
define('DB_PASSWORD', 'mysql');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');

$appNameFull = "DOC 0.01";

setlocale(LC_ALL, 'uk_UK.UTF-8');
mb_internal_encoding("UTF-8");
mb_http_input("UTF-8");
mb_http_output("UTF-8");
date_default_timezone_set('Europe/Kiev');

