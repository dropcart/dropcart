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

// Start API
require_once('libaries/Api_Inktweb/API.class.php');

if (empty($_SESSION['customerId'])) {
	// not logged in, redirect
	header('Location: /dc_login.php');
}


$_POST 		= sanitize($_POST);

$strSQL 		=
			"SELECT c.id,
			c.company,
			c.firstname,
			c.lastname,
			c.gender,
			c.email,
			ca.addressName,
			ca.company AS add_company,
			ca.firstname AS add_firstname,
			ca.lastname AS add_lastname,
			ca.address AS add_address,
			ca.houseNr AS add_houseNr,
			ca.houseNrAdd AS add_houseNrAdd,
			ca.zipcode AS add_zipcode,
			ca.city AS add_city,
			ca.lang AS add_lang
			FROM ".DB_PREFIX."customers c
			LEFT JOIN ".DB_PREFIX."customers_addresses ca ON (ca.custId = c.id)
			LEFT JOIN ".DB_PREFIX."customers_orders co ON (co.custId = c.id)
			WHERE 1
			AND ca.defaultInv = 1
			AND c.id = '".$_SESSION['customerId']."'
			";
$result 		= $objDB->sqlExecute($strSQL);
$objCust 		= $objDB->getObject($result);

$Api 			= new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

// Start displaying HTML
require_once('includes/php/dc_header.php');
?>

<div class="row" style="margin-top:40px">
	<div class="col-xs-12 col-sm-10 col-md-10 col-sm-offset-1 col-md-offset-1">

		<h2>Mijn account <small><a href="/dc_logout.php">(uitloggen?)</a></small></h2>
		<hr class="colorgraph">

		<h3>Persoonlijke gegevens</h3>
		<table class="table">
			<tr>
				<td style="width:35%">Bedrijfsnaam:</td>
				<td><?php echo $objCust->company; ?></td>
			</tr>
			<tr>
				<td>Naam:</td>
				<td><?php echo $objCust->firstname . " " . $objCust->lastname; ?></td>
			</tr>
			<tr>
				<td>Geslacht:</td>
				<td>
					<?php if ($objCust->gender == 0) echo 'Onbekend'; ?>
					<?php if ($objCust->gender == 1) echo 'Mannelijk'; ?>
					<?php if ($objCust->gender == 2) echo 'Vrouwelijk'; ?>
				</td>
			</tr>
			<tr>
				<td>E-mailadres:</td>
				<td><?php echo $objCust->email; ?></td>
			</tr>
			<tr>
				<td>Wachtwoord:</td>
				<td><a href="/dc_profile_edit.php">Wachtwoord resetten?</a></td>
			</tr>
			<tr>
				<td><a href="/dc_profile_edit.php" class="btn btn-success">Wijzigen?</a></td>
				<td>&nbsp;</td>
			</tr>
		</table>

		<h3>Mijn adressen</h3>
		<table class="table">
			<tr>
				<td style="width:35%">#:</td>
				<td><?php echo $objCust->addressName; ?></td>
			</tr>
			<tr>
				<td>Naam:</td>
				<td><?php echo $objCust->add_firstname . " " . $objCust->add_lastname; ?></td>
			</tr>
			<tr>
				<td>Adres:</td>
				<td><?php echo $objCust->add_address . " " . $objCust->add_houseNr . $objCust->add_houseNrAdd; ?></td>
			</tr>
			<tr>
				<td>Postcode:</td>
				<td><?php echo $objCust->add_zipcode; ?></td>
			</tr>
			<tr>
				<td>Woonplaats:</td>
				<td><?php echo $objCust->add_city; ?></td>
			</tr>
			<tr>
				<td><a href="/dc_profile_addresses.php" class="btn btn-success">Wijzigen?</a></td>
				<td>&nbsp;</td>
			</tr>
		</table>

		<h2>Bestellingen</h2>
		<hr class="colorgraph">
		<table class="table">
			<tr>
				<th>Ordernummer</th>
				<th>Besteldatum</th>
				<th>Items</th>
				<th>Betaalmethode</th>
				<th>Status</th>
			</tr>

			<?php
			$strSQL 	=
					"SELECT co.orderId,
					co.entryDate,
					co.paymethodId,
					co.status
					FROM ".DB_PREFIX."customers_orders co
					INNER JOIN ".DB_PREFIX."customers_orders_details cod ON (cod.orderId = co.orderId)
					WHERE co.custId = '".$_SESSION['customerId']."'
					GROUP BY co.orderId";
			$result 	= $objDB->sqlExecute($strSQL);

			if (empty($result->num_rows)) {
				echo '<tr><td colspan="5">Wij hebben (nog?) geen bestellingen van u gevonden.</td></tr>';
			}

			while ($objOrder = $objDB->getObject($result)) {

				$strSQL 	= "SELECT COUNT(productId) * quantity FROM ".DB_PREFIX."customers_orders_details WHERE orderId = '".$objOrder->orderId."'";
				$result 	= $objDB->sqlExecute($strSQL);
				list($items) 	= $objDB->getRow($result);

				$strSQL 	= "SELECT productId, price, discount, quantity FROM ".DB_PREFIX."customers_orders_details WHERE orderId = '".$objOrder->orderId."'";
				$result 	= $objDB->sqlExecute($strSQL);

				echo '<tr class="order" title="Klik om order details te bekijken">';
				echo '<td><a>'.formOption('order_number_prefix') . $objOrder->orderId.'</a></td>';
				echo '<td>'.$objOrder->entryDate.'</td>';
				echo '<td>'.$items.'</td>';
				echo '<td>'.$objOrder->paymethodId.'</td>';
				echo '<td>'.getStatusDesc($objOrder->status).'</td>';
				echo '</tr>';

				while ($objDetails = $objDB->getObject($result)) {

					$Product = $Api->getProduct($objDetails->productId);

					echo '<tr class="order_details active">';
						echo '<td colspan="2"><a href="/dc_product_details.php?productId='.$objDetails->productId.'">'.$Product->getTitle().'</a></td>';
						echo '<td>'.money_format('%(#1n', $objDetails->price).'</td>';
						echo '<td>'.$objDetails->quantity.'</td>';
						echo '<td>'.money_format('%(#1n', $objDetails->price * $objDetails->quantity).'</td>';
					echo '</tr>';
				}
			}
			?>
		</table>

	</div><!-- /col -->
</div><!-- /row -->

<script type="text/javascript">
$(document).ready(function() {
	$(".order_details").hide();

	$(".order").click(function() {
		$(".order_details").slideToggle();
	});
});
</script>

<?php
require('includes/php/dc_footer.php');
?>