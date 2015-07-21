<?php

// Required includes
require_once (__DIR__.'/../includes/php/dc_connect.php');
require_once (__DIR__.'/../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../beheer/includes/php/dc_config.php');

// Page specific includes
require_once (__DIR__.'/../beheer/includes/php/dc_functions.php');
// Start API
require_once(__DIR__.'../../libraries/Api_Inktweb/API.class.php');
$Api 			= new Inktweb\API(API_KEY, API_TEST, API_DEBUG);
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
  <li><a href="#categories" role="tab" data-toggle="tab">Categorie&euml;n</a></li>
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
	<?php
        $categories = array();
        $result = $Api->getProductsByCategory(0);

        $sqlCustomText = "SELECT * FROM ".DB_PREFIX."content_boilerplate";
        $resultCustomText = $objDB->sqlExecute($sqlCustomText);


        if( isset($result->categories) && is_array($result->categories)){
            $categories = $result->categories;
        }

        while($row = $objDB->getArray($resultCustomText)){

            foreach( $row as $col => $value){
                $customTextValues[$row['category_id']][$col] = $value;
            }
        }

    ?>
	<div class="tab-pane" id="categories">
		<div class="panel panel-default">
			<div class="panel-heading">Categorie&euml;n</div><!-- /panel-heading -->
			<div class="panel-body">
                <form role="form" action="<?php echo SITE_URL ?>/beheer/dc_categories_text.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Opslaan</button>
                </div>

                <?php foreach($categories as $category): ?>
                   
                    <div class="form-group">
                        <h2><?php echo $category->title?></h2>
                        <div class="form-group">
                            <a class="btn btn-default" href="<?php echo SITE_URL.'/categorie/'.$category->id.'/' ?>"><i class="fa fa-eye"></i> Bekijk categorie</a>
                        </div>
                        <div class="row">

                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="category_title_<?php echo $category->id ?>">Categorie titel</label>
                                    <input id="category_title_<?php echo $category->id ?>"
                                           type="text" class="form-control"
                                           name="categories[<?php echo $category->id?>][category_title]"
                                           placeholder="categorie titel"
                                            value="<?php echo
                                                    (isset($customTextValues[$category->id]['category_title']))
                                                    ? $customTextValues[$category->id]['category_title']
                                                    : null
                                                ?>"/>
                                </div>
                                <div class="form-group">
                                    <label for="category_desc_<?php echo $category->id ?>">Categorie beschrijving</label>
                                    <textarea class="form-control" name="categories[<?php echo $category->id?>][category_desc]" id="category_desc_<?php echo $category->id ?>" cols="30" rows="10" placeholder="Beschrijving categorie"><?php
                                        echo (isset($customTextValues[$category->id]['category_desc']))
                                        ? $customTextValues[$category->id]['category_desc'] : null
                                        ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label for="product_title_<?php echo $category->id ?>">Product titel</label>
                                    <input id="product_title_<?php echo $category->id ?>" type="text" class="form-control" name="categories[<?php echo $category->id?>][product_title]" placeholder="product titel" value="<?php
                                    echo (isset($customTextValues[$category->id]['product_title']))
                                        ? $customTextValues[$category->id]['product_title'] : null
                                    ?>"/>
                                </div>
                                <div class="form-group">
                                    <label for="product_desc_<?php echo $category->id ?>">Product beschrijving</label>
                                    <textarea class="form-control" name="categories[<?php echo $category->id?>][product_desc]" id="product_desc_<?php echo $category->id ?>" cols="30" rows="10" placeholder="Beschrijving categorie"><?php
                                        echo (isset($customTextValues[$category->id]['product_desc']))
                                            ? $customTextValues[$category->id]['product_desc'] : null
                                        ?></textarea>
                                </div>

                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Bevat markdown <input type="checkbox"
                                                                 name="categories[<?php echo $category->id?>][parse_markdown]"
                                                                 value="1"

                                            <?php
                                        echo (
                                            isset($customTextValues[$category->id]['parse_markdown'])
                                            && $customTextValues[$category->id]['parse_markdown'] == 1
                                        )
                                            ? 'checked="checked"'  : null
                                        ?>
                                            /></label>
                                    <label>Bevat boilerplate <input type="checkbox"
                                                                    name="categories[<?php echo $category->id?>][parse_boilerplate]"
                                                                    value="1"
                                            <?php
                                            echo (
                                                isset($customTextValues[$category->id]['parse_boilerplate'])
                                                && $customTextValues[$category->id]['parse_boilerplate'] == 1
                                            )
                                                ? 'checked="checked"'  : null
                                            ?>/></label>

                                </div>
                            </div>

                        </div>
                        </div>

                <?php endforeach; ?>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Opslaan</button>
                </div>
                    </form>
			</div>
		</div>
	</div>
</div>
	

</div><!-- /col -->

<script>
    $(function(){
        var hash = window.location.hash;
        hash && $('ul.nav a[href="' + hash + '"]').tab('show');

        $('.nav-tabs a').click(function (e) {
            $(this).tab('show');
            var scrollmem = $('body').scrollTop();
            window.location.hash = this.hash;
            $('html,body').scrollTop(scrollmem);
        });
    });
</script>

<?php require('includes/php/dc_footer.php'); ?>