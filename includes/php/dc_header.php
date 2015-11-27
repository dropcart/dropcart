<?php
require_once 'dc_functions.php';

$intSessionId = session_id();
$intCustomerId = (isset($_SESSION["customerId"])) ? $_SESSION["customerId"] : 0;

// New Inktweb Api object
if (empty($Api)) {
    $Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);
}

// New Cartobject
$objNode = new Cart();
$objNode->setDatabaseObject($objDB);
$objNode->setSessionId($intSessionId);
$objNode->setCustomerId($intCustomerId);
$strCanonical = null;

//get cart info for cart node
$result_header_cart = $objNode->getCart();

$intNodeItems = 0;
$dblNodePriceTotal = 0;

$arrCartItems = array();

$i = 0;
while ($objNodeCart = $objDB->getObject($result_header_cart)) {

    $Product_cart = $Api->getProduct($objNodeCart->productId);

    $arrCartItems[$i]['cartId'] = $objNodeCart->id;

    $arrImages = (array) $Product_cart->getImages();
    $arrCartItems[$i]['strImageUrl'] = @$arrImages[0]->url;

    // check if valid image (ignore warnings)
    if (@!getimagesize($arrCartItems[$i]['strImageUrl'])) {
        $arrCartItems[$i]['strImageUrl'] = DEFAULT_PRODUCT_IMAGE;
    }

    $arrCartItems[$i]['strProductTitle'] = $Product_cart->getTitle();
    $arrCartItems[$i]['intQuantity'] = $objNodeCart->quantity;
    $arrCartItems[$i]['dblPrice'] = calculateProductPrice($Product_cart->getPrice(), $objNodeCart->productId, $arrCartItems[$i]['intQuantity'], false);
    $arrCartItems[$i]['strPrice'] = money_format('%(#1n', $arrCartItems[$i]['dblPrice']);
    $arrCartItems[$i]['strPriceTotal'] = money_format('%(#1n', ($arrCartItems[$i]['dblPrice'] * $arrCartItems[$i]['intQuantity']));
    $arrCartItems[$i]['intStock'] = $Product_cart->getStock();

    $intNodeItems += $objNodeCart->quantity;
    $dblNodePriceTotal += $arrCartItems[$i]['dblPrice'] * $objNodeCart->quantity;

    $i++;
}

if (!empty($_SESSION["discountCode"])) {

    $arrDiscount = calculateDiscount($_SESSION["discountCode"]);

    $dblDiscountAmount = $arrDiscount['dblDiscountAmount'];
    $strDiscountAmount = '€ ' . number_format($dblDiscountAmount, 2, ',', ' ');
    $dblNodePriceTotal = $arrDiscount['dblPriceTotal'];

}

$strNodePriceSubtotal = money_format('%(#1n', $dblNodePriceTotal);
$dblShippingCosts = calculateSiteShipping($dblNodePriceTotal, '', false);
$strShippingCosts = money_format('%(#1n', $dblShippingCosts);
$dblNodePriceTotal = $dblNodePriceTotal + $dblShippingCosts;
$strNodePriceTotal = money_format('%(#1n', $dblNodePriceTotal);

