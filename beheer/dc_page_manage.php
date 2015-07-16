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
$strAction 	= $_GET['action'];

if ($strAction == "online" AND !empty($intId)) {
	$objDB->sqlExecute("UPDATE ".DB_PREFIX."pages_content SET online = '1' WHERE id = '".$intId."'");
	header('Location: '.SITE_URL.'/beheer/dc_page_admin.php');
}
elseif ($strAction == "offline" AND !empty($intId)) {
	$objDB->sqlExecute("UPDATE ".DB_PREFIX."pages_content SET online = '0' WHERE id = '".$intId."'");
	header('Location: '.SITE_URL.'/beheer/dc_page_admin.php');
}
elseif ($strAction == "remove" AND !empty($intId)) {
	$objDB->sqlDelete('pages_content', 'id', $intId);
	header('Location: '.SITE_URL.'/beheer/dc_page_admin.php?succes='.urlencode('De pagina is verwijderd.'));
}

$strSQL 	= "SELECT pc.id, pc.pageTitle, pc.pageDesc, pc.navTitle, pc.txt, pc.online FROM ".DB_PREFIX."pages_content pc WHERE pc.id = '".$intId."' ";
$result 	= $objDB->sqlExecute($strSQL);
$objPage  	= $objDB->getObject($result);

if ($_POST['navTitle'] AND $_POST['txt']) {

	$strPageTitle 	= $_POST['pageTitle'];
	$strPageDesc 	= $_POST['pageDesc'];
	$strNavTitle 		= $_POST['navTitle'];
	$strTxt 		= $_POST['txt'];

	if (empty($intId)) {
		$strSQL 	= "INSERT INTO ".DB_PREFIX."pages_content 
				(pageTitle, pageDesc, navTitle, txt)
				VALUES
				( '".$strPageTitle."',  '".$strPageDesc."',  '".$strNavTitle."',  '".$strTxt."')
				";
		$result 	= $objDB->sqlExecute($strSQL);
		$intId 		= $objDB->getInsertedId($result);

		if ($result === true) {
			header('Location: ?id='.$intId.'&action='.$strAction.'&succes='.urlencode('De pagina is aangemaakt.'));
		}
		else {
			header('Location: ?id='.$intId.'&action='.$strAction.'&fail='.urlencode('Er is iets fout gegaan.'));
		}
	}
	else {
		$strSQL 	= "UPDATE ".DB_PREFIX."pages_content 
				SET pageTitle = '".$strPageTitle."',
				pageDesc = '".$strPageDesc."', 
				navTitle = '".$strNavTitle."', 
				txt = '".$strTxt."'
				WHERE Id = '".$intId."' ";
		$result 	= $objDB->sqlExecute($strSQL);

		if ($result === true) {
			header('Location: ?id='.$intId.'&action='.$strAction.'&succes='.urlencode('De pagina is bijgewerkt.'));
		}
		else {
			header('Location: ?id='.$intId.'&action='.$strAction.'&fail='.urlencode('Er is iets fout gegaan.'));
		}
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

<h1>Pagina beheren <small><?php echo $objPage->navTitle; ?></small></h1>

<hr />

<form role="form" class="form-horizontal" method="POST">

	<div class="form-group">
		<label for="pageTitle" class="col-sm-2 control-label">Google titel</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="pageTitle" name="pageTitle" placeholder="" value="<?php echo $objPage->pageTitle; ?>">
			<p class="help-block">De pagina &lt;title&gt; welke Google gebruikt in de zoekresultaten</p>
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="pageDesc" class="col-sm-2 control-label">Pagina Omschrijving</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="pageDesc" name="pageDesc"><?php echo $objPage->pageDesc; ?></textarea>
			<p class="help-block">De &lt;meta name="description" /&gt; welke Google gebruikt in de zoekresultaten</p>
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="navTitle" class="col-sm-2 control-label">Navigatie titel</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="navTitle" name="navTitle" placeholder="" value="<?php echo $objPage->navTitle; ?>" required>
		</div><!-- /col -->
	</div><!-- /form group -->

	<div class="form-group">
		<label for="txt" class="col-sm-2 control-label">Pagina content</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="pagedownMe" name="txt" rows="7" required><?php echo $objPage->txt; ?></textarea>
		</div><!-- /col -->
	</div><!-- /form group -->	

	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
		<button type="submit" class="btn btn-primary">Pagina aanpassen</button>
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