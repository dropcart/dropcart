--
-- Remove "mail a friend" email
-- 
DELETE FROM `dc_emails_content` WHERE `navTitle` = 'Mail a friend met kortingscode';
DELETE FROM `dc_emails` WHERE `emailName` = 'discountCodeMail';