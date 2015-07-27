-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 11, 2014 at 01:39 PM
-- Server version: 5.5.9-log
-- PHP Version: 5.5.18
-- Dropcart Version: v1.6.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Table structure for table `dc_admin_users`
--

CREATE TABLE IF NOT EXISTS `dc_admin_users` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `dc_admin_users`
--

INSERT INTO `dc_admin_users` (`id`, `name`, `email`, `username`, `password`) VALUES
(1, 'admin', 'info@inktweb.nl', 'admin', '$2y$10$.95W2HD1KaUhERoYleNDv.QRF3476SZD6EnDYHYldoH/zKVqy9P/O');

--
-- Table structure for table `dc_cart`
--

CREATE TABLE IF NOT EXISTS `dc_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entryDate` datetime NOT NULL,
  `sessionId` varchar(80) NOT NULL,
  `customerId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Table structure for table `dc_content`
--

CREATE TABLE IF NOT EXISTS `dc_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` varchar(255) NOT NULL COMMENT 'for internal use',
  `parse_markdown` tinyint(1) NOT NULL DEFAULT '0',
  `parse_boilerplate` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `dc_content`
--

INSERT INTO `dc_content` (`id`, `type`, `name`, `label`, `value`, `description`, `parse_markdown`, `parse_boilerplate`) VALUES
(23, 2, 'default_product_content', 'Standaard product beschrijving', '[SITE_NAME] biedt het beste alternatief voor de (vaak dure) originele [PRODUCT_OEM]. Deze compatible [PRODUCT_TITLE] is net zo goed als de originele tegenhanger en zal de garantie van uw [PRODUCT_BRAND] printer niet doen vervallen. \r\n\r\nAls u zoekt naar de beste print kwaliteit voor zeer schappelijke prijs... kijk dan niet verder dan de compatible [PRODUCT_OEM].', '', 0, 1),
(24, 2, 'default_product_title', 'Standaard product titel', '[PRODUCT_BRAND] [PRODUCT_OEM] [PRODUCT_SPEC_COLOR] ([PRODUCT_SPEC_TYPE])', '', 0, 1),
(25, 2, 'html_footer_copyright', 'Footer copyright', 'Alle prijzen zijn inclusief BTW - Merknamen zijn alleen gebruikt om de toepasbaarheid van de producten aan te geven en dienen verder niet te worden geassocieerd met [SITE_NAME].', '', 0, 1),
(26, 2, 'html_footer_powered', 'Footer powered by', 'Powered by', '', 0, 0),
(27, 2, 'html_header_delivery', 'Delivery header tekst', 'Milieuvriendelijke manier van printen', '', 0, 0),
(28, 2, 'page_delivery_info', 'Delivery popup tekst', '#### Snelle levering! ####\r\n\r\nWij streven ernaar uw bestelling zo snel mogelijk te laten bezorgen. Is het langer dan 4 werkdagen geleden dat uw bestelling is doorgegeven en heeft u de bestelling nog niet ontvangen, neemt u dan contact met ons op. Als deze periode korter is dan 4 werkdagen, verzoeken wij u vriendelijk om geduld. Het is namelijk mogelijk dat uw pakket binnen deze termijn alsnog bezorgd wordt. Ook is het mogelijk dat de bezorger uw bestelling heeft afgeleverd op het adres van buren. Hierom verzoeken wij u om bij uw buren of medebewoners navraag te doen.\r\n\r\nBij alle artikelen op onze webshop staat een indicatieve voorraad aangegeven. De meeste artikelen hebben wij ruim op voorraad. Voor de artikelen die niet direct op voorraad zijn, gelden andere levertijden. Deze levertijden kunt u bij ons informeren.', '', 1, 0),
(41, 1, 'homepage_title', 'Homepage titel', 'Bestel cartridges en toner direct op [SITE_NAME].', '', 0, 1),
(42, 1, 'homepage_meta_description', 'Homepage meta description', 'Cartridges of toners kopen? Bestel direct op [SITE_NAME]! Voor cartridges, inktpatronen, toners en printpapier.', '', 0, 1),
(43, 1, 'category_title', 'Categorie titel', 'Bestel cartridges en toner direct op [SITE_NAME].', '', 0, 1),
(44, 1, 'category_meta_description', 'Categorie meta description', 'Cartridges of toners kopen? Bestel direct op [SITE_NAME]! Voor cartridges, inktpatronen, toners en printpapier.', '', 0, 1),
(45, 1, 'search_title', 'Zoeken titel', 'Bestel cartridges en toner direct op [SITE_NAME].', '', 0, 1),
(46, 1, 'search_meta_description', 'Zoeken meta description', 'Cartridges of toners kopen? Bestel direct op [SITE_NAME]! Voor cartridges, inktpatronen, toners en printpapier.', '', 0, 1),
(47, 1, 'product_title', 'Product titel', '[PRODUCT_BRAND] [PRODUCT_OEM] [PRODUCT_SPEC_COLOR] ([PRODUCT_SPEC_TYPE]) - [SITE_NAME]', '', 0, 1),
(48, 1, 'product_meta_description', 'Product meta description', 'Bestel de [PRODUCT_BRAND] [PRODUCT_OEM] [PRODUCT_SPEC_COLOR] ([PRODUCT_SPEC_TYPE]) bij [SITE_NAME]. Op voorraad is binnen 24 uur geleverd.', '', 0, 1),
(49, 1, 'page_title', 'Content pagina titel', 'Bestel cartridges en toner direct op [SITE_NAME].', '', 0, 1),
(50, 1, 'page_meta_description', 'Content pagina meta description', 'Cartridges of toners kopen? Bestel direct op [SITE_NAME]! Voor cartridges, inktpatronen, toners en printpapier.', '', 0, 1),
(51, 1, 'printer_title', 'Printer titel', 'Cartridges voor de [PRINTER_TYPE]', '', 0, 1),
(52, 1, 'printer_meta_description', 'Meta description voor printerpaginas', 'Producten geschikt voor de [PRINTER_BRAND] [PRINTER_TYPE]', '', 0, 1);

--
-- Table structure for table `dc_content_tags`
--

CREATE TABLE IF NOT EXISTS `dc_content_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) NOT NULL,
  `desc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dc_content_tags`
