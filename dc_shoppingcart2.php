<?php
session_start();

// Required includes
require_once 'includes/php/dc_connect.php';
require_once '_classes/class.database.php';
$objDB = new DB();
require_once 'includes/php/dc_config.php';

// Page specific includes
require_once '_classes/class.cart.php';
require_once 'includes/php/dc_functions.php';
require_once '_classes/class.password.php'; // Password compatibility library with PHP 5.5

// Start API
require_once 'libraries/Api_Inktweb/API.class.php';

//opening database
$objDB = new DB();

/* Initialize vars */
$customerId = (isset($_SESSION["customerId"])) ? $_SESSION["customerId"] : null;

$strFirstname = null;
$strLastname = null;
$strAddress = null;
$strHouseNr = null;
$strHouseNrAdd = null;
$strZipcode = null;
$strCity = null;
$strLang = null;
$strEmail = null;
$strDelFirstname = null;
$strDelLastname = null;
$strDelZipcode = null;
$strDelLang = null;
$strDelHouseNr = null;
$strDelHouseNrAdd = null;
$strDelAddress = null;
$strDelCity = null;

if (!empty($_POST)) {
    // Form submitted

    $_POST = sanitize($_POST); // Escape all $_POST variables

    // Invoice address details
    $strFirstname = $_POST["firstname"];
    $strLastname = $_POST["lastname"];
    $strCompany = $_POST["company"];
    $strAddress = $_POST["address"];
    $strHouseNr = $_POST["houseNr"];
    $strHouseNrAdd = $_POST["houseNrAdd"];
    $strZipcode = $_POST["zipcode"];
    $strCity = $_POST["city"];
    $strLang = $_POST["lang"];
    $intInvoiceDelivery = 1; // Invoice = Delivery address

    if ($_POST["deliveryAddress"] == 1) {

        // Delivery address details
        $intInvoiceDelivery = 0; // Invoice != Delivery address
        $strDelFirstname = $_POST["delFirstname"];
        $strDelLastname = $_POST["delLastname"];
        $strDelCompany = $_POST["delCompany"];
        $strDelAddress = $_POST["delAddress"];
        $strDelHouseNr = $_POST["delHouseNr"];
        $strDelHouseNrAdd = $_POST["delHouseNrAdd"];
        $strDelZipcode = $_POST["delZipcode"];
        $strDelCity = $_POST["delCity"];
        $strDelLang = $_POST['delLang'];

    }

    $strEmail = trim(strtolower($_POST["email"]));
    $strPassword = $_POST["password"];
    $hashAndSalt = password_hash($strPassword, PASSWORD_BCRYPT); // Hash password with bcrypt and default cost

    if ($strFirstname == '' || $strLastname == '' || $strAddress == '' || $strHouseNr == '' || $strZipcode == '' || $strCity == '' || $strEmail == '') {

        $intError = 1;

    } else {

        $intError = 0;

        $strSQL = "SELECT id FROM " . DB_PREFIX . "customers WHERE email = '" . $strEmail . "'";
        $result = $objDB->sqlExecute($strSQL);
        $intEmailExists = $objDB->getNumRows($result);

        if ($intEmailExists > 0) {
            // Emailaddress exists in database, retrieving Customer id

            list($intCustomerId) = $objDB->getRow($result);

            $strSQL = "UPDATE " . DB_PREFIX . "customers SET firstname = '" . $strFirstname . "', lastname = '" . $strLastname . "', company = '" . $strCompany . "', password = '" . $hashAndSalt . "' WHERE id = " . $intCustomerId;
            $result = $objDB->sqlExecute($strSQL);

            // Check if address exists in database
            $strSQL = "SELECT id FROM " . DB_PREFIX . "customers_addresses WHERE custId = " . $intCustomerId . " AND zipcode = '" . $strZipcode . "' AND houseNr = '" . $strHouseNr . "' AND houseNrAdd = '" . $strHouseNrAdd . "'";
            $resultInvoiceAddress = $objDB->sqlExecute($strSQL);
            $intInvoiceAddressExists = $objDB->getNumRows($resultInvoiceAddress);

            if ($intInvoiceDelivery == 0) {
                // Check if delivery address exists in database
                $strSQL = "SELECT id FROM " . DB_PREFIX . "customers_addresses WHERE custId = " . $intCustomerId . " AND zipcode = '" . $strDelZipcode . "' AND houseNr = '" . $strDelHouseNr . "' AND houseNrAdd = '" . $strDelHouseNrAdd . "'";
                $resultDeliveryAddress = $objDB->sqlExecute($strSQL);
                $intDeliveryAddressExists = $objDB->getNumRows($resultDeliveryAddress);
            }

        } else {
            // Create new customer

            $strSQL = "INSERT INTO " . DB_PREFIX . "customers (`entryDate`, `firstname`, `lastname`, `company`, `email`, `password`) VALUES (NOW(), '" . $strFirstname . "', '" . $strLastname . "', '" . $strCompany . "', '" . $strEmail . "', '" . $hashAndSalt . "')";
            $result = $objDB->sqlExecute($strSQL);
            $intCustomerId = $objDB->getInsertedId();

            $intAddressExists = 0;

        }

        if ($intInvoiceAddressExists == 0) {
            // Address does not exists in database, creating new record

            $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultInv` = 0 WHERE `defaultInv` = 1 AND custId = " . $intCustomerId;
            $result = $objDB->sqlExecute($strSQL);

            if ($intInvoiceDelivery == 1) {

                $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultDel` = 0 WHERE `defaultDel` = 1 AND custId = " . $intCustomerId;
                $result = $objDB->sqlExecute($strSQL);

            }

            $strSQL = "INSERT INTO " . DB_PREFIX . "customers_addresses (`custId`, `entryDate`, `defaultInv`, `defaultDel`, `firstname`, `lastname`, `company`, `address`, `houseNr`, `houseNrAdd`, `zipcode`, `city`, `lang`, `online`) VALUES " .
            "(" . $intCustomerId . ", NOW(), 1, " . $intInvoiceDelivery . ", '" . $strFirstname . "', '" . $strLastname . "', '" . $strCompany . "', '" . $strAddress . "', '" . $strHouseNr . "', '" . $strHouseNrAdd . "', '" . $strZipcode . "', '" . $strCity . "', '" . $strLang . "', 1)";
            $result = $objDB->sqlExecute($strSQL);
            $intAddressId = $objDB->getInsertedId();

        } else {

            list($intInvoiceAddressId) = $objDB->getRow($resultInvoiceAddress);

            $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultInv` = 0 WHERE `defaultInv` = 1 AND custId = " . $intCustomerId;
            $result = $objDB->sqlExecute($strSQL);

            $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultInv` = 1 WHERE id = " . $intInvoiceAddressId;
            $result = $objDB->sqlExecute($strSQL);

            if ($intInvoiceDelivery == 1) {

                $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultDel` = 0 WHERE `defaultDel` = 1 AND custId = " . $intCustomerId;
                $result = $objDB->sqlExecute($strSQL);

                $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultDel` = 1 WHERE id = " . $intInvoiceAddressId;
                $result = $objDB->sqlExecute($strSQL);

            }

        }

        if ($intDeliveryAddressExists == 0 && $intInvoiceDelivery == 0) {
            // Address does not exists in database, creating new record

            $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultDel` = 0 WHERE `defaultDel` = 1 AND custId = " . $intCustomerId;
            $result = $objDB->sqlExecute($strSQL);

            $strSQL = "INSERT INTO " . DB_PREFIX . "customers_addresses (`custId`, `entryDate`, `defaultInv`, `defaultDel`, `firstname`, `lastname`, `company`,  `address`, `houseNr`, `houseNrAdd`, `zipcode`, `city`, `lang`, `online`) VALUES " .
            "(" . $intCustomerId . ", NOW(), 0, 1, '" . $strDelFirstname . "', '" . $strDelLastname . "', '" . $strDelCompany . "', '" . $strDelAddress . "', '" . $strDelHouseNr . "', '" . $strDelHouseNrAdd . "', '" . $strDelZipcode . "', '" . $strDelCity . "', '" . $strDelLang . "', 1)";
            $result = $objDB->sqlExecute($strSQL);
            $intAddressId = $objDB->getInsertedId();

        } elseif ($intInvoiceDelivery == 0) {

            list($intDeliveryAddressId) = $objDB->getRow($resultDeliveryAddress);

            $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultDel` = 0 WHERE `defaultDel` = 1 AND id = " . $intCustomerId;
            $result = $objDB->sqlExecute($strSQL);

            $strSQL = "UPDATE " . DB_PREFIX . "customers_addresses SET `defaultDel` = 1 WHERE id = " . $intDeliveryAddressId;
            $result = $objDB->sqlExecute($strSQL);

        }

        doLogin($strEmail, $strPassword);
        header('Location: ' . SITE_URL . '/dc_shoppingcart3.php');
        exit;

    }

}

