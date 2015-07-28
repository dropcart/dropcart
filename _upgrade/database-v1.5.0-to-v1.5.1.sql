--
-- Remove deprecated options
-- 
DELETE FROM `dc_options` WHERE `optionName` = 'price_increment';
DELETE FROM `dc_options` WHERE `optionName` = 'price_increment_percentage';
DELETE FROM `dc_options` WHERE `optionName` = 'price_increment_addition';
DELETE FROM `dc_options` WHERE `optionName` = 'price_increment_deduction';
-- 
-- Add new options
-- 
INSERT INTO `dc_options` (`optionName`, `optionValue`) VALUES
('order_number_prefix', ''),
('price_operators', '["*"]'),
('price_values', '["1.21"]');

--
-- Set version number
--
UPDATE  `dc_options` SET  `optionValue` =  'v1.5.1' WHERE  `dc_options`.`optionName` = 'dropcart_version'