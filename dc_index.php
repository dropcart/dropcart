<?php
session_start();

// Required includes
require_once 'includes/php/dc_connect.php';
require_once '_classes/class.database.php';
$objDB = new DB();
require_once 'includes/php/dc_config.php';

// Page specific includes
require_once '_classes/class.cart.php';
echo 'blah';
require_once 'includes/php/dc_functions.php';

// Start API
require_once 'libraries/Api_Inktweb/API.class.php';

// Generate page title & meta tags
$strPageTitle = getContent('homepage_title');
$strMetaDescription = getContent('homepage_meta_description');

// Start displaying HTML
require_once 'includes/php/dc_header.php';
?>


<h1><?php echo formOption('site_name');?> <small>E-Commerce</small></h1>

<hr />


<?php
include 'includes/php/dc_include_selector.php';
?>

<div class="row">
<div class="col-xs-12">
	<h2>Goedkoop printen met <?php echo formOption('site_name');?></h2>

	<div class="col-xs-6">
		<p>Wij leveren printer inkt cartridges voor alle grote merken zoals HP, Brother, Epson en Canon. Gebruik onze <em>cartridge zoeker</em> hierboven om de juiste producten voor uw printer te vinden en browse door ons uitgebreid assortiment laag geprijsde en hoogwaardige printer verbruiksartikelen.</p>

		<p>Producten die op voorraad zijn worden nog dezelfde dag verzonden!</p>
	</div><!-- /col -->

	<div class="col-xs-6">
		<p class="pull-right"><img src="http://placehold.it/315x85" alt="Demo image" class="img-responsive"></p>
	</div><!-- /col -->
</div><!-- /col -->
</div><!-- /row -->

<?php
require 'includes/php/dc_footer.php';
?>

