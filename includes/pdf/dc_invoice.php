<?php
// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_config.php');

// Require login
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_session.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_functions.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/libraries/mpdf/mpdf.php'); // MPDF Libary
require_once ($_SERVER['DOCUMENT_ROOT'].'/libraries/Twig/Autoloader.php'); // Twig template engine

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