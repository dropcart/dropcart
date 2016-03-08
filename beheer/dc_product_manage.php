<?php

// Required includes
require_once __DIR__ . '/../includes/php/dc_connect.php';
require_once __DIR__ . '/../_classes/class.database.php';
$objDB = new DB();
require_once __DIR__ . '/../beheer/includes/php/dc_config.php';

// Page specific includes
require_once __DIR__ . '/../beheer/includes/php/dc_functions.php';
require_once __DIR__ . '/../libraries/Api_Inktweb/API.class.php';

$objDB = new DB();
$intId = (isset($_GET['id'])) ? intval($_GET['id']) : 0;

if (!empty($_POST) AND !empty($intId)) {

    $_POST = sanitize($_POST);

    $product_title = $_POST['product_title'];
    $product_price = $_POST['product_price'];
    $product_price_from = $_POST['product_price_from'];
    $product_description = $_POST['product_description'];

    $opt_cart = $_POST['check_cart'];
    $opt_top5 = $_POST['check_top5'];

    // Set to NULL if empty
    if (empty($product_title)) {
        $product_title = 'NULL';
    } else {
        $product_title = "'" . $product_title . "'"; // adds single quotes
    }

    // Set to NULL if empty
    if (empty($product_description)) {
        $product_description = 'NULL';
    } else {
        $product_description = "'" . $product_description . "'"; // adds single quotes
    }

    // Set to NULL if empty
    if (empty($product_price_from)) {
        $product_price_from = 'NULL';
    } else {
        $product_price_from = "'" . $product_price_from . "'"; // adds single quotes
    }

    // Set to NULL if empty
    if (empty($product_price)) {
        $product_price = 'NULL';
    } else {
        $product_price = "'" . $product_price . "'"; // adds single quotes
    }

    // Set to NULL if empty
    if (empty($opt_cart)) {
        $opt_cart = 'NULL';
    } else {
        $opt_cart = "'" . $opt_cart . "'"; // adds single quotes
    }

    // Set to NULL if empty
    if (empty($opt_top5)) {
        $opt_top5 = 'NULL';
    } else {
        $opt_top5 = "'" . $opt_top5 . "'"; // adds single quotes
    }

    $strSQL =
    "INSERT INTO " . DB_PREFIX . "products
        (id, title, description, price, opt_cart, opt_top5)
        VALUES
        (" . $intId . ", " . $product_title . ", " . $product_description . ", " . $product_price . ", " . $opt_cart . ", " . $opt_top5 . ")
        ON DUPLICATE KEY UPDATE
        title       = " . $product_title . ",
        description     = " . $product_description . ",
        price_from  = " . $product_price_from . ",
        price       = " . $product_price . ",
        opt_cart    = " . $opt_cart . ",
        opt_top5    = " . $opt_top5 . " ";
    $objDB->sqlExecute($strSQL);

    // Handle price tiered
    $arrTiersQuantity = $_POST['product_price_tiers_qty'];
    $arrTiersPercentage = $_POST['product_price_tiers_percentage'];
    $arrTiers = array_combine($arrTiersQuantity, $arrTiersPercentage); // merges seperate input fields into one array for easier looping

    // empty table to prevent duplicates
    $strSQL = "DELETE FROM " . DB_PREFIX . "products_tiered WHERE productId = '" . $intId . "' ";
    $objDB->sqlExecute($strSQL);

    // Add to table
    foreach ($arrTiers as $key => $value) {

        // Insert key/values only if not empty AND greater than 0
        if ($key > 0 AND $value > 0) {
            $strSQL =
            "INSERT INTO " . DB_PREFIX . "products_tiered
            (productId, quantity, percentage)
            VALUES
            ('" . $intId . "', '" . $key . "', '" . $value . "')";
            $objDB->sqlExecute($strSQL);
        }
    }

    // Redirect to same page (prevents double submits)
    header('Location: ?id=' . $intId . '&succes=' . urlencode('Product is aangepast.'));
}

$strSQL =
"SELECT p.id,
            p.price_from,
            p.price,
            p.title,
            p.description,
            p.opt_cart,
            p.opt_top5
            FROM " . DB_PREFIX . "products p
            WHERE p.id = '" . $intId . "'
            ";
$result = $objDB->sqlExecute($strSQL);
$objProduct = $objDB->getObject($result);

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);
$Product = $Api->getProduct($intId);

require __DIR__ . '/../beheer/includes/php/dc_header.php';

?>

<h1>Product details <small><?php if (isset($intId) && $intId != 0) {$intId;}
?></small></h1>

<?php

if (!empty($_GET['succes'])) {
    echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> ' . $_GET['succes'] . '</div>';
}

?>

<hr />
<?php if (isset($Product->errors)): ?>

    <div class="alert alert-danger" role="alert">
        Geen gelidge product ID opgegeven
    </div>