$intCartItems = $objDB->getRecordCount("cart", "id", "WHERE (customerId=" . intval($customerId) . " AND customerId != 0) OR sessionId='" . session_id() . "'");
if ($intCartItems == 0) {
    header('Location: ' . SITE_URL . '/dc_shoppingcart.php');
    exit;
}

if (!empty($_SESSION["customerId"])) {

    $strSQL = "SELECT ca_invoice.*, ca_invoice.id as invoiceAddressId, ca_delivery.firstname as delFirstname, ca_delivery.lastname as delLastname, ca_delivery.company as delCompany, ca_delivery.address as delAddress, ca_delivery.houseNr as delHouseNr, ca_delivery.houseNrAdd as delHouseNrAdd, ca_delivery.zipcode as delZipcode, ca_delivery.city as delCity, ca_delivery.lang as delLang, ca_delivery.id as deliveryAddressId, c.email FROM " . DB_PREFIX . "customers c " .
    "INNER JOIN " . DB_PREFIX . "customers_addresses ca_invoice ON ca_invoice.custId = c.id AND ca_invoice.defaultInv = 1 " .
    "INNER JOIN " . DB_PREFIX . "customers_addresses ca_delivery ON ca_delivery.custId = c.id AND ca_delivery.defaultDel = 1 " .
    "WHERE c.id = " . $_SESSION["customerId"];
    $result = $objDB->sqlExecute($strSQL);
    $objCustomer = $objDB->getObject($result);

    $strFirstname = $objCustomer->firstname;
    $strLastname = $objCustomer->lastname;
    $strCompany = $objCustomer->company;
    $strAddress = $objCustomer->address;
    $strHouseNr = $objCustomer->houseNr;
    $strHouseNrAdd = $objCustomer->houseNrAdd;
    $strZipcode = $objCustomer->zipcode;
    $strCity = $objCustomer->city;

    $strEmail = $objCustomer->email;
    $intDelivery = 0;

    if ($objCustomer->invoiceAddressId != $objCustomer->deliveryAddressId) {

        $intDelivery = 1;
        $strDelFirstname = $objCustomer->delFirstname;
        $strDelLastname = $objCustomer->delLastname;
        $strDelCompany = $objCustomer->delCompany;
        $strDelAddress = $objCustomer->delAddress;
        $strDelHouseNr = $objCustomer->delHouseNr;
        $strDelHouseNrAdd = $objCustomer->delHouseNrAdd;
        $strDelZipcode = $objCustomer->delZipcode;
        $strDelCity = $objCustomer->delCity;

    }

}
// Start displaying HTML
require_once 'includes/php/dc_header.php';
?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.2/css/bootstrapValidator.min.css"/>

