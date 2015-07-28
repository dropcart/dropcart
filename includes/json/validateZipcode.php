<?php
// Required includes
require_once '../php/dc_connect.php';
require_once '../../_classes/class.database.php';
$objDB = new DB();
require_once '../php/dc_config.php';

// Page specific includes
require_once '../../_classes/class.postcode.php';

$strKey = ZIPCODE_API_KEY;
$strSecret = ZIPCODE_API_SECRET;

$strZipcode = $_GET["zipcode"];
$strHouseNumber = $_GET["houseNr"];
$strHouseNumberAddition = $_GET["houseNrAdd"];

$client = new PostcodeNl_Api_RestClient($strKey, $strSecret);

if (!empty($_POST['showRawRequestResponse'])) {
    $client->setDebugEnabled();
}

// surpress warnings/exceptions, error 500 will cause page to break
$result = @$client->lookupAddress($strZipcode, $strHouseNumber, $strHouseNumberAddition, !empty($_POST['validateHouseNumberAddition']));

echo json_encode($result);