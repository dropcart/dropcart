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

$_GET = sanitize($_GET);

$strShow = (isset($_GET['show'])) ? strtolower($_GET['show']) : null;
$strWhere = null;
$strSortColumn = (isset($_GET['sort_column'])) ? $_GET["sort_column"] : null;
$strSortOrder = (isset($_GET['sort_order'])) ? strtoupper($_GET["sort_order"]) : null;
$strQuery = (isset($_GET['query'])) ? $_GET["query"] : null;
$intOffset = (isset($_GET['offset'])) ? (int) $_GET["offset"] : 0;
$intLimit = (isset($_GET['limit'])) ? (int) $_GET["limit"] : 15;

$strSort = ($strSortColumn != '') ? " ORDER BY `" . $strSortColumn . "` " . $strSortOrder : '';
$strWhere .= ($strQuery != '') ? " AND (au.name LIKE '%" . $strQuery . "%' OR au.email LIKE '%" . $strQuery . "%')" : '';
$strLimit = " LIMIT " . $intOffset . "," . $intLimit;

$strSQL =
"SELECT au.id, au.name, au.email, au.username
        FROM " . DB_PREFIX . "admin_users au
        WHERE 1
        " . $strWhere .
$strSort;
$resultCount = $objDB->sqlExecute($strSQL);
$count = $objDB->getNumRows($resultCount);

$strSQL = $strSQL . $strLimit;
$result = $objDB->sqlExecute($strSQL);

$arrJson['totalRows'] = $count;
$i = 0;

while ($objUser = $objDB->getObject($result)) {

    $arrJson['details'][$i][] = $objUser->name;
    $arrJson['details'][$i][] = $objUser->email;
    $arrJson['details'][$i][] = '<a href="' . SITE_URL . '/beheer/dc_user_manage.php?id=' . $objUser->id . '&action=edit"><span class="glyphicon glyphicon-edit"></span></a>';
    $arrJson['details'][$i][] = '<a href="' . SITE_URL . '/beheer/dc_user_manage.php?id=' . $objUser->id . '&action=remove" onclick="return confirm(\'Weet je zeker dat je deze gebruiker wilt verwijderen?\')"><span class="glyphicon glyphicon-remove"></span></a>';

    $i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>
