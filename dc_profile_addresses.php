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
require_once('libaries/Api_Inktweb/API.class.php');

if (empty($_SESSION['customerId'])) {
	// not logged in, redirect
	header('Location: /dc_login.php');
}

$_POST 		= sanitize($_POST);
$_GET 		= sanitize($_GET);

if ($_POST) {

	$intAddress 		= $_POST['addressId'];
	$intCustId 		= $_POST['custId'];
	$intDefaultInv	= $_POST['defaultInv'];
	$intDefaultDel 	= $_POST['defaultDel'];
	$strAddressName 	= $_POST['addressName'];
	$strCompany 	= $_POST['company'];
	$strFirstname 	= $_POST['firstname'];
	$strLastname 	= $_POST['lastname'];
	$strAddress 		= $_POST['address'];
	$strHouseNr		= $_POST['houseNr'];
	$strHouseNrAdd	= $_POST['houseNrAdd'];
	$strZipcode 		= $_POST['zipcode'];
	$strCity 		= $_POST['city'];
	$strLang		= $_POST['lang'];

	if (!empty($intAddress)) {

		// reset all defaults (prevents duplicates)
		if (!empty($intDefaultInv)) {
			$strSQL = "UPDATE ".DB_PREFIX."customers_addresses SET defaultInv = 0 WHERE custId = '".$intCustId."' ";
			$result = $objDB->sqlExecute($strSQL);	
		}
		if (!empty($intDefaultDel)) {
			$strSQL = "UPDATE ".DB_PREFIX."customers_addresses SET defaultDel = 0 WHERE custId = '".$intCustId."' ";
			$result  = $objDB->sqlExecute($strSQL);	
		}

		// Update information
		$strSQL 	= 
				"UPDATE ".DB_PREFIX."customers_addresses SET
				defaultInv = '".$intDefaultInv."',
				defaultDel = '".$intDefaultDel."',
				addressName = '".$strAddressName."',
				company = '".$strCompany."',
				firstname = '".$strFirstname."',
				lastname = '".$strLastname."',
				address = '".$strAddress."',
				houseNr = '".$strHouseNr."',
				houseNrAdd = '".$strHouseNrAdd."',
				zipcode = '".$strZipcode."',
				city = '".$strCity."',
				lang = '".$strLang."'
				WHERE id = '".$intAddress."' ";
		$result 	= $objDB->sqlExecute($strSQL);		
	}

	if ($result === true) {
		header('Location: ?id='.$intAddress.'&succes='.urlencode('Adres is aangepast.'));
	}
	else {
		header('Location: ?id='.$intAddress.'&fail='.urlencode('Er is iets fout gegaan.'));
	}

}

$strSQL 		= 
			"SELECT 
			ca.defaultInv,
			ca.defaultDel,
			ca.id,
			ca.custId,
			ca.addressName,
			ca.company,
			ca.firstname,
			ca.lastname,
			ca.address,
			ca.houseNr ,
			ca.houseNrAdd,
			ca.zipcode,
			ca.city,
			ca.lang
			FROM ".DB_PREFIX."customers_addresses ca
			WHERE 1
			AND ca.custId = '".$_SESSION['customerId']."' 
			";
$result 		= $objDB->sqlExecute($strSQL);

// Start displaying HTML
require_once('includes/php/dc_header.php');
?>

