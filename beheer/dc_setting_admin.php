<?php
// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_config.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_functions.php');

// For mollie paymethods
require_once ($_SERVER['DOCUMENT_ROOT'].'/libraries/Mollie/API/Autoloader.php');

$objDB 		= new DB();

// Update database
if (isset($_POST)) {

	$_POST 	= sanitize($_POST);

	if(is_array($_POST['price_values']) && is_array($_POST['price_operators'])) {
		foreach($_POST['price_values'] as $key=>$value) {
			$_POST['price_values'][$key] = str_replace(",", ".", $value);
		}
		$_POST['price_values'] 	= json_encode($_POST['price_values']);
		$_POST['price_operators'] 	= json_encode($_POST['price_operators']);
	}

	foreach ($_POST as $key => $value) {

		$value = trim($value);


		if (empty($value)) {
			$value = 'NULL';
		}
		else {
			$value = "'$value'";
		}

		$strSQL = "INSERT INTO ".DB_PREFIX."options (optionName, optionValue) VALUES ('".$key."', ".$value.") ON DUPLICATE KEY UPDATE optionName = '".$key."', optionValue = ".$value."";
		$objDB->sqlExecute($strSQL);

	}

}

require('includes/php/dc_header.php');
?>

<h1>Instellingen </h1>

<hr />

<?php

if (!empty($_GET['succes'])) {
	echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
}

?>

