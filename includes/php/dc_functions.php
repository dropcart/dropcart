<?php

require_once (SITE_PATH.'includes/dc_functions_global.php');

function doLogin($strEmail, $strPassword) {

	global $objDB;	

	//check if login exists
	$strSQL = "SELECT id, password FROM ".DB_PREFIX."customers WHERE email='".$strEmail."'";
	$result = $objDB->sqlExecute($strSQL);
	list($intId, $hashAndSalt) = $objDB->getRow($result);
	
	$blnOK = false;

	if(!empty($intId)) {
		
		if (password_verify($strPassword, $hashAndSalt)) {
			
			//set session
			$_SESSION["customerId"] = $intId;
			
			//creating cart object
			$objCart = new Cart();
			$objCart->setDatabaseObject($objDB);
			$objCart->setSessionId(session_id());
			$objCart->setCustomerId($_SESSION["customerId"]);
			
			//transfer cart to customerId
			$objCart->transferCart();
			
			//return
			$blnOk = true;
	
		}
	
	}
	
	//return
	return $blnOk;
}

function calculateDiscount($strDiscountCode, $intCustId = '') {
	
	global $objDB;
	global $intSessionId;
	global $intCustomerId;
	
	$strSQL = "
		SELECT
			*,
			dc_c.id as codeId,
			IF (dc.discountType = 'dynamic', dc_c.discountValue, dc.discountValue) AS discountValue,
			IF (dc.discountType = 'dynamic', dc_c.discountType, dc.discountType) AS discountType
		FROM ".DB_PREFIX."discountcodes dc
		INNER JOIN ".DB_PREFIX."discountcodes_codes dc_c ON dc_c.codeId = dc.id
		WHERE dc_c.code = '".$strDiscountCode."' ";
	$result = $objDB->sqlExecute($strSQL);
	$objCode = $objDB->getObject($result);
	
	// New Inktweb Api object
	$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);
	
	// New Cartobject
	$objCart = new Cart();
	$objCart->setDatabaseObject($objDB);
	if($intCustId != '') {
		
		$objCart->setCustomerId($intCustId);
		
	} else {
		
		$objCart->setSessionId($intSessionId);
		$objCart->setCustomerId($intCustomerId);
		
	}

	//getting cart
	$result_cart = $objCart->getCart();
	$dblPriceTotal = 0;
	
	//calculating total
	while($objCart = $objDB->getObject($result_cart)) {
		
		$Product	= $Api->getProduct($objCart->productId);
		$dblPrice 	= calculateProductPrice($Product->getPrice(), $objCart->productId, false);
				
		//adding to totals
		$dblPriceTotal += ($dblPrice * $objCart->quantity);
	}
	
	$dblPriceTotalOld = $dblPriceTotal;
	
	if($objCode->discountType == "price") {
		
		$dblPriceTotal = $dblPriceTotal - $objCode->discountValue;
		
		if($dblPriceTotal < 0) {
			
			$dblPriceTotal = 0;
		
		}
		
		$dblDiscountAmount	= $dblPriceTotal - $dblPriceTotalOld;
		$dblDiscountOver	= $objCode->discountValue + $dblDiscountAmount;

		$strDiscountAmount = '€ ' . number_format($dblDiscountAmount,2,',', ' ');
		
	} elseif($objCode->discountType == "percentage") {

		$dblDiscountAmount = $objCode->discountValue;
		$strDiscountAmount = $dblDiscountAmount . "%"; 
		
		$discountValue = (100 - $objCode->discountValue) / 100;
		$dblPriceTotal = $dblPriceTotal * $discountValue;
		$dblDiscountOver = 0;
		
	}
	
	return array(
		'dblDiscountAmount' => $dblDiscountAmount,
		'strDiscountAmount' => $strDiscountAmount,
		'dblDiscountOver' => $dblDiscountOver,
		'dblPriceTotal' => $dblPriceTotal)
	;
	
}

function generateCodeRemaingValue($strDiscountCode, $dblDiscountAmount, $intOrderId) {

	global $objDB;

	$strSQL = "SELECT *, dc_c.id as codeId, dc.discountValue, dc.discountType " .
		"FROM ".DB_PREFIX."discountcodes dc " .
		"INNER JOIN ".DB_PREFIX."discountcodes_codes dc_c ON dc_c.codeId = dc.id " .
		"WHERE dc_c.parentOrderId = 0 AND dc_c.code = '".$strDiscountCode."' ";
	$result = $objDB->sqlExecute($strSQL);
	$objCode = $objDB->getObject($result);

	if($objCode->discountType == "price") {
		
		$dblDiscountOver = $dblDiscountAmount + $objCode->discountValue;
		
		if($dblDiscountOver < 0) {
			
			$dblDiscountOver = 0;
		
		}
		
	}

	if($dblDiscountOver > 0) {
	
		$strChars	= "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$strCode	= "CRT" . substr( str_shuffle( $strChars ), 0, 5);
	
		$strSQL = "INSERT INTO dc_discountcodes_codes (codeId, parentOrderId, code, discountValue, discountType) VALUES (7, ".$intOrderId.", '".$strCode."', ".$dblDiscountOver.", 'price')";
		$result = $objDB->sqlExecute($strSQL);
		
		return $strCode;
		
	} else {
		
		return '';
		
	}
	
}

?>