$strPageTitle = (isset($strPageTitle)) ? $strPageTitle : SITE_NAME;
if (!empty($canonical)) {
    $strCanonical = '<link rel="canonical" href="' . $canonical . '" />';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo (isset($strMetaDescription)) ? $strMetaDescription : null?>">
    <meta name="generator" content="DropCart <?php echo (defined('DROPCART_VERSION')) ? DROPCART_VERSION : null;?>">
    <meta name="robots" content="<?php echo formOption('meta_robots');?>">

    <title><?php echo (isset($strPageTitle)) ? $strPageTitle : null;
echo ' - ' . formOption('SITE_NAME')?></title>

    <?php if (isset($strCanonical)) {$strCanonical;}
?>

    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL?>/includes/css/custom.css" rel="stylesheet">
    <link href="<?php echo SITE_URL?>/includes/css/magnify.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" ></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script src="<?php echo SITE_URL?>/includes/script/bootstrap-magnify.min.js" ></script>
    <script>
        var baseUrl = '<?php echo SITE_URL?>';
    </script>
</head>
<body>

<div class="container">

<div class="row hidden-print">
<div class="col-xs-12">
    <noscript>
        <div class="alert alert-danger" role="alert">
            <strong><?php echo $text['JAVASCRIPT_ALERT_BOLD']; ?> </strong> <?php echo $text['JAVASCRIPT_ALERT_TEXT']; ?>
            <p>
                <a href="<?php echo SITE_URL?>/dc_javascript.php" class="btn btn-primary"><?php echo $text['JAVASCRIPT_ALERT_TEXT']; ?></a>
            </p>
        </div>
    </noscript>
    <nav class="navbar navbar-right header-nav" role="navigation">
        <ul class="nav navbar-nav">
            <li><a href="<?php echo SITE_URL?>/dc_login.php"><?php echo $text['ACCOUNT']; ?></a></li>
            <li><a href="<?php echo SITE_URL?>/dc_shoppingcart.php"><?php echo $text['SHOPPING_CART']; ?></a></li>
        </ul><!-- /navbar -->
    </nav><!-- /nav -->
</div><!-- /col -->
</div><!-- /row -->

<div class="row header-top hidden-print">
    <div class="col-lg-3 col-md-3 hidden-sm hidden-xs">
        <a href="<?php echo SITE_URL?>">
        <?php
        $image = SITE_URL . "/images/logo_small.png";
        $logo = formOption('SITE_LOGO');

        if (!empty($logo)) {
            $image = SITE_URL . '/images/logo/' . $logo;
        }
        ?>
            <img src="<?php echo $image?>" alt="<?php echo formOption('SITE_NAME')?>" class="img-responsive"></a>
    </div><!-- /col logo -->

    <div class="col-lg-5 col-md-6 col-sm-8 col-xs-12">
        <div class="row hidden-print">
            <div class="col-xs-12">
                <div class="input-group form-search header-search">
                    <form method="get" action="<?php echo SITE_URL?>/search/" class="navbar-form">
                        <input class="form-control search-query" type="text" name="q" placeholder="<?php echo $text['SEARCH_BAR']; ?>">
                        <span class="input-group-btn pull-left" >
                            <button class="btn btn-default" type="submit"><?php echo $text['SEARCH']; ?></button>
                        </span>
                    </form>
                </div><!-- /search -->
            </div><!-- /col -->
        </div><!-- /row -->
    </div><!-- /col -->

    <div class="col-lg-4 col-md-3 col-sm-4 col-xs-12">
        <div id="cart">
            <h3><span class="glyphicon glyphicon-shopping-cart"></span> <?php echo $text['SHOPPING_CART']; ?></h3>
            <a href="<?php echo SITE_URL?>/dc_shoppingcart.php"><span class="cartItems"><?=$intNodeItems?></span> <?php echo $text['ARTICLES']; ?> - <span class="cartSubtotal"><?=$strNodePriceSubtotal?></span> <span class="caret"></span></a>
        </div><!-- /cart -->

    </div><!-- /col -->
</div><!-- /row -->

<div class="row hidden-print">
<div class="col-xs-12">
    <nav class="navbar navbar-default main-nav" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div><!-- /navbar-header -->
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-left">
                <li><a href="<?php echo SITE_URL?>"><span class="glyphicon glyphicon-home"></span></a></li>
                <li class="dropdown">
                    <a href="<?php echo SITE_URL?>/categorie/1/" id="cat1" role="button" data-toggle="dropdown" data-target="#"><?php echo $text['CATEGORY_CARTRIDGES']; ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="cat1">
                        <li><a href="<?php echo SITE_URL?>/categorie/1/">-- <?php echo $text['CATEGORY_SHOW']; ?> -- </a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/1/?&amp;brands=,2">HP <?php echo $text['CATEGORY_CARTRIDGES']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/1/?&amp;brands=,3">Canon <?php echo $text['CATEGORY_CARTRIDGES']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/1/?&amp;brands=,4">Epson <?php echo $text['CATEGORY_CARTRIDGES']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/1/?&amp;brands=,5">Lexmark <?php echo $text['CATEGORY_CARTRIDGES']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/1/?&amp;brands=,6">Brother <?php echo $text['CATEGORY_CARTRIDGES']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/1/?&amp;brands=,29">Dell <?php echo $text['CATEGORY_CARTRIDGES']; ?></a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="<?php echo SITE_URL?>/categorie/2/" id="cat2" role="button" data-toggle="dropdown" data-target="#"><?php echo $text['CATEGORY_TONERS']; ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="cat2">
                        <li><a href="<?php echo SITE_URL?>/categorie/2/">-- <?php echo $text['CATEGORY_SHOW']; ?> -- </a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/2/?&amp;brands=,2">HP <?php echo $text['CATEGORY_TONERS']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/2/?&amp;brands=,3">Canon <?php echo $text['CATEGORY_TONERS']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/2/?&amp;brands=,4">Epson <?php echo $text['CATEGORY_TONERS']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/2/?&amp;brands=,5">Lexmark <?php echo $text['CATEGORY_TONERS']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/2/?&amp;brands=,6">Brother <?php echo $text['CATEGORY_TONERS']; ?></a></li>
                        <li><a href="<?php echo SITE_URL?>/categorie/2/?&amp;brands=,29">Dell <?php echo $text['CATEGORY_TONERS']; ?></a></li>
                    </ul>
                </li>
                <li><a href="<?php echo SITE_URL?>/categorie/5/"><?php echo $text['CATEGORY_PAPER']; ?></a></li>
                <li><a href="<?php echo SITE_URL?>/categorie/25/"><?php echo $text['CATEGORY_LABELS']; ?></a></li>
                <li><a href="<?php echo SITE_URL?>/categorie/6/"><?php echo $text['CATEGORY_ACCESSORIES']; ?></a></li>
            </ul><!-- /navbar-left -->
        </div><!-- /navbar -collapse -->
    </nav><!-- /nav -->

    <?php
if (filter_var(formOption('api_test'), FILTER_VALIDATE_BOOLEAN) === true) {
    echo '<div class="alert alert-info" role="alert">';
    echo '<strong>'. $text['JAVASCRIPT_ALERT_BOLD'] . '</strong> ' . $text['TEST_ALERT'];
    echo '</div>';
}
?>

</div><!-- /col -->
</div><!-- /row -->