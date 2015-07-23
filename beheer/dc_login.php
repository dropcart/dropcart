<?php
session_start();  
// Required includes
require_once (__DIR__.'/../includes/php/dc_connect.php');
require_once (__DIR__.'/../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../beheer/includes/php/dc_config.php');

// Page specific includes
require_once (__DIR__.'/../beheer/includes/php/dc_functions.php');
require_once (__DIR__.'/../_classes/class.password.php'); // Password compatibility library with PHP 5.5

/*
	If user is already logged in, and tries to go the login screen,
	redirect him to the dashboard.
*/
if (isset($_SESSION['sessionAdminId'])) {
	header('Location: '.SITE_URL.'/beheer/dc_index.php');
    exit();
}

$error = NULL;

if (
		isset($_POST['username']) &&
		isset($_POST['password']) &&
		$_POST['username'] != "" &&
		$_POST['password'] != ""
	)
{

	$_POST 		= sanitize($_POST);
	$strUsername 	= strtolower($_POST['username']);
	$strPassInput 	= $_POST['password'];
	
	$strSQL 		= "SELECT password, id, username FROM ".DB_PREFIX."admin_users WHERE LOWER(username) = '".$strUsername."' ";
	$result 		= $objDB->sqlExecute($strSQL);
	list($strPassHash, $userId, $username)	= $objDB->getRow($result);

	if (!empty($strPassHash)) {

		if (password_verify($strPassInput, $strPassHash)) {
			// start session and redirect user
			$_SESSION['sessionAdminId'] = $userId;
			$_SESSION['sessionAdminUsername'] = $username;

			if ($username == "admin") {
				header('Location: '.SITE_URL.'/beheer/dc_user_admin.php?firsttime=1');
			}
			else {
				header('Location: '.SITE_URL.'/beheer/dc_index.php');
			}
		}
		else {
			$error = 'Gebruikersnaam en/of wachtwoord niet correct';
		}
	}
	else {
		$error = 'Gebruikersnaam en/of wachtwoord niet correct';
	}
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Login</title>

<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet">
<style type="text/css">
body {
	padding-top: 40px;
	padding-bottom: 40px;
	background-color: #eee;
}

.form-signin {
	max-width: 330px;
	padding: 15px;
	margin: 0 auto;
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
	margin-bottom: 10px;
}
.form-signin .checkbox {
	font-weight: normal;
}
.form-signin .form-control {
	position: relative;
	font-size: 16px;
	height: auto;
	padding: 10px;
	-webkit-box-sizing: border-box;
		 -moz-box-sizing: border-box;
					box-sizing: border-box;
}
.form-signin .form-control:focus {
	z-index: 2;
}
.form-signin input[type="text"] {
	margin-bottom: -1px;
	border-bottom-left-radius: 0;
	border-bottom-right-radius: 0;
}
.form-signin input[type="password"] {
	margin-bottom: 10px;
	border-top-left-radius: 0;
	border-top-right-radius: 0;
}</style>
</head>

<body>

<div id="wrap">
	<div class="container">
		<?php if( !is_null($error)): ?>
		<div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
		<?php endif; ?>

		<form class="form-signin" role="form" method="POST">
		<h2 class="form-signin-heading">Inloggen vereist</h2>
		<input name="username" id="username" type="text" class="form-control" placeholder="Gebruikersnaam" autocomplete="off" required autofocus>
		<input name="password" id="password" type="password" class="form-control" placeholder="Wachtwoord" autocomplete="off" required>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
		</form><!-- /form -->
	</div><!-- /container -->
</div><!-- /wrap -->
