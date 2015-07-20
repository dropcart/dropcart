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
require_once('libraries/Mollie/API/Autoloader.php');

// Start API
require_once('libraries/Api_Inktweb/API.class.php');

// Mollie Payment API
$mollie = new Mollie_API_Client;
$mollie->setApiKey(MOLLIE_API_KEY);

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

$intSessionId 	= session_id();
$intCustomerId 	= (isset($_SESSION["customerId"])) ? $_SESSION["customerId"] : 0;

//opening database
$objDB = new DB();

$objCart = new Cart();
$objCart->setDatabaseObject($objDB);
$objCart->setSessionId($intSessionId);
$objCart->setCustomerId($intCustomerId);

$result_header_cart = $objCart->getCart();
$intNodeItems = 0;
$dblNodePriceTotal = 0;

$arrCartItems = array();
$discountCode = (isset($_SESSION["discountCode"])) ? $_SESSION["discountCode"] : null;

$i = 0;
while($objNodeCart = $objDB->getObject($result_header_cart)) {

	$Product	= $Api->getProduct($objNodeCart->productId);
	$arrImages 	= (array) $Product->getImages();

	$arrCartItems[$i]['intQuantity']		= $objNodeCart->quantity;
	$arrCartItems[$i]['dblPrice']			= calculateProductPrice($Product->getPrice(), $objNodeCart->productId, $arrCartItems[$i]['intQuantity'], false);

	$intNodeItems 		+= $objNodeCart->quantity;
	$dblNodePriceTotal 	+= $arrCartItems[$i]['dblPrice'] * $objNodeCart->quantity;

	$i++;
}

$dblShippingcosts 		= calculateSiteShipping($dblNodePriceTotal, '', false);
$dblNodePriceTotal		= $dblNodePriceTotal + $dblShippingcosts;

if(!empty($_SESSION["discountCode"])) {

	$arrDiscount = calculateDiscount($_SESSION["discountCode"]);

	$dblDiscountAmount	= $arrDiscount['dblDiscountAmount'];
	$dblDiscountOver	= $arrDiscount['dblDiscountOver'];

	if($dblDiscountOver > 0) {

		$dblShippingcosts = $dblShippingcosts - $dblDiscountOver;

		if($dblShippingcosts < 0) $dblShippingcosts = 0;
	}

	$dblNodePriceTotal	= $arrDiscount['dblPriceTotal'] + $dblShippingcosts;

}

$intCartItems = $objDB->getRecordCount("cart", "id", "WHERE (customerId=".$intCustomerId." AND customerId != 0)");
if($intCartItems == 0)
{
	header('Location: '.SITE_URL.'/dc_shoppingcart.php');
	exit;
} elseif(!empty($_POST)) {
	$_POST = sanitize($_POST);
	$_SERVER = sanitize($_SERVER);

	$strSQL = "INSERT INTO ".DB_PREFIX."customers_orders_id (customerId) VALUES (".$intCustomerId.")";
	$result = $objDB->sqlExecute($strSQL);
	$intOrderId = $objDB->getInsertedId();




	if ($dblNodePriceTotal > 0) {

		/* Get the cart items result */
		$cartResult = $objCart->getCart();



		/* Save all the cart items to the cart archive table, which can't be manipulated by the user */
		while($row = $objDB->getObject($cartResult) ){


			$newCartItem = "INSERT INTO ".DB_PREFIX."cart_archive
			(entryDate, orderId, customerId, productId, quantity)
			VALUES (
				'{$row->entryDate}',
				'{$intOrderId}',
				'{$row->customerId}',
				'{$row->productId}',
				'{$row->quantity}'
				)";

			$objDB->sqlExecute($newCartItem);

		}


		$protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
		$hostname = $_SERVER['HTTP_HOST'];

		$method = $_POST['paymentMethod'];

		$transactionFeeAddition = formOption($method . '_fee_addition');
		$transactionFeePercentage = formOption($method . '_fee_percent') / 100;

		$dblNodePriceTotal = round($dblNodePriceTotal + ($dblNodePriceTotal * $transactionFeePercentage) + $transactionFeeAddition,2);

		/*
		 * Payment parameters:
		 *   amount        Amount in EUROs. This example creates a € 10,- payment.
		 *   description   Description of the payment.
		 *   redirectUrl   Redirect location. The customer will be redirected there after the payment.
		 *   metadata      Custom metadata that is stored with the payment.
		 */
		$payment = $mollie->payments->create(array(
			"amount"       => $dblNodePriceTotal,
			"method"       => $method,
			"description"  => SITE_NAME . " Ordernr. " . formOption('order_number_prefix') . $intOrderId,
			"redirectUrl"  => "{$protocol}://{$hostname}{$path}/dc_shoppingcart4.php?order_id={$intOrderId}",
			"metadata"     => array(
				"order_id" => $intOrderId,
			),
		));

		$strSQL = "UPDATE ".DB_PREFIX."customers_orders_id SET discountCode = '".$_SESSION["discountCode"]."', status = '".$payment->status."', transactionId = '".$payment->id."' WHERE orderId = ".$intOrderId;
		$result = $objDB->sqlExecute($strSQL);

		header("Location: " . $payment->getPaymentUrl());

	} else {

		// Price is 0, so we give a custom status to process the order
		$strSQL = "UPDATE ".DB_PREFIX."customers_orders_id SET discountCode = '".$_SESSION["discountCode"]."', status = 'ready' WHERE orderId = ".$intOrderId;
		$result = $objDB->sqlExecute($strSQL);

		header('Location: '.SITE_URL.'/dc_shoppingcart3_process.php?orderId='.$intOrderId);

	}

}
unset($arrCartItems);


