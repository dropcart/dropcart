<?php
// Required includes
require_once (__DIR__.'/../includes/php/dc_connect.php');
require_once (__DIR__.'/../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../beheer/includes/php/dc_config.php');

// Page specific includes
require_once (__DIR__.'/../beheer/includes/php/dc_functions.php');

// Upload class
require_once(__DIR__.'/../libraries/Tools/ImageUpload.php');

$objDB 		= new DB();

if( $_SERVER['REQUEST_METHOD'] !== "POST"){
   header('Location: '.SITE_URL.'/beheer');
   exit();
}

$imageUpload = new \Tools\ImageUpload('logo', __DIR__.'/../images/logo', ('logo-'.date('U')));

if( !$imageUpload->upload() ){
    $_SESSION['logo_upload_error'] = $imageUpload->error();
    header('Location: '.SITE_URL.'/beheer/dc_setting_admin.php');
    exit();
}

$strSQL = "INSERT INTO ".DB_PREFIX."options
                (optionName, optionValue)
                VALUES
                (   'SITE_LOGO',
                    '{$imageUpload->getName()}'
        ) ON DUPLICATE KEY UPDATE
        optionName = 'SITE_LOGO', optionValue = '{$imageUpload->getName()}'";

$objDB->sqlExecute($strSQL);

$_SESSION['logo_upload_success'] = "Het nieuwe logo is succesvol ge-upload";
header('Location: '.SITE_URL.'/beheer/dc_setting_admin.php');

