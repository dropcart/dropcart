<?php

$strSQL = "SELECT * FROM ".DB_PREFIX."customers_orders WHERE id = ".$intOrderId;
$result = $objDB->sqlExecute($strSQL);
$objOrder = $objDB->getObject($result);

$strEmail = $text['MAIL_HEADER'] . " " . $objOrder->firstname." ".$objOrder->lastname.",<br/>" . $text['MAIL_BODY'] . " " . SITE_NAME . ".<br/>" .
    "";

?>