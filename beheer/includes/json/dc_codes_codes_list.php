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

$strShow 	= (isset($_GET['show'])) ? strtolower($_GET['show']) : null;
$strWhere = null;
$strSortColumn	= (isset($_GET['sort_column'])) ? $_GET["sort_column"] : null;
$strSortOrder	= (isset($_GET['sort_order'])) ? strtoupper($_GET["sort_order"]) : null;
$strQuery		= (isset($_GET['query'])) ? $_GET["query"] : null;
$strShow		= (isset($_GET['show'])) ? $_GET["show"] : null;
$intCodeId		= (isset($_GET['codeId'])) ? (int) $_GET["codeId"] : null;
$strCodeType	= (isset($_GET['type'])) ? $_GET["type"] : null;
$intOffset		= (isset($_GET['offset'])) ? (int) $_GET["offset"] : 0;
$intLimit		= (isset($_GET['limit'])) ? (int) $_GET["limit"] : 15;

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

	/* is there a values set for the code ? */
	$discountValue = (!is_null($objCode->discountValue))
		? $objCode->discountValue 			# Yes, use it
		: $objCode->discountValueCodes;		# No, use default

	$arrJson['details'][$i][]	= $objCode->code;
	
	if($objCode->discountType == 'price') {
		$arrJson['details'][$i][]	= '&euro; '.$discountValue;
	} elseif($objCode->discountType == 'percentage') {
		$arrJson['details'][$i][]	= $discountValue.' %';
	} elseif($objCode->discountTypeCodes == 'price') {
		$arrJson['details'][$i][]	= '&euro; '.$discountValue;
	} elseif($objCode->discountTypeCodes == 'percentage') {
		$arrJson['details'][$i][]	= $discountValue.' %';
	}
	
	$arrJson['details'][$i][]	= ($objCode->parentOrderId != 0) ? '<a href="dc_order_manage.php?id='.$objCode->parentOrderId.'&action=view">'.$objCode->parentOrderId.'</a>' : '';
	$arrJson['details'][$i][]	= $objCode->validationCode;
	$arrJson['details'][$i][]	= ($objCode->orderId != 0) ? 'Ja (<a href="dc_order_manage.php?id='.$objCode->orderId.'&action=view">'.$objCode->orderId.'</a>)' : 'Nee';
	$arrJson['details'][$i][]	= ($objCode->export == 1) ? 'Ja' : 'Nee';
	$arrJson['details'][$i][]	= '<a href="/beheer/dc_codes_codes_manage.php?codeId='.$objCode->codeId.'&amp;id='.$objCode->id.'">
	<span class="glyphicon glyphicon-edit"></span></a>';

	$i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>