// Start displaying HTML
require_once('includes/php/dc_header.php');

?>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.2/css/bootstrapValidator.min.css"/>

<div class="row">
	<div class="col-xs-12">
		<ul class="nav nav-tabs">
			<li class=""><a href="<?php SITE_URL?>/dc_shoppingcart.php"><strong>Stap 1)</strong> Winkelmand</a></li>
			<li class=""><a href="<?php SITE_URL?>/dc_shoppingcart2.php"><strong>Stap 2)</strong> Gegevens</a></li>
			<li class="active"><a href="<?php SITE_URL?>/dc_shoppingcart3.php"><strong>Stap 3)</strong> Betaling</a></li>
			<li class="disabled"><a href="<?php SITE_URL?>/dc_shoppingcart4.php"><strong>Stap 4)</strong> Bestelling geplaatst</a></li>
		</ul>
	</div><!-- /col -->
</div><!-- /row -->

<div class="row">
<div class="col-md-8 col-md-offset-2">
<form class="form-horizontal checkoutForm" role="form" action="dc_shoppingcart3.php" method="post">
	<fieldset>

	<legend>Klopt alles?</legend>

	<?php
	$strSQL = "SELECT ca_invoice.*, ca_delivery.firstname as delFirstname, ca_delivery.lastname as delLastname, ca_delivery.address as delAddress, ca_delivery.houseNr as delHouseNr, ca_delivery.houseNrAdd as delHouseNrAdd, ca_delivery.zipcode as delZipcode, ca_delivery.city as delCity, ca_delivery.lang as delLang, c.email, c.id as customerId FROM ".DB_PREFIX."customers c " .
		"INNER JOIN ".DB_PREFIX."customers_addresses ca_invoice ON ca_invoice.custId = c.id AND ca_invoice.defaultInv = 1 " .
		"INNER JOIN ".DB_PREFIX."customers_addresses ca_delivery ON ca_delivery.custId = c.id AND ca_delivery.defaultDel = 1 " .
		"WHERE c.id = " . $intCustomerId;
	$result = $objDB->sqlExecute($strSQL);
	$objCustomer = $objDB->getObject($result);


	?>

	<div class="row" style="margin-bottom:20px">
		<div class="col-md-4">
		<strong>Factuuradres</strong><br/>
		<?=($objCustomer->company != '') ? $objCustomer->company . '<br/>' : ''?>
		<?=$objCustomer->firstname?> <?=$objCustomer->lastname?><br/>
		<?=$objCustomer->address?> <?=$objCustomer->houseNr?><?=$objCustomer->houseNrAdd?><br/>
		<?=$objCustomer->zipcode?> <?=$objCustomer->city?><br/>
		<?php
		if ($objCustomer->lang == 'be') {
			echo 'België';
		}
		else {
			echo 'Nederland';
		}
		?>
		</div>

		<div class="col-md-4">
		<strong>Afleveradres</strong><br/>
		<?=(isset($objCustomer->delCompany) && $objCustomer->delCompany != '') ? $objCustomer->delCompany . '<br/>' : ''?>
		<?=$objCustomer->delFirstname?> <?=$objCustomer->delLastname?><br/>
		<?=$objCustomer->delAddress?> <?=$objCustomer->delHouseNr?><?=$objCustomer->delHouseNrAdd?><br/>
		<?=$objCustomer->delZipcode?> <?=$objCustomer->delCity?><br/>
		<?php
		if ($objCustomer->delLang == 'be') {
			echo 'België';
		}
		else {
			echo 'Nederland';
		}
		?>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<strong>Uw bestelling</strong>
		<?php
		$intCartRows = count($arrCartItems);

		if($intCartRows > 0) { ?>

			<table class="table shoppingcart">
				<thead>
					<tr>
						<th style="width:60%">Product</th>
						<th style="width:10%">Aantal</th>
						<th style="width:15%">Prijs</th>
						<th style="width:15%" class="text-right">Totaal</th>
					</tr>
				</thead>
				<tbody>

			<?php foreach($arrCartItems as $arrCartItem) {



					$strStock = ($arrCartItem['intStock'] >= $arrCartItem['intQuantity']) ? 'Op voorraad' : 'Niet op voorraad (circa 3 werkdagen levertijd)';

					echo '
						<tr>
							<td class="text-left">
								<img class="img-responsive pull-left" alt="product naam" src="' . $arrCartItem['strImageUrl'] . '" width="72" />
								'.$arrCartItem['strProductTitle'].'
							</td>
							<td class="text-left">' . $arrCartItem['intQuantity'] . '</td>
							<td class="text-left">'.$arrCartItem['strPrice'].'</td>
							<td class="text-right productTotal">'.$arrCartItem['strPriceTotal'].'</td>
						</tr>';

				}

			if($discountCode != null) {

				echo '
					<tr class="table-footer">
						<td>&nbsp;</td>
						<td style="text-align:right;" colspan="2">Kortingscode '.$discountCode.'</td>
						<td class="discountAmount">' . $dblDiscountAmount . '</td>
					</tr>';

			}
			echo '
				<tr class="table-footer">
					<td>&nbsp;</td>
					<td style="text-align:right;" colspan="2">Subtotaal</td>
					<td class="subtotal">' . $strNodePriceSubtotal . '</td>
				</tr>
				<tr class="table-footer">
					<td>&nbsp;</td>
					<td style="text-align:right;" colspan="2">Verzendkosten</td>
					<td class="shippingCosts">' . $strShippingCosts . '</td>
				</tr>
				<tr class="table-footer transactionFeeRow" style="display:none;"">
					<td>&nbsp;</td>
					<td style="text-align:right;" colspan="2">Transactiekosten</td>
					<td class="transactionFee">0</td>
				</tr>
				<tr class="table-footer">
					<td>&nbsp;</td>
					<td style="text-align:right;" colspan="2">Totaal</td>
					<td><h3 class="total" data-total="' . $dblNodePriceTotal . '">' . $strNodePriceTotal . '</h3></td>
				</tr>
				</tbody>
			</table>
			';

		} else {

			echo '<p>Geen producten in winkelwagen.</p>';

		}

		?>
		</div>
	</div><!-- /col -->




	<div class="form-group">
		<div class="col-md-12">
			<strong>Kies uw betaal methode:</strong>
			<?php
			$methods = $mollie->methods->all();
			foreach ($methods as $method):
				$addition = formOption($method->id . '_fee_addition');
				$percentage = formOption($method->id . '_fee_percent');

				if($percentage > 0 && $addition > 0) {
					$transactionCost = sprintf('%s%% en %s euro', $percentage, $addition);
				} elseif($percentage > 0 && empty($addition)) {
					$transactionCost = sprintf('%s%%', $percentage);
				} elseif($addition > 0 && empty($percentage)) {
					$transactionCost = sprintf('%s euro', $addition);
				} else {
					$transactionCost = 'geen';
				}

				$transactionNotice= sprintf('Wanneer u via %s betaalt, worden er %s extra transactiekosten bovenop de totaalprijs in rekening gebracht.', $method->description, $transactionCost);
			?>
			<div class="radio paymentMethod" style="line-height:40px; vertical-align:top">
				<label>
					<input<?php echo ($method->id == 'ideal' ? ' checked' : ''); ?> data-addition="<?php echo $addition; ?>" data-percent="<?php echo $percentage; ?>" class="paymentMethodInput" type="radio" style="margin-top:15px;" name="paymentMethod" value="<?php echo $method->id; ?>">
					<img src="<?php echo htmlspecialchars($method->image->normal) ?>">
					<?php echo htmlspecialchars($method->description) . ' (' .  htmlspecialchars($method->id) . ')'; ?>
				</label>
				<div class="alert alert-info transactionNotice" style="padding: 5px 5px;line-height:18px;">
					<?php echo $transactionNotice; ?>
				</div>
			</div>
			<?php endforeach; ?>
			<p id="paymentMethodNotice" style="display:none;">Bij betalingen anders dan via iDeal wordt er 3% transactiekosten bovenop de totaalprijs gerekend.</p>
			<script>
				$(function() {
					$('.transactionNotice').hide();

					$('.paymentMethodInput').change(function() {
						$('.transactionNotice').slideUp();
						var $this = $(this);
						if($this.is(':checked')) {
							var percent = parseFloat($this.data('percent'));
							var addition = parseFloat($this.data('addition'));
							var total = parseFloat($('.total').data('total'));
							var transactionFee = 0;
							if(percent > 0 && addition > 0) {
								$('.transactionFeeRow').show();
								transactionFee = (parseFloat(total*(percent/100)) + parseFloat(addition));
							} else if(percent > 0 && isNaN(addition)) {
								$('.transactionFeeRow').show();
								transactionFee = (parseFloat(total*(percent/100)));
							} else if(addition > 0 && isNaN(percent)) {
								$('.transactionFeeRow').show();
								transactionFee = (parseFloat(addition));
							} else {
								$('.transactionFeeRow').hide();
							}

							$('.transactionFee').html('&euro; ' + transactionFee.toFixed(2).replace('.', ','));
							$('.total').html('&euro; ' + (total + transactionFee).toFixed(2).replace('.', ','));
							$this.parents('.paymentMethod').find('.transactionNotice').slideDown();
						}
					});

					$('.paymentMethodInput:eq(0)').trigger('change');
				});
			</script>
		</div>
	</div>

	<div class="form-group checkbox">
		<div class="col-sm-12">
			<label><input type="checkbox" name="conditions"> Ik ga akoord met de algemene voorwaarden</label> </a>
		</div><!-- /col -->
	</div><!-- /form-group -->

	<hr />

	<div class="form-group">
		<div class="col-sm-8">
			<strong>Heeft u een kortings of vouchercode?</strong>
			<p><a id="discountCode"><span class="glyphicon glyphicon-arrow-right"></span> Code invoeren</a></p>
			<div class="discount_container" style="display:none">
				<div class="discount_input">
					<input type="text" name="discountcode" id="discountCodeValue" placeholder="Uw kortingscode.." class="discountValue" value="<?php echo $_SESSION["discountCode"]; ?>" /><a class="btn btn-primary btn-xs" id="discountCodeSend">Versturen</a>
				</div>
				<div class="discount_code"></div>
				<div class="discount_message"></div>
				<div class="discount_error error"></div>
			</div>
		</div><!-- /col -->

		<div class="col-sm-4">
			<div class="pull-right">
				<button type="submit" class="btn btn-primary btn-lg">Bestellen met betaalplicht</button>
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

