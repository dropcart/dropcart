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

$intId 		= $_GET['id'];
$strAction 	= $_GET['action'];

if ($strAction == "remove" AND !empty($intId)) {
	$objDB->sqlDelete('pages_content', 'id', $intId);
	header('Location: /beheer/dc_page_admin.php?succes='.urlencode('De pagina is verwijderd.'));
}

$strSQL 	= "SELECT ec.emailId, ec.navTitle, ec.navDesc, ec.title, ec.txt, e.fromName, e.fromEmail, e.bcc
	FROM ".DB_PREFIX."emails e
	INNER JOIN ".DB_PREFIX."emails_content ec ON ec.emailId = e.id
	WHERE e.id = '".$intId."' ";
$result 	= $objDB->sqlExecute($strSQL);
$objEmail  	= $objDB->getObject($result);

if ($_POST) {

	$strNavTitle 		= $_POST['navTitle'];
	$strNavDesc 	= $_POST['navDesc'];
	$strFromEmail 	= $_POST['fromEmail'];
	$strFromName 	= $_POST['fromName'];
	$strBcc		 	= $_POST['bcc'];
	$strTitle 		= $_POST['title'];
	$strTxt 		= $_POST['txt'];
	
	$strSQL 		= "UPDATE ".DB_PREFIX."emails
				SET fromEmail = '".$strFromEmail."',
				fromName = '".$strFromName."', 
				bcc = '".$strBcc."'
				WHERE id = '".$intId."' ";
	$result 		= $objDB->sqlExecute($strSQL);

	$strSQL 		= "UPDATE ".DB_PREFIX."emails_content 
				SET navTitle = '".$strNavTitle."',
				navDesc = '".$strNavDesc."', 
				title = '".$strTitle."', 
				txt = '".$strTxt."'
				WHERE emailId = '".$intId."' ";
	$result 		= $objDB->sqlExecute($strSQL);

	if ($result === true) {
		header('Location: ?id='.$intId.'&action='.$strAction.'&succes='.urlencode('De email is bijgewerkt.'));
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

<h1>Email beheren <small><?php echo $objEmail->navTitle; ?></small></h1>

<hr />

<form role="form" class="form-horizontal" method="POST">

	<div class="form-group">
		<label for="navTitle" class="col-sm-2 control-label">navTitle</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="navTitle" name="navTitle" placeholder="" value="<?php echo $objEmail->navTitle; ?>">
			<p class="help-block">Alleen voor intern gebruik</p>
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="navDesc" class="col-sm-2 control-label">navDesc</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="navDesc" name="navDesc"><?php echo $objEmail->navDesc; ?></textarea>
			<p class="help-block">Alleen voor intern gebruik</p>
		</div><!-- /col -->
	</div><!-- /form group -->
	
	<div class="form-group">
		<label for="fromEmail" class="col-sm-2 control-label">E-mailadres afzender</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="fromEmail" name="fromEmail" value="<?php echo $objEmail->fromEmail; ?>" required>
		</div><!-- /col -->
	</div><!-- /form group -->
	
	<div class="form-group">
		<label for="fromName" class="col-sm-2 control-label">Naam afzender</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="fromName" name="fromName" value="<?php echo $objEmail->fromName; ?>" required>
		</div><!-- /col -->
	</div><!-- /form group -->
	
	<div class="form-group">
		<label for="bcc" class="col-sm-2 control-label">BCC e-mailadres</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="bcc" name="bcc" value="<?php echo $objEmail->bcc; ?>">
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="title" class="col-sm-2 control-label">Onderwerp</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="title" name="title" value="<?php echo $objEmail->title; ?>" required>
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="txt" class="col-sm-2 control-label">Email content</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="pagedownMe" name="txt" rows="7" required><?php echo $objEmail->txt; ?></textarea>
		</div><!-- /col -->
	</div><!-- /form group -->	

	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
		<button type="submit" class="btn btn-primary">Email aanpassen</button>
		</div><!-- /col -->
	</div><!-- /form group -->	

<hr />


<script type="text/javascript" src="/beheer/includes/script/jquery.pagedown-bootstrap.combined.min.js"></script>
<script type="text/javascript">
(function () {
 
	$("textarea#pagedownMe").pagedownBootstrap();
 
})();
</script>
<?php require('includes/php/dc_footer.php'); ?>