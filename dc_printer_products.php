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
require_once('libraries/Api_Inktweb/API.class.php');

$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

$_GET 			= sanitize($_GET);
$_POST 			= sanitize($_POST);

$intPageNumber		= (int) (isset($_GET["pageNumber"]) ? $_GET["pageNumber"] : 1);
$intOffset			= ($intPageNumber - 1) * MAXIMUM_PAGE_PRODUCTS;
$strSort			= (isset($_GET["sort"]) ? $_GET["sort"] : 'titleAsc');

$intPrinterId			= (int) $_GET['printerId'];
$arrProducts			= $Api->getProductsByPrinter($intPrinterId, '?limit=' . MAXIMUM_PAGE_PRODUCTS . '&offset=' . $intOffset . '&sort=' . $strSort . '&fields=price,images');

$intTotalProducts		= $arrProducts->itemsTotal;
$intPages			= ceil($arrProducts->itemsTotal / MAXIMUM_PAGE_PRODUCTS);

// Generate page title & meta tags
$strPageTitle			= getContent('printer_title', true, '', $arrProducts);
$strMetaDescription		= getContent('printer_meta_description', true, '', $arrProducts);

// Start displaying HTML
require_once('includes/php/dc_header.php');

$arrSortOptions 		= array (
	'priceAsc'		=> 'Laagste prijs',
	'priceDesc'		=> 'Hoogste prijs',
	'titleAsc'		=> 'Titel A-Z'
);

?>

<div class="row">
	<div class="col-md-3 col-xs-12">
		<div class="well well-small">
			<ul class="nav nav-list">

				<li class="nav-header">Sorteer op</li>
				<?php foreach($arrSortOptions as $sortIndex => $sortTitle) {
					
					$active = ($sortIndex == $strSort) ? 'class="active" ' : '';
					echo '<li ' . $active . '><a href="?sort='.$sortIndex.'"><span class="glyphicon glyphicon-ok"></span> '.$sortTitle.'</a></li>';
					
				} ?>

			</ul>
		</div><!-- /well -->
	</div><!-- /col -->

	<div class="col-md-9 col-xs-12 cat">
		<h1>Producten geschikt voor de <?php echo $arrProducts->printers->brand . " " . $arrProducts->printers->serie . " " . $arrProducts->printers->printer; ?></h1>


		<div class="row">
		
			<?php
			
			if(count($arrProducts->products) > 0) {

				foreach($arrProducts->products as $arrProduct) {
					
					$objPrice	= $arrProduct->details[0];
					$strPrice 	= calculateProductPrice($objPrice, $arrProduct->id);

					$strProductImg = $arrProduct->details[1]->images->url;

					// check if valid image (ignore warnings)
					if (@!getimagesize($strProductImg)) {
						$strProductImg 	= DEFAULT_PRODUCT_IMAGE;
					}
					
				?>
				
					<div class="col-md-3 col-xs-4">
						<div class="image">
							<a href="<?php echo SITE_URL?>/<?php echo rewriteUrl( $arrProduct->categorie->title ) ?>/<?php echo rewriteUrl( $arrProduct->title ); ?>/<?php echo $arrProduct->id; ?>/">
								<img src="<?php echo $strProductImg; ?>" class="img-responsive" alt="<?php echo $arrProduct->title; ?>" style="height:195px;margin:0px auto;" />
								<span class="label label-primary"><?php echo $strPrice; ?></span>
							</a>
						</div><!-- /image -->
						
						<h4><a href="<?php echo SITE_URL.'/'.rewriteUrl( $arrProduct->categorie->title ) ?>/<?php echo rewriteUrl( $arrProduct->title ); ?>/<?php echo $arrProduct->id; ?>/" class="truncate"><?php echo $arrProduct->title; ?></a></h4>
					</div><!-- /col -->
				
				<?php
				}
			
			} else {
				echo "<p>Geen geschikte inkt cartridges, toners papier of andere toebehoren gevonden voor deze printer. Misschien dat u met onderstaande links verder komt?</p>";
			}
			?>
			
		</div><!-- /row -->

		<div class="row text-center">
			<ul class="pagination">
				
				<?php
				if($intPageNumber > 1) {
				
					echo '<li><a href="#">&laquo;</a></li>';
				
				}
				
				for($i=1;$i<=$intPages;$i++) {
					
					$active = ($intPageNumber == $i) ? 'class="active" ' : '';
					echo '<li ' . $active . '><a href="?sort=' . $strSort . '&pageNumber=' . $i . '">' . $i . '</a></li>';
					
				}

				if(($i-1) != $intPageNumber && $i != 1) {
				
					echo '<li><a href="#">&raquo;</a></li>';
				
				}
				?>
			</ul>
		</div><!-- /row -->

	</div><!-- /col -->
</div><!-- /row -->


<?php
require('includes/php/dc_footer.php');
?>