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

