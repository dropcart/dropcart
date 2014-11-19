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

$strSort	= ($strSortColumn != '') ? " ORDER BY `" . $strSortColumn . "` ".$strSortOrder : '';
$strWhere	.= ($strQuery != '') ? " AND (c.company LIKE '%".$strQuery."%' OR c.firstname LIKE '%".$strQuery."%' OR c.lastname LIKE '%".$strQuery."%' OR c.email LIKE '%".$strQuery."%')" : '';
$strLimit	= " LIMIT ".$intOffset.",".$intLimit;

$strSQL 	= 
		"SELECT c.id,
		c.entryDate,
		c.company,
		c.firstname,
		c.lastname,
		c.email,
		COUNT(co.orderId) AS numOrders
		FROM ".DB_PREFIX."customers c
		LEFT JOIN ".DB_PREFIX."customers_orders co ON (co.custId = c.id)
		WHERE 1
		".$strWhere . 
		" GROUP BY c.id DESC" .
		$strSort;
$resultCount = $objDB->sqlExecute($strSQL);
$count 	= $objDB->getNumRows($resultCount);

$strSQL = $strSQL . $strLimit;
$result = $objDB->sqlExecute($strSQL);

$arrJson['totalRows'] = $count;
$i=0;

while ($objCust = $objDB->getObject($result)) {

	$name = "";
	if (!empty($objCust->company)) {
		$name .= $objCust->company . ", ";
	}
	$name .= $objCust->firstname . " ";
	$name .= $objCust->lastname . " ";
		
	$arrJson['details'][$i][]	= $objCust->id;
	$arrJson['details'][$i][]	= $objCust->entryDate;
	$arrJson['details'][$i][]	= $name;
	$arrJson['details'][$i][]	= $objCust->email;
	$arrJson['details'][$i][]	= $objCust->numOrders;
	$arrJson['details'][$i][]	= '<a href="/beheer/dc_customer_manage.php?id='.$objCust->id.'&action=view"><span class="glyphicon glyphicon-edit"></span></a>';
	
	$i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>