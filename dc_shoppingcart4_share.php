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
require_once 'includes/php/dc_mail.php';
// Start API
require_once 'libraries/Api_Inktweb/API.class.php';

$intOrderId = (int) $_POST["orderId"];
$intCustomerId = (int) $_SESSION["customerId"];

// Check if order exists with current customer
$strSQL = "SELECT id FROM ".DB_PREFIX."customers_orders_id WHERE orderId = ".$intOrderId." AND customerId = ".$intCustomerId;
$result = $objDB->sqlExecute($strSQL);
$intOrderExists = $objDB->getNumRows($result);

if (!empty($_POST) && $intOrderExists == 1) {

    $_POST = sanitize($_POST); // Escape all $_POST variables
    
    $strEmail    = $_POST["mafEmail"];
    $strName    = $_POST["mafName"];
    
    // send order mail
    sendMail('discountCodeMail', $strEmail, $strName, array(
        'FRIEND_NAME' => $strName
    ));
    $strMessageTitle = $text['SHARE_DISCOUNT_SEND_TITLE'];
    $strMessage = '<p>' . $text['SHARE_DISCOUNT_SEND_MESSAGE_1'] . '</p>';

}

// Start displaying HTML
require_once 'includes/php/dc_header.php';
?>

<div class="row">
    <div class="col-xs-12">
        <ul class="nav nav-tabs">
            <li class="disabled"><a href="#"><strong><?php echo $text['STEP']; ?> 1)</strong> <?php echo $text['SHOPPING_BASKET']; ?></a></li>
            <li class="disabled"><a href="#"><strong><?php echo $text['STEP']; ?> 2)</strong> <?php echo $text['SHOPPING_DATA']; ?></a></li>
            <li class="disabled"><a href="#"><strong><?php echo $text['STEP']; ?> 3)</strong> <?php echo $text['SHOPPING_PAYMENT']; ?></a></li>
            <li class="active"><a href="#"><strong><?php echo $text['STEP']; ?> 4)</strong> <?php echo $text['ORDER_PLACED']; ?></a></li>
        </ul>
    </div><!-- /col -->
</div><!-- /row -->

<div class="row">
<div class="col-md-8 col-md-offset-2">
    <h1><?=$strMessageTitle?></h1>
    
    <?=$strMessage?>
    
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
</script>

<?php
require('includes/php/dc_footer.php');
?>