<?php else: ?>
<div class="col-md-8">

    <div class="alert alert-warning" role="alert"><strong>Let op</strong>: Indien een veld niet ingevuld is wordt de informatie uit de API of Boilerplate content gehaald.</div>

    <div class="panel panel-default">
        <div class="panel-heading">Product eigenschappen</div><!-- /panel-heading -->
        <div class="panel-body">


        <form class="form-horizontal" role="form" method="POST">
            <div class="form-group">
            <label for="product_title" class="col-sm-2 control-label">Title</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="product_title" name="product_title" value="<?php if (isset($objProduct->title)) {echo $objProduct->title;}?>" autocomplete="off">
                    <p class="help-block">Wordt (momenteel) enkel gebruikt op de products_details pagina</p>
                </div><!-- /col -->
            </div><!-- /form-group -->
            <div class="form-group">
            <label for="product_description" class="col-sm-2 control-label">Description</label>
                <div class="col-sm-8">
                    <textarea class="form-control" id="product_description" name="product_description" rows="7"><?php if (isset($objProduct->description)) {echo $objProduct->description;}?></textarea>
                </div><!-- /col -->
            </div><!-- /form-group -->
            <div class="form-group">
            <label for="product_price" class="col-sm-2 control-label">Price from</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="product_price_from" name="product_price_from" value="<?php if (isset($objProduct->price_from)) {$objProduct->price_from;}?>" autocomplete="off">
                </div><!-- /col -->
            </div><!-- /form-group -->
            <div class="form-group">
            <label for="product_price" class="col-sm-2 control-label">Price</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="product_price" name="product_price" value="<?php if (isset($objProduct->price)) {echo $objProduct->price;}?>" autocomplete="off">
                    <p class="help-block">Overschrijf standaard prijsformule. Verplicht voor het invullen van volume prijzen.</p>
                    <p class="help-block">Geldige values: <code>price</code> = Inktweb.nl prijs, inclusief BTW, <code>purchase</code> = Inkoopprijs exclusief BTW, <code>msrp</code> = Adviesprijs exclusief BTW.</p>
                </div><!-- /col -->

            </div><!-- /form-group -->

            <div id="productTiers">
            <div class="col-sm-6 col-sm-offset-4"><p class="help-block">Volume prijzen. Vul in het eerste veld vanaf welk aantal (bijvoorbeeld  &ldquo;3&rdquo;) en in het tweede veld het percentage korting wat gegeven wordt (bijvoorbeeld &ldquo;5&rdquo;).</p></div><!-- /col -->

                <?php
                $strSQL = "SELECT quantity, percentage FROM " . DB_PREFIX . "products_tiered WHERE productId = '" . $intId . "' ORDER BY quantity ASC ";
                $result = $objDB->sqlExecute($strSQL);
                $numTiers = $objDB->getNumRows($result);
                if ($numTiers > 0) {
                    while ($objTier = $objDB->getObject($result)) { ?>
                        <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-2">
                            <label for="product_price_tiers_qty" class="col-sm-3 control-label">Vanaf</label>
                            <div class="col-sm-3">
                                <input type="number" min="1" class="form-control" id="product_price_tiers_qty" name="product_price_tiers_qty[]"  value="<?=$objTier->quantity;?>" autocomplete="off">
                            </div><!-- /col -->
                            <label for="product_price_tiers_percentage" class="col-sm-3 control-label">Percentage korting</label>
                            <div class="col-sm-3">
                                <input type="number" min="1" class="form-control" id="product_price_tiers_percentage" name="product_price_tiers_percentage[]"  value="<?=$objTier->percentage;?>" autocomplete="off">
                            </div><!-- /col -->
                        </div><!-- /col -->
                        </div><!-- /form-group -->
                    <?php
                    }
                } else {
                ?>
                <div class="form-group">
                <div class="col-sm-8 col-sm-offset-2">
                    <label for="product_price_tiers_qty" class="col-sm-3 control-label">Vanaf</label>
                    <div class="col-sm-3">
                        <input type="number" min="1" class="form-control" id="product_price_tiers_qty" name="product_price_tiers_qty[]"  autocomplete="off">
                    </div><!-- /col -->
                    <label for="product_price_tiers_percentage" class="col-sm-3 control-label">Percentage korting</label>
                    <div class="col-sm-3">
                        <input type="number" min="1" class="form-control" id="product_price_tiers_percentage" name="product_price_tiers_percentage[]"  autocomplete="off">
                    </div><!-- /col -->
                </div><!-- /col -->
                </div><!-- /form-group -->
            <?php
            }
            ?>
            </div><!-- / #productTiers -->

            <div class="form-group">
                <div class="col-sm-3 pull-right">
                    <a  class="btn btn-sm btn-success addTier"><i class="glyphicon glyphicon-plus"></i></a>
                </div><!-- /col -->
            </div><!-- /form-group -->

            <script type="text/javascript">
            $(document).ready(function() {
                $(".addTier").click(function() {
                    $("#productTiers").append('<div class="form-group">\
                                <div class="col-sm-8 col-sm-offset-2">\
                                    <label for="product_price_tiers_qty" class="col-sm-3 control-label">Vanaf</label>\
                                    <div class="col-sm-3">\
                                        <input type="number" min="1" class="form-control" id="product_price_tiers_qty" name="product_price_tiers_qty[]"  autocomplete="off">\
                                    </div><!-- /col -->\
                                    <label for="product_price_tiers_percentage" class="col-sm-3 control-label">Percentage korting</label>\
                                    <div class="col-sm-3">\
                                        <input type="number" min="1" class="form-control" id="product_price_tiers_percentage" name="product_price_tiers_percentage[]"  autocomplete="off">\
                                    </div><!-- /col -->\
                                </div><!-- /col -->\
                                </div><!-- /form-group -->');
                });
            });
            </script>
            <hr />

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label for="check_cart"><input type="checkbox" name="check_cart" id="check_cart" value="1" <?php if (isset($objProduct->opt_cart) && $objProduct->opt_cart == "1") {echo 'checked';}?>> Tonen in winkelmand</label>
                    </div>
                </div><!-- /col -->
            </div><!-- /form-group -->

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label for="check_top5"><input type="checkbox" name="check_top5" id="check_top5" value="1" <?php if (isset($objProduct) && $objProduct->opt_top5 == "1") {echo 'checked';}?>> Tonen in Top 5</label>
                    </div>
                </div><!-- /col -->
            </div><!-- /form-group -->

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">Product bewerken</button>
                </div><!-- /col -->
            </div><!-- /form-group -->

        </form><!-- /form -->

        </div><!-- /panel-body -->
    </div><!-- /panel -->

    <p>
        <a href="https://www.inktweb.nl/products_detail.php?id=<?php echo $intId;?>" class="btn btn-default">Bekijk op Inktweb.nl</a>
        <a href="<?php echo SITE_URL?>/product/<?php echo $intId;?>/" class="btn btn-primary">Bekijk op website</a>
    </p>