<div class="row">
    <div class="col-xs-12">
        <ul class="nav nav-tabs">
            <li class=""><a href="<?php echo SITE_URL?>/dc_shoppingcart.php"><strong>Stap 1)</strong> Winkelmand</a></li>
            <li class="active"><a href="#"><strong>Stap 2)</strong> Gegevens</a></li>
            <li class="<?php if (empty($_SESSION["customerId"])) {
    echo 'disabled';
}
?>"><a href="<?php if (!empty($_SESSION["customerId"])) {
    echo SITE_URL . '/dc_shoppingcart3.php';
} else {
    '#';
}
?>"><strong>Stap 3)</strong> Betaling</a></li>
            <li class="disabled"><a href="#"><strong>Stap 4)</strong> Bestelling geplaatst</a></li>
        </ul>
    </div><!-- /col -->
</div><!-- /row -->

<div class="row">
<div class="col-md-8 col-md-offset-2">
<form class="form-horizontal registerForm" role="form" method="post">
    <fieldset>
    <legend>Waar mogen wij uw bestelling naar toezenden?</legend>

    <?php if (isset($intError) && $intError == 1) {
    echo '<p>U heeft niet alle verplichte velden ingevuld.</p>';
}
?>

    <div class="form-group">
        <label class="col-sm-2 control-label" for="textinput">Voornaam</label>
        <div class="col-sm-10">
            <input type="text" placeholder="" class="form-control" name="firstname" value="<?php echo $strFirstname;?>" data-bv-notempty="true">
        </div><!-- /col -->
    </div><!-- /form-group -->

    <div class="form-group">
        <label class="col-sm-2 control-label" for="textinput">Achternaam</label>
        <div class="col-sm-10">
            <input type="text" placeholder="" class="form-control" name="lastname" value="<?php echo $strLastname;?>" data-bv-notempty="true">
        </div><!-- /col -->
    </div><!-- /form-group -->

    <div class="form-group">
        <label class="col-sm-2 control-label" for="textinput">E-mailadres</label>
        <div class="col-sm-10">
            <input type="email" class="form-control" name="email" value="<?php echo $strEmail;?>" data-bv-notempty="true" data-bv-emailaddress="true" data-bv-message="Ongeldig e-mailadres">
        </div><!-- /col -->
    </div><!-- /form-group -->

    <div class="form-group">
        <label class="col-sm-2 control-label" for="textinput">Wachtwoord</label>
        <div class="col-sm-10">
            <div class="input-group">
                <input type="password" class="form-control pwd" value="" name="password" <?php if (!isset($strEmail)) {
    echo 'data-bv-notempty="true" data-bv-message="Dit veld is verplicht"';
}
?>>
                <span class="input-group-btn">
                    <button class="btn btn-default reveal" type="button"><i class="glyphicon glyphicon-eye-open"></i></button>
                </span>
            </div><!-- /input-group -->
            <p class="help-block">Vul hier een veilig wachtwoord in zodat u achteraf de status van uw bestelling kunt volgen.</p>
        </div><!-- /col -->
    </div><!-- /form-group -->

    <div class="form-group">
        <label class="col-sm-2 control-label" for"textinput">Bedrijfsnaam</label>
        <div class="col-sm-10">
            <input type="text" placeholder="" class="form-control" name="company" value="<?php echo $strCompany; ?>" data-bv-notempty="false">
        </div><!-- /col -->
    </div><!-- /from-group -->

    <hr />

    <div class="form-group">
        <label class="col-sm-2 control-label" for="textinput">Land</label>
        <div class="col-sm-10">
            <select class="form-control" name="lang" data-bv-notempty="true">
                <option value="nl" <?php if ($strLang == "nl") {
    echo "selected";
}
?>>Nederland</option>
                <option value="be" <?php if ($strLang == "be") {
    echo "selected";
}
?>>België</option>
            </select>
        </div><!-- /col -->
    </div><!-- /form-group -->

    <div class="form-group">
        <label class="col-sm-2 control-label" for="textinput">Postcode</label>
        <div class="col-sm-4">
            <input type="text" placeholder="1234AB" class="form-control zipcode" name="zipcode" value="<?php echo $strZipcode;?>" data-bv-notempty="true">
        </div><!-- /col -->

        <label class="col-sm-2 control-label" for="textinput">Huisnummer</label>
        <div class="col-sm-2">
            <input type="text" placeholder="21" class="form-control houseNr" name="houseNr" value="<?php echo $strHouseNr;?>" data-bv-notempty="true">
        </div><!-- /col -->
        <div class="col-sm-2">
            <input type="text" placeholder="b" class="form-control houseNrAdd" name="houseNrAdd" value="<?php echo $strHouseNrAdd;?>">
        </div><!-- /col -->
    </div><!-- /form-group -->


    <div class="form-group">
        <label class="col-sm-2 control-label" for="textinput">Straatnaam</label>
        <div class="col-sm-10">
            <input type="text" class="form-control address" name="address" value="<?php echo $strAddress;?>" data-bv-notempty="true" autocomplete="off">
        </div><!-- /col -->
    </div><!-- /form-group -->

    <div class="form-group">
        <label class="col-sm-2 control-label" for="textinput">Plaats</label>
        <div class="col-sm-10">
            <input type="text" class="form-control city" name="city" value="<?php echo $strCity;?>" data-bv-notempty="true">
        </div><!-- /col -->
    </div><!-- /form-group -->

    <div class="form-group checkbox">
        <div class="col-sm-12">
            <label><input type="checkbox" name="deliveryAddress" id="deliveryAddress" value="1" <?php if (!isset($intDelivery) || $intDelivery == 1) {
    echo 'checked="checked"';
}
?>> Mijn bestelling afleveren op een ander adres</label>
        </div><!-- /col -->
    </div><!-- /form-group -->

        <div id="delivery" <?php if (isset($intDelivery) && $intDelivery != 1) {
    echo 'style="display:none"';
}
?>>
            <legend>Bezorgadres</legend>

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="textinput">Land</label>
                    <div class="col-sm-10">
                        <select class="form-control" name="delLang">
                            <option value="nl" <?php if ($strDelLang == "nl") {
    echo "selected";
}
?>>Nederland</option>
                            <option value="be" <?php if ($strDelLang == "be") {
    echo "selected";
}
?>>België</option>
                        </select>
                    </div><!-- /col -->
                </div><!-- /form-group -->

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="textinput">Voornaam</label>
                    <div class="col-sm-10">
                        <input type="text" placeholder="" class="form-control" name="delFirstname" value="<?php echo $strDelFirstname;?>">
                    </div><!-- /col -->
                </div><!-- /form-group -->

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="textinput">Achternaam</label>
                    <div class="col-sm-10">
                        <input type="text" placeholder="" class="form-control" name="delLastname" value="<?php echo $strDelLastname;?>">
                    </div><!-- /col -->
                </div><!-- /form-group -->

                 <div class="form-group">
                    <label class="col-sm-2 control-label" for"textinput">Bedrijfsnaam</label>
                    <div class="col-sm-10">
                        <input type="text" placeholder="" class="form-control" name="delCompany" value="<?php echo $strDelCompany; ?>" data-bv-notempty="false">
                    </div><!-- /col -->
                </div><!-- /from-group -->

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="textinput">Postcode</label>
                    <div class="col-sm-4">
                        <input type="text" placeholder="1234AB" class="form-control zipcode" name="delZipcode" value="<?php echo $strDelZipcode;?>">
                    </div><!-- /col -->

                    <label class="col-sm-2 control-label" for="textinput">Huisnummer</label>
                    <div class="col-sm-2">
                        <input type="text" placeholder="21" class="form-control houseNr" name="delHouseNr" value="<?php echo $strDelHouseNr;?>">
                    </div><!-- /col -->
                    <div class="col-sm-2">
                        <input type="text" placeholder="b" class="form-control houseNrAdd" name="delHouseNrAdd" value="<?php echo $strDelHouseNrAdd;?>">
                    </div><!-- /col -->
                </div><!-- /form-group -->


                <div class="form-group">
                    <label class="col-sm-2 control-label" for="textinput">Straatnaam</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control address" name="delAddress" value="<?php echo $strDelAddress;?>">
                    </div><!-- /col -->
                </div><!-- /form-group -->

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="textinput">Plaats</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control city" name="delCity" value="<?php echo $strDelCity;?>">
                    </div><!-- /col -->
                </div><!-- /form-group -->
        </div><!-- /delivery -->

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
        <div class="pull-right">
            <button type="submit" class="btn btn-primary btn-lg">Naar afrekenen</button>
        </div>
        </div><!-- /col -->
    </div><!-- /form-group -->

    </fieldset>
