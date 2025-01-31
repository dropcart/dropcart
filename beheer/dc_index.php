<?php
// Required includes
require_once __DIR__ . '/../includes/php/dc_connect.php';
require_once __DIR__ . '/../_classes/class.database.php';
$objDB = new DB();
require_once __DIR__ . '/../beheer/includes/php/dc_config.php';

// Page specific includes
require_once __DIR__ . '/../beheer/includes/php/dc_functions.php';

require 'includes/php/dc_header.php';
?>

<h1>Dashboard</h1>

<hr />

<div class="col-md-12" style="text-align:center;">
    <?php
    // total accounts
    $strSQL = "SELECT COUNT(c.id) AS intAccounts FROM " . DB_PREFIX . "customers c";
    $result = $objDB->sqlExecute($strSQL);
    list($intAccounts) = $objDB->getRow($result);

    // total orders
    $strSQL = "SELECT COUNT(co.orderId) AS intOrders FROM " . DB_PREFIX . "customers_orders co";
    $result = $objDB->sqlExecute($strSQL);
    list($intOrders) = $objDB->getRow($result);

    // total volume
    $strSQL = "SELECT SUM(co.totalPrice) AS dbVolume FROM " . DB_PREFIX . "customers_orders co";
    $result = $objDB->sqlExecute($strSQL);
    list($dbVolume) = $objDB->getRow($result);

    ?>
    <div class="col-md-4">
        <h1><?php echo $intAccounts;?></h1>
        <p>Klantaccounts</p>
    </div><!-- /col -->
    <div class="col-md-4">
        <h1><?php echo $intOrders;?></h1>
        <p>Bestellingen</p>
    </div><!-- /col -->
    <div class="col-md-4">
        <h1><?php echo moneyFormat('%(#1n', $dbVolume);?></h1>
        <p>Omzet</p>
    </div><!-- /col -->
</div><!-- /col -->

<div class="col-md-12">
    <?php

    // Set amount of days
    $intMaximumDays = 30;

    // Returns all orders grouped by day and count orders for each day
    $strSQL = "
            SELECT DATE_FORMAT(co.entryDate, '%Y, %m-1, %d') AS datefield, COUNT(co.orderId) AS orderCount
            FROM " . DB_PREFIX . "customers_orders co
            WHERE co.entryDate BETWEEN CURDATE() - INTERVAL " . $intMaximumDays . " DAY AND CURDATE() + INTERVAL 1 DAY
            GROUP BY DATE(co.entryDate)";
    $result = $objDB->sqlExecute($strSQL);
    while ($objOrders = $objDB->getArray($result)) {

        $orders[$objOrders['datefield']] = $objOrders['orderCount'];

    }

    $periodStart = new DateTime();
    $periodStart->modify('-' . $intMaximumDays . ' days');

    $periodEnd = new DateTime();
    $periodEnd->modify('+1 day');

    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($periodStart, $interval, $periodEnd);

    foreach ($period as $day) {

        $numOrders = (!empty($orders[$day->format("Y, m-1, d")])) ? $orders[$day->format("Y, m-1, d")] : 0;
        $dataOrders[] = "[Date.UTC(" . $day->format("Y, m-1, d") . ") , " . $numOrders . "]";

    }
    ?>

    <div id="container" style="width:100%; height:400px;"></div>

    <script type="text/javascript">
    $(document).ready(function() {
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'container'
            },
            title: {
                text: 'Laatste 30 dagen'
            },
            xAxis: {
                type: 'datetime',
                tickInterval: 24 * 3600 * 1000,
                dateTimeLabelFormats: {
                    day: '%d-%m',
                },
            },
            yAxis: {
                floor: 0,
                title: {
                    text:null
                }
            },
            series: [{
                name: 'Bestellingen',
                data: [<?php echo implode($dataOrders, ',')?>],
                showInLegend: false,
            }],
            credits: {
                    enabled: false
            },
        });
    });
    </script>
</div><!-- /col -->

<?php require 'includes/php/dc_footer.php';?>
