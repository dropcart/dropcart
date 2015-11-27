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

// Start API
require_once 'libraries/Api_Inktweb/API.class.php';

//opening database
$objDB = new DB();

$intOrderId = (isset($_GET["order_id"])) ? $_GET["order_id"] : 0;
$intCustomerId = (isset($_SESSION["customerId"])) ? $_SESSION["customerId"] : 0;

$strSQL = "SELECT status FROM " . DB_PREFIX . "customers_orders_id WHERE orderId = " . $intOrderId . " AND customerId = " . $intCustomerId;
$result = $objDB->sqlExecute($strSQL);
$objResult = $objDB->getObject($result);

$status = (isset($objResult->status)) ? $objResult->status : null;

$_SESSION["discountCode"] = "";

$strMessageTitle = null;
$strMessage = null;
switch ($status) {
    case 'paid':
    case 'paidout':
        // Betaling voltooid
        $strMessageTitle = $text['THANKS_ORDER_TITLE'];
        $strMessage = '<p>' . $text['THANKS_ORDER_MESSAGE_1'] . '</p>' .
        '<p>' . $text['THANKS_ORDER_MESSAGE_2'] . '</p>';
        
        // Share remaining discount if code was generated        
		$strSQL = "SELECT * FROM ".DB_PREFIX."discountcodes_codes dc_c WHERE dc_c.parentOrderId = ".$intOrderId;
		$result = $objDB->sqlExecute($strSQL);
		$intCodeExists = $objDB->getNumRows($result);

		if($intCodeExists == 1) {
	
			$objCode = $objDB->getObject($result);
			
			$strMessage .= '
				<p>&nbsp;</p>
				<h4>' . $text['SHARE_DISCOUNT_TITLE'] . '</h4>
				<p>' . $text['SHARE_DISCOUNT_MESSAGE_1'] . '</p>
				<div class="well text-center">
					<h3>' . $text['SHARE_DISCOUNT_MESSAGE_2'] . ': ' . money_format('%(#1n', $objCode->discountValue) . '</h3>
					<span class="btn btn-lg btn-success" style="margin:10px 0 20px" id="btn_maf">' . $text['SHARE_DISCOUNT_MESSAGE_3'] . '</span>
				
					<div style="display:none" id="div_maf">
		
						<p class="text-left">' . $text['SHARE_DISCOUNT_MESSAGE_4'] . '</p>
					
						<form class="form-horizontal mailAFriendForm" role="form" method="post" action="dc_shoppingcart4_share.php">
							<input type="hidden" name="orderId" value="'.$intOrderId.'" />
							<div class="form-group">
								<label class="col-sm-2 control-label" for="textinput">' . $text['NAME'] . '</label>
								<div class="col-sm-4">
									<input type="text" class="form-control" name="mafName" data-bv-notempty="true">
								</div><!-- /col -->
							</div><!-- /form-group -->
							<div class="form-group">
								<label class="col-sm-2 control-label" for="textinput">' . $text['EMAIL'] . '</label>
								<div class="col-sm-4">
									<input type="email" class="form-control" name="mafEmail" data-bv-notempty="true" data-bv-emailaddress="true" data-bv-message="' . $text['INVALID'] . ' ' . $text['EMAIL'] . '">
								</div><!-- /col -->
								<div class="col-sm-4">
									<button type="submit" class="btn btn-primary btn-default">' . $text['SEND'] . '</button>
								</div><!-- /col -->
							</div><!-- /form-group -->
						</form>
					
					</div>
				
				</div>
				
				';
			
			
		}

        break;
    case 'open':
    case 'pending':
        // Betaling nog niet voltooid
        $strMessageTitle = $text['WAIT_ORDER_TITLE'];
        $strMessage = '<p>' . $text['WAIT_ORDER_MESSAGE_1'] . '</p>' .
        '<p>' . $text['WAIT_ORDER_MESSAGE_2'] . '</p>';

        break;
    case 'cancelled':
    case 'expired':
        // Betaling verlopen of gestopt
        header('Location: ' . SITE_URL . '/dc_shoppingcart3.php');
        exit();

        break;

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

$(document).ready(function() {
    $('.mailAFriendForm').bootstrapValidator({
        message: <?php echo $text['REQUIRED_FIELD']; ?>,
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        }
    });
});

$('#btn_maf').click(function(){
    $('#div_maf').slideDown();
});
</script>
<script src="/libraries/bootstrapValidator/js/bootstrapValidator.min.js" ></script>

<?php
require 'includes/php/dc_footer.php';
?>