--

INSERT INTO `dc_content_tags` (`id`, `tag`, `desc`) VALUES
(1, '[PRODUCT_BRAND]', 'Printermerk van het product'),
(2, '[SITE_NAME]', 'Naam van de website'),
(3, '[PRODUCT_ID]', 'Database ID van product'),
(4, '[PRODUCT_EAN]', '13 cijferige unieke code'),
(5, '[PRODUCT_OEM]', 'OEM/Artikelnummer'),
(6, '[PRODUCT_TITLE]', 'Inktweb.nl product title'),
(7, '[PRODUCT_PRICE]', 'Prijs van product (float)'),
(8, '[SITE_URL]', ''),
(9, '[SITE_EMAIL]', 'Standaard website email'),
(10, '[PRODUCT_SPEC_COLOR]', 'Specificatie: kleur'),
(11, '[PRODUCT_SPEC_WEIGHT]', 'Specificatie: gewicht (in gram)'),
(12, '[PRODUCT_SPEC_CAPACITY]', 'Specificatie: inhoud (e.g. 4 bij een multipack)'),
(13, '[PRODUCT_SPEC_ML]', 'Specificatie: inhoud millimeters'),
(14, '[PRODUCT_SPEC_PAGES]', 'Specificatie: inhoud aantal pagina''s'),
(15, '[PRODUCT_SPEC_TYPE]', 'Specificatie: type product (e.g. Inkjet Cartridge)');

--
-- Table structure for table `dc_customers`
--

CREATE TABLE IF NOT EXISTS `dc_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entryDate` datetime NOT NULL,
  `birthDate` date NOT NULL,
  `gender` tinyint(1) NOT NULL,
  `company` varchar(200) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;


--
-- Table structure for table `dc_customers_addresses`
--

CREATE TABLE IF NOT EXISTS `dc_customers_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `custId` int(11) NOT NULL,
  `entryDate` datetime NOT NULL,
  `defaultInv` tinyint(1) NOT NULL,
  `defaultDel` tinyint(1) NOT NULL,
  `dropshipment` tinyint(1) NOT NULL,
  `addressName` varchar(120) NOT NULL,
  `company` varchar(120) NOT NULL,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(120) NOT NULL,
  `address` varchar(120) NOT NULL,
  `houseNr` varchar(10) NOT NULL,
  `houseNrAdd` varchar(10) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `city` varchar(120) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `online` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `custId` (`custId`),
  KEY `default` (`defaultInv`),
  KEY `defaultDel` (`defaultDel`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Table structure for table `dc_customers_orders`
--

CREATE TABLE IF NOT EXISTS `dc_customers_orders` (
  `uuid` char(36) NOT NULL,
  `orderId` int(11) NOT NULL AUTO_INCREMENT,
  `extOrderId` int(11) NOT NULL,
  `custId` int(11) NOT NULL,
  `entryDate` datetime NOT NULL,
  `deliveryDate` datetime NOT NULL,
  `shippingDate` datetime NOT NULL,
  `company` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `houseNr` varchar(50) NOT NULL,
  `houseNrAdd` varchar(10) NOT NULL,
  `zipcode` char(7) NOT NULL,
  `city` varchar(255) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `delCompany` varchar(255) NOT NULL,
  `delFirstname` varchar(255) NOT NULL,
  `delLastname` varchar(255) NOT NULL,
  `delAddress` varchar(255) NOT NULL,
  `delHouseNr` varchar(50) NOT NULL,
  `delHouseNrAdd` varchar(10) NOT NULL,
  `delZipcode` char(7) NOT NULL,
  `delCity` varchar(255) NOT NULL,
  `delLang` varchar(2) NOT NULL,
  `phoneNr` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `shippingCosts` double(10,2) NOT NULL,
  `totalPrice` double(10,2) NOT NULL,
  `trackAndTrace` varchar(255) NOT NULL,
  `paymentStatus` tinyint(1) NOT NULL,
  `paymethodId` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `comments` text NOT NULL,
  `internalComments` text NOT NULL,
  `ip` varchar(255) NOT NULL,
  `kortingscode` varchar(40) NOT NULL,
  `kortingsbedrag` double(10,2) NOT NULL,
  `export` tinyint(1) NOT NULL,
  PRIMARY KEY (`orderId`),
  KEY `custId` (`custId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `dc_customers_orders_details`
