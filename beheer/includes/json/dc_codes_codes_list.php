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
$intCodeId		= (int) $_GET["codeId"];
$strCodeType	= $_GET["type"];
$intOffset		= (int) $_GET["offset"];
$intLimit		= (int) $_GET["limit"];

$strSort	= ($strSortColumn != '') ? " ORDER BY `" . $strSortColumn . "` ".$strSortOrder : '';
$strWhere	= ($strQuery != '') ? " AND (dc_c.code LIKE '%".$strQuery."%' OR dc_c.validationCode LIKE '%".$strQuery."%')" : '';
$strLimit	= " LIMIT ".$intOffset.",".$intLimit;

if($strCodeType == 'used') {
	$strWhere .= " AND dc_c.orderId != 0 ";
} elseif($strCodeType == 'notused') {
	$strWhere .= " AND dc_c.orderId = 0 ";
} elseif($strCodeType == 'notexported') {
	$strWhere .= " AND dc_c.export = 0 AND dc_c.orderid != 0 ";
} elseif($strCodeType == 'exported') {
	$strWhere .= " AND dc_c.export = 1 AND dc_c.orderid != 0 ";
}

$strSQL = 
		"SELECT dc_c.*, dc.discountValue as discountValueCodes, dc.discountType as discountTypeCodes
		FROM ".DB_PREFIX."discountcodes_codes dc_c
		INNER JOIN ".DB_PREFIX."discountcodes dc ON dc.id = dc_c.codeId
		WHERE dc_c.codeId = ".$intCodeId." " .
		$strWhere . $strSort;
$resultCount = $objDB->sqlExecute($strSQL);
$count 	= $objDB->getNumRows($resultCount);

$strSQL = $strSQL . $strLimit;
$result = $objDB->sqlExecute($strSQL);

$arrJson['totalRows'] = $count;
$i=0;

while ($objCode = $objDB->getObject($result)) {

	$arrJson['details'][$i][]	= $objCode->code;
	
	if($objCode->discountType == 'price') {
		$arrJson['details'][$i][]	= '&euro; '.$objCode->discountValue;
	} elseif($objCode->discountType == 'percentage') {
		$arrJson['details'][$i][]	= $objCode->discountValue.' %';
	} elseif($objCode->discountTypeCodes == 'price') {
		$arrJson['details'][$i][]	= '&euro; '.$objCode->discountValueCodes;
	} elseif($objCode->discountTypeCodes == 'percentage') {
		$arrJson['details'][$i][]	= $objCode->discountValueCodes.' %';
	}
	
	$arrJson['details'][$i][]	= ($objCode->parentOrderId != 0) ? '<a href="dc_order_manage.php?id='.$objCode->parentOrderId.'&action=view">'.$objCode->parentOrderId.'</a>' : '';
	$arrJson['details'][$i][]	= $objCode->validationCode;
	$arrJson['details'][$i][]	= ($objCode->orderId != 0) ? 'Ja (<a href="dc_order_manage.php?id='.$objCode->orderId.'&action=view">'.$objCode->orderId.'</a>)' : 'Nee';
	$arrJson['details'][$i][]	= ($objCode->export == 1) ? 'Ja' : 'Nee';
	
	$i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>