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
require_once('includes/php/dc_mail.php'); // Password reset email

// Start API
require_once('libaries/Api_Inktweb/API.class.php');

if (!empty($_SESSION['customerId'])) {
	header('Location: /dc_profile.php');
}


$_POST 		= sanitize($_POST);
$_GET 		= sanitize($_GET);

$strEmail 		= strtolower($_POST['email_forgot']);
if (empty($strEmail)) { $strEmail = $_GET['email']; }
$strTokenGet 	= $_GET['token'];

// If token is set; user is trying to login and reset password
if (!empty($strTokenGet) AND !empty($strEmail)) {

	// check if token is valid
	$strSQL 		= "SELECT entryDate, id FROM ".DB_PREFIX."customers WHERE LOWER(email) = '".$strEmail."' ";
	$result 		= $objDB->sqlExecute($strSQL);
	$objCustomer		= $objDB->getObject($result);

	// random token based on api_key (unique, not known to public) and customers entryDate (unique to customer, doesn't change)
	$strToken 		= sha1(formOption('api_key').$objCustomer->entryDate);

	// check if token entered and token generated are the same
	if ($strToken == $strTokenGet) {

		// login user
		$_SESSION['customerId'] = $objCustomer->id;
		header('Location: /dc_profile_edit.php');
		
	}
	else {
		header('Location: ?fail='.urlencode('De link is niet geldig.'));
	}

	// user should have been redirected by now
	// make sure the page doesn't run anything else just in case
	exit();

}

// Else user is trying to generate a reset email
if (!empty($strEmail)) {


	$strSQL 		= "SELECT entryDate, email, firstname, lastname FROM ".DB_PREFIX."customers WHERE LOWER(email) = '".$strEmail."' ";
	$result 		= $objDB->sqlExecute($strSQL);
	$objCustomer		= $objDB->getObject($result);

	if (!empty($objCustomer->email)) {

		// random token based on api_key (unique, not known to public) and customers entryDate (unique to customer, doesn't change)
		$strToken 	= sha1(formOption('api_key').$objCustomer->entryDate);
		$strLoginLink 	= formOption('site_url')."dc_password_reset.php?token=".$strToken."&amp;email=".$objCustomer->email; 

		// send email to user with password reset link
		sendMail('password_reset', $objCustomer->email, $objCustomer->firstname . ' ' . $objCustomer->lastname, array(
			'LOGIN_LINK' => $strLoginLink,
		));

		header('Location: ?success='.urlencode('Een email met een link om het wachtwoord te resetten is onderweg.'));
	}
	else {
		// email not found
		header('Location: ?fail='.urlencode('Onder dit emailadres is geen account aanwezig.'));
	}

}

// Start displaying HTML
require_once('includes/php/dc_header.php');
?>

<div class="row" style="margin-top:40px">
<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">

	<?php

	if (!empty($_GET['success'])) {
		echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
	}

	if (!empty($_GET['fail'])) {
		echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Fout!</strong> '.$_GET['fail'].'</div>';
	}
	?>

	<form role="form" method="post">
		<fieldset>
		<h2>Wachtwoord vergeten?</h2>

		<p>Vul hieronder uw emailadres in en wij zullen u een link toesturen om uw wachtwoord te resetten.</p>

		<hr class="colorgraph">

		<div class="form-group">
			<input type="email" name="email_forgot" id="email_forgot" class="form-control input-lg" placeholder="Email">
		</div><!-- /form-group -->

		<div class="row">
			<div class="col-xs-6 col-sm-6 col-md-6">
				<input type="submit" class="btn btn-lg btn-success btn-block" value="Wachtwoord resetten">
			</div><!-- /col -->
		</div><!-- /row -->

		<span class="button-checkbox">
			<a href="/dc_login.php" class="btn btn-link pull-right">Wacht, ik weet het weer!</a>
		</span><!-- /button-checkbox -->

		</fieldset>
	</form>
</div><!-- /col -->
</div><!-- /row -->

<?php
require('includes/php/dc_footer.php');
?>