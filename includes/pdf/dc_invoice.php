<?php
// Required includes
require_once (__DIR__.'/../../includes/php/dc_connect.php');
require_once (__DIR__.'/../../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../../includes/php/dc_config.php');

// Require login
require_once (__DIR__.'/../../beheer/includes/php/dc_session.php');

// Page specific includes
require_once (__DIR__.'/../../includes/php/dc_functions.php');
require_once (__DIR__.'/../../libraries/mpdf/mpdf.php'); // MPDF Libary
require_once (__DIR__.'/../../libraries/Twig/Autoloader.php'); // Twig template engine

require_once('../../libraries/Api_Inktweb/API.class.php');

// Initialize variables for generateInvoicePDF()
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);
$mpdf = new mPDF();
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem("../templates");
$twig = new Twig_Environment( $loader, array("cache" => false) );


$intOrderId = (int) $_GET["orderId"];

// Get invoice and display
$strTemplate = generateInvoicePDF($intOrderId, false);
$mpdf->Output();