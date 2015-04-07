<?php
session_start();

// Required includes
require_once('../php/dc_connect.php');
require_once('../../_classes/class.database.php');
$objDB = new DB();
require_once('../php/dc_config.php');

// Start API
require_once('../../libraries/Api_Inktweb/API.class.php');

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

$intPrinterBrand	= (int) $_GET["printerBrandId"];
$arrPrinters		= $Api->getPrinterSeries($intPrinterBrand);

function sortByTitle($a, $b) {
	return strcmp($a->title, $b->title);
}

usort($arrPrinters->serie, 'sortByTitle');

echo json_encode($arrPrinters->serie);
?>