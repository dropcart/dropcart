<?php
// Required includes
require_once (__DIR__.'/../includes/php/dc_connect.php');
require_once (__DIR__.'/../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../beheer/includes/php/dc_config.php');

// Page specific includes
require_once (__DIR__.'/../beheer/includes/php/dc_functions.php');

$objDB 	= new DB();

$_POST 	= sanitize($_POST);
$_GET 	= sanitize($_GET);

$intId 		= (isset($_GET['id'])) ? $_GET['id'] : null;
$intUuid	= (isset($_GET['uuid'])) ? $_GET['uuid'] : null;
$strAction 	= (isset($_GET['action'])) ? strtolower($_GET['action']) : null;

if (empty($intId)) {
	header('Location: '.SITE_URL.'/beheer/dc_order_admin.php?fail='.urlencode('Geen ?id= opgegeven.'));
	exit();
}

if (!empty($_POST)) {

	foreach ($_POST as $key => $value) {
		
		$strSQL = "UPDATE ".DB_PREFIX."customers_orders SET `".$key."` = '".$value."' WHERE orderId = '".$intId."'";
		$objDB->sqlExecute($strSQL);

	}
}

if ($strAction == "export") {
	
	if(isset($_GET["uuid"]) && $_GET["id"])  {
		
		$strSQL 		= 
					"SELECT c.id, c.email, c.gender, c.firstname, c.lastname, co.phoneNr, co.ip, co.extOrderId, co.company, co.firstname, co.lastname, co.address, co.houseNr, co.houseNrAdd, co.zipcode, co.city, co.lang, co.delCompany, co.delFirstname, co.delLastname, co.delAddress, co.delHouseNr, co.delHouseNrAdd, co.delZipcode, co.delCity, co.delLang, co.entryDate, co.totalPrice, co.shippingCosts, co.paymentStatus, co.paymethodId, co.status, co.internalComments, co.kortingscode, co.kortingsbedrag, dc_c.validationCode
					FROM ".DB_PREFIX."customers_orders co
					INNER JOIN ".DB_PREFIX."customers c ON (c.id = co.custId)
					LEFT JOIN ".DB_PREFIX."discountcodes_codes dc_c ON dc_c.orderId = co.orderId
					WHERE co.orderId = '".$intId."' AND co.uuid = '".$intUuid."'
					GROUP BY co.orderId";
		$result 		= $objDB->sqlExecute($strSQL);
		
		if($objDB->getNumRows($result) == 0) {
			
			$arrJson['error'] = 'Order not found.';
			header('Content-type: application/json');
			echo json_encode($arrJson);
			exit();
			
		}
		
		$objOrder 			= $objDB->getObject($result);
			
		// Remove spaces
		$objOrder->zipcode 		= str_replace(" ", "", $objOrder->zipcode);
		$objOrder->delZipcode 	= str_replace(" ", "", $objOrder->delZipcode);
	
		$arrJson 			= array (
			'id' 			=>	$objOrder->id,
			'firstName'		=> $objOrder->firstname,
			'lastName'		=> $objOrder->lastname,
			'email' 			=> $objOrder->email,
			'gender' 		=> $objOrder->gender,
			'phoneNr' 		=> $objOrder->phoneNr,
			'mobileNr'		=> NULL,
			'ip'			=> $objOrder->ip,
		);
	
		$zipcodeBilling 		= substr($objOrder->zipcode, 0, 4);
	
		if (strlen($objOrder->zipcode) == 6) {
			$zipcodeCharsBilling= substr($objOrder->zipcode, -2);
			$zipcodeCharsBilling= strtoupper($zipcodeCharsBilling);
		}
	
		$zipcodeShipping 		= substr($objOrder->delZipcode, 0, 4);
	
		if (strlen($objOrder->delZipcode) == 6) {
			$zipcodeCharsShipping= substr($objOrder->delZipcode, -2);
			$zipcodeCharsShipping= strtoupper($zipcodeCharsShipping);
		}
	
	
		$arrJson['billingAddress'] 	= array (
			'company' 		=> $objOrder->company,
			'firstName' 		=> $objOrder->firstname,
			'lastName' 		=> $objOrder->lastname,
			'address' 		=> $objOrder->address,
			'houseNr' 		=> $objOrder->houseNr,
			'houseNrAdd' 		=> $objOrder->houseNrAdd,
			'zipcode' 		=> $zipcodeBilling,
			'zipcodeChars'	=> $zipcodeCharsBilling,
			'city' 			=> $objOrder->city,
			'lang' 			=> $objOrder->lang,
		);
	
		$arrJson['shippingAddress'] = array (
			'company' 		=> $objOrder->delCompany,
			'firstName' 		=> $objOrder->delFirstname,
			'lastName' 		=> $objOrder->delLastname,
			'address' 		=> $objOrder->delAddress,
			'houseNr' 		=> $objOrder->delHouseNr,
			'houseNrAdd' 		=> $objOrder->delHouseNrAdd,
			'zipcode' 		=> $zipcodeShipping,
			'zipcodeChars'	=> $zipcodeCharsShipping,
			'city' 			=> $objOrder->delCity,
			'lang' 			=> $objOrder->delLang,
		);
	
		$arrOrderDetails 		= array();
	
		$strSQL 			= "SELECT productId, price, discount, tax, quantity FROM ".DB_PREFIX."customers_orders_details WHERE orderId = '".$intId."'";
		$result 			= $objDB->sqlExecute($strSQL);
	
		while ($objDetails = $objDB->getObject($result)) {
	
			$arrOrderDetails[] 	= array (
				'productId' 	=> $objDetails->productId,
				'quantity' 	=> $objDetails->quantity,
				'price' 		=> $objDetails->price,
				'taxRate' 	=> $objDetails->tax,
			);
	
		}
		
	
		$arrJson['order'] 		= array (
			'orderDate'		=> $objOrder->entryDate,
			'shippingCosts'	=> $objOrder->shippingCosts,
			'totalPrice'		=> $objOrder->totalPrice,
			'tax'			=> NULL,
			'payMethod'		=> 'Mollie',
			'paymentStatus'	=> $objOrder->paymentStatus,
			'discountCode'	=> $objOrder->kortingscode . (($objOrder->validationCode != '') ? ' / ' . $objOrder->validationCode : ''),
			'discountAmount' 	=> abs($objOrder->kortingsbedrag),
			'items' 			=> $arrOrderDetails,
		);

		$arrJson['metadata'] 		= array (
			'dc_version'		=> DROPCART_VERSION,

		);
	
		header('Content-type: application/json');
		echo json_encode($arrJson);
	
		exit();
		
	} else {
		
		$arrJson['error'] = 'Unique identifier required.';
		header('Content-type: application/json');
		echo json_encode($arrJson);
		exit();	
		
	}
	
}
elseif ($strAction == "remove") {

	echo "Orders kunnen (nog) niet vanuit het systeem verwijderd worden.";

	exit();
}

