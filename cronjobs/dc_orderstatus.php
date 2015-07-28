<?php
require_once __DIR__ . '/../includes/php/dc_connect.php';
require_once __DIR__ . '/../_classes/class.database.php';
$objDB = new DB();

require_once __DIR__ . '/../includes/php/dc_config.php';
require_once __DIR__ . '/../includes/php/dc_functions.php';
require_once __DIR__ . '/../includes/php/dc_mail.php';
require_once __DIR__ . '/../_classes/class.cart.php';
require_once __DIR__ . '/../libraries/Api_Inktweb/API.class.php'; // Inktweb API
require_once __DIR__ . '/../libraries/mpdf/mpdf.php'; // MPDF Libary
require_once __DIR__ . '/../libraries/Twig/Autoloader.php'; // Twig template engine

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

$strSQL = "SELECT * " .
"FROM " . DB_PREFIX . "customers_orders co " .
"WHERE co.status = 0 AND co.extOrderId != 0";
$result = $objDB->sqlExecute($strSQL);
while ($objOrder = $objDB->getObject($result)) {

    $Order = $Api->getOrderStatus($objOrder->extOrderId);

    // TEST CODE
    //    $Order = new StdClass(); #TEST
    //    $Order->status_code = 4; #TEST
    //    $Order->error = null;  #TEST

    if (empty($Order->error) && $Order->status_code != $objOrder->status) {

//      $strSQL = "UPDATE ".DB_PREFIX."customers_orders SET status = " . $Order->status_code . " WHERE orderId = " . $objOrder->orderId;
        //      $update = $objDB->sqlExecute($strSQL);

        if ($Order->status_code == 4) {

            // Set twig variable
            // Initialize Twig
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem(SITE_PATH . "/includes/templates");
            $twig = new Twig_Environment($loader, array("cache" => false));

            // New mPDF object
            $mpdf = new mPDF();

            $intCustomerId = $objOrder->custId;
            $intOrderId = $objOrder->orderId;

            $strAttachement = generateInvoicePDF($intOrderId, false);

            // send order shipping mail
            sendMail('sentmail', $objOrder->email, $objOrder->firstname . ' ' . $objOrder->lastname, array(), $strAttachement);

            // Unset all vars to prevent from wrong data being used next loop
            $twig = null;
            $loader = null;
            $strAttachement = null;
            $mpdf = null;
        }

    }

}
?>