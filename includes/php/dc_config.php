<?php

/**
 * Function to get options and values from the database
 * @param  string optionName as used in the database 'dc_options'
 * @return string optionValue 
 */
function formOption($optionName) {
	global $objDB;

	$optionName = strtolower($optionName);

	$strSQL = "SELECT optionValue FROM ".DB_PREFIX."options WHERE optionName = '".$optionName."' ";
	$result = $objDB->sqlExecute($strSQL);
	list($optionValue) = $objDB->getRow($result);

	return $optionValue;
}

define('DROPCART_VERSION', formOption('DROPCART_VERSION'));

define('SITE_URL', formOption('SITE_URL')); // ends in a slash
define('SITE_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
define('SITE_NAME', formOption('SITE_NAME'));
define('SITE_SHIPPING', formOption('SITE_SHIPPING'));
define('SITE_EMAIL_TEMPLATE', SITE_PATH.formOption('SITE_EMAIL_TEMPLATE'));
define('TMP_PATH', SITE_PATH.formOption('TMP_PATH'));

define('API_KEY', formOption('API_KEY'));
define('API_TEST', filter_var(formOption('API_TEST'), FILTER_VALIDATE_BOOLEAN));
define('API_DEBUG', filter_var(formOption('API_DEBUG'), FILTER_VALIDATE_BOOLEAN));
define('API_RESTRICT', formOption('API_RESTRICT'));

define('MAXIMUM_PAGE_PRODUCTS', formOption('MAXIMUM_PAGE_PRODUCTS')); // increment of 4 works best
define('DEFAULT_PRODUCT_IMAGE', formOption('DEFAULT_PRODUCT_IMAGE'));

define('MOLLIE_API_KEY', formOption('MOLLIE_API_KEY'));

define('ZIPCODE_API_KEY', formOption('ZIPCODE_API_KEY'));
define('ZIPCODE_API_SECRET', formOption('ZIPCODE_API_SECRET'));

setlocale(LC_MONETARY, formOption('LC_MONETARY'));

// Turn off all error reporting
error_reporting(0);

?>