// ELSE do nothing and continue on

require('includes/php/dc_header.php');

$strSQL 		= 
			"SELECT c.id, 
			c.email, 
			c.gender, 
			c.firstname, 
			c.lastname, 
			co.phoneNr, 
			co.orderId,
			co.ip, 
			co.extOrderId, 
			co.company, 
			co.firstname, 
			co.lastname, 
			co.address, 
			co.houseNr, 
			co.houseNrAdd, 
			co.zipcode, 
			co.city, 
			co.lang, 
			co.delCompany, 
			co.delFirstname, 
			co.delLastname, 
			co.delAddress, 
			co.delHouseNr, 
			co.delHouseNrAdd, 
			co.delZipcode, 
			co.delCity, 
			co.delLang, 
			co.entryDate, 
			co.totalPrice, 
			co.shippingCosts, 
			co.paymentStatus, 
			co.paymethodId, 
			co.status, 
			co.internalComments, 
			co.kortingscode, 
			co.kortingsbedrag, 
			dc_c.validationCode
			FROM ".DB_PREFIX."customers_orders co
			INNER JOIN ".DB_PREFIX."customers c ON (c.id = co.custId)
			LEFT JOIN ".DB_PREFIX."discountcodes_codes dc_c ON dc_c.orderId = co.orderId
			WHERE co.orderId = '".$intId."'
			GROUP BY co.orderId";
$result 		= $objDB->sqlExecute($strSQL);
$objOrder 		= $objDB->getObject($result);

// New Inktweb Api object
$Api 					= new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

?>

<h1>Order details <small><?php echo $intId; ?></small></h1>