$(document).ready(function() {
    $('.checkoutForm').bootstrapValidator({
        message: 'Dit veld is verplicht',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
		},
		fields: {
			conditions: {
				validators: {
					notEmpty: {
						message: 'U bent niet akkoord gegaan met de voorwaarden'
					}
				}
			}

		}
    });
});
</script>
<script>
$('#discountCode').click(function(){

	$(this).hide();
	$('.discount_container').show();

});

$('#discountCodeSend').click(function(){

	var discountCode = $('#discountCodeValue').val();

	$.ajax({
		type:		'POST',
		dataType:	'json',
		url:		'<?php echo SITE_URL?>/includes/json/calculateDiscount.php',
		data:		{ code: discountCode, timestamp: '<?=$_SERVER["REQUEST_TIME"]?>' },
		success: function(data, textStatus) {
		// Handle success

			$('.discount_error').html('');

			if(data.validationRequired == 1) {

				$('.discount_input').hide();
				$('.discount_code').html('<div>Kortingscode: '+ discountCode +'</div>');
				$('.discount_message').html(
					'<div class="italic">Voor deze code is een validatiecode vereist.<br/>Vul uw validatiecode in die u heeft ontvangen.</div>' +
					'<input type="text" name="validationCode" id="validationCodeValue" placeholder="Uw validatiecode.." class="discountValue" value="<?=$_SESSION["validationCode"]?>" />' +
					'<a class="btn btn-primary btn-xs" id="validationCodeSend">Versturen</a>'
				);

				<?php if($_SESSION["validationCode"] != "") { ?>
					$('#validationCodeSend').click();
				<?php } ?>

			} else {

				$('.discountAmount_container').show();
				$('.discount_error').html('');
				$('.discount_message').html('');
				$('.discountAmount_code').html(data.discountCode);
				$('.discountAmount').html(data.cartDiscountAmount);
				$('.subtotal').html(data.cartSubTotal);
				$('.shippingCosts').html(data.cartShippingcosts);
				$('.total').html(data.cartTotal);

			}


		},
		error: function(xhr, textStatus, errorThrown) {
			// Handle error
			var errorMessage = $.parseJSON(xhr.responseText).error;
			$('.discount_error').html(errorMessage);
		}
	});

});

