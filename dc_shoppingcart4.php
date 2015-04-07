<?php
session_start();

// Required includes
require_once('includes/php/dc_connect.php');
require_once('_classes/class.database.php');
$objDB = new DB();
require_once('includes/php/dc_config.php');

// Page specific includes
require_once('_classes/class.cart.php');
require_once('includes/php/dc_functions.php');

// Start API
require_once('libraries/Api_Inktweb/API.class.php');

//opening database
$objDB = new DB();

$intOrderId		= (int) $_GET["order_id"];
$intCustomerId	= (int) $_SESSION["customerId"];

$strSQL = "SELECT status FROM ".DB_PREFIX."customers_orders_id WHERE orderId = ".$intOrderId." AND customerId = ".$intCustomerId;
$result = $objDB->sqlExecute($strSQL);
$objResult = $objDB->getObject($result);

$status = $objResult->status;

$_SESSION["discountCode"] = "";

switch($status) {
	
	case 'paid':
	case 'paidout':
		// Bestelling voltooid
		
		$strMessageTitle = 'Bedankt voor uw bestelling';
		$strMessage = '<p>Uw bestelling is in goede orde ontvangen en wij gaan direct aan de slag om uw bestelling te verwerken. Wij hebben ter bevestiging een email gestuurd naar uw email adres.</p>' .
			'<p>Wanneer uw bestelling wordt verzonden sturen wij u nog een email met informatie over hoe u uw pakket kunt volgen.</p>';
			
		$strSQL = "SELECT * FROM ".DB_PREFIX."discountcodes_codes dc_c WHERE dc_c.parentOrderId = ".$intOrderId;
		$result = $objDB->sqlExecute($strSQL);
		$intCodeExists = $objDB->getNumRows($result);

		if($intCodeExists == 1) {
	
			$objCode = $objDB->getObject($result);
			
			$strMessage .= '
				<p>&nbsp;</p>
				<h4>Geef uw resterende tegoed aan iemand cadeau!</h4>
				<p>U heeft met uw gebruikte vouchercode nog een tegoed over. Wij bieden u de mogelijkheid om dit resterende tegoed cadeau te geven aan een vriend of familielid.</p>
				<div class="well text-center">
					<h3>Uw resterende tegoed: ' . money_format('%(#1n', $objCode->discountValue) . '</h3>
					<span class="btn btn-lg btn-success" style="margin:10px 0 20px" id="btn_maf">Geef uw tegoed cadeau per e-mail</span>
					<span class="btn btn-lg btn-primary" style="margin:10px 0 20px;background:linear-gradient(to bottom, #428bca 0px, #2d6ca2 100%)" onclick="window.open(\'https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fwww.dropcart.nl%2Fdc_share_code_facebook.php%3Ftegoed='.$objCode->discountValue.'%26code='.$objCode->code.'\',\'facebook\',
\'width=500,height=300,scrollbars=no,toolbar=no,location=no\'); return false">Deel uw tegoed op Facebook</span>
				
					<div style="display:none" id="div_maf">
		
						<p class="text-left">Vul hieronder de naam en het e-mailadres in van degene aan wie u dit cadeau wilt schenken en hij/zij ontvangt het tegoed per e-mail.</p>
					
						<form class="form-horizontal mailAFriendForm" role="form" method="post" action="dc_shoppingcart4_tellafriend.php">
							<input type="hidden" name="orderId" value="'.$intOrderId.'" />
							<div class="form-group">
								<label class="col-sm-2 control-label" for="textinput">Naam</label>
								<div class="col-sm-4">
									<input type="text" class="form-control" name="mafName" data-bv-notempty="true">
								</div><!-- /col -->
							</div><!-- /form-group -->
							<div class="form-group">
								<label class="col-sm-2 control-label" for="textinput">E-mailadres</label>
								<div class="col-sm-4">
									<input type="email" class="form-control" name="mafEmail" data-bv-notempty="true" data-bv-emailaddress="true" data-bv-message="Ongeldig e-mailadres">
								</div><!-- /col -->
								<div class="col-sm-4">
									<button type="submit" class="btn btn-primary btn-default">Versturen</button>
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
		// Bestelling nog niet voltooid
		
		$strMessageTitle = 'Wachten op betaling';
		$strMessage = '<p>Uw bestelling wacht op een bevestiging van betaling. Zodra uw betaling is geverifieerd ontvangt u van ons een e-mail met de bevestiging van uw bestelling.</p>' .
			'<p>Wanneer uw bestelling wordt verzonden sturen wij u nog een email met informatie over hoe u uw pakket kunt volgen.</p>';
		
		break;
	case 'cancelled':
	case 'expired':
		// Bestelling niet verkopen of gestopt
	
		header('Location: /dc_shoppingcart3.php');
		exit;
	
		break;
	
}

// Start displaying HTML
require_once('includes/php/dc_header.php');
?>

<div class="row">
	<div class="col-xs-12">
		<ul class="nav nav-tabs">
			<li class="disabled"><a href="#"><strong>Stap 1)</strong> Winkelmand</a></li>
			<li class="disabled"><a href="#"><strong>Stap 2)</strong> Gegevens</a></li>
			<li class="disabled"><a href="#"><strong>Stap 3)</strong> Betaling</a></li>
			<li class="active"><a href="#"><strong>Stap 4)</strong> Bestelling geplaatst</a></li>
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
        message: 'Dit veld is verplicht',
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
require('includes/php/dc_footer.php');
?>