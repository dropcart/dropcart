--
-- Remove "mail a friend" email
-- 
DELETE FROM `dc_emails_content` WHERE `navTitle` = 'Mail a friend met kortingscode';
DELETE FROM `dc_emails` WHERE `emailName` = 'discountCodeMail';

--
-- Set version number
--
UPDATE  `dc_options` SET  `optionValue` =  'v1.6.1' WHERE  `dc_options`.`optionName` = 'dropcart_version'