<?php if($_SESSION["discountCode"] != "") { ?>
	$('#discountCode').click();
	$('#discountCodeSend').click();
<?php } ?>

$(document).on('click','#validationCodeSend',function(){

	var discountCode	= $('#discountCodeValue').val();
	var validationCode	= $('#validationCodeValue').val();

	$.ajax({
		type:		'POST',
		dataType:	'json',
		url:		'<?php echo SITE_URL?>/includes/json/calculateDiscount.php',
		data:		{ code: discountCode, validationCode: validationCode, timestamp: '<?=$_SERVER["REQUEST_TIME"]?>' },
		success: function(data, textStatus) {
		// Handle success

			$('.discountAmount_container').show();
			$('.discount_error').html('');
			$('.discount_message').html('');
			$('.discountAmount_code').html(data.discountCode);
			$('.discountAmount').html(data.cartDiscountAmount);
			$('.subtotal').html(data.cartSubTotal);
			$('.shippingCosts').html(data.cartShippingcosts);
			$('.total').html(data.cartTotal);

//			$('.cartItems').html(data.cartItems);
//			$('.cartSubtotal').html(data.cartSubTotal);


		},
		error: function(xhr, textStatus, errorThrown) {
			// Handle error
			var errorMessage = $.parseJSON(xhr.responseText).error;
			$('.discount_error').html(errorMessage);
		}
	});

});

