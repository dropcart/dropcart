<?php

/*

Global includes, for every function that needs to work in both CMS and Live website
For CMS use: /beheer/includes/php/dc_functions.php
For Live use: /includes/php/dc_functions.php

 */

if (!function_exists('money_format')) {
    function money_format($format, $number) {
        $regex = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?' .
        '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
        if (setlocale(LC_MONETARY, 0) == 'C') {
            setlocale(LC_MONETARY, '');
        }
        $locale = localeconv();
        preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
        foreach ($matches as $fmatch) {
            $value = floatval($number);
            $flags = array(
                'fillchar' => preg_match('/\=(.)/', $fmatch[1], $match) ?
                $match[1] : ' ',
                'nogroup' => preg_match('/\^/', $fmatch[1]) > 0,
                'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                $match[0] : '+',
                'nosimbol' => preg_match('/\!/', $fmatch[1]) > 0,
                'isleft' => preg_match('/\-/', $fmatch[1]) > 0,
            );
            $width = trim($fmatch[2]) ? (int) $fmatch[2] : 0;
            $left = trim($fmatch[3]) ? (int) $fmatch[3] : 0;
            $right = trim($fmatch[4]) ? (int) $fmatch[4] : $locale['int_frac_digits'];
            $conversion = $fmatch[5];

            $positive = true;
            if ($value < 0) {
                $positive = false;
                $value *= -1;
            }
            $letter = $positive ? 'p' : 'n';

            $prefix = $suffix = $cprefix = $csuffix = $signal = '';

            $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
            switch (true) {
                case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                    $prefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                    $suffix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                    $cprefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                    $csuffix = $signal;
                    break;
                case $flags['usesignal'] == '(':
                case $locale["{$letter}_sign_posn"] == 0:
                    $prefix = '(';
                    $suffix = ')';
                    break;
            }
            if (!$flags['nosimbol']) {
                $currency = $cprefix .
                ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
                $csuffix;
            } else {
                $currency = '';
            }
            $space = $locale["{$letter}_sep_by_space"] ? ' ' : '';

            $value = number_format($value, $right, $locale['mon_decimal_point'],
                $flags['nogroup'] ? '' : $locale['mon_thousands_sep']);
            $value = @explode($locale['mon_decimal_point'], $value);

            $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
            if ($left > 0 && $left > $n) {
                $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
            }
            $value = implode($locale['mon_decimal_point'], $value);
            if ($locale["{$letter}_cs_precedes"]) {
                $value = $prefix . $currency . $space . $value . $suffix;
            } else {
                $value = $prefix . $value . $space . $currency . $suffix;
            }
            if ($width > 0) {
                $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                    STR_PAD_RIGHT : STR_PAD_LEFT);
            }

            $format = str_replace($fmatch[0], $value, $format);
        }
        return $format;
    }
}

function pre($strPre, $exit = false) {
    echo '<pre>';
    print_r($strPre);
    echo '</pre>';

    if ($exit == true) {
        exit();
    }
}

function cleanInput($input) {

    // http://css-tricks.com/snippets/php/sanitize-database-inputs/

    $search = array(
        '@<script[^>]*?>.*?</script>@si', // Strip out javascript
        '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
        '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
        '@<![\s\S]*?--[ \t\n\r]*>@', // Strip multi-line comments
    );

    $output = preg_replace($search, '', $input);
    return $output;
}

function sanitize($input) {

    global $objDB;
    $output = null;

    // http://css-tricks.com/snippets/php/sanitize-database-inputs/

    if (is_array($input)) {
        foreach ($input as $var => $val) {
            $output[$var] = sanitize($val);
        }
    } else {
        $input = cleanInput($input);
        $output = $objDB->escapeString($input);
    }
    return $output;
}

function curPage() {
    return substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
}

function ip() {
    if (getenv("HTTP_X_FORWARDED_FOR")) {
        $IPadres = getenv("HTTP_X_FORWARDED_FOR");
    } elseif (getenv("HTTP_CLIENT_IP")) {
        $IPadres = getenv("HTTP_CLIENT_IP");
    } else {
        $IPadres = $_SERVER["REMOTE_ADDR"];
    }

    return $IPadres;
}

