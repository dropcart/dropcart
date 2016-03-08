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
		url:		'<?php echo  SITE_URL ?>/includes/json/calculateDiscount.php',
		data:		{ code: discountCode, timestamp: '<?php echo $_SERVER["REQUEST_TIME"]?>' },
		success: function(data, textStatus) {
		// Handle success

			$('.discount_error').html('');

			if(data.validationRequired == 1) {

				$('.discount_input').hide();
				$('.discount_code').html('<div>Kortingscode: '+ discountCode +'</div>');
				$('.discount_message').html(
					'<div class="italic">Voor deze code is een validatiecode vereist.<br/>Vul uw validatiecode in die u heeft ontvangen.</div>' +
					'<input type="text" name="validationCode" id="validationCodeValue" placeholder="Uw validatiecode.." class="discountValue" value="<?php echo $_SESSION["validationCode"]?>" />' +
					'<a class="btn btn-primary btn-xs" id="validationCodeSend">Versturen</a>'
				);

				<?php if($_SESSION["validationCode"] != "") { ?>
					$('#validationCodeSend').click();
				<?php } ?>

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
		url:		'<?php echo  SITE_URL ?>/includes/json/calculateDiscount.php',
		data:		{ code: discountCode, validationCode: validationCode, timestamp: '<?php echo $_SERVER["REQUEST_TIME"]?>' },
		success: function(data, textStatus) {
		// Handle success

			$('.discountAmount_container').show();
			$('.discount_error').html('');
			$('.discount_message').html('');
			$('.discountAmount_code').html(data.discountCode);
			$('.discountAmount').html(data.cartDiscountAmount);
			$('.subtotal').html(data.cartSubTotal);
			$('.shippingCosts').html(data.cartShippingCosts);
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
		'<?php echo  SITE_URL ?>/includes/json/updateCartQuantity.php',
		{
			cartId		: intCartId,
			quantity	: intQuantity,
			timestamp	: '<?php echo $_SERVER["REQUEST_TIME"]?>'
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
		'<?php echo  SITE_URL ?>/includes/json/deleteCartItem.php',
		{
			cartId		: intCartId,
			timestamp	: '<?php echo $_SERVER["REQUEST_TIME"]?>'
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