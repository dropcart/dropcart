<?php
session_start();
// log out the user

session_destroy();
header('Location: '.SITE_URL.'/beheer/dc_login.php');

?>