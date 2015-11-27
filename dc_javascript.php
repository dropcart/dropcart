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

// Set page title, meta
$strPageTitle = $text['PAGE_TITLE_JAVASCRIPT'];
$strMetaDescription = $text['PAGE_DESCRIPTION_JAVASCRIPT'];

// Start displaying HTML
require_once 'includes/php/dc_header.php';

?>

<div class="row">
    <div class="col-lg-12">
        <h1><?php echo $text['PAGE_CONTENT_JAVASCRIPT']['TITLE']; ?></h1>
        <h2><?php echo $text['PAGE_CONTENT_JAVASCRIPT']['SUBTITLE']; ?></h2>
        <p>
        <?php echo $text['PAGE_CONTENT_JAVASCRIPT']['CONTENT']; ?>
        </p>
        <ul>
            <?php foreach ($text['PAGE_CONTENT_JAVASCRIPT']['BROWSER'] as $browser => $browserValue) {
                    echo '<li><a href="#' . $text['PAGE_CONTENT_JAVASCRIPT']['BROWSER'][$browser]['ID'] . '">' . $text['PAGE_CONTENT_JAVASCRIPT']['BROWSER'][$browser]['NAME'] . '</a></li>';
            } ?>
        </ul>

        <?php foreach ($text['PAGE_CONTENT_JAVASCRIPT']['BROWSER'] as $browser => $browserValue) {
            echo '<h2 id="' . $text['PAGE_CONTENT_JAVASCRIPT']['BROWSER'][$browser]['ID'] . '">' . $text['PAGE_CONTENT_JAVASCRIPT']['BROWSER'][$browser]['NAME'] . '</h2>'; ?>
            <ul>
                <?php foreach ($text['PAGE_CONTENT_JAVASCRIPT']['BROWSER'][$browser]['LIST'] as $key => $listValue) {
                    echo "<li>" . $listValue . "</li>";
                } ?>
            </ul>
            <?php
        } ?>
    </div>
</div>
