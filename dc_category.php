<?php
session_start();

// if POST, redirect it to a GET
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (isset($_POST['brands'])) {

        if (!isset($_GET['pageNumber']) || empty($_GET['pageNumber'])) {
            $_GET['pageNumber'] = "1";
        }

        $strBrands = implode(',', $_POST['brands']);

        header('Location: ?pageNumber=' . $_GET['pageNumber'] . '&brands=' . $strBrands);
    }
}

// Required includes
require_once 'includes/php/dc_connect.php';
require_once '_classes/class.database.php';
$objDB = new DB();
require_once 'includes/php/dc_config.php';

// Page specific includes
require_once '_classes/class.cart.php';
require_once 'includes/php/dc_functions.php';

// Start API
require_once 'libraries/Api_Inktweb/API.class.php';

$_GET = sanitize($_GET);
$_POST = sanitize($_POST);

$canonical = "/categorie/" . (int) $_GET['categoryId'] . "/";

// Generate page title & meta tags
$strPageTitle = getCategoryPageTitle($_GET['categoryId']);
$strMetaDescription = getCategoryMetaDescription($_GET['categoryId']);

// Start displaying HTML
require_once 'includes/php/dc_header.php';

$intPageNumber = (int) (isset($_GET["pageNumber"]) ? $_GET["pageNumber"] : 1);
$intOffset = ($intPageNumber - 1) * MAXIMUM_PAGE_PRODUCTS;
$strSort = (isset($_GET["sort"]) ? $_GET["sort"] : 'titleAsc');

$strBrands = (isset($_GET['brands'])) ? $_GET['brands'] : null;
$queryBrands = "";
if (!empty($strBrands)) {
    $queryBrands = "&brands=" . $strBrands;
}

$intCategoryId = (int) $_GET['categoryId'];
$arrProducts = $Api->getProductsByCategory($intCategoryId, '?limit=' . MAXIMUM_PAGE_PRODUCTS . '&offset=' . $intOffset . '&fields=price,images' . $queryBrands);

$intTotalProducts = $arrProducts->itemsTotal;
$intPages = ceil($arrProducts->itemsTotal / MAXIMUM_PAGE_PRODUCTS);

$arrSortOptions = array(
    'salesDesc' => 'Populair',
    'priceAsc' => 'Laagste prijs',
    'priceDesc' => 'Hoogste prijs',
    'titleAsc' => 'Titel A-Z',
);

$arrBrandOptions = array(
    '2' => 'HP',
    '3' => 'Canon',
    '4' => 'Epson',
    '5' => 'Lexmark',
    '6' => 'Brother',
    '29' => 'Dell',
    '31' => 'Herma',
    '50' => 'Dymo',
);
?>


<div class="row">
    <div class="col-md-3">
        <div class="well well-small">
            <ul class="nav nav-list">
            <form method="post" id="form-brand">
                <li class="nav-header">Geschikt voor</li>
                <label class="sr-only"><input type="hidden" name="brands[]" value="" checked /></label>
                <?php
                foreach ($arrBrandOptions AS $brandKey => $brandValue) {

                    $selected = '';
                    $arrGetBrand = explode(',', $strBrands);
                    foreach ($arrGetBrand AS $getBrand) {
                        if ($getBrand == $brandKey) {
                            $selected = "checked";
                        }
                    }
                    echo '<li><label><input type="checkbox" name="brands[]" onclick="document.getElementById(\'form-brand\').submit();" value="' . $brandKey . '" ' . $selected . ' /> ' . $brandValue . '</label></li>';
                }
                ?>
            </form>
            </ul>
        </div><!-- /well -->
    </div><!-- /col -->

    <div class="col-md-9 cat">

        <h1><?=getCustomCategoryTitle($arrProducts->categories[0]->id)?></h1>

        <div class="row">

        <?php
        if (count($arrProducts->products) > 0) {

            foreach ($arrProducts->products as $arrProduct) {

                $objPrice = $arrProduct->details[0];
                $strPrice = calculateProductPrice($objPrice, $arrProduct->id);

                $strProductImg = null;
                if (isset($arrProduct->details[1]->images->url)) {
                    $strProductImg = $arrProduct->details[1]->images->url;
                }

                // check if valid image (ignore warnings)
                if (@!getimagesize($strProductImg)) {
                    $strProductImg = DEFAULT_PRODUCT_IMAGE;
                }

                ?>

                <div class="col-md-3 col-xs-4">
                <div class="image">
                    <a href="<?php echo SITE_URL . '/' . rewriteUrl($arrProducts->categories[0]->name)?>/<?php echo rewriteUrl($arrProduct->title);?>/<?php echo $arrProduct->id;?>/">
                    <img src="<?php echo $strProductImg;?>" class="img-responsive" alt="<?php echo $arrProduct->title;?>" style="height:195px;margin:0px auto;" />
                    <span class="label label-primary"><?php echo $strPrice;?></span>
                </a>
                </div><!-- /image -->

                <h4><a href="<?php echo SITE_URL . '/' . rewriteUrl($arrProducts->categories[0]->name)?>/<?php echo rewriteUrl($arrProduct->title);?>/<?php echo $arrProduct->id;?>/" class="truncate"><?php echo $arrProduct->title;?></a></h4>
                </div><!-- /col -->

            <?php
            }

        } else {
            echo "<p>Geen resultaten gevonden.</p>";
        }
        ?>
        </div><!-- /row -->

        <div class="row text-center">
            <ul class="pagination">
                <?php
                $split = 5; // Maximum number of pages left and right of active
                $start = $intPageNumber - $split;
                $end = $intPageNumber + $split;

                if ($start < 1) {
                    $start = 1;
                    $end = $split * 2;
                }

                if ($end > $intPages) {
                    $end = $intPages;
                    $start = $end - ($split * 2) + 1;

                    if ($start < 1) {
                        $start = 1;
                    }
                }

                if ($intPageNumber > 1) {
                    echo '<li><a href="' . SITE_URL . '/categorie/' . $intCategoryId . '/?sort=' . $strSort . $queryBrands . '&pageNumber=1">&laquo;</a></li>';
                }

                for ($i = $start; $i <= $end; $i++) {
                    $active = ($intPageNumber == $i) ? 'class="active" ' : '';
                    echo '<li ' . $active . '><a href="' . SITE_URL . '/categorie/' . $intCategoryId . '/?sort=' . $strSort . '&pageNumber=' . $i . $queryBrands . '">' . $i . '</a></li>';
                }

                if (($intPages - 1) != $intPageNumber && $intPages != 1) {
                    echo '<li><a href="' . SITE_URL . '/categorie/' . $intCategoryId . '/?sort=' . $strSort . $queryBrands . '&pageNumber=' . $intPages . '">&raquo;</a></li>';
                }
                ?>
            </ul>
        </div><!-- /row -->

    </div><!-- /col -->
</div><!-- /row -->


<?php
require 'includes/php/dc_footer.php';
?>