<div class="row" style="margin-top:40px">
	<div class="col-xs-12 col-sm-10 col-md-10 col-sm-offset-1 col-md-offset-1">

	<?php
	if (!empty($_GET['succes'])) {
		echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
	}

	if (!empty($_GET['fail'])) {
		echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Fout!</strong> '.$_GET['fail'].'</div>';
	}
	?>

		<form class="form-horizontal" role="form" method="GET">
			<div class="form-group">

				<h3>Mijn adressen</h3>
				<p>Selecteer een adres om deze aan te bewerken.</p>

				<label for="addresses" class="col-sm-2 control-label">Adressen</label>
				<div class="col-sm-10">
					<select class="form-control" name="id" onchange="this.form.submit()">
						<option value="0">-- selecteer --</option>
						<?php
						while ($objAd = $objDB->getObject($result)) {
							$line = "";
							$selected = "";

							if ($_GET['id'] == $objAd->id) { $selected = "selected"; }
							echo '<option value="'.$objAd->id.'" '.$selected.' >';

							if (!empty($objAd->addressName)) { $line .= '('.$objAd->addressName.')'; }
							$line .= $objAd->company . " ";
							$line .= $objAd->firstname . " ";
							$line .= $objAd->lastname . " ";
							$line .= $objAd->address . " ";
							$line .= $objAd->houseNr . "";
							$line .= $objAd->houseNrAdd . "";
							$line .= ", ";
							$line .= $objAd->zipcode . " ";
							$line .= $objAd->city . " ";

							// Remove excess white space
							$line = preg_replace( '/\s+/', ' ', $line );
							echo $line;

							echo '</option>';
						}
						?>
					</select>
				</div><!-- /col -->
			</div><!-- /form-group -->
		</form>


		<hr />

		<?php

		if (!empty($_GET['id'])) {

			$result 	= $objDB->sqlExecute($strSQL . "AND id = '".$_GET['id']."'");
			$objAd 	= $objDB->getObject($result);
			
			?>

			<h3>Adres bewerken</h3>
			<hr class="colorgraph">

			<form class="form-horizontal" role="form" method="POST">

			<input type="hidden" name="addressId" value="<?php echo $objAd->id; ?>"; />
			<input type="hidden" name="custId" value="<?php echo $objAd->custId; ?>"; />

			<div class="form-group">
				<label for="addressName" class="col-sm-2 control-label">Omschrijving</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="addressName" name="addressName" value="<?php echo $objAd->addressName; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="company" class="col-sm-2 control-label">Bedrijfsnaam</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="company" name="company" value="<?php echo $objAd->company; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="firstname" class="col-sm-2 control-label">Voornaam</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $objAd->firstname; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="lastname" class="col-sm-2 control-label">Achternaam</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $objAd->lastname; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="address" class="col-sm-2 control-label">Straatnaam</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="address" name="address" value="<?php echo $objAd->address; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="houseNr" class="col-sm-2 control-label">Huisnummer</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="houseNr" name="houseNr" value="<?php echo $objAd->houseNr; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="houseNrAdd" class="col-sm-2 control-label">Huisnummer toevoeging</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="houseNrAdd" name="houseNrAdd" value="<?php echo $objAd->houseNrAdd; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="zipcode" class="col-sm-2 control-label">Postcode</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="zipcode" name="zipcode" value="<?php echo $objAd->zipcode; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="city" class="col-sm-2 control-label">Stad</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="city" name="city" value="<?php echo $objAd->city; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="lang" class="col-sm-2 control-label">Land</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="lang" name="lang" value="<?php echo $objAd->lang; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<div class="checkbox">
					<label>
						<input type="checkbox" name="defaultInv" value="1" <?php if ($objAd->defaultInv == 1) { echo 'checked'; } ?>> Dit adres is mijn standaard factuuradres
					</label>
					</div><!-- /checkbox -->
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<div class="checkbox">
					<label>
						<input type="checkbox" name="defaultDel" value="1" <?php if ($objAd->defaultDel == 1) { echo 'checked'; } ?>> Dit adres is mijn standaard afleveradres
					</label>
					</div><!-- /checkbox -->
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<input type="submit" class="btn btn-default" value="Aanpassen" />
				</div><!-- /col -->
			</div><!-- /form-group -->
		</form>

		<?php

		}

		?>

	</div><!-- /col -->
</div><!-- /row -->



<?php
require('includes/php/dc_footer.php');
?>