--

CREATE TABLE IF NOT EXISTS `dc_customers_orders_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `price` double(10,2) NOT NULL,
  `discount` double(10,2) NOT NULL,
  `tax` double(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`),
  KEY `customersordersdetails_product_index` (`productId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

--
-- Table structure for table `dc_customers_orders_id`
--

CREATE TABLE IF NOT EXISTS `dc_customers_orders_id` (
  `orderId` int(11) NOT NULL AUTO_INCREMENT,
  `customerId` int(11) NOT NULL,
  `discountCode` varchar(150) NOT NULL,
  `transactionId` varchar(150) NOT NULL,
  `status` varchar(150) NOT NULL,
  PRIMARY KEY (`orderId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Table structure for table `dc_discountcodes`
--

CREATE TABLE IF NOT EXISTS `dc_discountcodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `validFrom` datetime NOT NULL,
  `validTill` datetime NOT NULL,
  `validationCodeRequired` tinyint(1) NOT NULL,
  `fixedShipping` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = false, 1 = true',
  `discountValue` double(7,2) DEFAULT NULL,
  `discountType` enum('price','percentage','dynamic') NOT NULL,
  `online` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Table structure for table `dc_discountcodes_codes`
--

CREATE TABLE IF NOT EXISTS `dc_discountcodes_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codeId` int(11) NOT NULL,
  `orderId` int(11) NOT NULL,
  `parentOrderId` int(11) NOT NULL,
  `code` varchar(150) NOT NULL,
  `validationCode` varchar(150) NOT NULL,
  `discountValue` double(7,2) DEFAULT NULL,
  `discountType` enum('price','percentage') DEFAULT NULL,
  `export` tinyint(1) NOT NULL DEFAULT '0',
  `limit` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;


--
-- Table structure for table `dc_emails`
--

CREATE TABLE IF NOT EXISTS `dc_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emailName` varchar(80) NOT NULL,
  `fromEmail` varchar(80) NOT NULL,
  `fromName` varchar(80) NOT NULL,
  `cc` varchar(150) NOT NULL,
  `bcc` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `dc_emails`
--

INSERT INTO `dc_emails` (`id`, `emailName`, `fromEmail`, `fromName`, `cc`, `bcc`) VALUES
(1, 'template', '', '', '', ''),
(2, 'ordermail', 'info@dropcart.nl', 'Dropcart', '', 'bcc@dropcart.nl'),
(3, 'sentmail', 'info@dropcart.nl', 'Dropcart', '', 'bcc@dropcart.nl'),
(4, 'password_reset', 'info@dropcart.nl', 'Dropcart', '', '');

--
-- Table structure for table `dc_emails_content`
--

CREATE TABLE IF NOT EXISTS `dc_emails_content` (
  `emailId` int(11) NOT NULL,
  `navTitle` varchar(255) NOT NULL COMMENT 'internal use',
  `navDesc` varchar(255) NOT NULL COMMENT 'internal use',
  `title` varchar(255) NOT NULL,
  `txt` longtext NOT NULL,
  PRIMARY KEY (`emailId`),
  UNIQUE KEY `emailId` (`emailId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dc_emails_content`
--

INSERT INTO `dc_emails_content` (`emailId`, `navTitle`, `navDesc`, `title`, `txt`) VALUES
(1, 'E-mail template algemeen', 'Algemene template', 'E-mail template algemeen', '![Dropcart][1]\n\n[BODY]\n\nMet vriendelijke groet,\n\n[SITE_NAME]\n\n  [1]: http://www.dropcart.nl/images/logo_small.png "Dropcart"'),
(2, 'Order bevestiging', 'Wordt direct gestuurd na het plaatsen van de bestelling met o.a. ordernummer en een kopie van de ingevoerde gegevens.', 'Uw bestelling bij [SITE_NAME] met ordernummer [ORDER_NR]', 'Beste [CUSTOMER_NAME],\r\n\r\nHartelijk dank voor uw bestelling op [SITE_NAME].\r\nHierbij ontvangt u de bevestiging van uw bestelling.\r\n\r\n[ORDER_ADDRESSES]\r\n\r\n[ORDER_DETAILS]\r\n\r\n'),
(3, 'Order verzonden', 'Wordt gestuurd na het uitsturen van de bestelling met o.a. ordernummer en een kopie van de ingevoerde gegevens.', 'Uw bestelling bij [SITE_NAME] met ordernummer [ORDER_NR] is verzonden', 'Beste [CUSTOMER_NAME],\r\n\r\nUw bestelling op [SITE_NAME] met ordernummer [ORDER_NR] is verzonden.\r\n\r\n[ORDER_ADDRESSES]\r\n\r\n[ORDER_DETAILS]\r\n\r\n[SHIPMENT]\r\n\r\n'),
(4, 'Wachtwoord vergeten', 'wachtwoord vergeten', 'wachtwoord vergeten', 'Beste [CUSTOMER_NAME],\r\n\r\nKlik op onderstaande link om direct in te loggen op [SITE_NAME] om vervolgens uw wachtwoord te resetten.\r\n\r\n[LOGIN_LINK]\r\n\r\nMet vriendelijke groet,\r\n\r\n[SITE_NAME]');

--
-- Table structure for table `dc_options`
--

CREATE TABLE IF NOT EXISTS `dc_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optionName` varchar(255) NOT NULL,
  `optionValue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `optionName` (`optionName`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Dumping data for table `dc_options`
--

INSERT INTO `dc_options` (`id`, `optionName`, `optionValue`) VALUES
(1, 'site_name', 'Dropcart Demo'),
(3, 'site_email', 'info@dropcart.nl'),
(4, 'default_product_image', 'https://dropcart.nl/images/thumbnail.png'),
(5, 'api_key', '- inktweb api key here -'),
(6, 'zipcode_api_key', '- postcode.nl api key here -'),
(7, 'zipcode_api_secret', '- postcode.nl secret key here -'),
(8, 'maximum_page_products', '20'),
(9, 'mollie_api_key', '- mollie api key here -'),
(10, 'api_test', 'false'),
(11, 'api_debug', 'false'),
(12, 'api_restrict', 'true'),
(13, 'price_operators', '["*"]'),
(14, 'price_values', '["1.21"]'),
(15, 'price_base', 'price'),
(16, 'site_shipping', '5.95'),
(17, 'dropcart_version', 'v1.7.0'),
(18, 'email_bcc', 'bcc@dropcart.nl'),
(19, 'site_email_template', 'includes/templates/dc_mail_template.html'),
(20, 'tmp_path', 'tmp/'),
(21, 'lc_monetary', 'nl_NL.UTF-8'),
(22, 'meta_robots', 'index,follow'),
(23, 'mail_server', 'smtp'),
(24, 'smtp_server', 'smtp.gmail.com'),
(25, 'smtp_port', '465'),
(26, 'smtp_secure', 'ssl'),
(27, 'smtp_auth', 'true'),
(28, 'smtp_username', '- email username here -'),
(29, 'smtp_password', '- email password here - '),
(30, 'update_build', 'stable'),
(31, 'order_number_prefix', ''),
(32, 'site_shipping_free_from', '0'),
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
-- Table structure for table `dc_pages_content`
--

CREATE TABLE IF NOT EXISTS `dc_pages_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageTitle` varchar(155) NOT NULL,
  `pageDesc` varchar(155) NOT NULL,
  `navTitle` varchar(150) NOT NULL,
  `txt` text NOT NULL,
  `online` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

--
-- Table structure for table `dc_products`
--

CREATE TABLE IF NOT EXISTS `dc_products` (
  `id` int(11) NOT NULL,
  `price_from` double(10,2) DEFAULT NULL,
  `price` double(10,2) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `opt_cart` tinyint(1) DEFAULT NULL,
  `opt_top5` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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

ALTER TABLE  `dc_products_tiered` CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;


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

--
-- Table structure for table `dc_content_tags`
--

CREATE TABLE IF NOT EXISTS `dc_content_tags` (
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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
