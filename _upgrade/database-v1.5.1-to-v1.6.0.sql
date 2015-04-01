--
-- Table structure for table `dc_products_tiered`
--
CREATE TABLE IF NOT EXISTS `dc_products_tiered` (
`id` int(11) unsigned NOT NULL,
  `productId` int(11) unsigned NOT NULL,
  `quantity` int(5) unsigned DEFAULT NULL,
  `percentage` int(3) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `dc_products_tiered`
 ADD PRIMARY KEY (`id`), ADD KEY `productId` (`productId`);


 --
 -- Set version number
 --
 UPDATE  `dc_options` SET  `optionValue` =  'v1.6.0' WHERE  `dc_options`.`optionName` = 'dropcart_version'