</div><!-- /col -->

<div class="col-md-4">
<div class="list-group">
    <a class="list-group-item">
        <h4 class="list-group-item-heading">ID</h4>
        <p class="list-group-item-text"><?php echo $Product->getId();?></p>
    </a>
    <a class="list-group-item">
        <h4 class="list-group-item-heading">Title</h4>
        <p class="list-group-item-text"><?php echo $Product->getTitle();?></p>
    </a>
    <a class="list-group-item">
        <h4 class="list-group-item-heading">Brand</h4>
        <p class="list-group-item-text"><?php echo $Product->getBrand();?></p>
    </a>
    <a class="list-group-item">
        <h4 class="list-group-item-heading">EAN</h4>
        <p class="list-group-item-text"><?php echo $Product->getEan();?></p>
    </a>
    <a class="list-group-item">
        <h4 class="list-group-item-heading">OEM</h4>
        <p class="list-group-item-text"><?php echo $Product->getOem();?></p>
    </a>

    <br />
    <?php
    $objPrice = $Product->getPrice();
    $strPrice = calculateProductPrice($objPrice, $Product->getId());
    ?>

    <a class="list-group-item">
        <h4 class="list-group-item-heading">Price (Purchase ex)</h4>
        <small>Excl. BTW.</small>
        <p class="list-group-item-text"><?php echo (isset($objPrice->pricePurchase)) ? $objPrice->pricePurchase : '<i> niet beschikbaar</i>';?></p>
    </a>

    <a class="list-group-item">
        <h4 class="list-group-item-heading">Price (Inktweb.nl)</h4>
        <small>Incl. BTW.</small>
        <p class="list-group-item-text"><?php echo $objPrice->price;?></p>
    </a>

    <a class="list-group-item">
        <h4 class="list-group-item-heading">Price (MSRP)</h4>
        <small>Incl. BTW.</small>
        <p class="list-group-item-text"><?php echo (isset($objPrice->priceMSRP)) ? $objPrice->priceMSRP : '<i> niet beschikbaar</i>';?></p>
    </a>

    <a class="list-group-item">
        <h4 class="list-group-item-heading">Price <span class="label label-primary" title="De prijs die momenteel op de website staat.">LIVE</span></h4>
        <small>Incl. BTW.</small>
        <p class="list-group-item-text"><?php echo $strPrice;?></p>
    </a>
</div><!-- /list-group -->
    <?php endif;?>
</div>

<script type="text/javascript" src="<?php echo SITE_URL?>/beheer/includes/script/jquery.pagedown-bootstrap.combined.min.js"></script>
<script type="text/javascript">
(function () {

    $("textarea#product_description").pagedownBootstrap();

})();
</script>
<?php require 'includes/php/dc_footer.php';?>