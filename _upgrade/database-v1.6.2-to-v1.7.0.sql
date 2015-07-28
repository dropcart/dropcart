--
-- Set version number
--
UPDATE  `dc_options` SET  `optionValue` =  'v1.7.0' WHERE  `dc_options`.`optionName` = 'dropcart_version';


--
-- Add address information to options table
--
INSERT INTO `dc_options` (`id`, `optionName`, `optionValue`)
VALUES
(33, 'site_street_name', 'Straatnaam'),
(34, 'site_street_number', '2'),
(35, 'site_street_number_addition', NULL),
(36, 'site_postal_code', '1000 AA'),
(37, 'site_city_name', 'Duckstad'),
(38, 'site_phone_number', '0123456789'),
(40, 'site_kvk', '123456'),
(41, 'site_btw', 'NLBTW'),
(42, 'site_iban', 'NLIBAN'),
(43, 'site_bic', 'RABONL2U');


--
-- Table structure for table `dc_cart_archive`
--

CREATE TABLE IF NOT EXISTS `dc_cart_archive` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entryDate` datetime NOT NULL,
  `orderId` int(11) NOT NULL,
  `customerId` int(11) DEFAULT NULL,
  `productId` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Rename current table
RENAME TABLE `dc_content_boilerplate` TO `dc_content_tags` ;

--
-- Table structure for table `dc_content_boilerplate`
--

CREATE TABLE IF NOT EXISTS `dc_content_boilerplate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `category_title` text,
  `category_desc` text,
  `product_title` text,
  `product_desc` text,
  `parse_markdown` tinyint(4) NOT NULL DEFAULT '0',
  `parse_boilerplate` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

