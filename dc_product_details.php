<?php
session_start();

// Required includes
require_once 'includes/php/dc_connect.php';
require_once '_classes/class.database.php';
$objDB = new DB();
require_once 'includes/php/dc_config.php';

// Page specific includes
require_once '_classes/class.cart.php';
require_once 'includes/php/dc_functions.php';
require_once 'libraries/Parsedown/Parsedown.php';

// Start API
require_once 'libraries/Api_Inktweb/API.class.php';

$_GET = sanitize($_GET);
$_POST = sanitize($_POST);

$intProductId = (int) $_GET["productId"];

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);
$Product = $Api->getProduct($intProductId);

if (!empty($Product->errors)) {
    notFoundPage();
}

$category_id = $Product->getCategorieId();

// Generate page title & meta tags
$strPageTitle = getProductPageTitle($category_id, $Product);

$strMetaDescription = getProcutMetaDescription($category_id, $Product);

// Handle pretty urls / canonical / 301 old URLs
if (!is_null($Product->getCategorieTitle()) AND !is_null($Product->getTitle()) AND !empty($intProductId)) {

    // $canonical gets set to full `link rel` in dc_header.php
    $canonical = SITE_URL . '/' . rewriteUrl($Product->getCategorieTitle()) . '/' . rewriteUrl($Product->getTitle()) . '/' . $Product->getId() . '/';

    // TODO: add support for https
    $request = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    // Redirect to canonical if current url doesnt match (prevent duplicate indexing)
    if ($request !== $canonical) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: " . $canonical);
    }
}

// Start displaying HTML
require_once 'includes/php/dc_header.php';

$objPrice = $Product->getPrice();
$dblPrice = calculateProductPrice($objPrice, $intProductId, '', false);
$strPrice = money_format('%(#1n', $dblPrice);
$productPriceFrom = getPriceFrom($intProductId);

$intStock = $Product->getStock();
$objSpecifications = $Product->getSpecifications();
$objPrinters = $Product->getCompatible();
$arrImages = (array) $Product->getImages();
$strProductImg = @$arrImages[0]->url;

// Support infinite stock by setting it to high value
if ($intStock == 'infinite') {
    $intStock = 99;
}

// check if valid image (ignore warnings)
if (@!getimagesize($strProductImg)) {
    $strProductImg = DEFAULT_PRODUCT_IMAGE;
}

?>
<div class="row">
    <div class="col-xs-12">
        <h1><?php echo getCustomProductTitle($category_id, $Product);?></h1>
    </div><!-- /col -->
</div><!-- /row -->

<div class="row">
    <div class="col-md-3 col-sm-4 col-xs-12">
        <img src="<?php echo $strProductImg;?>" data-toggle="magnify" class="img-responsive magnify" alt="foto <?php echo $Product->getOem();?>" style="margin:0 auto; max-height:195px" />

        <div class="col-md-10 col-md-offset-1 hidden-xs">
        <ul class="list-unstyled ">
            <li><span class="glyphicon glyphicon-ok"></span> <?php echo $text['GARANTY']; ?></li>
            <li><span class="glyphicon glyphicon-ok"></span> <?php echo $text['TIME_TO_CONSIDER']; ?></li>
            <li><span class="glyphicon glyphicon-ok"></span> <?php echo $text['SELLING_POINT']; ?></li>
        </ul>
        </div>
    </div><!-- /col -->

    <div class="col-md-5 col-sm-8 col-xs-12">
        <div class="product-header">
            <h2 id="productPrice" data-type="text" data-pk="<?php echo $intProductId;?>" data-url="/post" data-title="Verander prijs">
                <?=($productPriceFrom != NULL) ? '<small><del>&euro; ' . $productPriceFrom . '</del></small>' : '';?>
                <?php echo $strPrice?> <small><?php echo $text['TAX']; ?></small>
            </h2>

            <?php
$strSQL = "SELECT quantity, percentage FROM " . DB_PREFIX . "products_tiered WHERE productId = '" . $intProductId . "' ORDER BY quantity ASC ";
$result = $objDB->sqlExecute($strSQL);
$numTiers = $objDB->getNumRows($result);
if ($numTiers > 0) {
    echo '<div class="productTiered well">';
}
while ($objTier = $objDB->getObject($result)) {
    $dblSaving = ($dblPrice / 100) * $objTier->percentage;
    $dblPiecePrice = $dblPrice - $dblSaving;
    echo '<p>' . $text['SAVINGS_1'] . ' ' . $objTier->quantity . ' ' . $text['SAVINGS_2'] . '' . money_format('%(#1n', $dblPiecePrice) . '</strong> ' . $text['SAVINGS_3'] . ' ' . $objTier->percentage . '%</strong>.</p>';
}
if ($numTiers > 0) {
    echo '</div>';
}
?>

            <div class="product-stock">
                <p><strong><?php echo $text['STOCK']; ?></strong>:
                <?php
// Display stock
if ($intStock > 10) {
    echo $text['STOCK_SURPLACE'];
} elseif ($intStock > 0) {
    echo $text['IN_STOCK'];
} else {
    echo $text['OUT_STOCK'];
}

