<?php

// Required includes
require_once (__DIR__.'/../includes/php/dc_connect.php');
require_once (__DIR__.'/../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../beheer/includes/php/dc_config.php');

// Page specific includes
require_once (__DIR__.'/../beheer/includes/php/dc_functions.php');

// Redirects user when not logged in as ADMIN
require_once (__DIR__.'/../beheer/includes/php/dc_session.php');

$userId = intval($_GET['id']);

if (empty($userId)) {
	echo "Geen ?id= opgegeven.";
	exit();
}


// Login as user
$_SESSION['customerId'] = $userId;
header('Location: /dc_profile.php'); 
