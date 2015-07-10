<?php

/*

Global includes, for every function that needs to work in both CMS and Live website
For CMS use: /beheer/includes/php/dc_functions.php
For Live use: /includes/php/dc_functions.php

*/

if( !function_exists('money_format') ){
	function money_format($format, $number)
	{
		$regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?'.
			'(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
		if (setlocale(LC_MONETARY, 0) == 'C') {
			setlocale(LC_MONETARY, '');
		}
		$locale = localeconv();
		preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
		foreach ($matches as $fmatch) {
			$value = floatval($number);
			$flags = array(
				'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ?
					$match[1] : ' ',
				'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
				'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
					$match[0] : '+',
				'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
				'isleft'    => preg_match('/\-/', $fmatch[1]) > 0
			);
			$width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
			$left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
			$right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits'];
			$conversion = $fmatch[5];

			$positive = true;
			if ($value < 0) {
				$positive = false;
				$value  *= -1;
			}
			$letter = $positive ? 'p' : 'n';

			$prefix = $suffix = $cprefix = $csuffix = $signal = '';

			$signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
			switch (true) {
				case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
					$prefix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
					$suffix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
					$cprefix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
					$csuffix = $signal;
					break;
				case $flags['usesignal'] == '(':
				case $locale["{$letter}_sign_posn"] == 0:
					$prefix = '(';
					$suffix = ')';
					break;
			}
			if (!$flags['nosimbol']) {
				$currency = $cprefix .
					($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
					$csuffix;
			} else {
				$currency = '';
			}
			$space  = $locale["{$letter}_sep_by_space"] ? ' ' : '';

			$value = number_format($value, $right, $locale['mon_decimal_point'],
				$flags['nogroup'] ? '' : $locale['mon_thousands_sep']);
			$value = @explode($locale['mon_decimal_point'], $value);

			$n = strlen($prefix) + strlen($currency) + strlen($value[0]);
			if ($left > 0 && $left > $n) {
				$value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
			}
			$value = implode($locale['mon_decimal_point'], $value);
			if ($locale["{$letter}_cs_precedes"]) {
				$value = $prefix . $currency . $space . $value . $suffix;
			} else {
				$value = $prefix . $value . $space . $currency . $suffix;
			}
			if ($width > 0) {
				$value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
					STR_PAD_RIGHT : STR_PAD_LEFT);
			}

			$format = str_replace($fmatch[0], $value, $format);
		}
		return $format;
	}
}

function pre($strPre, $exit = false){
	echo '<pre>';
	print_r($strPre);
	echo '</pre>';

	if ($exit == true) {
		exit();
	}
}

function cleanInput($input) {

	// http://css-tricks.com/snippets/php/sanitize-database-inputs/

	$search = array(
	'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
	'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
	'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
	'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
	);

	$output = preg_replace($search, '', $input);
	return $output;
}

function sanitize($input) {

	global $objDB;
	$output = null;

	// http://css-tricks.com/snippets/php/sanitize-database-inputs/

	if (is_array($input)) {
		foreach($input as $var=>$val) {
			$output[$var] = sanitize($val);
		}
	}
	else {
		$input  = cleanInput($input);
		$output = $objDB->escapeString($input);
	}
	return $output;
}


function curPage() {
	 return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}

function ip() {
	if(getenv("HTTP_X_FORWARDED_FOR"))
	{
		$IPadres = getenv("HTTP_X_FORWARDED_FOR");
	}
	elseif(getenv("HTTP_CLIENT_IP"))
	{
		$IPadres = getenv("HTTP_CLIENT_IP");
	}
	else
	{
		$IPadres = $_SERVER["REMOTE_ADDR"];
	}

	return $IPadres;
}