function calculateProductPrice($objPrice, $productId = null, $quantity = null, $format = true) {

    global $objDB;

    // if $productId is filled in, check if price is in DB
    if (!empty($productId)) {

        $strSQL = "SELECT price FROM " . DB_PREFIX . "products WHERE id = '" . $productId . "' ";
        $result = $objDB->sqlExecute($strSQL);
        list($price) = $objDB->getRow($result);
    }
    // Use DB price ifset ELSE default formula
    if (!empty($price)) {
        $productPrice = $price;

        // Check for tiered pricing
        if (!empty($quantity)) {

            // Returns the highest percentage discount for this quantity
            $strSQL = "SELECT percentage FROM " . DB_PREFIX . "products_tiered WHERE productId = '" . $productId . "' AND quantity <= '" . $quantity . "' ORDER BY quantity DESC LIMIT 1 ";
            $result = $objDB->sqlExecute($strSQL);
            list($intDiscountPercentage) = $objDB->getRow($result);

            if (!empty($intDiscountPercentage)) {
                $discountPerProduct = ($productPrice / 100) * $intDiscountPercentage;
                $productPrice = $productPrice - $discountPerProduct;
            }

        }

    } else {

        // get price
        $strSQL = "SELECT optionValue FROM " . DB_PREFIX . "options WHERE optionName = 'price_base'";
        $result = $objDB->sqlExecute($strSQL);
        list($price_base) = $objDB->getRow($result);

        if ($price_base == "purchase") {
            $apiPrice = $objPrice->pricePurchase;
        } elseif ($price_base == "msrp") {
            $apiPrice = $objPrice->priceMSRP;
        } else {
            if (is_object($objPrice)) {
                $apiPrice = $objPrice->price;

            } else {
                $apiPrice = $objPrice;
            }
        }

        $priceOperators = json_decode(formOption('price_operators'));
        $priceValues = json_decode(formOption('price_values'));

        $productPrice = $apiPrice;

        for ($i = 0; $i < count($priceOperators); $i++) {
            $operator = $priceOperators[$i];
            switch ($operator) {
                case '*':
                    $productPrice *= $priceValues[$i];
                    break;
                case '+':
                    $productPrice += $priceValues[$i];
                    break;
                case '-':
                    $productPrice -= $priceValues[$i];
                    break;
            }
        }

        // TODO: ceil / floor for rounding
        $productPrice = round($productPrice, 2);
    }

    if ($format == true) {
        $productPrice = money_format('%(#1n', $productPrice);
    }
    return $productPrice;

}

function getPriceFrom($productId) {
    global $objDB;
    $query = "SELECT price_from FROM " . DB_PREFIX . "products WHERE id = '" . $productId . "' ";

    $result = $objDB->sqlExecute($query);

    $count = $objDB->getNumRows($result);
//    die(var_dump($count));

    if ($count === 0) {

        return null;
    }

    $row = $objDB->getObject($result);

    return $row->price_from;
}

function calculateSiteShipping($cartTotal = 0, $deliveryLang = null, $format = true) {

    global $objDB;
    $shippingCosts = SITE_SHIPPING;

    if ($cartTotal > 0) {
        $freeShippingFrom = formOption('site_shipping_free_from');

        // If shipping is not 0 and current amount exceeds "free_shipping"
        if (!empty($freeShippingFrom) AND $cartTotal >= $freeShippingFrom) {
            $shippingCosts = "0.00";
        }
    }

    // @todo: dynamic based on delivery lang

    if ($format == true) {
        $shippingCosts = money_format('%(#1n', $shippingCosts);
    }
    return $shippingCosts;
}

/**
 * Get content from the database. Could be default values for products or more advanced like the text for the footer
 * @param  str $strContent how the content is called in the db
 * @param  bool $parse parse all content by default, set to 'false' to stop parsing content
 * @param  obj $Product  used for 'product content' PRODUCT_ boilerplate
 * @param  obj $arrProducts  used for 'product content' PRINTER_ boilerplate
 * @return content w/ all variables replaced
 */
