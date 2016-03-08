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

$intId = (isset($_GET['id'])) ? (int) $_GET['id'] : 0;
$intCodeId = (isset($_GET['codeId'])) ? (int) $_GET['codeId'] : null;
$strAction = (isset($_GET['action'])) ? $_GET['action'] : null;

$strSQL = "SELECT dc_c.* FROM " . DB_PREFIX . "discountcodes_codes dc_c WHERE dc_c.id = '" . $intId . "' ";
$result = $objDB->sqlExecute($strSQL);
$objCode = $objDB->getObject($result);

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $strCode = (isset($_POST['code'])) ? $_POST['code'] : null;
    $intLimit = (isset($_POST['limit'])) ? (int) $_POST['limit'] : null;
    $strValue = (isset($_POST['code_value'])) ? (int) $_POST['code_value'] : null;
    $strDiscountType = (isset($_POST['discount_type'])) ? $_POST['discount_type'] : null;

    if (strlen(trim($strCode)) == 0) {
        header('Location: ?id=' . $intId . '&action=' . $strAction . '&fail=' . urlencode('Het code veld is verplicht!'));
        return false;
    }

    if ($intId == 0) {

        $strSQL = "INSERT INTO " . DB_PREFIX . "discountcodes_codes
        (
            `codeId`,
            `code`,
            `limit`,
            `discountValue`,
            `discountType`
            ) VALUES (
            '" . $intCodeId . "',
            '" . $strCode . "',
            " . $intLimit . ",
            '" . $strValue . "',
            '" . $strDiscountType . "')";

        $result = $objDB->sqlExecute($strSQL);
        $intId = $objDB->getInsertedId();

    } else {

        $strSQL = "UPDATE " . DB_PREFIX . "discountcodes_codes
                SET `codeId` = '" . $intCodeId . "',
                `code` = '" . $strCode . "',
                `limit` = " . $intLimit . ",
                `discountValue` = '" . $strValue . "',
                `discountType` = '" . $strDiscountType . "'
                WHERE id = '" . $intId . "' ";
        $result = $objDB->sqlExecute($strSQL);

    }

    if ($result === true) {
        header('Location: ?id=' . $intId . '&action=' . $strAction . '&succes=' . urlencode('De code is bijgewerkt.'));
    } else {
        header('Location: ?id=' . $intId . '&action=' . $strAction . '&fail=' . urlencode('Er is iets fout gegaan.'));
    }

}

require 'includes/php/dc_header.php';

if (!empty($_GET['succes'])) {
    echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> ' . $_GET['succes'] . '</div>';
}

if (!empty($_GET['fail'])) {
    echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Fout!</strong> ' . $_GET['fail'] . '</div>';
}

?>

<h1>Codes beheren <small><?php echo (isset($objCode->title)) ? $objCode->title : null;?></small></h1>

<hr />

<form role="form" class="form-horizontal" method="POST">

    <div class="form-group">
        <label for="code" class="col-sm-2 control-label">Code</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="code" name="code" placeholder="" value="<?php echo (isset($objCode->code)) ? $objCode->code : null;?>">
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="limit" class="col-sm-2 control-label">Aantal keer te gebruiken</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="limit" name="limit" value="<?php echo (isset($objCode->limit)) ? $objCode->limit : null;?>">
            <p class="help-block">0 is oneindig</p>
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="discount_type" class="col-sm-2 control-label">Type</label>
        <div class="col-sm-10">

            <?php
            $selectedDiscountType = null;
            $selected = 'selected="selected"';
            if (isset($objCode->discountType) && !empty($objCode->discountType)) {
                $selectedDiscountType = $objCode->discountType;
            }
            ?>

            <select class="form-control" name="discount_type" id="discount_type">
                <option <?php echo ($selectedDiscountType == 'price') ? $selected : null?> value="price">Prijs</option>
                <option <?php echo ($selectedDiscountType == 'percentage') ? $selected : null?> value="percentage">Percentage</option>
            </select>
        </div><!-- /col -->
    </div><!-- /form group -->


    <div class="form-group">
        <label for="limit" class="col-sm-2 control-label">Waarde</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="code_value" name="code_value" value="<?php echo (isset($objCode->discountValue)) ? $objCode->discountValue : null;?>">
            <p class="help-block">Bijvoorbeeld: 10.00</p>
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary">Code aanpassen</button>
        </div><!-- /col -->
    </div><!-- /form group -->

<hr />

<?php require 'includes/php/dc_footer.php';?>