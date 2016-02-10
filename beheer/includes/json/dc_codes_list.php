<?php
session_start();

require_once __DIR__ . '/../../../beheer/includes/php/dc_session.php';

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
$strWhere .= ($strQuery != '') ? " AND (dc.title LIKE '%" . $strQuery . "%')" : '';
$strLimit = " LIMIT " . $intOffset . "," . $intLimit;

if (empty($strShow) OR $strShow == "new") {
    $strWhere .= " AND online = 1 ";
} elseif ($strShow == "archive") {
    $strWhere .= " AND online = 0 ";
}

$strSQL =
"SELECT dc.id, dc.title, DATE_FORMAT(dc.validFrom, '%d-%m-%Y') as validFrom, DATE_FORMAT(dc.validTill, '%d-%m-%Y') as validTill
        FROM " . DB_PREFIX . "discountcodes dc
        WHERE 1
        " . $strWhere . $strSort;
$resultCount = $objDB->sqlExecute($strSQL);
$count = $objDB->getNumRows($resultCount);

$strSQL = $strSQL . $strLimit;
$result = $objDB->sqlExecute($strSQL);

$arrJson['totalRows'] = $count;
$i = 0;

while ($objCode = $objDB->getObject($result)) {

    $arrJson['details'][$i][] = $objCode->id;
    $arrJson['details'][$i][] = $objCode->title;
    $arrJson['details'][$i][] = $objCode->validFrom;
    $arrJson['details'][$i][] = $objCode->validTill;
    $arrJson['details'][$i][] = '<a href="' . SITE_URL . '/beheer/dc_codes_codes.php?id=' . $objCode->id . '&amp;type=notexported"><span class="glyphicon glyphicon-list-alt"></span></a>';
    $arrJson['details'][$i][] = '<a href="' . SITE_URL . '/beheer/dc_codes_manage.php?id=' . $objCode->id . '&amp;action=view"><span class="glyphicon glyphicon-edit"></span></a>';

    $i++;
}

header('Content-type: application/json');
echo json_encode($arrJson);

//close db
$objDB->closeDB();
?>