<hr />
<form role="form" method="post">
<div class="col-md-8">

	<div class="well"><h2>Orderstatus: <?php echo getStatusDesc($objOrder->status); ?></h2></div>

	<table class="table">
		<tr>
			<th colspan="2">Klant informatie <a href="/beheer/dc_customer_manage.php?id=<?php echo $objOrder->id; ?>&amp;action=view" class="btn btn-primary btn-xs pull-right">Bekijk klantaccount</a></th>
		</tr>
		<tr>
			<th>Bedrijfsnaam</th>
			<td><?php echo $objOrder->company; ?></td>
		</tr>
		<tr>
			<th>Klantnaam</th>
			<td><?php echo $objOrder->firstname . " " . $objOrder->lastname; ?></td>
		</tr>
		<tr>
			<th colspan="2">&nbsp;</th>
		</tr>
		<tr>
			<th>Ordernr</th>
			<td><?php echo $objOrder->orderId; ?></td>
		</tr>
		<tr>
			<th>Ordernr (extern)</th>
			<td><?php echo $objOrder->extOrderId; ?></td>
		</tr>
		<tr>
			<th>Besteldatum</th>
			<td><?php echo $objOrder->entryDate; ?></td>
		</tr>
		<tr>
			<th>Betaalstatus</th>
			<td>
			<?php
			if ($objOrder->paymentStatus == 1) {
				echo "Betaald";
			}
			else {
				echo "Niet betaald";
			}
			?>
			</td>
		</tr>
		<tr>
			<th>Betaalmethode</th>
			<td>
			<?php
			if ($objOrder->paymethodId == 1) {
				echo "Mollie";
			}
			else {
				echo "Onbekend";
			}
			?></td>
		</tr>
	</table>

	<table class="table table-hover table-striped">
		<tr>
			<th>Artikel</th>
			<th>Stukprijs</th>
			<th>Aantal</th>
			<th>Totaal</th>
		</tr>
		<?php
		$strSQL 	= "SELECT productId, price, discount, tax, quantity FROM ".DB_PREFIX."customers_orders_details WHERE orderId = '".$intId."'";
		$result 	= $objDB->sqlExecute($strSQL);

		$subTotal = "";
		while ($objDetails = $objDB->getObject($result)) {

			$Product = $Api->getProduct($objDetails->productId);

			echo '<tr>';
			echo '<td><a href="/dc_product_details.php?productId='.$objDetails->productId.'">'.$Product->getTitle().'</a></td>';
			echo '<td>'.money_format('%(#1n', round($objDetails->price * $objDetails->tax, 2)).'</td>';
			echo '<td>'.$objDetails->quantity.'</td>';
			echo '<td>'.money_format('%(#1n', round($objDetails->price * $objDetails->tax, 2) * $objDetails->quantity).'</td>';
			echo '</tr>';

			$subTotal = $subTotal + (round($objDetails->price * $objDetails->tax, 2) * $objDetails->quantity);
		}
		?>
		<tr>
			<td colspan="3">Subtotaal</td>
			<td><?php echo money_format('%(#1n', $subTotal); ?></td>
		</tr>
		<tr>
			<td colspan="3">Verzendkosten</td>
			<td><?php echo money_format('%(#1n', $objOrder->shippingCosts); ?></td>
		</tr>
		<tr>
			<td colspan="3">Korting (Code: <?php echo $objOrder->kortingscode; echo ($objOrder->validationCode != '') ? ' / Validatiecode: ' . $objOrder->validationCode : ''; ?>)</td>
			<td><?php echo money_format('%(#1n', $objOrder->kortingsbedrag); ?></td>
		</tr>
		<tr>
			<td colspan="3">Totaal</td>
			<td style="font-size:large;"><?php echo money_format('%(#1n', $objOrder->totalPrice); ?></td>
		</tr>
	</table>
</div><!-- /col-md-8 -->

<div class="col-md-4">
	<table class="table">
		<tr>
			<th colspan="2">Factuuradres</th>
		</tr>
		<tr>
			<th>Bedrijfsnaam</th>
			<td><?php echo $objOrder->company; ?></td>
		</tr>
		<tr>
			<th>Naam</th>
			<td><?php echo $objOrder->firstname . " " . $objOrder->lastname; ?></td>
		</tr>
		<tr>
			<th>Adres</th>
			<td><?php echo $objOrder->address . " " . $objOrder->houseNr.$objOrder->houseNrAdd; ?></td>
		</tr>
		<tr>
			<th>Postcode</th>
			<td><?php echo $objOrder->zipcode; ?></td>
		</tr>
		<tr>
			<th>Stad</th>
			<td><?php echo $objOrder->city; ?></td>
		</tr>
		<tr>
			<th>Land</th>
			<td><?php echo $objOrder->lang; ?></td>
		</tr>
		<tr>
			<th colspan="2">Afleveradres</th>
		</tr>
		<tr>
			<th>Bedrijfsnaam</th>
			<td><?php echo $objOrder->delCompany; ?></td>
		</tr>
		<tr>
			<th>Naam</th>
			<td><?php echo $objOrder->delFirstname . " " . $objOrder->delLastname; ?></td>
		</tr>
		<tr>
			<th>Adres</th>
			<td><?php echo $objOrder->delAddress . " " . $objOrder->delHouseNr.$objOrder->delHouseNrAdd; ?></td>
		</tr>
		<tr>
			<th>Postcode</th>
			<td><?php echo $objOrder->delZipcode; ?></td>
		</tr>
		<tr>
			<th>Stad</th>
			<td><?php echo $objOrder->delCity; ?></td>
		</tr>
		<tr>
			<th>Land</th>
			<td><?php echo $objOrder->delLang; ?></td>
		</tr>
		<tr>
			<td colspan="2">Interne opmerkingen <br /><textarea name="internalComments" class="form-control" rows="3"><?php echo $objOrder->internalComments; ?></textarea></td>
		</tr>	
	</table>
</div><!-- /col-md-4 -->

<div class="col-md-12">
	<input type="submit" value="Wijzigen" class="btn btn-primary btn-lg pull-right" />
</form>
</div><!-- /col-md-12 -->

<?php require('includes/php/dc_footer.php'); ?>