<div class="col-md-12">

	<div class="panel panel-default">
		<div class="panel-heading">Website instellingen</div><!-- /panel-heading -->
		<div class="panel-body">

		<form class="form-horizontal" role="form" method="POST">
			<div class="form-group">
			<label for="site_name" class="col-sm-2 control-label">site_name</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo formOption('site_name'); ?>" autocomplete="off">
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div class="form-group">
			<label for="site_url" class="col-sm-2 control-label">site_url</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="site_url" name="site_url" value="<?php echo formOption('site_url'); ?>" autocomplete="off">
					<p class="help-block">URL van deze website. Moet eindigen op een slash / (b.v. <code>http://www.webshop.nl/</code>)</p>
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div class="form-group">
			<label for="site_email" class="col-sm-2 control-label">site_email</label>
				<div class="col-sm-8">
					<input type="email" class="form-control" id="site_email" name="site_email" value="<?php echo formOption('site_email'); ?>" autocomplete="off">
					<p class="help-block">Algemene email adres voor de website (hieruit worden orderbevestigingen e.d. verstuurd)</p>
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div class="form-group">
			<label for="site_shipping" class="col-sm-2 control-label">site_shipping</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="site_shipping" name="site_shipping" value="<?php echo formOption('site_shipping'); ?>" autocomplete="off">
					<p class="help-block">Verzendkosten inclusief BTW</p>
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div class="form-group">
			<label for="site_shipping_free_from" class="col-sm-2 control-label">site_shipping_free_from</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="site_shipping_free_from" name="site_shipping_free_from" value="<?php echo formOption('site_shipping_free_from'); ?>" autocomplete="off">
					<p class="help-block">Vanaf welk bedrag (inclusief BTW) moet de verzendkosten komen te vervallen? Zet op <code>0</code> om hier geen gebruik van te maken.</p>
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div class="form-group">
			<label for="meta_robots" class="col-sm-2 control-label">meta_robots</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="meta_robots" name="meta_robots" value="<?php echo formOption('meta_robots'); ?>" autocomplete="off">
					<p class="help-block">Wordt op elke pagina geplaatst. Meer informatie: <a href="http://www.metatags.nl/meta_name_robots">Metatags.nl</a></p>
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div class="form-group">
			<label for="default_product_image" class="col-sm-2 control-label">default_product_image</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="default_product_image" name="default_product_image" value="<?php echo formOption('default_product_image'); ?>" autocomplete="off">
					<p class="help-block">URL van standaard productafbeelding (circa 140x195 pixels)</p>
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div class="form-group">
			<label for="maximum_page_products" class="col-sm-2 control-label">maximum_page_products</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="maximum_page_products" name="maximum_page_products" value="<?php echo formOption('maximum_page_products'); ?>" autocomplete="off">
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div class="form-group">
			<label for="order_number_prefix" class="col-sm-2 control-label">order_number_prefix</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="order_number_prefix" name="order_number_prefix" value="<?php echo formOption('order_number_prefix'); ?>" autocomplete="off">
				</div><!-- /col -->
			</div><!-- /form-group -->
			<hr />

			<div class="form-group">
			<label for="mail_server" class="col-sm-2 control-label">mail_server</label>
				<div class="col-sm-8">
					<select class="form-control" id="mail_server" name="mail_server" onChange="
						if(this.selectedIndex == 1) {
							document.getElementById('smtp-settings').style.display = 'block';
						} else {
							document.getElementById('smtp-settings').style.display = 'none';
						}
					">
						<option value="mail" <?php if(formOption('mail_server') == 'mail') echo 'selected="selected"'; ?>>Mail van server gebruiken</option>
						<option value="smtp" <?php if(formOption('mail_server') == 'smtp') echo 'selected="selected"'; ?>>Eigen smtp server opgeven</option>
					</select>
					<p class="help-block">Hier kunt u een eventueel eigen SMTP server opgeven</p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div id="smtp-settings" style=" <?php if(formOption('mail_server') != 'smtp') echo 'display:none'; ?>">

				<div class="form-group">
				<label for="site_email" class="col-sm-2 control-label">smtp_server</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="smtp_server" name="smtp_server" value="<?php echo formOption('smtp_server'); ?>" autocomplete="off">
						<p class="help-block">Geef de SMTP server op</p>
					</div><!-- /col -->
				</div><!-- /form-group -->

				<div class="form-group">
				<label for="smtp_port" class="col-sm-2 control-label">smtp_port</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo formOption('smtp_port'); ?>" autocomplete="off">
						<p class="help-block">Geef de poort van de SMTP server op</p>
					</div><!-- /col -->
				</div><!-- /form-group -->

				<div class="form-group">
				<label for="smtp_secure" class="col-sm-2 control-label">smtp_secure</label>
					<div class="col-sm-8">
						<select class="form-control" id="smtp_secure" name="smtp_secure">
							<option value="">Onbeveiligd</option>
							<option value="ssl" <?php if(formOption('smtp_secure') == 'ssl') echo 'selected="selected"'; ?>>SSL</option>
							<option value="tls" <?php if(formOption('smtp_secure') == 'tls') echo 'selected="selected"'; ?>>TLS</option>
						</select>
						<p class="help-block">Maak een keuze uit een beveiligde of onbeveiligde verbinding</p>
					</div><!-- /col -->
				</div><!-- /form-group -->

				<div class="form-group">
				<label for="smtp_auth" class="col-sm-2 control-label">smtp_auth</label>
					<div class="col-sm-8">
						<select class="form-control" id="smtp_auth" name="smtp_auth" onChange="
						if(this.selectedIndex == 1) {
							document.getElementById('smtp-auth').style.display = 'block';
						} else {
							document.getElementById('smtp-auth').style.display = 'none';
						}
					">
							<option value="false" <?php if(formOption('smtp_auth') == 'false') echo 'selected="selected"'; ?>>nee</option>
							<option value="true" <?php if(formOption('smtp_auth') == 'true') echo 'selected="selected"'; ?>>ja</option>
						</select>
						<p class="help-block">Inloggen met gebruikersnaam en wachtwoord voor de SMTP server</p>
					</div><!-- /col -->
				</div><!-- /form-group -->

				<div id="smtp-auth" style=" <?php if(formOption('smtp_auth') != 'true') echo 'display:none'; ?>">

					<div class="form-group">
					<label for="smtp_username" class="col-sm-2 control-label">smtp_username</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo formOption('smtp_username'); ?>" autocomplete="off">
							<p class="help-block">Geef de gebruikersnaam van de SMTP server op</p>
						</div><!-- /col -->
					</div><!-- /form-group -->

					<div class="form-group">
					<label for="smtp_password" class="col-sm-2 control-label">smtp_password</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="smtp_password" name="smtp_password" value="<?php echo formOption('smtp_password'); ?>" autocomplete="off">
							<p class="help-block">Geef het wachtwoord van de SMTP server op</p>
						</div><!-- /col -->
					</div><!-- /form-group -->

				</div>

			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-default">Bewerken</button>
				</div><!-- /col -->
			</div><!-- /form-group -->

		</form><!-- /form -->

		</div><!-- /panel-body -->
	</div><!-- /panel -->


	<div class="panel panel-default">
		<div class="panel-heading">Prijs instellingen</div><!-- /panel-heading -->
		<div class="panel-body">

		<form class="form-horizontal" role="form" method="POST">
			<div class="form-group">
			<label for="price_base" class="col-sm-2 control-label">price_base</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="price_base" name="price_base" value="<?php echo formOption('price_base'); ?>" autocomplete="off">
					<p class="help-block">Geldige values: <code>price</code> = Inktweb.nl prijs, inclusief BTW, <code>purchase</code> = Inkoopprijs exclusief BTW, <code>msrp</code> = Adviesprijs exclusief BTW.</p>
				</div><!-- /col -->
			</div><!-- /form-group -->
			<div id="formula">

			<div class="form-group">
				<label for="dcFormula" class="col-sm-2 control-label">Prijs formule</label>
					<div class="col-sm-8">
						<p id="dcPreviewContainer"></p>
						<p><a id="dcFormulaToggle">Wijzigen?</a></p>
					</div>
					<div class="col-sm-8 dcFormulaEdit" style="display:none;">
						<div id="dcFormula"></div>
						<p class="help-block">Selecteer een <code>operator</code> en <code>waarde</code>. Bijvoorbeeld <code>&times;</code> en <code>1.21</code> om de <code>price_base</code> met 21% te verhogen.</p>
						
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10 dcFormulaEdit" style="display:none;">
					<div class="btn-group">
						<a href="#" class="btn btn-sm btn-danger" id="dcRemoveButton"><i class="glyphicon glyphicon-minus"></i></a>
						<a href="#" class="btn btn-sm btn-success" id="dcAddButton"><i class="glyphicon glyphicon-plus"></i></a>
					</div>				
				</div>
			</div>
			<hr>

			<div class="col-sm-offset-2 col-sm-10">
				<p class="help-block">Extra transactiekosten per betaalmethode.</p>
			</div>

			<?php
			try {
				$mollie = new Mollie_API_Client;
				$mollie->setApiKey(MOLLIE_API_KEY);
			?>
				<?php
					$methods = $mollie->methods->all();
					foreach ($methods as $method):
						$optionId = $method->id . '_fee';
				?>
				<div class="form-group">
				<label for="<?php echo $optionId; ?>" class="col-sm-2 control-label"><?php echo $method->description; ?> fee</label>
					<div class="col-sm-1">
						<div class="input-group">
	  					<span class="input-group-addon">%</span>
						<input type="text" class="form-control" id="<?php echo $optionId; ?>" name="<?php echo $optionId; ?>_percent" value="<?php echo formOption($optionId . '_percent'); ?>" autocomplete="off">
						</div>
					</div><!-- /col -->
					<div class="col-sm-1">
						<div class="input-group">
	  					<span class="input-group-addon">+</span>
						<input type="text" class="form-control" id="<?php echo $optionId; ?>" name="<?php echo $optionId; ?>_addition" value="<?php echo formOption($optionId . '_addition'); ?>" autocomplete="off">
						</div>
					</div><!-- /col -->
				</div><!-- /form-group -->
				<?php
				endforeach;
			}
			catch (Exception $e) {
				echo '<div class="col-sm-offset-2 col-sm-10"><p class="help-block"><strong>Foutmelding</strong>: ' .$e->getMessage().'</p></div>';
			}
			?>


			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-default">Bewerken</button>
				</div><!-- /col -->
			</div><!-- /form-group -->
		</form><!-- /form -->

		</div><!-- /panel-body -->
	</div><!-- /panel -->

	<div class="panel panel-default">
		<div class="panel-heading">Technische instellingen</div><!-- /panel-heading -->
		<div class="panel-body">

		<form class="form-horizontal" role="form" method="POST">

			<div class="form-group">
			<label for="api_key" class="col-sm-2 control-label">api_key</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="api_key" name="api_key" value="<?php echo formOption('api_key'); ?>" autocomplete="off">
					<p class="help-block">Wordt aangeleverd door Inktweb.nl</p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
			<label for="api_test" class="col-sm-2 control-label">api_test</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="api_test" name="api_test" value="<?php echo formOption('api_test'); ?>" autocomplete="off">
					<p class="help-block">Zet op <code>true</code> tijdens het testen. Geplaatste bestellingen e.d. komen dan binnen op de Inktweb.nl developmentserver en worden niet afgehandeld. <br />
					Werkt alles? Zet dit dan weer op <code>false</code>.</p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
			<label for="api_debug" class="col-sm-2 control-label">api_debug</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="api_debug" name="api_debug" value="<?php echo formOption('api_debug'); ?>" autocomplete="off">
					<p class="help-block">Geldige waardes <code>true</code> en <code>false</code>. Schakelt de debug modes in/uit.</p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
			<label for="api_restrict" class="col-sm-2 control-label">api_restrict</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="api_restrict" name="api_restrict" value="<?php echo formOption('api_restrict'); ?>" autocomplete="off">
					<p class="help-block">Geldige waardes <code>true</code> en <code>false</code>. Schakelt <em>custom</em> assortiment in/uit.</p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<hr />

			<div class="form-group">
			<label for="mollie_api_key" class="col-sm-2 control-label">mollie_api_key</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="mollie_api_key" name="mollie_api_key" value="<?php echo formOption('mollie_api_key'); ?>" autocomplete="off">
					<p class="help-block">Verkrijgbaar via <a href="https://www.dropcart.nl/">Dropcart.nl</a> of <a href="https://www.mollie.nl/">rechtstreeks bij Mollie.nl</a>. Webhook: <code><?php echo formOption('site_url'); ?>dc_shoppingcart3_process.php</code></p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<hr />

			<div class="form-group">
			<label for="zipcode_api_key" class="col-sm-2 control-label">zipcode_api_key</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="zipcode_api_key" name="zipcode_api_key" value="<?php echo formOption('zipcode_api_key'); ?>" autocomplete="off">
					<p class="help-block">Verkrijgbaar via <a href="https://api.postcode.nl/">Postcode.nl</a></p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
			<label for="zipcode_api_secret" class="col-sm-2 control-label">zipcode_api_secret</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="zipcode_api_secret" name="zipcode_api_secret" value="<?php echo formOption('zipcode_api_secret'); ?>" autocomplete="off">
					<p class="help-block">Verkrijgbaar via <a href="https://api.postcode.nl/">Postcode.nl</a></p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-default">Bewerken</button>
				</div><!-- /col -->
			</div><!-- /form-group -->
		</form><!-- /form -->

		</div><!-- /panel-body -->
	</div><!-- /panel -->

	<?php
	// GET ALL TABLES
	$result = $objDB->sqlExecute("SHOW TABLES LIKE '".DB_PREFIX."%' ");
	while($row = $objDB->getArray($result)) {
		$tables[] = $row[0];
	}
	?>

	<div class="panel panel-default">
		<div class="panel-heading">Database backup</div><!-- /panel-heading -->
		<div class="panel-body">
			<form class="form-horizontal" role="form" action="dc_backup.php" method="POST">
				<div class="form-group">
					<label for="tables_backup" class="col-sm-2 control-label">Maak backup</label>
					<div class="col-sm-8">
						<select name="tables[]" class="form-control" id="tables_backup" multiple="multiple" size="11">
						<?php foreach($tables as $table): ?>
							<option value="<?php echo $table; ?>" selected><?php echo $table; ?></option>
						<?php endforeach; ?>
						</select>
					</div><!-- /col -->
				</div><!-- /form-group -->
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" class="btn btn-default">Download backup</button>
					</div><!-- /col -->
				</div><!-- /form-group -->
			</form><!-- /form -->
		</div><!-- /panel-body -->
	</div><!-- /panel -->


	<div class="panel panel-default">
		<div class="panel-heading">Download bestellingsoverzicht</div><!-- /panel-heading -->
		<div class="panel-body">
			<form class="form-horizontal" role="form" action="dc_overview_excel.php" method="POST">
				<div class="form-group">
					<label for="overviewFromDate" class="col-sm-2 control-label">Overzicht downloaden</label>
					<div class="col-sm-8">
						<p class="help-block">Van</p>
						<input type="text" class="form-control" name="overviewFromDate" id="overviewFromDate" value="" placeholder="dd-mm-jjjj">
						<p class="help-block">Tot</p>
						<input type="text" class="form-control" name="overviewToDate" id="overviewToDate" value="" placeholder="dd-mm-jjjj">
					</div>
				</div><!-- /form-group -->
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" class="btn btn-default">Download overzicht</button>
					</div><!-- /col -->
				</div><!-- /form-group -->
			</form><!-- /form -->
		</div><!-- /panel-body -->
	</div><!-- /panel -->

</div><!-- /col -->

<!-- Price calculator -->
<script src="/includes/script/jquery.dcpriceformula.js"></script>
<script type="text/template" id="dcPriceFormulaTemplate">

<select  name="price_operators[]" class="operator form-control" style="width:15%; margin-right:5px; float:left;">
	<option value="+">+</option>
	<option value="-">-</option>
	<option value="*">&times;</option>
</select>

<input type="text" name="price_values[]" class="value form-control" style="width:25%; margin-bottom:5px;" />

</script>
<script>
    $(function() {
        $('#formula').dcPriceFormula({
            operators: <?php echo (null !== formOption('price_operators')) ? formOption('price_operators') : "[]"; ?>,
            values: <?php echo (null !== formOption('price_values')) ? formOption('price_values') : "[]"; ?>
        });
    });

    $('#dcFormulaToggle').click(function() {
    	$(".dcFormulaEdit").show();
    	$("#dcFormulaToggle").hide();
    	$("#dcPreviewContainer").hide();
    });

    $('#price_base').keydown(function() {
    	$(".dcFormulaEdit").show();
    	$("#dcFormulaToggle").hide();
    	$("#dcPreviewContainer").hide();
    });


</script>
<!-- /Price calculator -->


<?php require('includes/php/dc_footer.php'); ?>