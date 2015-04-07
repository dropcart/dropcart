<?php
function formatTemplateVars($strTemplate, $templateVars = array()) {
	
	global $objDB;
	global $intCustomerId;
	global $intOrderId;
	
	$intCustomerId	= (int) $intCustomerId;
	$intOrderId		= (int) $intOrderId;

	/**
	 * Array of possibles variables.
	 * For each variable, the following options are available:
	 * @param [Array} Get contents out MySQL database: {String} table name, column {String} or {Array}, {String} where
	 * @param [String} Returns given string
	 * @param [Function} Returns string from given function
	 */
	
	$orderNumberPrefix = formOption('order_number_prefix');
	 
	$arrVariables = array(
		
		'SITE_NAME' => array(
			DB_PREFIX.'options',
			'optionValue',
			'optionName=\'site_name\''
		),
		'CUSTOMER_NAME' => array(
			DB_PREFIX.'customers',
			array('firstname', 'lastname'),
			'id='.$intCustomerId
		),
		'DISCOUNT_CODE' => array(
			DB_PREFIX.'discountcodes_codes',
			'code',
			'parentOrderId='.$intOrderId
		),
		'DISCOUNT_AMOUNT' => array(
			DB_PREFIX.'discountcodes_codes',
			'discountValue',
			'parentOrderId='.$intOrderId
		),
		'ORDER_NR' => $orderNumberPrefix.$intOrderId,
		'ORDER_ADDRESSES' => loadAddresses(),
		'ORDER_DETAILS' => loadOrderDetails()
		
	);
	
	$arrVariables = array_merge($arrVariables, $templateVars);
	
	foreach($arrVariables as $strVariable => $arrResult) {
		// Search for every variable
		
		if (strpos($strTemplate,$strVariable) !== false) {
			// Variable found, replace variable dynamic
			
			if(is_array($arrResult)) {
				
				$strFrom = $arrResult[0];
				
				if(is_array($arrResult[1])) {
					
					$strColumns = implode(',', $arrResult[1]);
					
				} else {
					
					$strColumns = $arrResult[1];
					
				}
				
				$strWhere = $arrResult[2];
				$strWhere = str_replace('%1', "'" . $strVariable . "'", $strWhere);
			
				$strSQL = "SELECT " . $strColumns . " FROM  " . $strFrom . " WHERE " . $strWhere;
				$result = $objDB->sqlExecute($strSQL);
				$objResult = $objDB->getObject($result);
				
				if(is_array($arrResult[1])) {
					
					$strReplace = '';
					
					foreach($arrResult[1] as $column) {
						$strReplace .=  $objResult->$column . ' ';
					}
					
				} else {
					
					$strReplace = $objResult->$arrResult[1];
					
				}
				
				$strTemplate = str_replace('[' . $strVariable . ']', $strReplace, $strTemplate);
				
			} else {

				$strTemplate = str_replace('[' . $strVariable . ']', $arrResult, $strTemplate);
				
			}
			
			
		}
		
	}
	
	return $strTemplate;
	
	
}

function sendMail($strMailName, $strToEmail, $strToName, $templateVars = array(), $strAttachment = '') {

	global $objDB;

	require_once SITE_PATH.'_classes/PHPMailerAutoload.php';
	require_once SITE_PATH.'libraries/Parsedown/Parsedown.php';

	$strSQL = "SELECT ec.txt " .
		"FROM ".DB_PREFIX."emails e " .
		"INNER JOIN ".DB_PREFIX."emails_content ec ON ec.emailId = e.id ".
		"WHERE e.emailName = 'template'";
	$result = $objDB->sqlExecute($strSQL);
	list($strTemplate)	= $objDB->getRow($result);

	$strSQL = "SELECT * " .
		"FROM ".DB_PREFIX."emails e " .
		"INNER JOIN ".DB_PREFIX."emails_content ec ON ec.emailId = e.id ".
		"WHERE e.emailName = '".$strMailName."'";
	$result = $objDB->sqlExecute($strSQL);
	$objEmail = $objDB->getObject($result);

	$mail 		= new PHPMailer;
	$Parsedown 	= new Parsedown();

	if(formOption('mail_server') != 'smtp') {
		
		$mail->isMail();	 // Set mailer to use PHP mail() function
		
	} else {
		
		$mail->isSmtp();	 // Set mailer to use SMTP server
		$mail->Host 		= formOption('smtp_server');  // Specify main and backup SMTP servers
		$mail->Port 		= formOption('smtp_port');
		if(formOption('smtp_secure') != '') {
			$mail->SMTPSecure 	= formOption('smtp_secure');
		}
		
		if(formOption('smtp_auth') == 'true') {
			$mail->SMTPAuth 	= true;
			$mail->Username 	= formOption('smtp_username');
			$mail->Password 	= formOption('smtp_password');
		}
	}
	
	$mail->From = formatTemplateVars($objEmail->fromEmail, $templateVars);
	$mail->FromName = formatTemplateVars($objEmail->fromName, $templateVars);
	$mail->addAddress($strToEmail, $strToName);     // Add a recipient
	$mail->addReplyTo($objEmail->fromEmail, $objEmail->fromName);
	$mail->addBCC(formatTemplateVars($objEmail->bcc, $templateVars));

	//adding attachment
	if($strAttachment != '') {
//		$mail->addAttachment($strAttachment);
		$mail->AddStringAttachment($strAttachment, 'attachment.pdf', 'base64', 'application/pdf');
	}
	
	$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
	$mail->isHTML(true);                                  // Set email format to HTML
	
	$mail->Subject = formatTemplateVars($objEmail->title, $templateVars);

	//reading e-mail template
	$arrTemplate = file(SITE_EMAIL_TEMPLATE);
	for($x=0;$x<count($arrTemplate);$x++) {
		$strEmailTemplate .= $arrTemplate[$x];
	}

	$strBody = formatTemplateVars($Parsedown->text($objEmail->txt), $templateVars);
	$strBodyTemplate = str_replace("[BODY]",$strBody,formatTemplateVars($Parsedown->text($strTemplate), $templateVars));
	
	//putting in the body
	$strEmailBody = str_replace("##BODY##",$strBodyTemplate,$strEmailTemplate);

	$mail->Body    = $strEmailBody;

	if(!$mail->send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	} else {
//		echo 'Message has been sent';
	}
	
}

