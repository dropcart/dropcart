<?php
// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_config.php');

// Require login
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_session.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_functions.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/libraries/mpdf/mpdf.php'); // MPDF Libary
require_once ($_SERVER['DOCUMENT_ROOT'].'/libraries/Twig/Autoloader.php'); // Twig template engine

// Start API
require_once('../../libraries/Api_Inktweb/API.class.php');
$Api = new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

$intOrderId = (int) $_GET["orderId"];
$output = (empty($_GET["output"])) ? '' : $_GET["output"];

$strSQL = "SELECT *, DATE_FORMAT(co.entryDate, '%d-%m-%Y') as entryDate
	FROM ".DB_PREFIX."customers_orders co
	WHERE co.orderId = ".$intOrderId;
$result 	= $objDB->sqlExecute($strSQL);
$objOrder 	= $objDB->getObject($result);

// Load order details
$strSQL = "SELECT * 
	FROM ".DB_PREFIX."customers_orders_details cod
	WHERE cod.orderId = ".$intOrderId;
$result 	= $objDB->sqlExecute($strSQL);
while($objDetails = $objDB->getObject($result)) {

	$Product = $Api->getProduct($objDetails->productId, '?fields=title');
	
	$objDetails->title				= $Product->getTitle();
	$objDetails->taxRate			= $objDetails->tax;
	$objDetails->taxPerc			= $objDetails->tax * 100 - 100;
	$objDetails->priceTotal 		= number_format($objDetails->price * $objDetails->tax * $objDetails->quantity, 2, ',', ' ');
	$objDetails->price				= number_format($objDetails->price * $objDetails->tax, 2, ',', ' ');
	$objDetails->priceEx			= number_format($objDetails->price, 2, ',', ' ');
//	$arrTax[$objDetails->taxPerc]	= $objDetails->price * $objDetails->tax - $objDetails->price;
	
	$dblTotalEx += $objDetails->price * $objDetails->quantity;
	$details[] = $objDetails;
}

$dblTotalEx = $objOrder->totalPrice / 1.21;
$arrTax[21] = number_format($objOrder->totalPrice - $dblTotalEx, 2, ',', ' ');
$arrLanguages = array('nl' => 'Nederland', 'be' => 'België', 'pl' => 'Polen', 'de' => 'Duitsland', 'uk' => 'Engeland', '' => 'Nederland');

Twig_Autoloader::register();
$twig = new Twig_Environment( new Twig_Loader_Filesystem("../templates"), array("cache" => false) );

$tpl = $twig->loadTemplate( "dc_invoice_template.tpl" );
$strTemplate = $tpl->render(
	array(
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
		'site_path' => dirname(dirname(__DIR__))
	)
);

$mpdf=new mPDF();
$mpdf->setAutoTopMargin = 'pad';
$mpdf->setAutoBottomMargin = 'pad';
$mpdf->WriteHTML($strTemplate);
if($output == 'D') {
	$mpdf->Output('factuur-'.str_pad($objOrder->orderId,9,'0',STR_PAD_LEFT).'.pdf', 'D');
} else {
	$mpdf->Output();
}
exit;
?>