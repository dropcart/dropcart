--
-- Set version number
--
UPDATE  `dc_options` SET  `optionValue` =  'v1.7.5' WHERE  `dc_options`.`optionName` = 'dropcart_version';

--
-- Set `default_product_image`
--
UPDATE  `dc_options` SET  `optionValue` =  '/images/defaultthumb.png' WHERE  `dc_options`.`optionName` = 'default_product_image';
