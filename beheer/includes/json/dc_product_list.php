<?php
session_start();

// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_config.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_functions.php');

// Start API
require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/Api_Inktweb/API.class.php');
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);


$_GET = sanitize($_GET);

$strShow 	= strtolower($_GET['show']);

$strSortColumn	= $_GET["sort_column"];
$strSortOrder	= strtoupper($_GET["sort_order"]);
$strQuery		= $_GET["query"];
$strShow		= $_GET["show"];
$intOffset		= (int) $_GET["offset"];
$intLimit		= (int) $_GET["limit"];

$strSort	= ($strSortColumn != '') ? " ORDER BY `" . $strSortColumn . "` ".$strSortOrder : '';
$strWhere	.= ($strQuery != '') ? " AND 1" : '';
$strLimit	= " LIMIT ".$intOffset.",".$intLimit;

$strSQL 	= 
		"SELECT p.id,
		p.price
		FROM ".DB_PREFIX."products p
		WHERE 1
		".$strWhere .
		" GROUP BY p.id " .
		$strSort;
$resultCount = $objDB->sqlExecute($strSQL);
$count 	= $objDB->getNumRows($resultCount);

$strSQL = $strSQL . $strLimit;
$result = $objDB->sqlExecute($strSQL);

$arrJson['totalRows'] = $count;
$i=0;

while ($objProduct = $objDB->getObject($result)) {
	
	$Product = $Api->getProduct($objProduct->id, '?fields=title');

	$arrJson['details'][$i][]	= $Product->getId();
	$arrJson['details'][$i][]	= $Product->getTitle();
	$arrJson['details'][$i][]	= calculateProductPrice($objProduct->price, $objProduct->id);
	$arrJson['details'][$i][]	= '<a href="/beheer/dc_product_manage.php?id='.$objProduct->id.'"><span class="glyphicon glyphicon-edit"></span></a>';
	
	$i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>