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
require_once ('_classes/class.password.php'); // Password compatibility library with PHP 5.5

// Start API
require_once('libaries/Api_Inktweb/API.class.php');

if (empty($_SESSION['customerId'])) {
	// not logged in, redirect
	header('Location: /dc_login.php');
}

$strSQL 			= "SELECT entryDate, gender, company, firstname, lastname, email, password FROM ".DB_PREFIX."customers WHERE id = '".$_SESSION['customerId']."' ";
$result 			= $objDB->sqlExecute($strSQL);
$objUser  			= $objDB->getObject($result);


$_POST 			= sanitize($_POST);

if ($_POST) {
	
	$intGender 		= $_POST['gender'];
	$strCompany 	= $_POST['company'];
	$strFirstname	= $_POST['firstname'];
	$strLastname	= $_POST['lastname'];
	$strEmail 		= $_POST['email'];
	$strPassword1 	= $_POST['password1'];
	$strPassword2 	= $_POST['password2'];
	$strPassword 	= $objUser->password; // gets overwritten if user wants to change

	if (!empty($strPassword1) AND !empty($strPassword2)) {

		if ($strPassword1 == $strPassword2) {

			 // Hash password with bcrypt and default cost
			$strPassword = password_hash($strPassword1, PASSWORD_BCRYPT);

		}
		else {
			header('Location: ?fail='.urlencode('Wachtwoorden komen niet overeen.'));
		}

	}

	$strSQL 		= "UPDATE ".DB_PREFIX."customers 
				SET gender = '".$intGender."',
				company = '".$strCompany."',
				firstname = '".$strFirstname."', 
				lastname = '".$strLastname."', 
				email = '".$strEmail."', 
				password = '".$strPassword."'
				WHERE id = '".$_SESSION['customerId']."' ";
	$result 		= $objDB->sqlExecute($strSQL);

	if ($result === true) {
		header('Location: ?succes='.urlencode('Gebruiker is aangepast.'));
	}
	else {
		header('Location: ?fail='.urlencode('Er is iets fout gegaan.'));
	}

}

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

		<h2>Mijn account</h2>
		<hr class="colorgraph">

		<form class="form-horizontal" role="form" method="POST">
			<div class="form-group">
				<label for="entryDate" class="col-sm-2 control-label">Klant sinds</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $objUser->entryDate; ?>" disabled>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="gender" class="col-sm-2 control-label">Geslacht</label>
				<div class="col-sm-10">
					<select class="form-control" name="gender">
						<option value="0" <?php if ($objUser->gender == 0) echo 'selected'; ?>>Onbekend</option>
						<option value="1" <?php if ($objUser->gender == 1) echo 'selected'; ?>>Mannelijk</option>
						<option value="2" <?php if ($objUser->gender == 2) echo 'selected'; ?>>Vrouwelijk</option>
					</select>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="company" class="col-sm-2 control-label">Bedrijfsnaam</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="company" name="company" placeholder="Bedrijfsnaam" value="<?php echo $objUser->company; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="firstname" class="col-sm-2 control-label">Voornaam</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $objUser->firstname; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="lastname" class="col-sm-2 control-label">Achternaam</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $objUser->lastname; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<label for="email" class="col-sm-2 control-label">Email</label>
				<div class="col-sm-10">
					<input type="email" class="form-control" id="email" name="email" value="<?php echo $objUser->email; ?>">
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<input type="submit" class="btn btn-default" value="Aanpassen" />
				</div><!-- /col -->
			</div><!-- /form-group -->

			<hr />

			<div class="form-group">
				<label for="password1" class="col-sm-2 control-label">Wachtwoord resetten</label>
				<div class="col-sm-10">
					<input type="password" class="form-control" id="password1" name="password1" placeholder="Wachtwoord.." autocomplete="off">
					<br>
					<input type="password" class="form-control" id="password2" name="password2" placeholder="Wachtwoord herhalen..." autocomplete="off">
					<p class="help-block">Laat deze velden leeg als u het wachtwoord niet wenst te resetten</p>
				</div><!-- /col -->
			</div><!-- /form-group -->

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<input type="submit" class="btn btn-default" value="Aanpassen" />
				</div><!-- /col -->
			</div><!-- /form-group -->
		</form>

	</div><!-- /col -->
</div><!-- /row -->



<?php
require('includes/php/dc_footer.php');
?>