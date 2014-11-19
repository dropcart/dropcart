<?php
session_start();

// Required includes
require_once('../php/dc_connect.php');
require_once('../../_classes/class.database.php');
$objDB = new DB();
require_once('../php/dc_config.php');

// Page specific includes
require_once('../../_classes/class.cart.php');
require_once('../php/dc_functions.php');

// Start API
require_once('../../libaries/Api_Inktweb/API.class.php');

$intSessionId 	= session_id();
$intCustomerId 	= (isset($_SESSION["customerId"])) ? $_SESSION["customerId"] : 0;

$intProductId	= (int) $_GET["productId"];
$intQuantity	= (int) $_GET["quantity"];

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

// New Databaseobject
$objDB = new DB();

// New Cartobject
$objCart = new Cart();
$objCart->setDatabaseObject($objDB);
$objCart->setSessionId($intSessionId);
$objCart->setCustomerId($intCustomerId);
$objCart->addProduct($intProductId, $intQuantity);

//getting cart
$result_cart = $objCart->getCart();

//calculating total
while($objCart = $objDB->getObject($result_cart)) {
	
	$Product	= $Api->getProduct($objCart->productId);
	$dblPrice 	= calculateProductPrice($Product->getPrice(), $objCart->productId, false);
	
	//adding to totals
	$intItems += $objCart->quantity;
	$dblPriceTotal += ($dblPrice * $objCart->quantity);
}

$arrOutput['cartItems']			= $intItems;
$arrOutput['cartSubTotal']		= money_format('%(#1n', $dblPriceTotal);
$arrOutput['cartShippingCosts']	= money_format('%(#1n', SITE_SHIPPING);
$arrOutput['cartTotal']			= money_format('%(#1n', $dblPriceTotal + SITE_SHIPPING);

echo json_encode($arrOutput);

//close db
$objDB->closeDB();
?>