<?php
session_start();

require_once('includes/php/dc_connect.php');
require_once('_classes/class.database.php');
$objDB = new DB();
require_once('includes/php/dc_config.php');

require_once('includes/php/dc_functions.php');
require_once('includes/php/dc_mail.php');
require_once('_classes/class.cart.php');
require_once('libaries/Api_Inktweb/API.class.php');	// DropCart API

$intOrderId		= (int) $_POST["orderId"];
$intCustomerId	= (int) $_SESSION["customerId"];

$strSQL = "SELECT status FROM ".DB_PREFIX."customers_orders_id WHERE orderId = ".$intOrderId." AND customerId = ".$intCustomerId;
$result = $objDB->sqlExecute($strSQL);
$objResult = $objDB->getObject($result);

if(!empty($_POST)) {

	$_POST = sanitize($_POST); // Escape all $_POST variables
	
	$strEmail	= $_POST["mafEmail"];
	$strName	= $_POST["mafName"];
	
	// send order mail
	sendMail('discountCodeMail', $strEmail, $strName, array(
		'FRIEND_NAME' => $strName
	));

	$strMessageTitle = 'Cadeaubon verzonden';
	$strMessage = '<p>De cadeaubon met het resterende tegoed is naar het door u opgegeven e-mailadres verzonden.</p>';
	
}
		
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
</script>

<?php
require('includes/php/dc_footer.php');
?>