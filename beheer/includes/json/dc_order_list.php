<?php
session_start();

// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_config.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_functions.php');

$_GET = sanitize($_GET);

$strShow 	= strtolower($_GET['show']);

$strSortColumn	= $_GET["sort_column"];
$strSortOrder	= strtoupper($_GET["sort_order"]);
$strQuery		= $_GET["query"];
$strShow		= $_GET["show"];
$intOffset		= (int) $_GET["offset"];
$intLimit		= (int) $_GET["limit"];
$orderNumberPrefix = formOption('order_number_prefix');

$strSort	= ($strSortColumn != '') ? " ORDER BY `" . $strSortColumn . "` ".$strSortOrder : '';
$strWhere	.= ($strQuery != '') ? " AND (co.company LIKE '%".$strQuery."%' OR co.firstname LIKE '%".$strQuery."%' OR co.lastname LIKE '%".$strQuery."%')" : '';
$strLimit	= " LIMIT ".$intOffset.",".$intLimit;

if (empty($strShow) OR $strShow == "new") {
	$strWhere .= " AND (co.status = 0 OR co.status = 1)";
}
elseif ($strShow == "done") {
	$strWhere .= " AND co.status = 4";
}

$strSQL 	=
		"SELECT co.orderId,
		co.custId,
		co.entryDate,
		co.company,
		co.firstname,
		co.lastname,
		co.shippingCosts,
		co.totalPrice,
		co.paymentStatus,
		co.paymethodId,
		co.status,
		SUM(cod.quantity) AS items
		FROM ".DB_PREFIX."customers_orders co
		INNER JOIN ".DB_PREFIX."customers_orders_details cod ON (cod.orderId = co.orderId)
		WHERE 1
		".$strWhere .
		" GROUP BY co.orderId DESC" .
		$strSort;
$resultCount = $objDB->sqlExecute($strSQL);
$count 	= $objDB->getNumRows($resultCount);

$strSQL = $strSQL . $strLimit;
$result = $objDB->sqlExecute($strSQL);

$arrJson['totalRows'] = $count;
$i=0;

while ($objOrder = $objDB->getObject($result)) {

	$name = "";
	if (!empty($objOrder->company)) {
		$name .= $objOrder->company . ", ";
	}
	$name .= $objOrder->firstname . " ";
	$name .= $objOrder->lastname . " ";

	$arrJson['details'][$i][]	= $orderNumberPrefix . $objOrder->orderId;
	$arrJson['details'][$i][]	= $objOrder->entryDate;
	$arrJson['details'][$i][]	= $name;
	$arrJson['details'][$i][]	= $objOrder->items;
	$arrJson['details'][$i][]	= money_format('%(#1n', $objOrder->totalPrice);
	$arrJson['details'][$i][]	= '<a href="/includes/pdf/dc_invoice.php?orderId='.$objOrder->orderId.'"><span class="glyphicon glyphicon-file"></span></a>';
	$arrJson['details'][$i][]	= '<a href="/beheer/dc_order_manage.php?id='.$objOrder->orderId.'&action=view"><span class="glyphicon glyphicon-edit"></span></a>';

	$i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>