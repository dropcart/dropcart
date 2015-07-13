<?php
// Required includes
require_once (__DIR__.'/../includes/php/dc_connect.php');
require_once (__DIR__.'/../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../beheer/includes/php/dc_config.php');

// Page specific includes
require_once (__DIR__.'/../beheer/includes/php/dc_functions.php');

// Dropcart API
require_once (__DIR__.'/../libraries/Api_Dropcart/API.class.php');

$Api_Dropcart = new Dropcart\API(API_KEY, API_DEBUG);
$Api_Dropcart->debug = true;

$strBuild = (formOption('UPDATE_BUILD') != '') ? formOption('UPDATE_BUILD') : 'stable'; // if build is not set, set to stable

$arrVersion = $Api_Dropcart->getVersions('?upgradeFrom='.DROPCART_VERSION.'&build='.$strBuild);
$strVersionDate		= new DateTime($arrVersion->{'version_'.$strBuild}->release_date);
$strVersionDate		= $strVersionDate->format('d-m-Y');
$strVersionNumber	= $arrVersion->{'version_'.$strBuild}->number;

$objDB 		= new DB();

if (isset($_GET["update"]) && $_GET["update"] == 1) {

	$arrError = array();

	$strUpdateUrl	= $arrVersion->{'version_'.$strBuild}->upgrade_url;
	$strUpdateFile	= basename($strUpdateUrl);
	$strTempDir		= TMP_PATH;

	file_put_contents($strTempDir.$strUpdateFile, file_get_contents($strUpdateUrl));
	
	$zip = new ZipArchive;
	
	if ($zip->open($strTempDir . $strUpdateFile) === TRUE) {
		
		// Make temp dir for update files
		mkdir($strTempDir . $strVersionNumber, 0777);
		
		$zip->extractTo($strTempDir . $strVersionNumber);
		
		for ($i = 0; $i < $zip->numFiles; $i++) {
			
			$filename = $zip->getNameIndex($i);
			$extenstion = pathinfo($filename, PATHINFO_EXTENSION);
			
			if($filename == '_upgrade/.htaccess') {
				continue;	
			}
			
			if($extenstion == 'sql') {
				// sql update script

				$strSQL = file_get_contents($strTempDir.$strVersionNumber.'/'.$filename);
				
				/* execute multi query */
				$result = $objDB->multi_query($strSQL);
				
				if($result['message'] != '') {
					$arrError[] = "Fout bij het updaten van de database.<br/>Foutmelding: <em>".$result['message']."</em>";
					break;
				}
				
			} else {
			
				if (!copy($strTempDir.$strVersionNumber.'/'.$filename, '../'.$filename)) {
					// Can't copy file, stop updating
					
					$arrError[] = "Kan bestand ".$filename." niet updaten. Controleer de bestandsrechten.";
//					break;
					
				}
				
			}
			
		}
		
		$zip->close();
		
		// Remove temporary files
		deleteDir($strTempDir . $strVersionNumber);
		unlink($strTempDir . $strUpdateFile);
		
		
	} else {
		
		$arrError[] = "Kan het updatebestand niet opslaan in ".$strTempDir.". Controleer of de map schrijfbaar is.";
	  
	}
	
	if (empty($arrError)) {
		$strMessage = '<div class="alert alert-success"><strong>Update voltooid.</strong></div>';
	} elseif (!empty($arrError)) {
		$strMessage = '<div class="alert alert-danger"><strong>Update mislukt.</strong><br/>'.implode('<br/>',$arrError).'</div>';
	}


}

require('includes/php/dc_header.php');
?>

<h1>Update dropcart</h1>

<hr />

<div class="col-md-12">

	<?=$strMessage?>

	<div class="panel panel-default">
		<div class="panel-heading">Update dropcart</div><!-- /panel-heading -->
		<div class="panel-body">
			<p>Uw huidige versie van Dropcart is <strong><?=DROPCART_VERSION?></strong></p>
				
			<?php if(!empty($arrVersion->{'version_'.$strBuild})) { ?>
			
				<p>Beschikbare versie: <strong><?=$strVersionNumber?></strong> (uitgebracht op <?=$strVersionDate?>)</p>
				<a class="btn btn-primary" href="?update=1">Nu updaten</a>

			<?php } else { ?>
			
				<p><strong>Geen update beschikbaar</strong></p>
				
			<?php } ?>
			
		</div><!-- /panel-body -->
	</div><!-- /panel -->

</div><!-- /col -->

<?php require('includes/php/dc_footer.php'); ?>