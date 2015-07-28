<?php
// Required includes
require_once __DIR__ . '/../includes/php/dc_connect.php';
require_once __DIR__ . '/../_classes/class.database.php';
$objDB = new DB();
require_once __DIR__ . '/../beheer/includes/php/dc_config.php';

// Page specific includes
require_once __DIR__ . '/../beheer/includes/php/dc_functions.php';

$objDB = new DB();

require 'includes/php/dc_header.php';
?>

<h1>Gebruikers</h1>

<hr />

<?php

if (!empty($_GET['succes'])) {
    echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> ' . $_GET['succes'] . '</div>';
}

if (!empty($_GET['firsttime'])) {
    echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Belangrijk!</strong> Maak <a href="' . SITE_URL . '/beheer/dc_user_manage.php?action=add">een nieuwe gebruiker aan</a> met een veilig wachtwoord en verwijder daarna het <em>admin</em> account.</div>';
}

?>

<input type="search" id="search" value="" class="form-control search-json" placeholder="Zoeken" style="margin-bottom:20px" data-json-table="#table">

<span class="pull-right"><a href="<?php echo SITE_URL?>/beheer/dc_user_manage.php?action=add"><span class="glyphicon glyphicon-plus"></span> Gebruiker toevoegen</a></span></span>

<table class="table table-striped table-json" id="table" data-json-file="dc_user_list.json">
    <thead>
        <tr>
            <th width="45%" data-json-column="name" data-json-sort="asc">Naam</th>
            <th width="45%" data-json-column="email">Email</th>
            <th width="5%">Edit</th>
            <th width="5%">Delete</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<ul class="pagination pagination-json" data-json-table="#table" data-json-items="25"></ul>

<script src="<?php echo SITE_URL?>/beheer/includes/script/jquery.dynamic-table.js"></script>

<?php require 'includes/php/dc_footer.php';?>