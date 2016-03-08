<?php
// Required includes
require_once __DIR__ . '/../includes/php/dc_connect.php';
require_once __DIR__ . '/../_classes/class.database.php';
$objDB = new DB();
require_once __DIR__ . '/../beheer/includes/php/dc_config.php';

// Page specific includes
require_once __DIR__ . '/../beheer/includes/php/dc_functions.php';

$_POST = sanitize($_POST);
$_GET = sanitize($_GET);

$strAction = (isset($_GET['action'])) ? $_GET["action"] : null;
$intCodeId = (isset($_GET['id'])) ? (int) $_GET["id"] : null;

switch ($strAction) {
    case 'export':

        $intExportType = (int) $_GET["exportType"];

        if ($intExportType == 1) {
            $strWhere = 'AND dc_c.export = 0 AND dc_c.orderid != 0 ';
        } elseif ($intExportType == 2) {
            $strWhere = 'AND dc_c.export = 1 AND dc_c.orderid != 0 ';
        } elseif ($intExportType == 3) {
            $strWhere = 'AND dc_c.orderId != 0 ';
        } elseif ($intExportType == 4) {
            $strWhere = 'AND dc_c.orderId = 0 ';
        } else {
            $strWhere = '';
        }

        $strSQL = "
	            SELECT code, validationCode, discountValue
	            FROM " . DB_PREFIX . "discountcodes_codes dc_c
	            WHERE dc_c.codeId = " . $intCodeId . " " .
        $strWhere;
        $result = $objDB->sqlExecute($strSQL);

        if ($intExportType == 1) {

            $strSQL = "UPDATE " . DB_PREFIX . "discountcodes_codes SET export = 1 WHERE codeId = " . $intCodeId . " AND export = 0 AND orderid != 0";
            $update = $objDB->sqlExecute($strSQL);

        }

        //building CSV-file
        $strCSV = 'Vouchercode;Validatiecode' . "\r\n";

        while ($objResult = $objDB->getObject($result)) {
            //continue building CSV-file
            $strCSV .= $objResult->code . ';';
            $strCSV .= $objResult->validationCode;
            $strCSV .= "\r\n";
        }

        //set vars
        $strPath = TMP_PATH;
        $strFileName = 'codes.csv';
        $strFile = $strFileName;

        //create file
        $objFile = fopen($strPath . $strFileName, "w");
        fwrite($objFile, $strCSV);
        fclose($objFile);

        //close database
        $objDB->closeDB($objDB);

        //download file

        //set contenttype header
        $strContentType = 'application/force-download';

        //displaying
        header("Content-Type: " . $strContentType . "");
        header("Content-Length: " . filesize($strPath . $strFile));
        header("Content-Disposition: attachment; filename=\"" . $strFileName . "\"");

        $fd = fopen($strPath . $strFile, "r");
        while (!feof($fd)) {
            echo fread($fd, 4096);
            ob_flush();
        }

        exit;

        break;
}

$strShow = (isset($_GET['show'])) ? strtolower($_GET['show']) : null;

if (empty($strShow) OR $strShow == "all") {
    $sqlWhere = " ";
}

require 'includes/php/dc_header.php';
?>

<h1>Vouchercodes</h1>

<hr />

<div class="form-group" style="height:40px">
    <div class="col-sm-8">
        <input type="search" id="search" value="" class="form-control search-json" placeholder="Zoeken" style="margin-bottom:20px" data-json-table="#table">
    </div><!-- /col -->
    <div class="col-sm-2">
        <form method="get" name="filter">
            <input type="hidden" name="id" value="<?php echo $intCodeId?>" />
            <select class="form-control" name="type" data-json-table="#table" data-json-key="type">
                <option data-json-value="" value="">Alle codes weergeven</option>
                <option data-json-value="used" value="used">Alleen gebruikte codes</option>
                <option data-json-value="notexported" value="notexported" selected>Alleen niet verzilverde codes</option>
                <option data-json-value="notused" value="notused">Alleen niet gebruikte codes</option>
                <option data-json-value="exported" value="exported">Alleen verzilverde codes</option>
            </select>
        </form>
    </div><!-- /col -->
    <div class="col-sm-2">
        <select onChange="if(this.options.selectedIndex > 0) document.location.href='?id=<?php echo $intCodeId?>&action=export&exportType='+this.options[this.selectedIndex].value" class="form-control" name="type">
            <option value="">Exporteren</option>
            <option value="0">Alle codes exporteren</option>
            <option value="1">Niet verzilverde codes exporteren</option>
            <option value="2">Reeds verzilverde codes exporteren</option>
            <option value="3">Alle gebruikte codes exporteren</option>
            <option value="4">Alle niet gebruikte codes exporteren</option>
        </select>
    </div><!-- /col -->
</div><!-- /form group -->

<?php

if (!empty($_GET['succes'])) {
    echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> ' . $_GET['succes'] . '</div>';
}

?>

<span class="pull-right"><a href="dc_codes_codes_manage.php?codeId=<?php echo $_GET["id"]?>"><span class="glyphicon glyphicon-plus"></span> Code aanmaken</a></span>

<ul class="nav nav-tabs" data-json-table="#table" data-json-key="show">
    <li class="active" data-json-value="new"><a>Alles</a></li>
</ul>


<table class="table table-striped table-json" id="table" data-json-file="dc_codes_codes_list.json" data-json-parameters="&codeId=<?php echo $_GET["id"]?>">
    <thead>
    <tr>
        <th data-json-column="code" data-json-sort="asc">Code</th>
        <th width="10%" data-json-column="discountValue">Waarde</th>
        <th width="10%" data-json-column="parentOrderId">Uitgegeven door</th>
        <th width="10%" data-json-column="validationCode">Validatiecode</th>
        <th width="10%" data-json-column="orderId">Gebruikt</th>
        <th width="5%" data-json-column="export">Verzilverd</th>
        <th width="10%" data-json-column="export">Bewerken</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<ul class="pagination pagination-json" data-json-table="#table" data-json-items="25"></ul>

<script src="<?php echo SITE_URL?>/beheer/includes/script/jquery.dynamic-table.js"></script>

<?php require 'includes/php/dc_footer.php';?>