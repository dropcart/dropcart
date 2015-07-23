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

$intId 		= $_GET['id'];
$strAction 	= strtolower($_GET['action']);

if (empty($intId)) {
	header('Location: '.SITE_URL.'/beheer/dc_customer_admin.php?fail='.urlencode('Geen ?id= opgegeven.'));
	exit();
}

if (!empty($_POST)) {

	foreach ($_POST as $key => $value) {
		
		$strSQL = "UPDATE ".DB_PREFIX."customers SET `".$key."` = '".$value."' WHERE id = '".$intId."'";
		$objDB->sqlExecute($strSQL);

	}
}

$strSQL 		= 
			"SELECT c.entryDate,
			c.firstname,
			c.lastname,
			c.company,
			c.email
			FROM ".DB_PREFIX."customers c
			LEFT JOIN ".DB_PREFIX."customers_addresses ca ON (ca.custId = c.id)
			WHERE c.id = '".$intId."' ";
$result 		= $objDB->sqlExecute($strSQL);
$objCust 		= $objDB->getObject($result);

require('includes/php/dc_header.php');

?>

<h1>Klantaccount <small><?php echo $intId; ?></small></h1>
<hr />

<form role="form" class="form-horizontal" method="POST">
	
	<div class="form-group">
		<label for="loginUser" class="col-sm-2 control-label">Klantaccount</label>
		<div class="col-sm-8">
			<a href="<?php SITE_URL?>/beheer/dc_login_user.php?id=<?php echo $intId; ?>" class="uneditable-input">Inloggen als deze klant</a>
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="entryDate" class="col-sm-2 control-label">Aangemaakt</label>
		<div class="col-sm-8">
			<span class="uneditable-input"><?php echo $objCust->entryDate; ?></span>
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="firstname" class="col-sm-2 control-label">Voornaam</label>
		<div class="col-sm-8">
			<input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $objCust->firstname; ?>" autocomplete="off">
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="lastname" class="col-sm-2 control-label">Achternaam</label>
		<div class="col-sm-8">
			<input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $objCust->lastname; ?>" autocomplete="off">
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="company" class="col-sm-2 control-label">Bedrijfsnaam</label>
		<div class="col-sm-8">
			<input type="text" class="form-control" id="company" name="company" value="<?php echo $objCust->company; ?>" autocomplete="off">
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="email" class="col-sm-2 control-label">E-mailadres</label>
		<div class="col-sm-8">
			<input type="email" class="form-control" id="email" name="email" value="<?php echo $objCust->email; ?>" autocomplete="off">
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="col-sm-8 col-sm-offset-2">
	<table class="table table-striped table-bordered">
		<tr>
			<th>Ordernummer</th>
			<th>Datum</th>
			<th>Bedrag</th>
			<th style="width:5%;">Bekijk</th>
		</tr>
		<?php
		$strSQL = 
			"SELECT co.orderId,
			co.entryDate,
			co.totalPrice
			FROM ".DB_PREFIX."customers_orders co 
			WHERE co.custId = '".$intId."' ";
		$result = $objDB->sqlExecute($strSQL);

		while ($objCustOrders = $objDB->getObject($result)) {
		?>
			<tr>
				<td><?php echo $objCustOrders->orderId; ?></td>
				<td><?php echo $objCustOrders->entryDate; ?></td>
				<td><?php echo $objCustOrders->totalPrice; ?></td>
				<td><a href="<?php SITE_URL?>/beheer/dc_order_manage.php?id=<?php echo $objCustOrders->orderId; ?>&action=view"><span class="glyphicon glyphicon-edit"></span></a></td>
			</tr>
		<?php
		}
		?>
	</table>
	</div><!-- /form group -->

	<div class="clearfix"></div>

	<?php
	/**
	 * @todo: Select w/ dropdown customer addresses and specifc page to edit these. Much like the user can do himself
	 */
	?>

	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-8">
		<button type="submit" class="btn btn-primary">Gebruiker aanpassen</button>
		</div><!-- /col -->
	</div><!-- /form group -->


<?php require('includes/php/dc_footer.php'); ?>