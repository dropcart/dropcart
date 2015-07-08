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

// Start displaying HTML
require_once('includes/php/dc_header.php');

$result_header_cart = $objNode->getCart();
$intNodeItems = 0;
$dblNodePriceTotal = 0;

$arrCartItems = array();



$i = 0;
while($objNodeCart = $objDB->getObject($result_header_cart)) {
	
	$Product	= $Api->getProduct($objNodeCart->productId);
	$arrImages 	= (array) $Product->getImages();
	
	$arrCartItems[$i]['cartId']			= $objNodeCart->id;
	$arrCartItems[$i]['strImageUrl']		= $arrImages[0]->url;

	// check if valid image (ignore warnings)
	if (@!getimagesize($arrCartItems[$i]['strImageUrl'])) {
		$arrCartItems[$i]['strImageUrl'] 	= DEFAULT_PRODUCT_IMAGE;
	}

	$arrCartItems[$i]['strProductTitle']		= $Product->getTitle();
	$arrCartItems[$i]['intQuantity']		= $objNodeCart->quantity;
	$arrCartItems[$i]['dblPrice']			= calculateProductPrice($Product->getPrice(), $objNodeCart->productId, $arrCartItems[$i]['intQuantity'], false);
	$arrCartItems[$i]['strPrice']			= money_format('%(#1n', $arrCartItems[$i]['dblPrice']);
	$arrCartItems[$i]['strPriceTotal']		= money_format('%(#1n', ($arrCartItems[$i]['dblPrice'] * $arrCartItems[$i]['intQuantity']) );
	$arrCartItems[$i]['intStock']			= $Product->getStock();
	$arrCartItems[$i]['intProductId']		= $Product->getId();

	$intNodeItems 				+= $objNodeCart->quantity;
	$dblNodePriceTotal 				+= $arrCartItems[$i]['dblPrice'] * $objNodeCart->quantity;
	
	$i++;
}


$strNodePriceSubtotal	= money_format('%(#1n', $dblNodePriceTotal);
$dblShippingCosts		= calculateSiteShipping($dblNodePriceTotal, '', false);
$strShippingCosts		= money_format('%(#1n', $dblShippingCosts);
$dblNodePriceTotal		= $dblNodePriceTotal + $dblShippingCosts;
$strNodePriceTotal		= money_format('%(#1n', $dblNodePriceTotal);
?>