function calculateProductPrice($objPrice, $productId = null, $quantity = null, $format = true) {

	global $objDB;

	// if $productId is filled in, check if price is in DB
	if (!empty($productId)) {

		$strSQL 	= "SELECT price FROM ".DB_PREFIX."products WHERE id = '".$productId."' ";
		$result 	= $objDB->sqlExecute($strSQL);
		list($price) 	= $objDB->getRow($result);
	}
	// Use DB price ifset ELSE default formula
	if (!empty($price)) {
		$productPrice = $price;

		// Check for tiered pricing
		if (!empty($quantity)) {

			// Returns the highest percentage discount for this quantity
			$strSQL = "SELECT percentage FROM ".DB_PREFIX."products_tiered WHERE productId = '".$productId."' AND quantity <= '".$quantity."' ORDER BY quantity DESC LIMIT 1 ";
			$result = $objDB->sqlExecute($strSQL);
			list($intDiscountPercentage) = $objDB->getRow($result);

			if (!empty($intDiscountPercentage)) {
				$discountPerProduct = ($productPrice / 100) * $intDiscountPercentage;
				$productPrice = $productPrice - $discountPerProduct;
			}	


		}

	}
	else {

		// get price
		$strSQL 	= "SELECT optionValue FROM ".DB_PREFIX."options WHERE optionName = 'price_base'";
		$result 	= $objDB->sqlExecute($strSQL);
		list($price_base) = $objDB->getRow($result);

		if ($price_base == "purchase") {
			$apiPrice = $objPrice->pricePurchase;
		}
		elseif ($price_base == "msrp") {
			$apiPrice = $objPrice->priceMSRP;
		}
		else {
			$apiPrice = $objPrice->price;
		}

		$priceOperators = json_decode(formOption('price_operators'));
		$priceValues = json_decode(formOption('price_values'));


		$productPrice = $apiPrice;

		for($i=0; $i< count($priceOperators); $i++) {
			$operator = $priceOperators[$i];
			switch($operator) {
				case '*':
					$productPrice *= $priceValues[$i];
				break;
				case '+':
					$productPrice += $priceValues[$i];
				break;
				case '-':
					$productPrice -= $priceValues[$i];
				break;
			}
		}

		// TODO: ceil / floor for rounding
		$productPrice = round($productPrice, 2);
	}

	if ($format == true) {
		$productPrice = money_format('%(#1n', $productPrice);
	}
	return $productPrice;

}



function calculateSiteShipping($cartTotal = 0, $deliveryLang = null, $format = true) {

	global $objDB;
	$shippingCosts = SITE_SHIPPING;

	if ($cartTotal > 0) {
		$freeShippingFrom = formOption('site_shipping_free_from');

		// If shipping is not 0 and current amount exceeds "free_shipping"
		if (!empty($freeShippingFrom) AND $cartTotal >= $freeShippingFrom) {
			$shippingCosts = "0.00";
		}
	}

	// @todo: dynamic based on delivery lang


	if ($format == true) {
		$shippingCosts = money_format('%(#1n', $shippingCosts);
	}
	return $shippingCosts;
}

/**
 * Get content from the database. Could be default values for products or more advanced like the text for the footer
 * @param  str $strContent how the content is called in the db
 * @param  bool $parse parse all content by default, set to 'false' to stop parsing content
 * @param  obj $Product  used for 'product content' PRODUCT_ boilerplate
 * @param  obj $arrProducts  used for 'product content' PRINTER_ boilerplate
 * @return content w/ all variables replaced
 */
function getContent($strContent, $parse = true, $Product = null, $arrProducts = null) {

	global $objDB;

	$strContent = strtolower($strContent);
	$strSQL = "SELECT value, parse_markdown, parse_boilerplate FROM ".DB_PREFIX."content WHERE name = '".$strContent."'";
	$result = $objDB->sqlExecute($strSQL);
	$objContent = $objDB->getObject($result);

	$strContent = $objContent->value;

	if (!empty($objContent->parse_boilerplate) AND $parse == true) {
		$strContent = parseBoilerplate($strContent, $Product, $arrProducts);
	}

	if (!empty($objContent->parse_markdown) AND $parse == true) {
		$strContent = parseMarkdown($strContent);
	}

	return $strContent;
}

/**
 * parse **Markdown** syntax to more human readable content
 */
function parseMarkdown($content) {

	require_once ($_SERVER['DOCUMENT_ROOT'].'/libraries/Parsedown/Parsedown.php');

	$Parsedown = new Parsedown();
	$content = $Parsedown->text($content);
	return $content;

}

