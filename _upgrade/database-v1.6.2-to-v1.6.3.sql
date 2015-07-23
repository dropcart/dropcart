--
-- Set version number
--
UPDATE  `dc_options` SET  `optionValue` =  'v1.6.3' WHERE  `dc_options`.`optionName` = 'dropcart_version';


INSERT INTO `dc_options` (`id`, `optionName`, `optionValue`)
  VALUES
    (33, 'site_street_name', 'Professor van der Waalstraat'),
    (34, 'site_street_number', '2'),
    (35, 'site_street_number_addition', NULL),
    (36, 'site_postal_code', '1821 BT'),
    (37, 'site_city_name', 'Alkmaar'),
    (38, 'site_phone_number', '072-5675055'),
    (40, 'site_kvk', '60680326'),
    (41, 'site_btw', 'NL854012965B01'),
    (42, 'site_iban', 'NL77RABO0148966128'),
    (43, 'site_bic', 'RABONL2U');