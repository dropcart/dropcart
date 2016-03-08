<?php
/**
 *  Make a JSON productfeed based on all product this website sells
 *  with the correct pricing
 *
 *  Optional:
 *  ?categories=ID,ID,ID,ID (comma separated list of categories -- 0 to 100 is used if not set)
 *
 */

// required files & vars
require_once __DIR__ . '/../includes/php/dc_connect.php';
require_once __DIR__ . '/../_classes/class.database.php';
$objDB = new DB();

require_once __DIR__ . '/../includes/php/dc_config.php';
require_once __DIR__ . '/../includes/php/dc_functions.php';
require_once __DIR__ . '/../libraries/Api_Inktweb/API.class.php'; // DropCart API

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

// GET vars
$_GET = sanitize($_GET);

// start product array
$arrProducts = array();
$resultCategories = $Api->getProductsByCategory(0);

if (!isset($resultCategories->categories)) {
    exit();
}

// loop all categories
foreach ($resultCategories->categories as $category) {

    // get all products for category
    $arrApiProducts = $Api->getProductsByCategory($category->id, '?limit=999999&offset=0');

    /* Dont go into the loop if there are no products */
    if (!(isset($arrApiProducts->products))) {
        continue;
    }

    // loop through products and put in array
    foreach ($arrApiProducts->products as $product) {

        $arrProduct = array(
            'ID' => $product->id,
            'OEM-nummer' => $product->oem,
            'EAN' => $product->ean,
            'Merk' => $product->brand,
            'Titel' => $product->title,
            'Verzendkosten' => SITE_SHIPPING,
            'Prijs' => calculateProductPrice($product->details[0], $product->id, '', false),
            'URL' => SITE_URL . '/' . rewriteUrl($category->title) . '/' . rewriteUrl($product->title) . '/' . $product->id . '/',
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
} else {
    // all good, display proper JSON
    echo json_encode($arrProducts);
}