/**
 * parses a bunch of text and replaces values as defined in `content_boilerplate` with variables
 * @param  str $content the text that needs parsing
 * @param  obj $Product needs to be set for [PRODUCT_] replacing
 * @param  obj $arrProducts needs to be set for [PRINTER_] replacing
 * @return str $content but now fully parsed
 */
function parseBoilerplate($content, $Product = null, $arrProducts = null) {

	global $objDB;

	$arrReplace = array();

	// Product is set so its likely to be PRODUCT_ related
	if (!empty($Product)) {

		$arrReplace['[PRODUCT_ID]'] 		= $Product->getId();
		$arrReplace['[PRODUCT_EAN]'] 		= $Product->getEan();
		$arrReplace['[PRODUCT_OEM]'] 		= $Product->getOem();
		$arrReplace['[PRODUCT_BRAND]']	= $Product->getBrand();
		$arrReplace['[PRODUCT_TITLE]'] 		= $Product->getTitle();

		$objPrice 					= $Product->getPrice();
		$strPrice 					= calculateProductPrice($objPrice, $Product->getId());
		$arrReplace['[PRODUCT_PRICE]'] 	= $strPrice;

		// check if PRODUCT_SPEC_
		if (strpos($content, '[PRODUCT_SPEC_') > -1) {

			foreach ($Product->getSpecifications() AS $spec) {

				if ($spec->label == "Kleur") {
					$spec_color = $spec->value;
				}

				if ($spec->label == "Gewicht") {
					$spec_weight = $spec->value;
				}

				if ($spec->label == "Inhoud") {
					$spec_capacity = $spec->value;
				}

				if ($spec->label == "Aantal mililiter") {
					$spec_ml = $spec->value . $spec->unit;
				}

				if ($spec->label == "Aantal  pagina's") {
					$spec_pages = $spec->value . $spec->unit;
				}

				if ($spec->label == "Type") {
					$spec_type = $spec->value;
				}
			}

			// @ ignores warnings/errors like "not set"
			$arrReplace['[PRODUCT_SPEC_COLOR]'] 	= @$spec_color;
			$arrReplace['[PRODUCT_SPEC_WEIGHT]'] 	= @$spec_weight;
			$arrReplace['[PRODUCT_SPEC_CAPACITY]'] 	= @$spec_capacity;
			$arrReplace['[PRODUCT_SPEC_ML]'] 		= @$spec_ml;
			$arrReplace['[PRODUCT_SPEC_PAGES]'] 	= @$spec_pages;
			$arrReplace['[PRODUCT_SPEC_TYPE]'] 		= @$spec_type;

		}

	}

	// check if SITE_
	if (strpos($content, '[SITE_') > -1) {

		$arrReplace['[SITE_NAME]'] 		= formOption('site_name');
		$arrReplace['[SITE_URL]'] 			= formOption('site_url');
		$arrReplace['[SITE_EMAIL]']		= formOption('site_email');
	}

	// check if PRINTER_
	if (!empty($arrProducts)) {

		$arrReplace['[PRINTER_BRAND]'] 		= $arrProducts->printers->brand;
		$arrReplace['[PRINTER_SERIE]'] 		= $arrProducts->printers->serie;
		$arrReplace['[PRINTER_TYPE]']		= $arrProducts->printers->printer;
	}

	$content = str_replace (
		array_keys($arrReplace),
		array_values($arrReplace),
		$content
	);

	return $content;

}

/**
 * Check for custom product title, else use boilerplate
 * @param  object $objProduct complete API object
 * @param  int $productId
 * @return str max 255 chars product title
 */
function getProductTitle($objProduct, $productId = null) {

	global $objDB;

	// if $productId is entered, check if it has a custom title in DB
	if (!empty($productId)) {

		$strSQL 	= "SELECT title FROM ".DB_PREFIX."products WHERE id = '".$productId."' ";
		$result 	= $objDB->sqlExecute($strSQL);
		list($strTitle) 	= $objDB->getRow($result);

	}

	// empty means database didn't contain a custom title
	// fallback to boilerplate content
	if (empty($strTitle)) {

		$strSQL 	= "SELECT value FROM ".DB_PREFIX."content WHERE name = 'default_product_title'";
		$result 	= $objDB->sqlExecute($strSQL);
		list($boilerContent) = $objDB->getRow($result);
		$strTitle 	= parseBoilerplate($boilerContent, $objProduct);
	}

	return $strTitle;

}