<div class="row">
	<div class="col-xs-12">
		<ul class="nav nav-tabs">
			<li class="active"><a href="/dc_shoppingcart.php"><strong>Stap 1)</strong> Winkelmand</a></li>
			<li class="<?php if(empty($_SESSION["customerId"])) echo 'disabled'; ?>"><a href="<?php if(!empty($_SESSION["customerId"])) echo '/dc_shoppingcart2.php'; else '#'; ?>"><strong>Stap 2)</strong> Gegevens</a></li>
			<li class="<?php if(empty($_SESSION["customerId"])) echo 'disabled'; ?>"><a href="<?php if(!empty($_SESSION["customerId"])) echo '/dc_shoppingcart3.php'; else '#'; ?>"><strong>Stap 3)</strong> Betaling</a></li>
			<li class="disabled"><a href="#"><strong>Stap 4)</strong> Bestelling geplaatst</a></li>
		</ul>
	</div><!-- /col -->
	<div class="col-xs-9">

		<?php
		$intCartRows = count($arrCartItems);
		
		if($intCartRows > 0) { ?>
		
			<table class="table table-striped shoppingcart">
				<thead>
					<tr>
						<th style="width:55%">Product</th>
						<th style="width:5%">Aantal</th>
						<th style="width:15%">Prijs</th>
						<th style="width:15%">Totaal</th>
						<th style="width:10%">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
		
			<?php foreach($arrCartItems as $arrCartItem) { ?> 

					<script>
						var stock_<?php echo $arrCartItem['intProductId']; ?> = "<?php echo $arrCartItem['intStock']; ?>";
					</script>

			<?php 		if ($arrCartItem['intStock'] >= $arrCartItem['intQuantity'] OR $arrCartItem['intStock'] == 'infinite') {
						$strStock = 'Op voorraad';
					}
					elseif ($arrCartItem['intStock'] > 0) {
						$strStock = 'Niet voldoende op voorraad';
					}
					else {
						$strStock = 'Niet op voorraad (circa 3 werkdagen levertijd)';
					}

					echo '
						<tr>
							<td class="text-left">
								<img class="img-responsive pull-left" alt="'.$arrCartItem['strProductTitle'].'" src="' . $arrCartItem['strImageUrl'] . '" width="72" />
								<h4>'.$arrCartItem['strProductTitle'].'</h4>
								<p><strong>Voorraad: </strong><span class="stock_message">'.$strStock.'</span></p>
							</td>
							<td><input type="number" value="' . $arrCartItem['intQuantity'] . '" min="1" class="cartQuantity" data-cartid="'.$arrCartItem['cartId'].'" data-productid="'.$arrCartItem['intProductId'].'" /></td>
							<td class="text-left">'.$arrCartItem['strPrice'].'</td>
							<td class="text-left productTotal">'.$arrCartItem['strPriceTotal'].'</td>
							<td><a class="btn btn-danger btn-sm deleteItem" title="Verwijder artikel uit winkelmandje" data-cartid="'.$arrCartItem['cartId'].'"><span class="glyphicon glyphicon-remove"></span> Verwijder</a></td>
						</tr>';
					}

		
			echo '
			
				<tr class="table-footer discountAmount_container" style="display:none">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style="text-align:right" colspan="2">Kortingscode <span class="discountAmount_code"></span></td>
					<td class="discountAmount"></td>
				<tr class="table-footer">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style="text-align:right" colspan="2">Subtotaal</td>
					<td class="subtotal">' . $strNodePriceSubtotal . '</td>
				</tr>
				<tr class="table-footer">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style="text-align:right" colspan="2">Verzendkosten</td>
					<td class="shippingCosts">' . $strShippingCosts . '</td>
				</tr>
				<tr class="table-footer">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style="text-align:right" colspan="2">Totaal</td>
					<td><h3 class="total">' . $strNodePriceTotal . '</h3></td>
				</tr>
				<tr class="table-footer table-checkout">
					<td class="text-left" colspan="4">
						<strong>Heeft u een kortings of vouchercode?</strong>
						<p><a id="discountCode"><span class="glyphicon glyphicon-arrow-right"></span> Code invoeren</a></p>
						<div class="discount_container" style="display:none">
							<div class="discount_input">
								<input type="text" name="discountcode" id="discountCodeValue" placeholder="Uw kortingscode.." class="discountValue" value="'.$_SESSION["discountCode"].'" /><a class="btn btn-primary btn-xs" id="discountCodeSend">Versturen</a>
							</div>
							<div class="discount_code"></div>
							<div class="discount_message"></div>
							<div class="discount_error error"></div>
						</div>
						
					</td>
					<td><a href="/dc_shoppingcart2.php" class="btn btn-primary">Bestelling afronden</a></td>
				</tr>
				</tbody>
			</table>
			';
			
		} else {
		
			echo '<p>Geen producten in winkelwagen.</p>';
			
		}
		
		?>
	</div><!-- /col -->
	<div class="col-xs-3">
		<table class="table table-condensed table-bargain">
		<?php

		$strSQL = "SELECT id FROM ".DB_PREFIX."products WHERE opt_cart = 1 ";
		$result = $objDB->sqlExecute($strSQL);
		while ($objProduct = $objDB->getObject($result)) {

			$Product 		= $Api->getProduct($objProduct->id, '?fields=title,images,price');
			$arrImages 		= (array) $Product->getImages();
			$strProductImg 	= @$arrImages[0]->url;

			if (@!getimagesize($strProductImg)) {
				$strProductImg 	= DEFAULT_PRODUCT_IMAGE;
			}
		?>
		<tr>
			<td><img class="img-responsive pull-left" alt="<?php echo $Product->getTitle(); ?>" src="<?php echo $strProductImg; ?>" style="width:72px;" /></td>
			<td>
				<p><strong><?php echo $Product->getTitle(); ?></strong></p>
				<p><?php echo calculateProductPrice($Product->getPrice(), $objProduct->id); ?></p>
				<p><a href="/product/<?php echo $Product->getId(); ?>/" class="btn btn-primary btn-xs" title="Voeg toe aan winkelmandje"><span class="glyphicon glyphicon-plus"></span> In winkelwagen</a></p>
			</td>
		</tr>
		<?php
		}

		?>
				
		</table>
	</div><!-- /col -->
</div><!-- /row -->

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
		url:		'/includes/json/calculateDiscount.php',
		data:		{ code: discountCode, timestamp: '<?=$_SERVER["REQUEST_TIME"]?>' },
		success: function(data, textStatus) {
		// Handle success

			$('.discount_error').html('');
			
			if(data.validationRequired == 1) {
				
				$('.discount_input').hide();
				$('.discount_code').html('<div>Kortingscode: '+ discountCode +'</div>');				
				$('.discount_message').html(
					'<div class="italic">Voor deze code is een controlecode vereist.<br/>Vul uw controlecode in die u heeft ontvangen.</div>' +
					'<input type="text" name="validationCode" id="validationCodeValue" placeholder="Uw controlecode.." class="discountValue" value="<?=$_SESSION["validationCode"]?>" />' +
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
		url:		'/includes/json/calculateDiscount.php',
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
	var intQuantity	= parseInt($(this).val());
	var intCartId	 	= $(this).data('cartid');
	var intProductId 	= $(this).data('productid');
	var stockQty	 	= parseInt(window['stock_'+intProductId]);

	if (stockQty >= intQuantity) {
		$('.stock_message').html('Op voorraad.');
	}
	else if (stockQty < intQuantity) {
		$('.stock_message').html('Niet voldoende op voorraad.');
	}
	else {
		$('.stock_message').html('Niet op voorraad (circa 3 werkdagen levertijd).');
	}

	$.get(
		'/includes/json/updateCartQuantity.php',
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
			
			if ($('#discountCodeValue').val() != "") {
				$('#discountCode').click();
				$('#discountCodeSend').click();
			}
			
		},
		'json'
	);
});

$('.deleteItem').click(function() {
	
	var curThis		= $(this);
	var intCartId	= $(this).data('cartid');
	
	$.get(
		'/includes/json/deleteCartItem.php',
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
				$('.shippingCosts').html(data.cartShippingcosts);
				$('.total').html(data.cartTotal);
				
				$('.cartItems').html(data.cartItems);
				$('.cartSubtotal').html(data.cartSubTotal);
				
				$('#discountCode').click();
				$('#discountCodeSend').click();

			}
			
		},
		'json'
	);
	
});
</script>
<?php
require('includes/php/dc_footer.php');
?>