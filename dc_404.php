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

// Generate page title & meta tags
$strPageTitle		= "Deze pagina bestaat niet";
$strMetaDescription	= "De pagina die u heeft opgevraagd bestaat niet!";

header("HTTP/1.0 404 Not Found");

// Start displaying HTML
require_once('includes/php/dc_header.php');
?>

<div class="row">
    <div class="col-xs-12">
        <h1>Deze pagina bestaat niet.</h1>
        <p>
            De pagina die u heeft opgevraagd kon niet worden gevonden door ons systeem.
            Neem gerust contact met ons op via <a href="mailto:<?php echo formOption('SITE_EMAIL') ?>">
                <?php echo formOption('SITE_EMAIL') ?>
            </a> als u er niet uitkomt.</p>
    </div>

    </div>