/**
 * Check for custom product description, else use boilerplate
 * @param  object $objProduct complete API object
 * @param  int $productId
 * @return str description of product, may contain html/boilerplate markup in the future
 */
function getProductDesc($objProduct, $productId = null) {

	global $objDB;
	global $Parsedown;

	// if $productId is entered, check if it has a custom desc in DB
	if (!empty($productId)) {

		$strSQL 	= "SELECT description FROM ".DB_PREFIX."products WHERE id = '".$productId."' ";
		$result 	= $objDB->sqlExecute($strSQL);
		list($strDesc) 	= $objDB->getRow($result);

		if (!empty($strDesc)) {
			$strDesc = $Parsedown->text($strDesc);
		}

	}

	// empty means database didn't contain a custom desc
	// fallback to boilerplate content
	if (empty($strDesc)) {

		$strSQL 	= "SELECT value FROM ".DB_PREFIX."content WHERE name = 'default_product_content'";
		$result 	= $objDB->sqlExecute($strSQL);
		list($boilerContent) = $objDB->getRow($result);
		$strDesc 	= parseBoilerplate($boilerContent, $objProduct);
	}

	return $strDesc;
}


/**
 * returns description of Inktweb.nl orderstatus based on databaseId
 * @param  int database status id returned from API
 * @return str textual description of what the status means
 */
function getStatusDesc($intOrderStatus) {

	switch($intOrderStatus) {
		case 0:
			$strStatus = "Nieuw";
			break;
		case 1:
			$strStatus = "In behandeling genomen";
			break;
		case 2:
			$strStatus = "Producten niet op voorraad";
			break;
		case 3:
			$strStatus = "Vandaag versturen";
			break;
		case 4:
			$strStatus = "Verstuurd";
			break;
		case 5:
			$strStatus = "In behandeling genomen";
			break;
		case 6:
			$strStatus = "In behandeling genomen";
			break;
		case 7:
			$strStatus = "Wachten op betaling";
			break;
		case 8:
			$strStatus = "Afgehaald";
			break;
		case 9:
			$strStatus = "Kan worden afgehaald";
			break;
		case 10:
			$strStatus = "Wachtlijst versturen";
			break;
		case 11:
			$strStatus = "Gereserveerd";
			break;
		case 99:
			$strStatus = "Verwijderd";
			break;
		default:
			$strStatus = "Nieuw";
	}

	return $strStatus;
}

function deleteDir($dir) {
	$iterator = new RecursiveDirectoryIterator($dir);
	foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {

		if ($file->isDir()) {
			rmdir($file->getPathname());
		} else {
			unlink($file->getPathname());
		}
	}
	rmdir($dir);
}

