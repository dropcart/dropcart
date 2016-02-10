<?php
// Required includes
require_once '../php/dc_connect.php';
require_once '../../_classes/class.database.php';
$objDB = new DB();
require_once '../php/dc_config.php';

// Page specific includes
require_once '../../libraries/Api_Inktweb/API.class.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

$intPrinterBrand = (int) $_GET["printerBrandId"];
$intPrinterSerie = (int) $_GET["printerSerieId"];
$arrPrinters = $Api->getPrinterTypes($intPrinterBrand, $intPrinterSerie);

echo json_encode($arrPrinters->printer);