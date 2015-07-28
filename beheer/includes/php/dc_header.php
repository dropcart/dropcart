<?php

require_once __DIR__ . '/../../../beheer/includes/php/dc_functions.php';
require_once __DIR__ . '/../../../beheer/includes/php/dc_session.php';
require_once __DIR__ . '/../../../libraries/Api_Inktweb/API.class.php';
require_once __DIR__ . '/../../../libraries/Api_Dropcart/API.class.php';

// Start Dropcart API
$strBuild = (formOption('UPDATE_BUILD') != '') ? formOption('UPDATE_BUILD') : 'stable'; // if build is not set, set to stable
$Api_Dropcart = new Dropcart\API(API_KEY, API_DEBUG);
$Versions = $Api_Dropcart->getVersions('?upgradeFrom=' . DROPCART_VERSION . '&build=' . $strBuild);

// get num of open orders
$result_numOrders = $objDB->sqlExecute("SELECT COUNT(orderId) FROM " . DB_PREFIX . "customers_orders WHERE status = 0");
list($numOrders) = $objDB->getRow($result_numOrders);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
<link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
<link href="<?php echo SITE_URL?>/beheer/includes/style/jquery.pagedown-bootstrap.css" rel="stylesheet">
<link href="<?php echo SITE_URL?>/beheer/includes/style/custom.css" rel="stylesheet">

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
<div class="row" id="top-bar">
<div class="col-xs-12 hidden-md hidden-lg" >



        <a href="#" data-toggle="offcanvas" class="block-trigger">
            <i class="fa fa-bars"></i> Swipe of klik hier om het menu te open
        </a>

    <!-- /col -->
</div><!-- /col -->
</div>

<div class="row offcanvas offcanvas-left">
    <div class="col-xs-6 col-sm-6 col-md-2 main-sidebar">
        <div class="sidebar-wrapper">
        <ul class="nav">
            <li class="static text-center faded">
                <p>
                    Dropcart <strong><?php echo DROPCART_VERSION;?></strong>

                    <?php
// compare versions
if (isset($Versions->version_stable->number)) {
    echo '<a href="dc_update.php" class="label label-info">' . $Versions->version_stable->number . ' beschikbaar</a>';
}
?>
                </p>
            </li>
            <li class="static title">Menu</li>
            <li class="static">
                <p>
                    Welkom <strong>

                        <?php echo (isset($_SESSION['sessionAdminUsername']))
? $_SESSION['sessionAdminUsername']
: '[ Gebruikersnaam niet gevonden ]'?>
                    </strong>
                </p>

            </li>
            <li <?php if (curPage() == "dc_index.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_index.php"> <span class="glyphicon glyphicon-home"></span> Home</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_order_admin.php" OR curPage() == "dc_order_manage.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_order_admin.php"> <span class="glyphicon glyphicon-euro"></span> Bestellingen <?php if ($numOrders > 0) {
    echo '<span class="label label-success">' . $numOrders . '</span>';
}
?></a><div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_customer_admin.php" OR curPage() == "dc_customer_manage.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_customer_admin.php"> <span class="glyphicon glyphicon-user"></span> Klanten</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_product_admin.php" OR curPage() == "dc_product_manage.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_product_admin.php"> <span class="glyphicon glyphicon-barcode"></span> Producten</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_codes_admin.php" OR curPage() == "dc_codes_list.php" OR curPage() == "dc_codes_manage.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_codes_admin.php"> <span class="glyphicon glyphicon-credit-card"></span> Vouchercodes</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_page_admin.php" OR curPage() == "dc_page_manage.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_page_admin.php"> <span class="glyphicon glyphicon-file"></span> Pagina's</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_email_admin.php" OR curPage() == "dc_email_manage.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_email_admin.php"> <span class="glyphicon glyphicon-envelope"></span> Emails</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_content_admin.php" OR curPage() == "dc_content_manage.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_content_admin.php"> <span class="glyphicon glyphicon-tags"></span> Content</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_user_admin.php" OR curPage() == "dc_user_manage.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_user_admin.php"> <span class="glyphicon glyphicon-user"></span> Gebruikers</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li <?php if (curPage() == "dc_setting_admin.php") {echo 'class="active" ';}
?>><a href="<?php echo SITE_URL?>/beheer/dc_setting_admin.php"> <span class="glyphicon glyphicon-wrench"></span> Instellingen</a> <div class="arrow"><div class="bubble-arrow-border"></div><div class="bubble-arrow"></div></div></li>
            <li><a href="<?php echo SITE_URL?>/beheer/dc_logout.php"><i class="fa fa-sign-out"></i> Uitloggen</a></a> </li>
        </ul>

        <ul class="nav dropcart">
            <li class="static title">Dropcart</li>
            <li><a href="<?php echo SITE_URL?>/beheer/dc_faq.php">
                    <i class="fa fa-question"></i> Veelgestelde vragen
                </a></li>
            <li><a href="<?php echo SITE_URL?>/beheer/dc_contact.php">
                    <i class="fa fa-envelope"></i> Contact</a>
            </li>
        </ul>
        </div>
    </div>

<div class="col-xs-12 col-sm-12 col-md-10 main-content">
    <!-- Used with side nav to dark the main content -->
    <div class="dark-overlay hidden"></div>