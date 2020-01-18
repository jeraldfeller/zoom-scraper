<?php
ob_start();
session_start();
define('DB_USER', 'admin_demo');
define('DB_PWD', 'iOKviRk8RS');
define('DB_NAME', 'admin_demo');
define('DB_HOST', 'localhost');

//define('DB_USER', 'root');
//define('DB_PWD', '');
//define('DB_NAME', 'zoominfo');
//define('DB_HOST', 'localhost');


define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME .'');
