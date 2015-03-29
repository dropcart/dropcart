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

$intId 		= (int) $_GET['id'];
$strAction 	= $_GET['action'];

$strSQL 	= "SELECT dc.* FROM ".DB_PREFIX."discountcodes dc WHERE dc.id = '".$intId."' ";
$result 	= $objDB->sqlExecute($strSQL);
$objCode  	= $objDB->getObject($result);

if ($_POST) {

	$strTitle 				= $_POST['title'];
	$strValidFrom 			= $_POST['validFrom'];
	$strValidTill				= $_POST['validTill'];
	$strDiscountType			= $_POST['discountType'];
	$strDiscountValue			= $_POST['discountValue'];
	$intValidationCodeRequired 		= (int) $_POST['validationCodeRequired'];
	$intFixedShipping	 		= (int) $_POST['fixedShipping'];
	$intOnline				= (int) $_POST['online'];

	if(empty($intId)) {
		
		$strSQL = "INSERT INTO ".DB_PREFIX."discountcodes (title, validFrom, validTill, discountType, discountValue, validationCodeRequired, fixedShipping, online) VALUES ('".$strTitle."', '".$strValidFrom."',  '".$strValidTill."', '".$strDiscountType."', ".$strDiscountValue.", ".$intValidationCodeRequired.", ".$intFixedShipping.", ".$intOnline.")";
		$result = $objDB->sqlExecute($strSQL);
		$intId = $objDB->getInsertedId();
		
	} else {
	
		$strSQL = "UPDATE ".DB_PREFIX."discountcodes 
				SET title = '".$strTitle."',
				validFrom = '".$strValidFrom."', 
				validTill = '".$strValidTill."', 
				discountType = '".$strDiscountType."', 
				discountValue = '".$strDiscountValue."', 
				validationCodeRequired = ".$intValidationCodeRequired.",
				fixedShipping = ".$intFixedShipping.",
				online = ".$intOnline."
				WHERE id = '".$intId."' ";
		$result 		= $objDB->sqlExecute($strSQL);
	
	}

	if ($result === true) {
		header('Location: ?id='.$intId.'&action='.$strAction.'&succes='.urlencode('De codes zijn bijgewerkt.'));
	}
	else {
		header('Location: ?id='.$intId.'&action='.$strAction.'&fail='.urlencode('Er is iets fout gegaan.'));
	}
	
}


require('includes/php/dc_header.php');


if (!empty($_GET['succes'])) {
	echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
}

if (!empty($_GET['fail'])) {
	echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Fout!</strong> '.$_GET['fail'].'</div>';
}

?>

<h1>Codes beheren <small><?php echo $objCode->title; ?></small></h1>

<hr />

<form role="form" class="form-horizontal" method="POST">

	<div class="form-group">
		<label for="navTitle" class="col-sm-2 control-label">Naam</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="title" name="title" placeholder="" value="<?php echo $objCode->title; ?>">
			<p class="help-block">Alleen voor intern gebruik</p>
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="navDesc" class="col-sm-2 control-label">Geldig vanaf</label>
		<div class="col-sm-10">
			<input type="text" class="form-control datepicker" id="validFrom" name="validFrom" placeholder="yyyy-mm-dd" data-provide="datepicker" data-date-format="yyyy-mm-dd" value="<?php echo $objCode->validFrom; ?>">
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="navDesc" class="col-sm-2 control-label">Geldig tot</label>
		<div class="col-sm-10">
			<input type="text" class="form-control datepicker" id="validTill" name="validTill" placeholder="yyyy-mm-dd" data-provide="datepicker" data-date-format="yyyy-mm-dd" value="<?php echo $objCode->validTill; ?>">
		</div><!-- /col -->
	</div><!-- /form group -->
	
	<div class="form-group">
		<label for="discountType" class="col-sm-2 control-label">Kortingstype</label>
		<div class="col-sm-10">
			<select class="form-control" id="discountType" name="discountType">
				<option value="price" <?php if($objCode->discountType == 'price') echo 'selected="selected"'; ?>>Bedrag</option>
				<option value="percentage" <?php if($objCode->discountType == 'percentage') echo 'selected="selected"'; ?>>Percentage</option>
				<option value="dynamic" <?php if($objCode->discountType == 'dynamic') echo 'selected="selected"'; ?>>Verschillend per code</option>
			</select>
		</div><!-- /col -->
	</div><!-- /form group -->
	
	<div class="form-group">
		<label for="discountValue" class="col-sm-2 control-label">Kortingswaarde</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="discountValue" name="discountValue" value="<?php echo $objCode->discountValue; ?>">
		</div><!-- /col -->
	</div><!-- /form group -->
	
	<div class="form-group">
		<label for="validationCodeRequired" class="col-sm-2 control-label">Validatiecode benodigd</label>
		<div class="col-sm-10">
			<input type="checkbox" name="validationCodeRequired" id="validationCodeRequired" value="1" <?=($objCode->validationCodeRequired == 1) ? 'checked="checked"' : '' ?> />
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="fixedShipping" class="col-sm-2 control-label">Vaste verzendkosten</label>
		<div class="col-sm-10">
			<input type="checkbox" name="fixedShipping" id="fixedShipping" value="1" <?=($objCode->fixedShipping == 1) ? 'checked="checked"' : '' ?> />
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="online" class="col-sm-2 control-label">Code actief</label>
		<div class="col-sm-10">
			<input type="checkbox" name="online" id="online" value="1" <?=($objCode->online == 1) ? 'checked="checked"' : '' ?> />
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
		<button type="submit" class="btn btn-primary">Codes aanpassen</button>
		</div><!-- /col -->
	</div><!-- /form group -->	

<hr />

<script src="/includes/script/bootstrap-datepicker.js"></script>
<?php require('includes/php/dc_footer.php'); ?>