function loadAddresses(){
	
	global $objDB;
	global $intOrderId;
	
	if(!empty($intOrderId)) {
	
		$strSQL = "SELECT company, firstname, lastname, address, houseNr, houseNrAdd, zipcode, city, lang, delCompany, delFirstname, delLastname, delAddress, delHouseNr, delHouseNrAdd, delZipcode, delCity, delLang " .
			"FROM ".DB_PREFIX."customers_orders " .
			"WHERE orderId = ".$intOrderId;
		$result = $objDB->sqlExecute($strSQL);
		$objOrder = $objDB->getObject($result);
		
		$strHtml = '
			<table cellpadding="0" valign="top" cellspacing="0" border="0" width="100%">
				<tr>
					<td width="40%"><table cellpadding="0" valign="top" cellspacing="0" border="0" width="100%">
							<tr>
								<td style="text-align: left; font-family: Arial, sans-serif; color: #000000; font-size: 16px; font-weight: normal; line-height: 30px; margin: 0;border-bottom: 1px solid #e1e1e1;">Factuuradres</td>
							</tr>
							<tr>
								<td style="color: #969696; font-family: Arial, sans-serif;text-align: left; font-size: 13px; font-weight: normal; line-height: 30px; border-bottom: 1px solid #e1e1e1;">'.$objOrder->firstname.' '.$objOrder->lastname.'</td>
							</tr>
							<tr>
								<td style="color: #969696; font-family: Arial, sans-serif;text-align: left; font-size: 13px; font-weight: normal; line-height: 30px; border-bottom: 1px solid #e1e1e1;">'.$objOrder->address.' '.$objOrder->houseNr.' '.$objOrder->houseNrAdd.'</td>
							</tr>
							<tr>
								<td style="color: #969696; font-family: Arial, sans-serif;text-align: left; font-size: 13px; font-weight: normal; line-height: 30px; border-bottom: 1px solid #e1e1e1;">'.$objOrder->zipcode.' '.$objOrder->city.'</td>
							</tr>
						</table></td>
					<td width="20%"></td>
					<td width="40%"><table cellpadding="0" valign="top" cellspacing="0" border="0" width="100%">
							<tr>
								<td style="text-align: left; font-family: Arial, sans-serif; color: #000000; font-size: 16px; font-weight: normal; line-height: 30px; margin: 0;border-bottom: 1px solid #e1e1e1;">Afleveradres</td>
							</tr>
							<tr>
								<td style="color: #969696; font-family: Arial, sans-serif;text-align: left; font-size: 13px; font-weight: normal; line-height: 30px; border-bottom: 1px solid #e1e1e1;">'.$objOrder->delFirstname.' '.$objOrder->delLastname.'</td>
							</tr>
							<tr>
								<td style="color: #969696; font-family: Arial, sans-serif;text-align: left; font-size: 13px; font-weight: normal; line-height: 30px; border-bottom: 1px solid #e1e1e1;">'.$objOrder->delAddress.' '.$objOrder->delHouseNr.' '.$objOrder->delHouseNrAdd.'</td>
							</tr>
							<tr>
								<td style="color: #969696; font-family: Arial, sans-serif;text-align: left; font-size: 13px; font-weight: normal; line-height: 30px; border-bottom: 1px solid #e1e1e1;">'.$objOrder->delZipcode.' '.$objOrder->delCity.'</td>
							</tr>
						</table></td>
				</tr>
			</table>';
		
		return $strHtml;
		
	} else {
		
		return false;
		
	}

}