// Display delivery
if ($intStock > 0) {
    echo '<p>' . $text['DELIVER_TIME_IN_STOCK_PREFIX'] . ' <a data-toggle="modal" data-target="#disclaimerDelivery">' . $text['DELIVER_TIME_IN_STOCK_AFFIX'] . ' *</a></p>';
} else {
    echo '<p>' . $text['DELIVER_TIME_OUT_STOCK'] . '</p>';
}
?>
            </div>

            <div class="product-header-footer well">
                <div class="pull-left">
                    <?php echo $text['QUANTITY']; ?>
                    <select name="quantity" class="quantity" id="quantitySelect">
                        <?php
$maxStock = 10;
for ($i = 1; $i <= $maxStock; $i++) {

    if ($i == 10) {

        $plus = '+';

    } else {

        $plus = '';

    }

    echo '<option value="' . $i . '">' . $i . $plus . '</option>';

}
?>

                    </select>

                    <input type="number" min="1" class="quantity hidden" id="quantityInput" name="quantity" />
                </div><!-- /pull-left -->
                <div class="pull-right">
                    <?php
if ($dblPrice <= 0) {
    echo $text['NO_SUPPLY'];
} else {
    echo '<a class="btn btn-primary" data-toggle="modal" data-target="#cartConfirm">' . $text['ADD_TO_CART'] . '</a>';
}
?>
                </div><!-- /pull-right -->

                <div class="clearfix"></div>
            </div><!-- /product-header-footer -->
        </div><!-- /product-header -->

        <div class="product-body">
            <?php

if (!empty($objSpecifications)) {

    echo '<table class="table product-table">';

    foreach ($objSpecifications as $objSpecification) {

        // extra check for NULL results
        if (empty($objSpecification->label) OR empty($objSpecification->value)) {
            continue;
        }

        $strUnit = (!empty($objSpecification->unit)) ? $objSpecification->unit : '';

        echo '<tr>
                                <td><strong>' . $objSpecification->label . ':</strong></td>
                                <td>' . $objSpecification->value . ' ' . $strUnit . '</td>
                              </tr>';

    }

    echo '</table>';

}

?>

            <h3><?php echo $text['DESCRIPTION']; ?></h3>
            <?php

echo getCustomProductDesc($category_id, $Product);
?>

            <?php
if (count($objPrinters->printers) > 0) {
    echo '<h3>'. $text['SUITABLE_PRINTERS_TITLE'] . '</h3>';
    echo '<p>' . $text['SUITABLE_PRINTERS_CONTENT'] . ': </p>';
    foreach ($objPrinters->printers as $printer) {
        print_r($printer->title);
        echo ", ";
    }
}
?>

        </div><!-- /product-body -->
    </div><!-- /col -->

    <div class="modal fade" id="cartConfirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $text['ADDED_TO_CART_TITLE']; ?></h4>
                <p><?php echo $text['ADDED_TO_CART_CONTENT']; ?></p>

            </div><!-- /modal body -->
            <div class="modal-footer">
                <a class="btn btn-default" data-dismiss="modal"><?php echo $text['CLOSE']; ?></a></button>
                <a href="<?php echo SITE_URL?>/dc_shoppingcart.php" class="btn btn-primary"><?php echo $text['SHOW_CART']; ?></a></button>
            </div><!-- /modal footer -->
        </div><!-- /modal content -->
        </div><!-- /modal dialog -->
    </div><!-- /modal -->
    <script>
    $('.magnify').magnify();

    $('#cartConfirm').on('show.bs.modal', function (e) {
        // Add product to cart

        var quantity = $('.quantity').val();
        $.get(
            '<?php echo SITE_URL?>/includes/json/addProductToCart.php',
            {
                productId   : <?=$intProductId?>,
                quantity    : quantity,
                timestamp   : '<?=$_SERVER["REQUEST_TIME"]?>'
            },
            function(data) {

                $('.cartItems').html(data.cartItems);
                $('.cartSubtotal').html(data.cartSubTotal);

            },
            'json'
        );

    })


    $(document).ready(function() {
        $('#quantitySelect').change(function(){
            var selected_item = $(this).val()

            if(selected_item == "10") {
                $('#quantityInput').val("").removeClass('hidden');
                $('#quantitySelect').val(selected_item).remove();
            } else {
                $('#quantityInput').val(selected_item).addClass('hidden');
            }
        });
    });
    </script>

    <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="well well-small">
            <ul class="nav nav-list">
                <li class="nav-header"><?php echo $text['TOP_5']; ?></li>

                <?php
$strSQL = "SELECT id FROM " . DB_PREFIX . "products WHERE opt_top5 = 1 ";
$result = $objDB->sqlExecute($strSQL);
while ($objProduct = $objDB->getObject($result)) {

    $Product = $Api->getProduct($objProduct->id, '?fields=title');

    echo '<li><a href="' . SITE_URL . '/' . rewriteUrl($Product->getCategorieTitle()) . '/' . rewriteUrl($Product->getTitle()) . '/' . $Product->getId() . '/">' . $Product->getTitle() . '</a></li>';

}
?>
            </ul>
        </div><!-- /well -->
    </div><!-- /col -->

    <?php
include 'includes/php/dc_include_logos.php';
?>

</div><!-- /row -->

<?php
require 'includes/php/dc_footer.php';
?>