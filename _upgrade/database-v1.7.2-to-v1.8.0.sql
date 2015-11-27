--
-- Set version number
--
UPDATE  `dc_options` SET  `optionValue` =  'v1.8.0' WHERE  `dc_options`.`optionName` = 'dropcart_version';

--
-- Share discount codes
--
ALTER TABLE `dc_discountcodes_codes` ADD `validFrom` DATETIME NULL DEFAULT NULL AFTER `discountType`, ADD `validTill` DATETIME NULL DEFAULT NULL AFTER `validFrom`; 
ALTER TABLE `dc_discountcodes` ADD `shareRemainingDiscount` TINYINT(1) NOT NULL DEFAULT '0' AFTER `discountType`; 

INSERT INTO `dc_emails` (`id`, `emailName`, `fromEmail`, `fromName`, `cc`, `bcc`) VALUES
(NULL, 'discountCodeMail', 'info@dropcart.nl', 'Dropcart', '', 'bcc@dropcart.nl');

INSERT INTO `dc_emails_content` (`emailId`, `navTitle`, `navDesc`, `title`, `txt`) VALUES
(LAST_INSERT_ID(), 'Mail a friend met kortingscode', 'Wordt verzonden indien de klant een resterend tegoed over heeft en dit wilt schenken aan iemand.', '[CUSTOMER_NAME] heeft een cadeaubon voor [FRIEND_NAME]', 'Beste [FRIEND_NAME],\r\n\r\nU heeft van [CUSTOMER_NAME] een cadeaubon ontvangen t.v.w. EUR [DISCOUNT_AMOUNT]** om te besteden op [SITE_NAME].\r\n\r\nOm uw cadeaubon te gebruiken kunt de volgende vouchercode bij het afrekenen invullen:\r\n\r\n**[DISCOUNT_CODE]**\r\n\r\nLet op! Dit tegoed is 3 maanden geldig, hierna vervalt het tegoed.\r\n\r\nMet vriendelijke groet,\r\n\r\n[SITE_NAME] namens [CUSTOMER_NAME]'), 