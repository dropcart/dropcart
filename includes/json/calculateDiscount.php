<?php
session_start();

// Required includes
require_once('../php/dc_connect.php');
require_once('../../_classes/class.database.php');
$objDB = new DB();
require_once('../php/dc_config.php');

// Page specific includes
require_once('../../_classes/class.cart.php');
require_once('../php/dc_functions.php');

// Start API
require_once('../../libraries/Api_Inktweb/API.class.php');

$intSessionId 	= session_id();
$intCustomerId 	= (isset($_SESSION["customerId"])) ? $_SESSION["customerId"] : 0;

// New Databaseobject
$objDB = new DB();

$strDiscountCode	= (isset($_POST["code"])) ? sanitize($_POST["code"]) : '';
$strValidationCode	= (isset($_POST["validationCode"])) ? sanitize($_POST["validationCode"]) : '';

$strSQL = "SELECT *, dc_c.id as codeId " .
	"FROM ".DB_PREFIX."discountcodes dc " .
	"INNER JOIN ".DB_PREFIX."discountcodes_codes dc_c ON dc_c.codeId = dc.id " .
	"WHERE dc_c.code = '".$strDiscountCode."' ";
$result = $objDB->sqlExecute($strSQL);
$intCodeExists = $objDB->getNumRows($result);
$objCode = $objDB->getObject($result);

$intError = 0;

if($intError == 0) {
	// Check code exists
	
	if($intCodeExists == 0) {
	
	$arrOutput['error_code']	= 400;
	$arrOutput['error'] 		= 'Onbekende code ingevoerd.';
	$intError					= 1;
	
	}

}

if($intError == 0) {
	// Check code is not expired
	
	$objCurDate = new DateTime('now');
	$objExpDate = new DateTime($objCode->validTill);
	
	if($objCurDate > $objExpDate) {
		
		$arrOutput['error_code']	= 400;
		$arrOutput['error']			= 'Deze code is niet meer geldig.';
		$intError					= 1;
		
	}

}

if($intError == 0) {
	// Check code is not used
	
	if($objCode->orderId != 0) {
		
		$arrOutput['error_code']	= 400;
		$arrOutput['error']			= 'Deze code is al gebruikt.';
		$intError					= 1;

	}

}

if($intError == 0) {
	// Check if validation code is needed

	if($objCode->validationCodeRequired == 1) {

		if($strValidationCode == '') {
	
			$arrOutput['validationRequired']	= 1;
		
		} else {
		
			if(strlen($strValidationCode) != 10 || $strValidationCode == $strDiscountCode) {
				
				$arrOutput['error']	= 'Onjuiste controlecode ingevoerd.';

				if (strlen($strValidationCode) != 10) {
					$arrOutput['error']	.= ' Deze code moet exact 10 cijfers zijn (ingevoerd: '.strlen($strValidationCode).'). ';
				}

				if ($strValidationCode == $strDiscountCode) {
					$arrOutput['error']	.= ' Deze code mag niet overeen komen met uw Grouponcode';
				}

				$arrOutput['error_code']	= 400;
				
				$intError			= 1;
				
			} else {
				
				$strSQL = "UPDATE ".DB_PREFIX."discountcodes_codes SET validationCode = '".$strValidationCode."' WHERE id = '".$objCode->codeId."'";
				$result = $objDB->sqlExecute($strSQL);
				
			}
			
		}
		
	}
	
}

if($intError == 0 && empty($arrOutput['validationRequired'])) {
	// Everything looks good, discount valid
		
	$arrDiscount		= calculateDiscount($strDiscountCode);	

	$strDiscountAmount	= $arrDiscount['strDiscountAmount'];
	$dblDiscountOver	= $arrDiscount['dblDiscountOver'];
	$dblPriceTotal	= $arrDiscount['dblPriceTotal'];
	$dblShippingCosts	= calculateSiteShipping($dblPriceTotal, '', false);
	$strShippingCosts	= money_format('%(#1n', $dblShippingCosts);
	

	// check if remaning discount should be removed from shippingcosts
	// 1 = true
	// 0 = false
	if ($objCode->fixedShipping == 0) {

		if($dblDiscountOver > 0) {
			$dblShippingcosts = $dblShippingcosts - $dblDiscountOver;
			if($dblShippingcosts < 0) $dblShippingcosts = 0;
		}

	}

	$arrOutput['valid']				= 1;
	$arrOutput['discountCode']			= $strDiscountCode;
	$arrOutput['cartDiscountAmount']		= $strDiscountAmount;
	$arrOutput['cartSubTotal']			= money_format('%(#1n', $dblPriceTotal);
	$arrOutput['cartShippingcosts']		= $strShippingCosts;
	$arrOutput['cartTotal']			= money_format('%(#1n', $dblPriceTotal + $dblShippingcosts);
	
	$_SESSION["discountCode"]		= $strDiscountCode;
	$_SESSION["validationCode"]		= $strValidationCode;
	
}

if($intError == 1) {

	header(' ', true, $arrOutput['error_code']);
	
}

echo json_encode($arrOutput);

?>