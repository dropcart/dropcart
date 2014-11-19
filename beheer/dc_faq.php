<?php
// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_config.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_functions.php');

require('includes/php/dc_header.php');
?>

<h1>FAQ <small>Veel gestelde vragen</small></h1>

<hr />

<div class="col-md-6 col-md-offset-3">
	<h2>Welke versie van DropCart draai ik nu?</h2>
	<p>De huidige versie is <?php echo DROPCART_VERSION; ?>. <a href="http://www.dropcart.nl/">Controleer op updates?</a></p>

	<h2>Waarom blijven alle orders staan op "Nieuw"?</h2>
	<p>De cronjob is waarschijnlijk niet (goed) ingesteld om de orderstatus te controleren. </p>

	<p><code><?php echo formOption('site_url'); ?>beheer/cronjobs/dc_orderstatus.php</code></p>
	
	<h2>Hoe kan ik een bestelling verwijderen?</h2>
	<p>Het is (nog) niet mogelijk om een order te verwijderen uit het beheer systeem. Dit omdat alle orders in dit systeem ook zijn ingevoerd op het Inktweb.nl systeem. Bestellingen moeten dan ook handmatig verwijderd / geannuleerd worden.</p>

</div><!-- /col -->

<?php require('includes/php/dc_footer.php'); ?>