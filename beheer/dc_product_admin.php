<?php
// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_config.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_functions.php');

// Start API
require_once($_SERVER['DOCUMENT_ROOT'].'/libaries/Api_Inktweb/API.class.php');
$Api 		= new Inktweb\API(API_KEY, API_TEST, API_DEBUG);

$_POST 	= sanitize($_POST);
$_GET 	= sanitize($_GET);

if (!empty($_POST['add_productId'])) {
	header('Location: /beheer/dc_product_manage.php?id='.$_POST['add_productId']);
	exit();
}

$strShow 	= strtolower($_GET['show']);

if (empty($strShow) OR $strShow == "all") {
	$sqlWhere = " ";
}


$strSQL 	= 
		"SELECT p.id,
		p.price
		FROM ".DB_PREFIX."products p
		WHERE 1
		".$sqlWhere . " GROUP BY p.id";
$result 	= $objDB->sqlExecute($strSQL);


require('includes/php/dc_header.php');
?>

<h1>Producten</h1>

<hr />

<input type="search" id="search" value="" class="form-control search-json" placeholder="Zoeken" style="margin-bottom:20px" data-json-table="#table">

<?php

if (!empty($_GET['succes'])) {
	echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
}

?>

<span class="pull-right"><a data-toggle="modal" data-target="#addProduct"><span class="glyphicon glyphicon-plus"></span> Product toevoegen</a></span>

<ul class="nav nav-tabs" data-json-table="#table" data-json-key="show">
	<li class="disabled"><a >Producten</a></li>
	<li data-json-value="new" class="active"><a>Alles</a></li>
</ul>


<table class="table table-striped table-json" id="table" data-json-file="dc_product_list.json">
	<thead>
	<tr>
		<th width="10%">ID</th>
		<th>Titel</th>
		<th width="5%">Prijs</th>
		<th width="5%">Details</th>
	</tr>
	</thead>
	<tbody>
	</tbody>
</table>


<div class="modal fade" id="addProduct" tabindex="-1" role="dialog" aria-labelledby="addProductLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			<h4 class="modal-title" id="addProductLabel">Product toevoegen</h4>
		</div>
		<div class="modal-body">
			<form method="POST">
			Product ID: <input type="number" value="" name="add_productId" />
			<input type="submit" class="btn btn-default" value="Product toevoegen" />
			</form>
		</div>
	</div>
	</div>
</div>

<ul class="pagination pagination-json" data-json-table="#table" data-json-items="25"></ul>

<script src="/beheer/includes/script/jquery.dynamic-table.js"></script>

<?php require('includes/php/dc_footer.php'); ?>