</form>
</div><!-- /col -->
</div><!-- /row -->

<script type="text/javascript">
$(".reveal").mousedown(function() {
    $(".pwd").replaceWith($('.pwd').clone().attr('type', 'text'));
})
.mouseup(function() {
    $(".pwd").replaceWith($('.pwd').clone().attr('type', 'password'));
})
.mouseout(function() {
    $(".pwd").replaceWith($('.pwd').clone().attr('type', 'password'));
});


$('#deliveryAddress').click(function(){
    $('#delivery').slideToggle();
});

$('.zipcode, .houseNr, .houseNrAdd').focusout(function(){

    var curThis             = $(this);
    var input_zipcode           = $(curThis).parent().parent().find('.zipcode').val();
    var input_houseNr           = $(curThis).parent().parent().find('.houseNr').val();
    var input_houseNrAdd        = $(curThis).parent().parent().find('.houseNrAdd').val();

    if(input_zipcode != '' && input_houseNr != '') {

        $.get( "<?php echo SITE_URL?>/includes/json/validateZipcode.php", {

                zipcode     : input_zipcode,
                houseNr     : input_houseNr,
                houseNrAdd  : input_houseNrAdd

            }, function( data ) {

                var street          = data.street;
                var houseNr         = data.houseNumber;
                var houseNumberAdd  = data.houseNumberAdd;
                var zipcode         = data.postcode;
                var city            = data.city;

                jQuery(curThis).parent().parent().next().find('.address').val(street);
                jQuery(curThis).parent().parent().next().next().find('.city').val(city);

                jQuery(curThis).parent().parent().next().find('.address').change();
                jQuery(curThis).parent().parent().next().next().find('.city').change();

        }, 'json');

    }
});

$(document).ready(function() {
    $('.registerForm').bootstrapValidator({
        message: 'Dit veld is verplicht',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            address: {
                trigger: 'change'
            },
            city: {
                trigger: 'change'
            }
       }
    });
});
</script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.2/js/bootstrapValidator.min.js" ></script>
<?php
require 'includes/php/dc_footer.php';
?>