function loadOrderDetails(){
	
	global $objDB;
	global $intOrderId;
	global $Api;
	
	if(!empty($intOrderId)) {
	
		$strHtml = '
			<div style="text-align: left; color: #000000; font-size: 16px; font-weight: normal; line-height: 30px;margin-top:20px">Uw bestelling</div>
			<table cellpadding="5" cellspacing="0" border="0" width="100%" align="left" style=" margin: 0 17px 0 0;table-layout: fixed;color: #969696; font-size: 13px; line-height: 20px;">
		';
		
		// get products ordered
		$strSQL = 
			"SELECT cod.productId,
			cod.quantity
			FROM ".DB_PREFIX."customers_orders_details cod " .
			"WHERE cod.orderId = ".$intOrderId;
		$result = $objDB->sqlExecute($strSQL);

		while($objCart = $objDB->getObject($result)) {
		
			$Product		= $Api->getProduct($objCart->productId);
			$dblPrice 		= calculateProductPrice($Product->getPrice(), $objCart->productId, $objCart->quantity, false);
			$strPrice 		= '&euro; ' . number_format($dblPrice, 2, ',', ' ');
			$dblPriceTotal 	+= $dblPrice * $objCart->quantity;

			$strHtml .= '
				<tr>
					<td valign="top" align="left" width="10%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
						'.$objCart->quantity.'x
					</td>
					<td valign="top" align="left" width="50%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
						'.$Product->getTitle().'
					</td>
					<td valign="top" width="40%" align="right" style="border-bottom: 1px solid #e1e1e1;">
						<span style="font-weight: bold;font-family: Arial, sans-serif; color: #000000; font-size: 13px;">' . $strPrice . '</span>
					</td>
				</tr>
			';
		}

		// get other order details
		$strSQL = 
			"SELECT co.shippingCosts, 
			co.totalPrice,
			co.kortingsbedrag
			FROM ".DB_PREFIX."customers_orders co
			WHERE co.orderId = '".$intOrderId."' ";
		$result = $objDB->sqlExecute($strSQL);
		$objOrder = $objDB->getObject($result);

		$strHtml .= '
			<tr>
				<td valign="top" align="left" width="10%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
					&nbsp;
				</td>
				<td valign="top" align="left" width="50%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
					Subtotaal
				</td>
				<td valign="top" width="40%" align="right" style="border-bottom: 1px solid #e1e1e1;">
					<span style="font-weight: bold;font-family: Arial, sans-serif; color: #000000; font-size: 13px;">' . money_format('%(#1n', $dblPriceTotal) . '</span>
				</td>
			</tr>
			<tr>
				<td valign="top" align="left" width="10%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
					&nbsp;
				</td>
				<td valign="top" align="left" width="50%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
					Verzendkosten
				</td>
				<td valign="top" width="40%" align="right" style="border-bottom: 1px solid #e1e1e1;">
					<span style="font-weight: bold;font-family: Arial, sans-serif; color: #000000; font-size: 13px;">' . money_format('%(#1n', $objOrder->shippingCosts) . '</span>
				</td>
			</tr>
		';

		if ($objOrder->kortingsbedrag > 0) {
			$strHtml .= '
				<tr>
					<td valign="top" align="left" width="10%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
						&nbsp;
					</td>
					<td valign="top" align="left" width="50%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
						Korting
					</td>
					<td valign="top" width="40%" align="right" style="border-bottom: 1px solid #e1e1e1;">
						<span style="font-weight: bold;font-family: Arial, sans-serif; color: #000000; font-size: 13px;">' . money_format('%(#1n', $objOrder->kortingsbedrag) . '</span>
					</td>
				</tr>
			';
		}

		$strHtml .= '
			<tr>
				<td valign="top" align="left" width="10%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
					&nbsp;
				</td>
				<td valign="top" align="left" width="50%" style="font-family: Arial, sans-serif; border-bottom: 1px solid #e1e1e1;">					
					Totaal
				</td>
				<td valign="top" width="40%" align="right" style="border-bottom: 1px solid #e1e1e1;">
					<span style="font-weight: bold;font-family: Arial, sans-serif; color: #000000; font-size: 13px;">' . money_format('%(#1n', $objOrder->totalPrice) . '</span>
				</td>
			</tr>
		';
		
		$strHtml .= '</table>';
		
		return $strHtml;
		
	} else {
		
		return false;
		
	}

}
?>
