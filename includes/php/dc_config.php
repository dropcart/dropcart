<?php

/**
 * Function to get options and values from the database
 * @param  string optionName as used in the database 'dc_options'
 * @return string optionValue
 */
function formOption($optionName) {
    global $objDB;

    $optionName = strtolower($optionName);

    $strSQL = "SELECT optionValue FROM " . DB_PREFIX . "options WHERE optionName = '" . $optionName . "' ";
    $result = $objDB->sqlExecute($strSQL);
    list($optionValue) = $objDB->getRow($result);

    return $optionValue;
}

function getSiteUrl() {

    $url = 'http';

    // Check if connection is secure
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
        $url .= 's'; # so it becomes https
    }

    $url .= '://' . $_SERVER['SERVER_NAME'];

    /* Determine if we have a subdirectory */


    /* Replace slaches for cross_platform */
    $path = str_replace('\\', '/', dirname(__FILE__));
    /* Substract document root from absolute path */
    $path = str_replace($_SERVER['DOCUMENT_ROOT'] . '', '', $path);

    /* Remove the config dir from the path */
    $path = str_replace('/includes/php', '', $path);

    $url .= $path;

    return $url;
}

/*
Turn on or off developer mode:
Enables all errors
 */

define('DEV_MODE', true); # NEVER change this value in production environment
//define('DROPCART_VERSION', formOption('DROPCART_VERSION'));
define('SITE_URL', getSiteUrl()); // ends in a slash

/*
Get the absolute path of the dropcart installation.
We need to use the dirname() 3 times, because the
config file is located 2 subdirectories deep.

http://php.net/manual/en/function.dirname.php

 */
define('SITE_PATH', dirname(dirname(dirname(__FILE__))) . "/");
//define('SITE_NAME', formOption('SITE_NAME'));
//define('SITE_SHIPPING', formOption('SITE_SHIPPING'));
//define('SITE_EMAIL_TEMPLATE', SITE_PATH . formOption('SITE_EMAIL_TEMPLATE'));
//define('TMP_PATH', SITE_PATH . formOption('TMP_PATH'));

//define('API_KEY', formOption('API_KEY'));
//define('API_TEST', filter_var(formOption('API_TEST'), FILTER_VALIDATE_BOOLEAN));
//define('API_DEBUG', filter_var(formOption('API_DEBUG'), FILTER_VALIDATE_BOOLEAN));
//define('API_RESTRICT', formOption('API_RESTRICT'));
//define('MAXIMUM_PAGE_PRODUCTS', formOption('MAXIMUM_PAGE_PRODUCTS')); // increment of 4 works best
//define('DEFAULT_PRODUCT_IMAGE', formOption('DEFAULT_PRODUCT_IMAGE'));
//define('MOLLIE_API_KEY', formOption('MOLLIE_API_KEY'));
//define('ZIPCODE_API_KEY', formOption('ZIPCODE_API_KEY'));
//define('ZIPCODE_API_SECRET', formOption('ZIPCODE_API_SECRET'));

//setlocale(LC_MONETARY, formOption('LC_MONETARY'));

if (DEV_MODE == true) {
    error_reporting(-1);
} else {
    // Turn off all error reporting
    error_reporting(0);
}

?>