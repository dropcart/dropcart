<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_functions.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_session.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/libaries/Api_Inktweb/API.class.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/libaries/Api_Dropcart/API.class.php');

// Start Dropcart API
$strBuild = (formOption('UPDATE_BUILD') != '') ? formOption('UPDATE_BUILD') : 'stable'; // if build is not set, set to stable
$Api_Dropcart = new Dropcart\API(API_KEY, API_DEBUG);
$Versions = $Api_Dropcart->getVersions('?upgradeFrom='.DROPCART_VERSION.'&build='.$strBuild);

// get num of open orders
$result_numOrders = $objDB->sqlExecute("SELECT COUNT(orderId) FROM ".DB_PREFIX."customers_orders WHERE status = 0");
list($numOrders) = $objDB->getRow($result_numOrders);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
<link href="/beheer/includes/style/jquery.pagedown-bootstrap.css" rel="stylesheet">
<link href="/beheer/includes/style/custom.css" rel="stylesheet">

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" ></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<script src="http://code.highcharts.com/highcharts.js"></script>
</head>
<body>

<div class="container-fluid">

<div class="col-xs-12" style="color:#999; margin:15px 0px; padding:0px; text-align:center;">
	<div class="col-xs-1 main-sidebar">
		<p>
		Dropcart <strong><?php echo DROPCART_VERSION; ?></strong>

		<?php
		// compare versions
		if ($Versions->version_stable->number) {
			echo '<a href="dc_update.php" class="label label-info">'.$Versions->version_stable->number.' beschikbaar</a>';
		}
		?>
		</p>
	</div><!-- /col -->
	<div class="col-xs-11">
		<p class="pull-right">Welkom <?php echo $_SESSION['sessionAdminUsername']; ?> (<a href="/beheer/dc_logout.php">Uitloggen</a>) | <a href="/beheer/dc_faq.php">Veelgestelde vragen</a> | <a href="/beheer/dc_contact.php">Contact</a></p>
	</div><!-- /col -->
</div><!-- /col -->

<div class="col-xs-1 main-sidebar">
	<ul class="nav">
		<li <?php if (curPage() == "dc_index.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_index.php"> <span class="glyphicon glyphicon-home"></span> Home</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
		<li <?php if (curPage() == "dc_order_admin.php" OR curPage() == "dc_order_manage.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_order_admin.php"> <span class="glyphicon glyphicon-euro"></span> Bestellingen <?php if ($numOrders > 0) echo '<span class="label label-success">'.$numOrders.'</span>'; ?></a><div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
		<li <?php if (curPage() == "dc_customer_admin.php" OR curPage() == "dc_customer_manage.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_customer_admin.php"> <span class="glyphicon glyphicon-user"></span> Klanten</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
		<li <?php if (curPage() == "dc_product_admin.php" OR curPage() == "dc_product_manage.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_product_admin.php"> <span class="glyphicon glyphicon-barcode"></span> Producten</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
		<li <?php if (curPage() == "dc_codes_admin.php" OR curPage() == "dc_codes_list.php" OR curPage() == "dc_codes_manage.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_codes_admin.php"> <span class="glyphicon glyphicon-credit-card"></span> Vouchercodes</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
		<li <?php if (curPage() == "dc_page_admin.php" OR curPage() == "dc_page_manage.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_page_admin.php"> <span class="glyphicon glyphicon-file"></span> Pagina's</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
		<li <?php if (curPage() == "dc_email_admin.php" OR curPage() == "dc_email_manage.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_email_admin.php"> <span class="glyphicon glyphicon-envelope"></span> Emails</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
		<li <?php if (curPage() == "dc_content_admin.php" OR curPage() == "dc_content_manage.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_content_admin.php"> <span class="glyphicon glyphicon-tags"></span> Content</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
		<li <?php if (curPage() == "dc_user_admin.php" OR curPage() == "dc_user_manage.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_user_admin.php"> <span class="glyphicon glyphicon-user"></span> Gebruikers</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>		
		<li <?php if (curPage() == "dc_setting_admin.php") { echo 'class="active" '; } ?>><a href="/beheer/dc_setting_admin.php"> <span class="glyphicon glyphicon-wrench"></span> Instellingen</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
	</ul>
</div><!-- /col -->

<div class="col-xs-11 main-content">
