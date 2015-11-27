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

$intId = (int) $_GET['id'];
$strAction = $_GET['action'];

$strSQL = "SELECT dc.* FROM " . DB_PREFIX . "discountcodes dc WHERE dc.id = '" . $intId . "' ";
$result = $objDB->sqlExecute($strSQL);
$objCode = $objDB->getObject($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $strTitle = (isset($_POST['title'])) ? $_POST['title'] : null;
    $strValidFrom = (isset($_POST['validFrom'])) ? $_POST['validFrom'] : null;
    $strValidTill = (isset($_POST['validTill'])) ? $_POST['validTill'] : null;
    $strDiscountType = (isset($_POST['discountType'])) ? $_POST['discountType'] : null;
    $strDiscountValue = (isset($_POST['discountValue'])) ? $_POST['discountValue'] : null;
    $intValidationCodeRequired = (isset($_POST['validationCodeRequired'])) ? (int) $_POST['validationCodeRequired'] : 0;
    $intFixedShipping = (isset($_POST['fixedShipping'])) ? (int) $_POST['fixedShipping'] : 0;
    $intShareRemainingDiscount = (isset($_POST['shareRemainingDiscount'])) ? (int) $_POST['shareRemainingDiscount'] : 0;
    $intOnline = (isset($_POST['online'])) ? (int) $_POST['online'] : 0;

    if (empty($intId)) {

        $strSQL = "INSERT INTO " . DB_PREFIX . "discountcodes (title, validFrom, validTill, discountType, discountValue, validationCodeRequired, fixedShipping, shareRemainingDiscount, online) VALUES ('" . $strTitle . "', '" . $strValidFrom . "',  '" . $strValidTill . "', '" . $strDiscountType . "', " . $strDiscountValue . ", " . $intValidationCodeRequired . ", " . $intFixedShipping . ", " . $intShareRemainingDiscount . ", " . $intOnline . ")";
        $result = $objDB->sqlExecute($strSQL);
        $intId = $objDB->getInsertedId();

    } else {

        $strSQL = "UPDATE " . DB_PREFIX . "discountcodes
                SET title = '" . $strTitle . "',
                validFrom = '" . $strValidFrom . "',
                validTill = '" . $strValidTill . "',
                discountType = '" . $strDiscountType . "',
                discountValue = '" . $strDiscountValue . "',
                validationCodeRequired = " . $intValidationCodeRequired . ",
                fixedShipping = " . $intFixedShipping . ",
                shareRemainingDiscount = " . $intShareRemainingDiscount . ",
                online = " . $intOnline . "
                WHERE id = '" . $intId . "' ";
        $result = $objDB->sqlExecute($strSQL);

    }

    if ($result === true) {
        header('Location: ?id=' . $intId . '&action=' . $strAction . '&succes=' . urlencode('De codes zijn bijgewerkt.'));
    } else {
        header('Location: ?id=' . $intId . '&action=' . $strAction . '&fail=' . urlencode('Er is iets fout gegaan.'));
    }

}

require 'includes/php/dc_header.php';

if (isset($_GET['success']) && !empty($_GET['succes'])) {
    echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> ' . $_GET['succes'] . '</div>';
}

if (isset($_GET['fail']) && !empty($_GET['fail'])) {
    echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Fout!</strong> ' . $_GET['fail'] . '</div>';
}

?>

<h1>Codes beheren <small><?php echo (isset($objCode->title)) ? $objCode->title : null;?></small></h1>

<hr />

<form role="form" class="form-horizontal" method="POST">

    <div class="form-group">
        <label for="navTitle" class="col-sm-2 control-label">Naam</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="title" name="title" placeholder="" value="<?php echo (isset($objCode->title)) ? $objCode->title : null;?>">
            <p class="help-block">Alleen voor intern gebruik</p>
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="navDesc" class="col-sm-2 control-label">Geldig vanaf</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="validFrom" name="validFrom" placeholder="yyyy-mm-dd" data-provide="datepicker" data-date-format="yyyy-mm-dd" value="<?php echo (isset($objCode->validFrom)) ? $objCode->validFrom : null?>">
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="navDesc" class="col-sm-2 control-label">Geldig tot</label>
        <div class="col-sm-10">
            <input type="text" class="form-control datepicker" id="validTill" name="validTill" placeholder="yyyy-mm-dd" data-provide="datepicker" data-date-format="yyyy-mm-dd" value="<?php echo (isset($objCode->validTill)) ? $objCode->validTill : null?>">
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="discountType" class="col-sm-2 control-label">Kortingstype</label>
        <div class="col-sm-10">
            <select class="form-control" id="discountType" name="discountType">
                <option value="price" <?php if (isset($objCode->discountType) && $objCode->discountType == 'price') {
    echo 'selected="selected"';
}
?>>Bedrag</option>
                <option value="percentage" <?php if (isset($objCode->discountType) && $objCode->discountType == 'percentage') {
    echo 'selected="selected"';
}
?>>Percentage</option>
                <option value="dynamic" <?php if (isset($objCode->discountType) && $objCode->discountType == 'dynamic') {
    echo 'selected="selected"';
}
?>>Verschillend per code</option>
            </select>
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="discountValue" class="col-sm-2 control-label">Kortingswaarde</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="discountValue" name="discountValue" value="<?php echo (isset($objCode->discountValue)) ? $objCode->discountValue : null;?>">
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="validationCodeRequired" class="col-sm-2 control-label">Validatiecode benodigd</label>
        <div class="col-sm-10">
            <input type="checkbox" name="validationCodeRequired" id="validationCodeRequired" value="1" <?=(isset($objCode->validationCodeRequired) && $objCode->validationCodeRequired == 1) ? 'checked="checked"' : ''?> />
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="fixedShipping" class="col-sm-2 control-label">Vaste verzendkosten</label>
        <div class="col-sm-10">
            <input type="checkbox" name="fixedShipping" id="fixedShipping" value="1" <?=(isset($objCode->fixedShipping) && $objCode->fixedShipping == 1) ? 'checked="checked"' : ''?> />
        </div><!-- /col -->
    </div><!-- /form group -->
    
    <div class="form-group">
        <label for="shareRemainingDiscount" class="col-sm-2 control-label">Resterend tegoed delen met vrienden</label>
        <div class="col-sm-10">
            <input type="checkbox" name="shareRemainingDiscount" id="shareRemainingDiscount" value="1" <?=(isset($objCode->shareRemainingDiscount) && $objCode->shareRemainingDiscount == 1) ? 'checked="checked"' : ''?> />
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="online" class="col-sm-2 control-label">Code actief</label>
        <div class="col-sm-10">
            <input type="checkbox" name="online" id="online" value="1" <?=(isset($objCode->online) && $objCode->online == 1) ? 'checked="checked"' : ''?> />
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary">Codes aanpassen</button>
        </div><!-- /col -->
    </div><!-- /form group -->

<hr />

<script src="<?php echo SITE_URL?>/includes/script/bootstrap-datepicker.js"></script>
<?php require 'includes/php/dc_footer.php';?>