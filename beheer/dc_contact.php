<?php
// Required includes
require_once __DIR__ . '/../includes/php/dc_connect.php';
require_once __DIR__ . '/../_classes/class.database.php';
$objDB = new DB();
require_once __DIR__ . '/../beheer/includes/php/dc_config.php';

// Page specific includes
require_once __DIR__ . '/../beheer/includes/php/dc_functions.php';

require 'includes/php/dc_header.php';
?>

<h1>Contact</h1>

<hr />

<div class="col-md-6 col-md-offset-3">
	<ul>
		<li><a href="http://www.dropcart.nl/">Dropcart</a></li>
		<li>Tel: 072 567 50 53</li>
		<li>Email: support@dropcart.nl</li>
	</ul>

</div><!-- /col -->

<?php require 'includes/php/dc_footer.php';?>