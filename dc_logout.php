<?php
require_once __DIR__ . '/includes/php/dc_connect.php';
require_once __DIR__ . '/_classes/class.database.php';
$objDB = new DB();
require_once __DIR__ . '/includes/php/dc_config.php';
// log out the user
session_start();
session_destroy();

header('Location: ' . SITE_URL);