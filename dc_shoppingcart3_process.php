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
require_once('libraries/Mollie/API/Autoloader.php');
require_once('includes/php/dc_mail.php');

// Start API
require_once('libraries/Api_Inktweb/API.class.php');

$mollie = new Mollie_API_Client;
$mollie->setApiKey(MOLLIE_API_KEY);

// New Inktweb Api object
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

//opening database
$objDB = new DB();

// process open orders
$strSQL = "SELECT * FROM ".DB_PREFIX."customers_orders_id WHERE status = 'open' OR status = 'pending' OR status = 'ready'";
$result2 = $objDB->sqlExecute($strSQL);
while($objStatus = $objDB->getObject($result2)) {
	
	if($objStatus->status == 'ready') {
		
		$intTransactionId	= 0;
		$strStatus			= 'paid';
		$intOrderId			= $objStatus->orderId;
		
	} else {
		
		$intTransactionId	= $objStatus->transactionId;
		$payment 			= $mollie->payments->get($intTransactionId);
		$strStatus			= $payment->status;
		$intOrderId			= $payment->metadata->order_id;
		
	}
	
	$strSQL = "UPDATE ".DB_PREFIX."customers_orders_id SET status = '".$strStatus."', transactionId = '".$intTransactionId."' WHERE orderId = ".$intOrderId;
	$result = $objDB->sqlExecute($strSQL);

	if ((isset($payment) && $payment->isPaid() == TRUE) || $objStatus->status == 'ready')
	{
	
		$intPayMethodId = 1;
		
		$strSQL = "SELECT ca_invoice.*, ca_delivery.firstname as delFirstname, ca_delivery.lastname as delLastname, ca_delivery.address as delAddress, ca_delivery.houseNr as delHouseNr, ca_delivery.houseNrAdd as delHouseNrAdd, ca_delivery.zipcode as delZipcode, ca_delivery.city as delCity, ca_delivery.lang as delLang, c.email, c.id as customerId, coi.discountCode " .
			"FROM ".DB_PREFIX."customers c " .
			"INNER JOIN ".DB_PREFIX."customers_addresses ca_invoice ON ca_invoice.custId = c.id AND ca_invoice.defaultInv = 1 " .
			"INNER JOIN ".DB_PREFIX."customers_addresses ca_delivery ON ca_delivery.custId = c.id AND ca_delivery.defaultDel = 1 " .
			"INNER JOIN ".DB_PREFIX."customers_orders_id coi ON coi.customerId = c.id " .
			"WHERE coi.orderId = " . $intOrderId;
		$result = $objDB->sqlExecute($strSQL);
		$objCustomer = $objDB->getObject($result);
		
		$intCustomerId = $objCustomer->customerId;

		// Create unique identifier
		$strSQL = "SELECT UUID()";
		$result_identifier = $objDB->sqlExecute($strSQL);	
		list($intUuid) = $objDB->getRow($result_identifier);

		$strSQL = "INSERT INTO ".DB_PREFIX."customers_orders (uuid, orderId, custId, entryDate, firstname, lastname, address, houseNr, houseNrAdd, zipcode, city, lang, delFirstname, delLastname, delAddress, delHouseNr, delHouseNrAdd, delZipcode, delCity, delLang, email, paymethodId, ip) " .
			"VALUES ('".$intUuid."', ".$intOrderId.", ".$objCustomer->customerId.", NOW(), '".addslashes($objCustomer->firstname)."', '".addslashes($objCustomer->lastname)."', '".addslashes($objCustomer->address)."', '".addslashes($objCustomer->houseNr)."', '".addslashes($objCustomer->houseNrAdd)."', '".addslashes($objCustomer->zipcode)."', '".addslashes($objCustomer->city)."', '".addslashes($objCustomer->lang)."', '".addslashes($objCustomer->delFirstname)."', '".addslashes($objCustomer->delLastname)."', '".addslashes($objCustomer->delAddress)."', '".addslashes($objCustomer->delHouseNr)."', '".addslashes($objCustomer->delHouseNrAdd)."', '".addslashes($objCustomer->delZipcode)."', '".addslashes($objCustomer->delCity)."', '".addslashes($objCustomer->delLang)."', '".$objCustomer->email."', ".$intPayMethodId.", '".ip()."')";
		$result = $objDB->sqlExecute($strSQL);

		$objCart = new Cart();
		$objCart->setDatabaseObject($objDB);
		$objCart->setCustomerId($objCustomer->customerId);
		$resultCart = $objCart->getCart();
		$dblPriceTotal = 0;
		
		while($objCart = $objDB->getObject($resultCart)) {
			
			$Product		= $Api->getProduct($objCart->productId);
			$dblTaxRate		= (float) $Product->getTaxRate();
			$dblPrice 		= (float) calculateProductPrice($Product->getPrice(), $objCart->productId, $objCart->quantity, false);
			$dblPrice_ex		= (float) calculateProductPrice($Product->getPrice(), $objCart->productId, $objCart->quantity, false) / $dblTaxRate;
			$dblPriceTotal 	+= $dblPrice * $objCart->quantity;
			
			$strSQL = "INSERT INTO ".DB_PREFIX."customers_orders_details (orderId, productId, price, tax, quantity) VALUES (".$intOrderId.", ".$objCart->productId.", ".$dblPrice_ex.", ".$dblTaxRate.", ".$objCart->quantity.")";
			$result = $objDB->sqlExecute($strSQL);
			
		}
		
		$dblShippingcosts = calculateSiteShipping($dblPriceTotal, '', false);
		$dblDiscountAmount = "0.00";

		if($objCustomer->discountCode != '') {
		
			$arrDiscount = calculateDiscount($objCustomer->discountCode, $objCustomer->customerId);
		
			$dblPriceTotal	= $arrDiscount['dblPriceTotal'];
			
			$strSQL = "UPDATE ".DB_PREFIX."discountcodes_codes SET orderId = ".$intOrderId." WHERE code = '".$objCustomer->discountCode."'";
			$result = $objDB->sqlExecute($strSQL);

			$dblDiscountAmount	= $arrDiscount['dblDiscountAmount'];
			$dblDiscountOver	= $arrDiscount['dblDiscountOver'];
		
			if($dblDiscountOver > 0) {
		
				$dblShippingcosts = $dblShippingcosts - $dblDiscountOver;
		
				if($dblShippingcosts < 0) $dblShippingcosts = 0;
				
				$dblDiscountAmount = $dblDiscountAmount - (SITE_SHIPPING - $dblShippingcosts);
				
				generateCodeRemaingValue($objCustomer->discountCode, $dblDiscountAmount, $intOrderId);
			}
						
		}
		
		$dblPriceTotal += $dblShippingcosts;

		$dblShippingcosts = calculateSiteShipping($dblPriceTotal, '', false);

		$strSQL = 
			"UPDATE ".DB_PREFIX."customers_orders 
			SET shippingCosts = ".$dblShippingcosts.", 
			totalPrice = ".$dblPriceTotal.", 
			kortingscode = '".$objCustomer->discountCode."', 
			kortingsbedrag = '".$dblDiscountAmount."',
			paymentStatus = '1' 
			WHERE orderId = ".$intOrderId;
		$result = $objDB->sqlExecute($strSQL);
		
		// import order
		$strJsonUrl = SITE_URL . 'beheer/dc_order_manage.php?action=export&uuid='.$intUuid.'&id='.$intOrderId;
		$Order	= $Api->importOrder($strJsonUrl);
		
		$intExtOrderId = ($Order->orderId != '') ? $Order->orderId : 0;
		
		$strSQL = "UPDATE ".DB_PREFIX."customers_orders SET extOrderId = ".$intExtOrderId." WHERE orderId = ".$intOrderId;
		$result = $objDB->sqlExecute($strSQL);
		
		$objDB->sqlDelete("cart","customerId",$objCustomer->customerId,'');
		
		// send order mail
		sendMail('ordermail', $objCustomer->email, $objCustomer->firstname . ' ' . $objCustomer->lastname);
					
	}

}

if(isset($_SESSION["customerId"])) {
	// Only redirect if this page visited by customer
	header("Location: dc_shoppingcart4.php?order_id=".$_GET["orderId"]);
}

?>