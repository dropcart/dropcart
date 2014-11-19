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
$strWhere	.= ($strQuery != '') ? " AND (pc.navTitle LIKE '%".$strQuery."%')" : '';
$strLimit	= " LIMIT ".$intOffset.",".$intLimit;

$strSQL 	= 
		"SELECT pc.id, pc.navTitle, pc.online
		FROM ".DB_PREFIX."pages_content pc
		WHERE 1
		".$strWhere . 
		$strSort;
$resultCount = $objDB->sqlExecute($strSQL);
$count 	= $objDB->getNumRows($resultCount);

$strSQL = $strSQL . $strLimit;
$result = $objDB->sqlExecute($strSQL);

$arrJson['totalRows'] = $count;
$i=0;

while ($objPage = $objDB->getObject($result)) {

	$arrJson['details'][$i][]	= $objPage->navTitle;
	$arrJson['details'][$i][]	= ($objPage->online == 0) ? '<a href="/beheer/dc_page_manage.php?id='.$objPage->id.'&action=online"><span class="glyphicon glyphicon-eye-close"></span></a>' : '<a href="/beheer/dc_page_manage.php?id='.$objPage->id.'&action=offline"><span class="glyphicon glyphicon-eye-open"></span></a>';
	$arrJson['details'][$i][]	= '<a href="/beheer/dc_page_manage.php?id='.$objPage->id.'&action=edit"><span class="glyphicon glyphicon-edit"></span></a>';
	$arrJson['details'][$i][]	= '<a href="/beheer/dc_page_manage.php?id='.$objPage->id.'&action=remove"><span class="glyphicon glyphicon-remove"></span></a>';
	
	$i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>