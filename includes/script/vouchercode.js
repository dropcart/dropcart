<script>
$('#discountCode').click(function(){
	
	$(this).hide();
	$('.discount_container').show();
	
});

$('#discountCodeSend').click(function(){

	var discountCode = $('#discountCodeValue').val();
	var verificationCodeContent = "<?php echo $text['VERIFICATION_CODE_CONTENT']; ?>";
    var clientInputContent = "<?php echo $text['YOUR_VERIFICATION_CODE']; ?>";
    var sendValue = "<?php echo $text['SEND']; ?>";

	$.ajax({
		type:		'POST',
		dataType:	'json',
		url:		'<?= SITE_URL ?>/includes/json/calculateDiscount.php',
		data:		{ code: discountCode, timestamp: '<?=$_SERVER["REQUEST_TIME"]?>' },
		success: function(data, textStatus) {
		// Handle success

			$('.discount_error').html('');
			
			if(data.validationRequired == 1) {
				
				$('.discount_input').hide();
				$('.discount_code').html('<div>Kortingscode: '+ discountCode +'</div>');				
				$('.discount_message').html(
                    '<div class="italic">'+ verificationCodeContent + '</div>' +
                    '<input type="text" name="validationCode" id="validationCodeValue" placeholder="'+ clientInputContent + '" class="discountValue" value="<?=$validationCode?>" />' +
                    '<a class="btn btn-primary btn-xs" id="validationCodeSend">'+ sendValue +'</a>'
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
		url:		'<?= SITE_URL ?>/includes/json/calculateDiscount.php',
		data:		{ code: discountCode, validationCode: validationCode, timestamp: '<?=$_SERVER["REQUEST_TIME"]?>' },
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
		'<?= SITE_URL ?>/includes/json/updateCartQuantity.php',
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
		'<?= SITE_URL ?>/includes/json/deleteCartItem.php',
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