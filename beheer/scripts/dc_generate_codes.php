<?php
/*
*
*	Script that generates voucher codes
*
*
*
*
*/
ini_set('display_errors',1);
require_once('../../includes/php/dc_config.php');
require_once('../../_classes/class.database.php');

// New Databaseobject
$objDB = new DB();

// Kill script so it doesn't generate codes by accident. Remove when generating codes
echo "Script is uitgeschakeld.";
exit();

function generateRandomString($length = 10) {
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

$intCodes = 5000;
$intLength = 10;

for($i=0;$i<$intCodes;$i++) {

	$strCode = generateRandomString($intLength);
	
	$strSQL = "INSERT INTO ".DB_PREFIX."discountcodes_codes (codeId, code) VALUES (2, '".$strCode."')";
	$result = $objDB->sqlExecute($strSQL);

}

?>