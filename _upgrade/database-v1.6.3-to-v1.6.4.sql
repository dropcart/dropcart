
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


