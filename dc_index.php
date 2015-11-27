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
	<h2><?php echo $text['ABOUT_BOLD'] . " " . formOption('site_name');?></h2>

	<div class="col-xs-6">
		<p> <?php echo $text['ABOUT_TEXT_1']; ?></p>

		<p><?php echo $text['ABOUT_TEXT_2']; ?></p>
	</div><!-- /col -->

	<div class="col-xs-6">
		<p class="pull-right"><img src="http://placehold.it/315x85" alt="Demo image" class="img-responsive"></p>
	</div><!-- /col -->
</div><!-- /col -->
</div><!-- /row -->

<?php
require 'includes/php/dc_footer.php';
?>

