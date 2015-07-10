<?php

// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_config.php');

// Page specific includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_functions.php');

if (isset($_POST)) {

	$_POST 	= sanitize($_POST);
	if( !empty($_POST) ) {
		foreach ($_POST as $key => $value) {

			// only insert actual content and not the labels
			if (!empty($value) AND ($value != "1")) {

				// get $value for markdown checkbox
				$parse_markdown = $_POST[$key . '_markdown'];
				if (empty($parse_markdown)) {
					$parse_markdown = '0';
				}

				// get $value for boilerplate checkbox
				$parse_boilerplate = $_POST[$key . '_boilerplate'];
				if (empty($parse_boilerplate)) {
					$parse_boilerplate = '0';
				}

				$strSQL =
					"INSERT INTO " . DB_PREFIX . "content
				(name, value, parse_markdown, parse_boilerplate) 
				VALUES 
				('" . $key . "', '" . $value . "', '" . $_POST[$key . '_markdown'] . "', '" . $_POST[$key . '_boilerplate'] . "')
				ON DUPLICATE KEY UPDATE 
				name = '" . $key . "',
				value = '" . $value . "',
				parse_markdown = '" . $_POST[$key . '_markdown'] . "',
				parse_boilerplate = '" . $_POST[$key . '_boilerplate'] . "' ";
				$objDB->sqlExecute($strSQL);
			}


		}
	}

}

require('includes/php/dc_header.php');
?>

<h1>Content </h1>

<hr />

<?php

if (!empty($_GET['succes'])) {
	echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> '.$_GET['succes'].'</div>';
}

?>

<div class="col-md-12">

<ul class="nav nav-tabs" role="tablist">
  <li class="active"><a href="#meta" role="tab" data-toggle="tab">Pagina specifiek</a></li>
  <li><a href="#other" role="tab" data-toggle="tab">Overig</a></li>
</ul>


<div class="tab-content">
  <div class="tab-pane active" id="meta">
  
	<div class="panel panel-default">
		<div class="panel-heading">Website content</div><!-- /panel-heading -->
		<div class="panel-body">

		<form class="form-horizontal" role="form" method="POST" autocomplete="off">

			<?php
			$strSQL 	= "SELECT name, label, value, description, parse_markdown, parse_boilerplate FROM ".DB_PREFIX."content WHERE type = 1";
			$result 	=$objDB->sqlExecute($strSQL);
			while ($objContent = $objDB->getObject($result)) {

			?>

				<div class="form-group">
				<label for="<?php echo $objContent->name; ?>" class="col-sm-2 control-label"><?php echo $objContent->label; ?></label>
					<div class="col-sm-8">
						<textarea class="form-control" id="<?php echo $objContent->name; ?>" name="<?php echo $objContent->name; ?>"><?php echo getContent($objContent->name, false); ?></textarea>
						<?php
						if (!empty($objContent->description)) {
							echo '<p class="help-block">'.$objContent->description.'</p>';
						}
						?>

						<label>
						<input type="checkbox" value="1" name="<?php echo $objContent->name; ?>_markdown" <?php if ($objContent->parse_markdown == 1) { echo 'checked'; } ?> /> Bevat Markdown
						</label>

						<label>
						<input type="checkbox" value="1" name="<?php echo $objContent->name; ?>_boilerplate" <?php if ($objContent->parse_boilerplate == 1) { echo 'checked'; } ?> /> Bevat Boilerplate
						</label>
					</div><!-- /col -->
				</div><!-- /form-group -->

			<?php
			}

			?>

					
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-8">
					<button type="submit" class="btn btn-default">Bewerken</button>
				</div><!-- /col -->
			</div><!-- /form-group -->
		</form><!-- /form -->

		</div><!-- /panel-body -->
	</div><!-- /panel -->
  
  </div>
  
  <div class="tab-pane" id="other">
  
	<div class="panel panel-default">
		<div class="panel-heading">Website content</div><!-- /panel-heading -->
		<div class="panel-body">

		<form class="form-horizontal" role="form" method="POST" autocomplete="off">

			<?php
			$strSQL 	= "SELECT name, label, value, description, parse_markdown, parse_boilerplate FROM ".DB_PREFIX."content WHERE type = 2";
			$result 	=$objDB->sqlExecute($strSQL);
			while ($objContent = $objDB->getObject($result)) {

			?>

				<div class="form-group">
				<label for="<?php echo $objContent->name; ?>" class="col-sm-2 control-label"><?php echo $objContent->label; ?></label>
					<div class="col-sm-8">
						<textarea class="form-control" id="<?php echo $objContent->name; ?>" name="<?php echo $objContent->name; ?>"><?php echo getContent($objContent->name, false); ?></textarea>
						<?php
						if (!empty($objContent->description)) {
							echo '<p class="help-block">'.$objContent->description.'</p>';
						}
						?>

						<label>
						<input type="checkbox" value="1" name="<?php echo $objContent->name; ?>_markdown" <?php if ($objContent->parse_markdown == 1) { echo 'checked'; } ?> /> Bevat Markdown
						</label>

						<label>
						<input type="checkbox" value="1" name="<?php echo $objContent->name; ?>_boilerplate" <?php if ($objContent->parse_boilerplate == 1) { echo 'checked'; } ?> /> Bevat Boilerplate
						</label>
					</div><!-- /col -->
				</div><!-- /form-group -->

			<?php
			}

			?>

					
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-8">
					<button type="submit" class="btn btn-default">Bewerken</button>
				</div><!-- /col -->
			</div><!-- /form-group -->
		</form><!-- /form -->

		</div><!-- /panel-body -->
	</div><!-- /panel -->
  
  </div>
</div>
	

</div><!-- /col -->

<script>
$('.nav-tabs a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
</script>

<?php require('includes/php/dc_footer.php'); ?>