function getContent($strContent, $parse = true, $Product = null, $arrProducts = null) {

    global $objDB;

    $strContent = strtolower($strContent);
    $strSQL = "SELECT value, parse_markdown, parse_boilerplate FROM " . DB_PREFIX . "content WHERE name = '" . $strContent . "'";
    $result = $objDB->sqlExecute($strSQL);
    $objContent = $objDB->getObject($result);

    if (is_null($objContent)) {
        return $strContent;
    }

    $strContent = $objContent->value;

    if (!empty($objContent->parse_boilerplate) AND $parse == true) {
        $strContent = parseBoilerplate($strContent, $Product, $arrProducts);
    }

    if (!empty($objContent->parse_markdown) AND $parse == true) {
        $strContent = parseMarkdown($strContent);
    }

    return $strContent;
}

/**
 * parse **Markdown** syntax to more human readable content
 */
function parseMarkdown($content) {

    require_once __DIR__ . '/../libraries/Parsedown/Parsedown.php';

    $Parsedown = new Parsedown();
    $content = $Parsedown->text($content);
    return $content;

}

/**
 * parses a bunch of text and replaces values as defined in `content_boilerplate` with variables
 * @param  str $content the text that needs parsing
 * @param  obj $Product needs to be set for [PRODUCT_] replacing
 * @param  obj $arrProducts needs to be set for [PRINTER_] replacing
 * @return str $content but now fully parsed
 */
function parseBoilerplate($content, $Product = null, $arrProducts = null) {

    global $objDB;

    $arrReplace = array();

    // Product is set so its likely to be PRODUCT_ related

    if (!empty($Product)) {

        $arrReplace['[PRODUCT_ID]'] = $Product->getId();
        $arrReplace['[PRODUCT_EAN]'] = $Product->getEan();
        $arrReplace['[PRODUCT_OEM]'] = $Product->getOem();
        $arrReplace['[PRODUCT_BRAND]'] = $Product->getBrand();
        $arrReplace['[PRODUCT_TITLE]'] = $Product->getTitle();

        $objPrice = $Product->getPrice();
        $strPrice = calculateProductPrice($objPrice, $Product->getId());
        $arrReplace['[PRODUCT_PRICE]'] = $strPrice;

        // check if PRODUCT_SPEC_
        if (strpos($content, '[PRODUCT_SPEC_') > -1) {

            foreach ($Product->getSpecifications() AS $spec) {

                if ($spec->label == "Kleur") {
                    $spec_color = $spec->value;
                }

                if ($spec->label == "Gewicht") {
                    $spec_weight = $spec->value;
                }

                if ($spec->label == "Inhoud") {
                    $spec_capacity = $spec->value;
                }

                if ($spec->label == "Aantal mililiter") {
                    $spec_ml = $spec->value . $spec->unit;
                }

                if ($spec->label == "Aantal  pagina's") {
                    $spec_pages = $spec->value . $spec->unit;
                }

                if ($spec->label == "Type") {
                    $spec_type = $spec->value;
                }
            }

            // @ ignores warnings/errors like "not set"
            $arrReplace['[PRODUCT_SPEC_COLOR]'] = @$spec_color;
            $arrReplace['[PRODUCT_SPEC_WEIGHT]'] = @$spec_weight;
            $arrReplace['[PRODUCT_SPEC_CAPACITY]'] = @$spec_capacity;
            $arrReplace['[PRODUCT_SPEC_ML]'] = @$spec_ml;
            $arrReplace['[PRODUCT_SPEC_PAGES]'] = @$spec_pages;
            $arrReplace['[PRODUCT_SPEC_TYPE]'] = @$spec_type;

        }

    }

    // check if SITE_
    if (strpos($content, '[SITE_') > -1) {

        $arrReplace['[SITE_NAME]'] = formOption('site_name');
        $arrReplace['[SITE_URL]'] = formOption('site_url');
        $arrReplace['[SITE_EMAIL]'] = formOption('site_email');
    }

    // check if PRINTER_
    if (!empty($arrProducts)) {

        $arrReplace['[PRINTER_BRAND]'] = $arrProducts->printers->brand;
        $arrReplace['[PRINTER_SERIE]'] = $arrProducts->printers->serie;
        $arrReplace['[PRINTER_TYPE]'] = $arrProducts->printers->printer;
    }

    $content = str_replace(
        array_keys($arrReplace),
        array_values($arrReplace),
        $content
    );

    return $content;

}

