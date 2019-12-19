<?php
ob_start();
session_start();
define('DB_USER', 'approval_staging');
define('DB_PWD', 'v+Eo,v#MwrHG');
define('DB_NAME', 'approval_test');
define('DB_HOST', '52.205.254.210');
define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME .'');
