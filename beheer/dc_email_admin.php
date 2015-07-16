<?php
// Required includes
require_once (__DIR__.'/../includes/php/dc_connect.php');
require_once (__DIR__.'/../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../beheer/includes/php/dc_config.php');

// Page specific includes
require_once (__DIR__.'/../beheer/includes/php/dc_functions.php');

require('includes/php/dc_header.php');
?>

<h1>Emails</h1>

<hr />

<?php

if (!empty($_GET['succes'])) {
	echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
}

?>

<input type="search" id="search" value="" class="form-control search-json" placeholder="Zoeken" style="margin-bottom:20px" data-json-table="#table">

<table class="table table-striped table-json" id="table" data-json-file="dc_email_list.json">
	<thead>
		<tr>
			<th width="25%" data-json-column="navTitle" data-json-sort="asc">Titel</th>
			<th width="60%">Omschrijving</th>
			<th width="5%">Edit</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<ul class="pagination pagination-json" data-json-table="#table" data-json-items="25"></ul>

<script src="<?php echo SITE_URL ?>/beheer/includes/script/jquery.dynamic-table.js"></script>
<?php require('includes/php/dc_footer.php'); ?>