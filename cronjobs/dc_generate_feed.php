<?php
/**
 * 	Make a JSON productfeed based on all product this website sells
 * 	with the correct pricing
 *
 * 	Optional:
 * 	?categories=ID,ID,ID,ID (comma separated list of categories -- 0 to 100 is used if not set)
 *
 */

// required files & vars
define('SITE_PATH_CRON',dirname(dirname(__FILE__)).'/'); // for cronjobs, because $_SERVER is not available
require_once(SITE_PATH_CRON.'includes/php/dc_connect.php');
require_once(SITE_PATH_CRON.'_classes/class.database.php');
$objDB = new DB();

require_once(SITE_PATH_CRON.'includes/php/dc_config.php');
require_once(SITE_PATH_CRON.'includes/php/dc_functions.php');
require_once(SITE_PATH_CRON.'libaries/Api_Inktweb/API.class.php');	// DropCart API

// New Inktweb Api object
$Api 			= new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

// GET vars
$_GET 		= sanitize($_GET);
$arrCategories 	= explode(',', $_GET['categories']);

// if not set; check all categories from 0 to 100
if (empty($_GET['categories'])) {
	$arrCategories = range(0,100);
}

// start product array
$arrProducts 		= array();

// loop all categories
foreach ($arrCategories as $intCategory) {
	
	// get all products for category
	$arrApiProducts = $Api->getProductsByCategory($intCategory, '?limit=999999&offset=0');

	// loop through products and put in array
	foreach ($arrApiProducts->products as $product) {

		$arrProduct = array(
			'ID'		 	=> $product->id,
			'OEM-nummer'	=> $product->oem,
			'EAN' 			=> $product->ean,
			'Merk' 			=> $product->brand,
			'Titel'			=> $product->title,
			'Verzendkosten'	=> SITE_SHIPPING,
			'Prijs'			=> calculateProductPrice($product->details[0], $product->id, false),
			'URL'			=> SITE_URL.'product/'.$product->id.'/'
		);

		$arrProducts[] = $arrProduct;
	}

}

header('Content-type: application/json');

// display error if array is empty
if (empty($arrProducts)) {

	$arrError = array(
		'code' => '204', // No content
		'message' => 'Er zijn geen producten gevonden...',
	);
	echo json_encode($arrError);
}
// all good, display proper JSON
else {
	echo json_encode($arrProducts);
}