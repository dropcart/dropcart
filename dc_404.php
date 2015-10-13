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
$strPageTitle = $text['PAGE_TITLE_404'];
$strMetaDescription = $text['PAGE_DECRIPTION_404'];

header("HTTP/1.0 404 Not Found");

// Start displaying HTML
require_once 'includes/php/dc_header.php';
?>

<div class="row">
    <div class="col-xs-12">
        <h1><?php echo $text['PAGE_TITLE_404']; ?>.</h1>
        <p>
        	<?php echo $text['PAGE_CONTENT_404_1']; ?>
    		<a href="mailto:<?php echo formOption('SITE_EMAIL')?>">
            	<?php echo formOption('SITE_EMAIL')?>
            </a>
            <?php echo $text['PAGE_CONTENT_404_2']; ?>    
       	</p>
    </div>

    </div>
