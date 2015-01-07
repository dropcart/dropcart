<?php
// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_config.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_functions.php');

$_POST 	= sanitize($_POST);
$_GET 	= sanitize($_GET);

$strShow 	= strtolower($_GET['show']);


$strSQL 	=
		"SELECT co.orderId,
		co.custId,
		co.entryDate,
		co.company,
		co.firstname,
		co.lastname,
		co.shippingCosts,
		co.totalPrice,
		co.paymentStatus,
		co.paymethodId,
		co.status,
		SUM(cod.quantity) AS items
		FROM ".DB_PREFIX."customers_orders co
		INNER JOIN ".DB_PREFIX."customers_orders_details cod ON (cod.orderId = co.orderId)
		WHERE 1
		".$sqlWhere . " GROUP BY co.orderId DESC";
$result 	= $objDB->sqlExecute($strSQL);


require('includes/php/dc_header.php');
?>

<h1>Bestellingen</h1>

<hr />

<input type="search" id="search" value="" class="form-control search-json" placeholder="Zoeken" style="margin-bottom:20px" data-json-table="#table">

<?php

if (!empty($_GET['succes'])) {
	echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
}

?>

<ul class="nav nav-tabs" data-json-table="#table" data-json-key="show">
	<li class="disabled"><a >Bestellingen</a></li>
	<li data-json-value="new" class="active"><a>Nieuw</a></li>
	<li data-json-value="done"><a>Verwerkt</a></li>
	<li data-json-value="all"><a>Alles</a></li>
</ul>


<table class="table table-striped table-json" id="table" data-json-file="dc_order_list.json">
	<thead>
	<tr>
		<th width="10%" data-json-column="orderId" data-json-sort="desc">Ordernummer</th>
		<th width="10%" data-json-column="entryDate">Datum</th>
		<th data-json-column="firstname">Naam</th>
		<th width="10%">Aantal art</th>
		<th width="10%">Bedrag</th>
		<th width="5%">Factuur</th>
		<th width="5%">Details</th>
	</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<ul class="pagination pagination-json" data-json-table="#table" data-json-items="25"></ul>

<script src="/beheer/includes/script/jquery.dynamic-table.js"></script>

<?php require('includes/php/dc_footer.php'); ?>