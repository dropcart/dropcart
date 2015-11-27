--
-- Set version number
--
UPDATE  `dc_options` SET  `optionValue` =  'v1.7.2' WHERE  `dc_options`.`optionName` = 'dropcart_version';

--
-- Set `site_logo`
--
INSERT INTO `dc_options` (`optionName`, `optionValue`) VALUES ('site_logo', 'logo.png');