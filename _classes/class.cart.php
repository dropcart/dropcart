<?php
class Cart {
	//setting vars
	var $objDB;
	var $intSessionId = 0;
	var $intCustomerId = 0;
	
	//setting database object to use
	function setDatabaseObject($objDB) {
		$this->objDB = $objDB;
	}
	
	//setting sessionid of user
	function setSessionId($intSessionId) {
		//setting session id
		$this->intSessionId = $intSessionId;
	}
	
	//setting customerid if user is logged in
	function setCustomerId($intCustomerId) {
		//setting session id
		$this->intCustomerId = $intCustomerId;
	}
	
	function getOrderCart() {
		//setting session id
		$this->order_cart = 1;
	}
	
	//getting cart of user
	function getCart() {

		//checking if user is logged in, if not, use sessionId	
		if(!empty($this->intCustomerId)) $strExtraSQL = "AND c.customerId=".$this->intCustomerId;
		else $strExtraSQL = "AND c.sessionId='".$this->intSessionId."'";
		
		//getting cart of user
		$strSQL = "SELECT c.* " .
					"FROM ".DB_PREFIX."cart c " .
					"WHERE 1 " . $strExtraSQL;
		$result = $this->objDB->sqlExecute($strSQL);

		//return
		return $result;
	}
	
	function setCartToOrder() {
		
		//checking if user is logged in, if not, use sessionId
		if(!empty($this->intCustomerId)) {
			$this->intSessionId = 0;
			$strExtraSQL = "WHERE customerId=".$this->intCustomerId;
		}
		else {
			$this->intCustomerId = 0;
			$strExtraSQL = "WHERE sessionId='".$this->intSessionId."'";
		}
		
		//removing product from cart
		$strSQL = "DELETE FROM ".DB_PREFIX."cart_order ".$strExtraSQL;
		$this->objDB->sqlExecute($strSQL);

		//get cart of current session
		$strSQL = "SELECT productId, quantity FROM ".DB_PREFIX."cart ".$strExtraSQL;
		$result_products = $this->objDB->sqlExecute($strSQL);
		
		while($objProduct = $this->objDB->getObject($result_products)) {
			$intProductId = $objProduct->productId;
			$intQuantity = $objProduct->quantity;
		
			//adding product to cart
			$strSQL = "INSERT INTO ".DB_PREFIX."cart_order(sessionId, customerId, productId, entryDate, quantity) " .
						"VALUES('".$this->intSessionId."', ".$this->intCustomerId.", ".$intProductId.", ".mktime().", ".$intQuantity.")";
			$this->objDB->sqlExecute($strSQL);
		}
	}
	
	//updating cart
	function updateCart() {
		//getting cart
		$result = $this->getCart();
		
		//looping through cart
		while($objProduct = $this->objDB->getObject($result)) {
			//getting vars
			$intQuantity = intval($_POST["quantity".$objProduct->id]);
			
			//product
			if($intQuantity > 0) $this->updateProductQuantity($objProduct->id, $intQuantity); //update
			else $this->deleteProduct($objProduct->id); //delete
		}
	}
	
	//transfer cart
	function transferCart() {
		//update cart items if customer has cart
		$intNumberOfItems = $this->objDB->getRecordCount("cart", "id", "WHERE customerId=".$this->intCustomerId);
		
		if($intNumberOfItems > 0) {
			//get cart of current session
			$strSQL = "SELECT productId, quantity FROM ".DB_PREFIX."cart WHERE sessionId='".$this->intSessionId."'";
			$result_products = $this->objDB->sqlExecute($strSQL);
			
			while($objProduct = $this->objDB->getObject($result_products)) {
				//check if customer has similar products in cart; update if neccesary
				$intNumberOfItems = $this->objDB->getRecordCount("cart", "id", "WHERE customerId=".$this->intCustomerId." AND productId=".$objProduct->productId);
				
				//update existing items
				if($intNumberOfItems > 0) {
					$strSQL = "UPDATE ".DB_PREFIX."cart SET quantity=quantity+".$objProduct->quantity." WHERE productId=".$objProduct->productId." AND customerId=".$this->intCustomerId;
					$this->objDB->sqlExecute($strSQL);
					
					//delete updated item
					$this->objDB->sqlDelete("cart", "productId", $objProduct->productId, "AND sessionId='".$this->intSessionId."' ");
				}
				//transferring new items in cart from session to customerId
				else {
					$strSQL = "UPDATE ".DB_PREFIX."cart " .
								"SET sessionId=0, " .
								"customerId=".$this->intCustomerId." " .
								"WHERE sessionId='".$this->intSessionId."'";
					$this->objDB->sqlExecute($strSQL);
				}
			}
		}
		else {
			//transferring items in cart from session to customerId
			$strSQL = "UPDATE ".DB_PREFIX."cart " .
						"SET sessionId=0, " .
						"customerId=".$this->intCustomerId." " .
						"WHERE sessionId='".$this->intSessionId."'";
			$this->objDB->sqlExecute($strSQL);
		}
	}
	