/**
 * Check for custom product title, else use boilerplate
 * @param  object $objProduct complete API object
 * @param  int $productId
 * @return str max 255 chars product title
 */
function getProductTitle($objProduct, $productId = null) {

    global $objDB;
    $strTitle = null;
    // if $productId is entered, check if it has a custom title in DB
    if (!empty($productId)) {

        $strSQL = "SELECT title FROM " . DB_PREFIX . "products WHERE id = '" . $productId . "' ";
        $result = $objDB->sqlExecute($strSQL);
        list($strTitle) = $objDB->getRow($result);

    }

    // empty means database didn't contain a custom title
    // fallback to boilerplate content
    if (empty($strTitle)) {

        $strSQL = "SELECT value FROM " . DB_PREFIX . "content WHERE name = 'default_product_title'";
        $result = $objDB->sqlExecute($strSQL);
        list($boilerContent) = $objDB->getRow($result);
        $strTitle = parseBoilerplate($boilerContent, $objProduct);
    }

    return $strTitle;

}

/**
 * Check for custom product description, else use boilerplate
 * @param  object $objProduct complete API object
 * @param  int $productId
 * @return str description of product, may contain html/boilerplate markup in the future
 */
function getProductDesc($objProduct, $productId = null) {

    global $objDB;
    global $Parsedown;

    // if $productId is entered, check if it has a custom desc in DB
    if (!empty($productId)) {

        $strSQL = "SELECT description FROM " . DB_PREFIX . "products WHERE id = '" . $productId . "' ";
        $result = $objDB->sqlExecute($strSQL);
        list($strDesc) = $objDB->getRow($result);

        if (!empty($strDesc)) {
            $strDesc = $Parsedown->text($strDesc);
        }

    }

    // empty means database didn't contain a custom desc
    // fallback to boilerplate content
    if (empty($strDesc)) {

        $strSQL = "SELECT value FROM " . DB_PREFIX . "content WHERE name = 'default_product_content'";
        $result = $objDB->sqlExecute($strSQL);
        list($boilerContent) = $objDB->getRow($result);
        $strDesc = parseBoilerplate($boilerContent, $objProduct);
    }

    return $strDesc;
}

/**
 * returns description of Inktweb.nl orderstatus based on databaseId
 * @param  int database status id returned from API
 * @return str textual description of what the status means
 */
function getStatusDesc($intOrderStatus) {

    switch ($intOrderStatus) {
        case 0:
            $strStatus = "Nieuw";
            break;
        case 1:
            $strStatus = "In behandeling genomen";
            break;
        case 2:
            $strStatus = "Producten niet op voorraad";
            break;
        case 3:
            $strStatus = "Vandaag versturen";
            break;
        case 4:
            $strStatus = "Verstuurd";
            break;
        case 5:
            $strStatus = "In behandeling genomen";
            break;
        case 6:
            $strStatus = "In behandeling genomen";
            break;
        case 7:
            $strStatus = "Wachten op betaling";
            break;
        case 8:
            $strStatus = "Afgehaald";
            break;
        case 9:
            $strStatus = "Kan worden afgehaald";
            break;
        case 10:
            $strStatus = "Wachtlijst versturen";
            break;
        case 11:
            $strStatus = "Gereserveerd";
            break;
        case 99:
            $strStatus = "Verwijderd";
            break;
        default:
            $strStatus = "Nieuw";
    }

    return $strStatus;
}

