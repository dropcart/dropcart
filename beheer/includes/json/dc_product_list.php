<?php
session_start();

// Required includes
require_once __DIR__ . '/../../../includes/php/dc_connect.php';
require_once __DIR__ . '/../../../_classes/class.database.php';
$objDB = new DB();
require_once __DIR__ . '/../../includes/php/dc_config.php';

// Page specific includes
require_once __DIR__ . '/../../includes/php/dc_functions.php';

// Start API
require_once __DIR__ . '/../../../libraries/Api_Inktweb/API.class.php';
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

$_GET = sanitize($_GET);

$strShow = (isset($_GET['show'])) ? strtolower($_GET['show']) : null;
$strWhere = null;
$strSortColumn = (isset($_GET['sort_column'])) ? $_GET["sort_column"] : null;
$strSortOrder = (isset($_GET['sort_order'])) ? strtoupper($_GET["sort_order"]) : null;
$strQuery = (isset($_GET['query'])) ? $_GET["query"] : null;
$strShow = (isset($_GET["show"])) ? $_GET['show'] : null;
$intOffset = (isset($_GET["offset"])) ? (int) $_GET["offset"] : 0;
$intLimit = (isset($_GET["limit"])) ? (int) $_GET["limit"] : 15;

$strSort = ($strSortColumn != '') ? " ORDER BY `" . $strSortColumn . "` " . $strSortOrder : '';
$strWhere .= ($strQuery != '') ? " AND 1" : '';
$strLimit = " LIMIT " . $intOffset . "," . $intLimit;

$strSQL =
"SELECT p.id,
        p.price
        FROM " . DB_PREFIX . "products p
        WHERE 1
        " . $strWhere .
" GROUP BY p.id " .
$strSort;
$resultCount = $objDB->sqlExecute($strSQL);
$count = $objDB->getNumRows($resultCount);

$strSQL = $strSQL . $strLimit;
$result = $objDB->sqlExecute($strSQL);

$arrJson['totalRows'] = $count;
$i = 0;

while ($objProduct = $objDB->getObject($result)) {

    $Product = $Api->getProduct($objProduct->id, '?fields=title,price');
    if (!is_object($Product) || (isset($Product->errors) && !empty($Product->errors))) {
        continue;
    }

    $arrJson['details'][$i][] = $Product->getId();
    $arrJson['details'][$i][] = $Product->getTitle();
    $arrJson['details'][$i][] = calculateProductPrice($Product->getPrice(), $objProduct->id);
    $arrJson['details'][$i][] = '<a href="' . SITE_URL . '/beheer/dc_product_manage.php?id=' . $objProduct->id . '"><span class="glyphicon glyphicon-edit"></span></a>';

    $i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>