$('.cartQuantity').change(function(){

	var curThis		= $(this);
	var intQuantity	= $(this).val();
	var intCartId	= $(this).data('cartid');

	$.get(
		'<?php echo SITE_URL ?>/includes/json/updateCartQuantity.php',
		{
			cartId		: intCartId,
			quantity	: intQuantity,
			timestamp	: '<?=$_SERVER["REQUEST_TIME"]?>'
		},
		function(data) {

			$(curThis).parent().parent().find('.productTotal').html(data.productTotal);
			$('.subtotal').html(data.cartSubTotal);
			$('.shippingCosts').html(data.cartShippingCosts);
			$('.total').html(data.cartTotal);

			$('.cartItems').html(data.cartItems);
			$('.cartSubtotal').html(data.cartSubTotal);

		},
		'json'
	);

});

$('.deleteItem').click(function() {

	var curThis		= $(this);
	var intCartId	= $(this).data('cartid');

	$.get(
		'<?php echo SITE_URL?>/includes/json/deleteCartItem.php',
		{
			cartId		: intCartId,
			timestamp	: '<?=$_SERVER["REQUEST_TIME"]?>'
		},
		function(data) {

			$(curThis).parent().parent().fadeOut(400, function() {
				$(this).remove();
			});

			if(data.cartItems == null) {
				// Zero product in cart left. Refresh page to show a empty cart.

				document.location.href = '?';

			} else {

				$('.subtotal').html(data.cartSubTotal);
				$('.shippingCosts').html(data.cartShippingCosts);
				$('.total').html(data.cartTotal);

				$('.cartItems').html(data.cartItems);
				$('.cartSubtotal').html(data.cartSubTotal);

			}

		},
		'json'
	);

});
</script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.2/js/bootstrapValidator.min.js" ></script>

<?php
require('includes/php/dc_footer.php');
?>