function generateInvoicePDF($intOrderId, $blnDownload = false) {

	global $Api;
	global $twig;
	global $mpdf;
	global $objDB;

	$intOrderId = intval($intOrderId);

	$tpl = $twig->loadTemplate( "dc_invoice_template.tpl" );

	$settings = array();

	/* get site settings */
	$siteSettingsSQL = "SELECT * FROM dc_options";
	$resultSettings = $objDB->sqlExecute($siteSettingsSQL);
	while($row = $objDB->getObject($resultSettings) ){
		$settings['_'.$row->optionName] = $row->optionValue;
	}


	// order information
	$strSQL =
		"SELECT co.firstname,
		co.lastname,
		co.address,
		CONCAT(co.houseNr, co.houseNrAdd),
		co.zipcode,
		co.city,
		co.lang,
		co.custId,
		co.orderId,
		DATE_FORMAT(co.entryDate, '%d-%m-%Y') as entryDate,
		co.kortingscode,
		co.kortingsbedrag,
		co.shippingCosts,
		co.totalPrice
		FROM ".DB_PREFIX."customers_orders co
		WHERE co.orderId = '".$intOrderId."';
		";
	$result = $objDB->sqlExecute($strSQL);
	$objOrder = $objDB->getObject($result);




	$dblTotalEx = $objOrder->totalPrice / 1.21;
	$arrTax[21] = number_format($objOrder->totalPrice - $dblTotalEx, 2, ',', ' ');
	$arrLanguages = array('nl' => 'Nederland', 'be' => 'België', 'pl' => 'Polen', 'de' => 'Duitsland', 'uk' => 'Engeland', '' => 'Nederland');

	// order details (products)
	$strSQL =
		"SELECT productId,
		price,
		discount,
		tax,
		quantity
		FROM ".DB_PREFIX."customers_orders_details
		WHERE orderId = '".$objOrder->orderId."'  ";
	$result = $objDB->sqlExecute($strSQL);
	while($objDetails = $objDB->getObject($result)) {

		$Product = $Api->getProduct($objDetails->productId, '?fields=title');

		$objDetails->title		= $Product->getTitle();
		$objDetails->taxRate		= $objDetails->tax;
		$objDetails->taxPerc		= $objDetails->tax * 100 - 100;
		$objDetails->priceTotal 	= number_format(round($objDetails->price * $objDetails->tax, 2) * $objDetails->quantity, 2, ',', ' ');
		$objDetails->price		= number_format(round($objDetails->price * $objDetails->tax, 2), 2, ',', ' ');
		$objDetails->priceEx		= number_format($objDetails->price, 2, ',', ' ');

		$dblTotalEx += $objDetails->price * $objDetails->quantity;
		$details[] = $objDetails;
	}

	$strTemplateVars = array(
		'name' => $objOrder->firstname.' '.$objOrder->lastname,
		'address' => $objOrder->address.' '.$objOrder->houseNr,
		'zipcode' => $objOrder->zipcode,
		'city' => $objOrder->city,
		'country' => $arrLanguages[$objOrder->lang],
		'customer_nr' => str_pad($objOrder->custId,9,'0',STR_PAD_LEFT),
		'invoice_nr' => str_pad($objOrder->orderId,9,'0',STR_PAD_LEFT),
		'invoice_date' => $objOrder->entryDate,
		'order_details' => $details,
		'discount_code' => $objOrder->kortingscode,
		'discount_amount' => number_format($objOrder->kortingsbedrag, 2, ',', ' '),
		'shipping_costs' => number_format($objOrder->shippingCosts, 2, ',', ' '),
		'total_ex' => number_format($dblTotalEx, 2, ',', ' '),
		'total' => number_format($objOrder->totalPrice, 2, ',', ' '),
		'tax' => $arrTax,
		'site_path' => dirname(__DIR__),
	);

	$strTemplateVars = array_merge($strTemplateVars, $settings);

	$strTemplate = $tpl->render($strTemplateVars);

	$mpdf->setAutoTopMargin = 'pad';
	$mpdf->setAutoBottomMargin = 'pad';
	$mpdf->WriteHTML($strTemplate);
	if($blnDownload === true) {
		$pdf = $mpdf->Output('factuur-'.str_pad($objOrder->orderId,9,'0',STR_PAD_LEFT).'.pdf', 'D');
	} else {
		$pdf = $mpdf->Output('', 'S');
	}

	return $pdf;
}

function rewriteUrl($strString) {
	
	$strString = stripslashes($strString);
	$strString = trim($strString);
	$strString = str_replace("'","",$strString);
	$strString = str_replace(",","",$strString);
	$strString = str_replace("\"","",$strString);
	$strString = str_replace("?","",$strString);
	$strString = str_replace("(","",$strString);
	$strString = str_replace(")","",$strString);
	$strString = str_replace("/","",$strString);
	$strString = str_replace("&","",$strString);
	$strString = str_replace("&iuml;","",$strString);
	$strString = str_replace(" ","-",$strString);
	$strString = str_replace("_","-",$strString);
	$strString = str_replace("“","",$strString);
	$strString = str_replace("”","",$strString);
	$strString = strtolower($strString);
	
	return $strString;
	
}

?>
