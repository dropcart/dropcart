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

require('includes/php/dc_header.php');
?>

<h1>Vouchercodes</h1>

<hr />

<input type="search" id="search" value="" class="form-control search-json" placeholder="Zoeken" style="margin-bottom:20px" data-json-table="#table">

<?php

if (!empty($_GET['succes'])) {
	echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
}

?>

<span class="pull-right"><a href="dc_codes_manage.php"><span class="glyphicon glyphicon-plus"></span> Code toevoegen</a></span>

<ul class="nav nav-tabs" data-json-table="#table" data-json-key="show">
	<li data-json-value="new" class="active"><a>Actieve codes</a></li>
	<li data-json-value="archive"><a>Archief</a></li>
</ul>


<table class="table table-striped table-json" id="table" data-json-file="dc_codes_list.json">
	<thead>
		<tr>
			<th width="10%" data-json-column="id" data-json-sort="asc">ID</th>
			<th data-json-column="title">Titel</th>
			<th width="10%" data-json-column="validFrom">Geldig van</th>
			<th width="10%" data-json-column="validTill">Geldig tot</th>
			<th width="5%">Codes</th>
			<th width="5%">Details</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<ul class="pagination pagination-json" data-json-table="#table" data-json-items="25"></ul>

<script src="/beheer/includes/script/jquery.dynamic-table.js"></script>

<?php require('includes/php/dc_footer.php'); ?>