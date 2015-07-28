<?php
// include this file in every page that needs to be login protected

session_start();

// redirect user to login.php if the session is not set
if (!isset($_SESSION['sessionAdminId'])) {

    $url = SITE_URL . '/beheer/dc_login.php';

    header('HTTP/1.0 403 Forbidden');
    header("Location: {$url}");
}
