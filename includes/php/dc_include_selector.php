<?php
$arrPrinters = $Api->getPrinterBrands();

if (!empty($smallSelector)) {
	$size 	= "7";
	$h 	= "h3";
	$btn 	= "btn-xs";
}
else {
	$size 	= "13";
	$h 	= "h2";
	$btn 	= "btn-lg";
}

?>
<div class="row">
	<div class="col-md-4">
		<<?php echo $h;?>>Selecteer merk <small>Stap 1</small></<?php echo $h;?>>

		<select id="printerBrandSelect" name="printerBrand" <?php echo 'size="'.$size.'"'; ?> class="form-control stap1">
			<?
			foreach($arrPrinters->brand as $printer) {
				
				echo '<option value="' . $printer->id . '">' . $printer->title . '</option>' . "\n";
				
			}
			?>
		</select>
	</div><!-- /col -->

	<div class="col-md-4">
		<<?php echo $h;?>>Selecteer type <small>Stap 2</small></<?php echo $h;?>>
		<select id="printerSerieSelect" name="printerSerie" <?php echo 'size="'.$size.'"'; ?> class="form-control stap2" disabled>
		</select>
	</div><!-- /col -->

	<div class="col-md-4">
		<<?php echo $h;?>>Selecteer model <small>Stap 3</small></<?php echo $h;?>>
		<select id="printerTypeSelect" name="printerType" <?php echo 'size="'.$size.'"'; ?> class="form-control stap3" disabled>
		</select>
	</div><!-- /col -->


</div><!-- /row -->

<div class="row">
	<div class="col-md-12">
		<br />
		<a disabled href="#" class="btn <?php echo $btn; ?> btn-primary pull-right submit-btn">Toon mijn cartridges</a>
	</div><!-- /col-->
</div><!-- /row -->

<script>

// Step 1
$('#printerBrandSelect').change(function(){
	
	var printerBrandId = $(this).val();
	
    $.get(
        '<?php echo SITE_URL.'/includes/json/getPrinterSeries.php'?>',
        {
			printerBrandId	: printerBrandId,
			timestamp		: '<?=$_SERVER["REQUEST_TIME"]?>'
        },
        function(data) {
			
			$('#printerSerieSelect').find('option').remove().end();
			$('#printerTypeSelect').find('option').remove().end();
			
			var index;
			for (index = 0; index < data.length; ++index) {
				
				$('#printerSerieSelect')
					.append($("<option></option>")
					.attr("value",data[index]['id'])
					.text(data[index]['title'])); 
			}
			
			$('#printerSerieSelect').prop('disabled', false);

			// Auto select first element
			$("#printerSerieSelect").val($("#printerSerieSelect option:first").val());
			$('#printerSerieSelect').trigger("change");
			
        },
        'json'
    );
	
});

// Step 2
$('#printerSerieSelect').change(function(){
	
	var printerBrandId = $('#printerBrandSelect').val();
	var printerSerieId = $(this).val();
	
    $.get(
        '/includes/json/getPrinterTypes.php',
        {
			printerBrandId	: printerBrandId,
			printerSerieId	: printerSerieId,
			timestamp		: '<?=$_SERVER["REQUEST_TIME"]?>'
        },
        function(data) {
			
			$('#printerTypeSelect').find('option').remove().end();
			
			var index;
			for (index = 0; index < data.length; ++index) {
				
				$('#printerTypeSelect')
					.append($("<option></option>")
					.attr("value",data[index]['id'])
					.text(data[index]['title'])); 
			}
			
			$('#printerTypeSelect').prop('disabled', false);

			// Auto select first element
			$("#printerTypeSelect").val($("#printerTypeSelect option:first").val());
			$('.submit-btn').removeAttr('disabled');
        },
        'json'
    );
	
});

$('.submit-btn').click(function(){

	var printerTypeId = $('#printerTypeSelect').val();
	document.location.href = '/printer/' + printerTypeId + '/';
	
});

</script>