	//adding product to cart
	function addProduct($intProductId, $intQuantity) {
		//checking if user is logged in, if not, use sessionId
		if(!empty($this->intCustomerId)) {
			$this->intSessionId = 0;
			$strExtraSQL = "AND customerId=".$this->intCustomerId;
		}
		else {
			$this->intCustomerId = 0;
			$strExtraSQL = "AND sessionId='".$this->intSessionId."'";
		}

		// Dont allow negative values to be inserted
		if (empty($intQuantity) OR $intQuantity < 1) {
			$intQuantity = 1;
		}

		//checking if product already exists in cart
		$intNrOf = $this->objDB->getRecordCount("cart","id","WHERE productId=".$intProductId." ".$strExtraSQL);
		if($intNrOf == 0) {
			//adding product to cart
			$strSQL = "INSERT INTO ".DB_PREFIX."cart(sessionId, customerId, productId, entryDate, quantity) " .
						"VALUES('".$this->intSessionId."', ".$this->intCustomerId.", ".$intProductId.", NOW(), ".$intQuantity.")";
			$this->objDB->sqlExecute($strSQL);
		}
		else {
			//updating quantity of product
			$strSQL = "UPDATE ".DB_PREFIX."cart " .
						"SET quantity = quantity + ".$intQuantity." " .
						"WHERE productId=".$intProductId." " .
						$strExtraSQL;
			$this->objDB->sqlExecute($strSQL);
		}
	}
	
	//updating quantity of product in cart
	function updateProductQuantity($intCartId, $intQuantity) {
		//checking if user is logged in, if not, use sessionId	
		if(!empty($this->intCustomerId)) $strExtraSQL = "AND customerId=".$this->intCustomerId;
		else $strExtraSQL = "AND sessionId='".$this->intSessionId."'";
		
		//updating quantity
		$strSQL = "UPDATE ".DB_PREFIX."cart " .
					"SET quantity = ".$intQuantity." " .
					"WHERE id=".$intCartId." " .
					$strExtraSQL;
		$this->objDB->sqlExecute($strSQL);
	}
	
	//deleting product from cart
	function deleteProduct($intCartId) {
		//checking if user is logged in, if not, use sessionId	
		if(!empty($this->intCustomerId)) $strExtraSQL = "AND customerId=".$this->intCustomerId;
		else $strExtraSQL = "AND sessionId='".$this->intSessionId."'";
		
		//removing product from cart
		$strSQL = "DELETE FROM ".DB_PREFIX."cart " .
					"WHERE id=".$intCartId." " .
					$strExtraSQL;
		$this->objDB->sqlExecute($strSQL);
	}

	function deleteProductById($intProductId) {
		//checking if user is logged in, if not, use sessionId	
		if(!empty($this->intCustomerId)) $strExtraSQL = "AND customerId=".$this->intCustomerId;
		else $strExtraSQL = "AND sessionId='".$this->intSessionId."'";
		
		//removing product from cart
		$strSQL = "DELETE FROM ".DB_PREFIX."cart " .
					"WHERE productId=".$intProductId." " .
					$strExtraSQL;
		$this->objDB->sqlExecute($strSQL);		
	}
	
	//empty cart
	function emptyCart() {
		//emptying cart
		$strSQL = "DELETE FROM ".DB_PREFIX."cart " .
					"WHERE customerId=".$this->intCustomerId;
		$this->objDB->sqlExecute($strSQL);
	}
	
}
 ?>