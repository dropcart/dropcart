<?

$strSQL = "SELECT * FROM ".DB_PREFIX."customers_orders WHERE id = ".$intOrderId;
$result = $objDB->sqlExecute($strSQL);
$objOrder = $objDB->getObject($result);

$strEmail = 
	"Beste ".$objOrder->firstname." ".$objOrder->lastname.",<br/>" .
	"Hartelijk dank voor uw bestelling bij " . SITE_NAME . ".<br/>" .
	"";

?>