function deleteDir($dir) {
    $iterator = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {

        if ($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($dir);
}

function generateInvoicePDF($intOrderId, $blnDownload = false) {

    global $Api;
    global $twig;
    global $mpdf;
    global $objDB;

    $intOrderId = intval($intOrderId);

    $tpl = $twig->loadTemplate("dc_invoice_template.tpl");

    $settings = array();

    /* get site settings */
    $siteSettingsSQL = "SELECT * FROM dc_options";
    $resultSettings = $objDB->sqlExecute($siteSettingsSQL);
    while ($row = $objDB->getObject($resultSettings)) {
        $settings['_' . $row->optionName] = $row->optionValue;
    }

    // order information
    $strSQL =
    "SELECT co.firstname,
        co.lastname,
        co.address,
        CONCAT(co.houseNr, co.houseNrAdd),
        co.zipcode,
        co.city,
        co.lang,
        co.custId,
        co.orderId,
        DATE_FORMAT(co.entryDate, '%d-%m-%Y') as entryDate,
        co.kortingscode,
        co.kortingsbedrag,
        co.shippingCosts,
        co.totalPrice
        FROM " . DB_PREFIX . "customers_orders co
        WHERE co.orderId = '" . $intOrderId . "';
        ";
    $result = $objDB->sqlExecute($strSQL);
    $objOrder = $objDB->getObject($result);

    $dblTotalEx = 0;
    $arrLanguages = array('nl' => 'Nederland', 'be' => 'België', 'pl' => 'Polen', 'de' => 'Duitsland', 'uk' => 'Engeland', '' => 'Nederland');

    // order details (products)
    $strSQL =
    "SELECT productId,
        price,
        discount,
        tax,
        quantity
        FROM " . DB_PREFIX . "customers_orders_details
        WHERE orderId = '" . $objOrder->orderId . "'  ";
    $result = $objDB->sqlExecute($strSQL);
    while ($objDetails = $objDB->getObject($result)) {

        $Product = $Api->getProduct($objDetails->productId, '?fields=title');

        $objDetails->title = $Product->getTitle();
        $objDetails->taxRate = $objDetails->tax;
        $objDetails->taxPerc = $objDetails->tax * 100 - 100;
        $objDetails->priceEx = $objDetails->price;
        $objDetails->price = round($objDetails->priceEx * $objDetails->tax, 2);
        $objDetails->priceTotal = $objDetails->price * $objDetails->quantity;

        $arrTax[$objDetails->taxPerc] += $objDetails->price - $objDetails->priceEx;
        $dblTotalEx += $objDetails->priceEx * $objDetails->quantity;
        
        // format price
        $objDetails->priceTotal = number_format($objDetails->priceTotal, 2, ',', ' ');
        $objDetails->price = number_format($objDetails->price, 2, ',', ' ');
        $objDetails->priceEx = number_format($objDetails->priceEx, 2, ',', ' ');

        $details[] = $objDetails;

    }
    
    // Add shipping costs tax
    $arrTax[21] += $objOrder->shippingCosts - $objOrder->shippingCosts / 1.21;
    $dblTotalEx += $objOrder->shippingCosts / 1.21;
    
    foreach ($arrTax as $taxPerc => $dblPrice) {
        $arrTax[$taxPerc] = number_format($dblPrice, 2, ',', ' ');
    }

    $strTemplateVars = array(
        'name' => $objOrder->firstname . ' ' . $objOrder->lastname,
        'address' => $objOrder->address . ' ' . $objOrder->houseNr,
        'zipcode' => $objOrder->zipcode,
        'city' => $objOrder->city,
        'logo' => SITE_URL . '/images/logo/' . formOption('SITE_LOGO'),
        'country' => $arrLanguages[$objOrder->lang],
        'customer_nr' => str_pad($objOrder->custId, 9, '0', STR_PAD_LEFT),
        'invoice_nr' => str_pad($objOrder->orderId, 9, '0', STR_PAD_LEFT),
        'invoice_date' => $objOrder->entryDate,
        'order_details' => $details,
        'discount_code' => $objOrder->kortingscode,
        'discount_amount' => number_format($objOrder->kortingsbedrag, 2, ',', ' '),
        'shipping_costs' => number_format($objOrder->shippingCosts, 2, ',', ' '),
        'total_ex' => number_format($dblTotalEx, 2, ',', ' '),
        'total' => number_format($objOrder->totalPrice, 2, ',', ' '),
        'tax' => $arrTax,
        'site_path' => dirname(__DIR__),
        'invoice_label' => $text['BILLING'],
        'customer_nr_label' => $text['CUSTOMER_NUMBER'],
        'invoice_nr_label' => $text['BILLING_NUMBER'],
        'invoice_date_label' => $text['BILLING_DATE'],
        'quantity_label' => $text['QUANTITY'],
        'title_label' => $text['DESCRIPTION'],
        'price_label' => $text['PRICE_A_PIECE'],
        'priceTotal_label' => $text['PRICE'],
        'shipping_label' => $text['SHIPPING_FEE'],
        'discount_label' => $text['DISCOUNT_CODE'],
        'total_ex_label' => $text['TOTAL_EX'],
        '_site_phone_number_label' => $text['PHONE'],
        '_site_email_label' => $text['INPUT_EMAIL'],
        '_site_url_label' => $text['SITE'],
        'total_label' => $text['TOTAL'],
        '_site_terms' => $text['TERMS'],
    );

    $strTemplateVars = array_merge($strTemplateVars, $settings);

    $strTemplate = $tpl->render($strTemplateVars);

    $mpdf->setAutoTopMargin = 'pad';
    $mpdf->setAutoBottomMargin = 'pad';
    $mpdf->WriteHTML($strTemplate);
    if ($blnDownload === true) {
        $pdf = $mpdf->Output('factuur-' . str_pad($objOrder->orderId, 9, '0', STR_PAD_LEFT) . '.pdf', 'D');
    } else {
        $pdf = $mpdf->Output('', 'S');
    }

    return $pdf;
}

function rewriteUrl($strString) {

    $strString = stripslashes($strString);
    $strString = trim($strString);
    $strString = str_replace("'", "", $strString);
    $strString = str_replace(",", "", $strString);
    $strString = str_replace("\"", "", $strString);
    $strString = str_replace("?", "", $strString);
    $strString = str_replace("(", "", $strString);
    $strString = str_replace(")", "", $strString);
    $strString = str_replace("/", "", $strString);
    $strString = str_replace("&", "", $strString);
    $strString = str_replace("&iuml;", "", $strString);
    $strString = str_replace(" ", "-", $strString);
    $strString = str_replace("_", "-", $strString);
    $strString = str_replace("“", "", $strString);
    $strString = str_replace("”", "", $strString);
    $strString = strtolower($strString);

    return $strString;

}

function urlOrigin($s = null, $use_forwarded_host = false) {
    if (is_null($s)) {
        $s = $_SERVER;
    }

    $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true : false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port = $s['SERVER_PORT'];
    $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function fullUrl($s = null, $use_forwarded_host = false) {
    return urlOrigin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

function getBoilerPlateContent(
    $category_id,
    $col,
    $parse = true,
    $parseWithUserSettings = true, # $parse must be true for this setting
    $forceMarkdown = true, # $parse must be true for this setting
    $forceBoilerplate = true, # $parse must be true for this setting
    $stripMarkdown = false, # $parse must be true for this setting
    $product = null,
    $arrPrinters = null
) {
    global $objDB;
    $sql = "SELECT
                  {$col}, /* Either category_title, category_desc, product_title or product_desc */
                  parse_markdown,
                  parse_boilerplate
            FROM " . DB_PREFIX . "content_boilerplate
            WHERE category_id = '{$category_id}'";

    $result = $objDB->sqlExecute($sql);

    $item = $objDB->getObject($result);
    $content = null;
    if (isset($item->{$col})) {
        $content = $item->{$col};
    }

    /* Fallback to default content if content is empty */
    if (empty($content)) {

        switch ($col) {
            case 'category_title':$alt = 'category_title';
                break;
            case 'category_desc':$alt = 'category_meta_description';
                break;
            case 'product_title':$alt = 'product_title';
                break;
            case 'product_desc':$alt = 'product_meta_description';
                break;
            default:return null;
                break;
        }

        return getContent($alt, $parse = true, $product, $arrPrinters);
    }

    if (!$parse) {
        return $content;
    }

    if (($parseWithUserSettings && !empty($item->parse_markdown)) || $forceMarkdown || $stripMarkdown) {
        $content = parseMarkdown($content);

        if ($stripMarkdown) {
            $content = stripHTML($content);
        }
    }

    if (($parseWithUserSettings && !empty($item->parse_boilerplate)) || $forceBoilerplate) {
        $content = parseBoilerplate($content, $product, $arrPrinters);
    }
    return $content;
}

/* For use in HTML title tag */
function getProductPageTitle($category_id, $product = null, $printers = null) {
    return getBoilerPlateContent(
        $category_id,
        $col = 'product_title',
        $parse = true,
        $parseWithUserSettings = false, # $parse must be true for this setting
        $forceMarkdown = false, # $parse must be true for this setting
        $forceBoilerplate = true, # $parse must be true for this setting
        $stripMarkdown = true, # $parse must be true for this setting
        $product,
        $printers
    ); //Append site name to the title
}

/* For use in meta description tag */
function getProcutMetaDescription($category_id, $product = null, $printers = null) {
    return getBoilerPlateContent(
        $category_id,
        $col = 'product_desc',
        $parse = true,
        $parseWithUserSettings = false, # $parse must be true for this setting
        $forceMarkdown = false, # $parse must be true for this setting
        $forceBoilerplate = true, # $parse must be true for this setting
        $stripMarkdown = true, # $parse must be true for this setting
        $product,
        $printers
    );
}

function getCustomProductDesc($category_id, $product = null, $printers = null) {
    return getBoilerPlateContent(
        $category_id,
        $col = 'product_desc',
        $parse = true,
        $parseWithUserSettings = true, # $parse must be true for this setting
        $forceMarkdown = false, # $parse must be true for this setting
        $forceBoilerplate = false, # $parse must be true for this setting
        $stripMarkdown = false, # $parse must be true for this setting
        $product,
        $printers
    );
}

function getCustomProductTitle($category_id, $product, $printers = null) {
    return getBoilerPlateContent(
        $category_id,
        $col = 'product_title',
        $parse = true,
        $parseWithUserSettings = true, # $parse must be true for this setting
        $forceMarkdown = false, # $parse must be true for this setting
        $forceBoilerplate = false, # $parse must be true for this setting
        $stripMarkdown = false, # $parse must be true for this setting
        $product,
        $printers
    );
}

function getCategoryPageTitle($category_id) {
    return getBoilerPlateContent(
        $category_id,
        $col = 'category_title',
        $parse = true,
        $parseWithUserSettings = false, # $parse must be true for this setting
        $forceMarkdown = false, # $parse must be true for this setting
        $forceBoilerplate = true, # $parse must be true for this setting
        $stripMarkdown = true, # $parse must be true for this setting
        $product = null,
        $printers = null
    );
}

function getCategoryMetaDescription($category_id) {
    return getBoilerPlateContent(
        $category_id,
        $col = 'category_desc',
        $parse = true,
        $parseWithUserSettings = false, # $parse must be true for this setting
        $forceMarkdown = false, # $parse must be true for this setting
        $forceBoilerplate = true, # $parse must be true for this setting
        $stripMarkdown = true, # $parse must be true for this setting
        $product = null,
        $printers = null
    );
}

function getCustomCategoryTitle($category_id) {
    return getBoilerPlateContent(
        $category_id,
        $col = 'category_title',
        $parse = true,
        $parseWithUserSettings = true, # $parse must be true for this setting
        $forceMarkdown = false, # $parse must be true for this setting
        $forceBoilerplate = false, # $parse must be true for this setting
        $stripMarkdown = false, # $parse must be true for this setting
        $product = null,
        $printers = null
    );
}

function stripHTML($content) {
    return strip_tags($content);
}

/**
 * Redirect a user to the given url relative to the SITE_URL
 * @author Dmitri Chebotarev <dmitri.chebotarev@gmail.com>
 * @param string $url - url of the string, do not prefix with a '/'
 * @return void
 */
function redirectTo($url) {
    header("Location: " . SITE_URL . '/' . $url);
}

function notFoundPage() {
    redirectTo('dc_404.php');
}