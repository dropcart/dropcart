<?php
// Turn off all error reporting
error_reporting(0);
ini_set('display_errors',0);

if (PHP_SAPI == 'cli')
    die('This should only be run from a Web Browser');

date_default_timezone_set('Europe/Amsterdam');

require_once '../libraries/phpexcel/PHPExcel.php';

// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');

require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_session.php');


// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
$objDB = new DB();
require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_config.php');


if($_POST && $_POST['overviewFromDate'] && $_POST['overviewToDate']) {

    $filename = 'overview';
    $creator  = 'Inktweb';
    $fromDate = date("Y-m-d", strtotime($_POST['overviewFromDate']));
    $toDate = date("Y-m-d", strtotime($_POST['overviewToDate']));
    $title    = 'Betalingsoverzicht';

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()->setCreator($creator)
                                 ->setLastModifiedBy($creator)
                                 ->setTitle($title)
                                 ->setSubject($title)
                                 ->setDescription($title);

    $objPHPExcel->getActiveSheet()->setTitle($title);
    $objPHPExcel->setActiveSheetIndex(0);


    $strSQL = "SELECT * FROM ".DB_PREFIX."customers_orders_id AS cod
               INNER JOIN ".DB_PREFIX."customers_orders AS co ON cod.orderId = co.orderId
               INNER JOIN ".DB_PREFIX."customers AS c ON c.id = cod.customerId
               INNER JOIN ".DB_PREFIX."customers_addresses AS ca ON ca.custId = c.id
               WHERE co.entryDate >= '" . $fromDate . "' AND co.entryDate <= '" . $toDate . "'";

    $result = $objDB->sqlExecute($strSQL);

    // Add title in document
    $objPHPExcel->getActiveSheet()->setCellValue('A1', $title . ' ' . $_POST['overviewFromDate'] . ' t/m '. $_POST['overviewToDate']);
    $objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setSize(24);

    // Make first row bold
    $objPHPExcel->getActiveSheet()->getStyle("A3:G3")->getFont()->setBold(true);

    // Headers
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Ordernummer');
    $objPHPExcel->getActiveSheet()->setCellValue('B3', 'Extern ordernr');
    $objPHPExcel->getActiveSheet()->setCellValue('C3', 'Klantnummer');
    $objPHPExcel->getActiveSheet()->setCellValue('D3', 'Klantnaam');
    $objPHPExcel->getActiveSheet()->setCellValue('E3', 'Plaats');
    $objPHPExcel->getActiveSheet()->setCellValue('F3', 'Status');
    $objPHPExcel->getActiveSheet()->setCellValue('G3', 'Bedrag');

    // Increase cell sizes
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(16);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(16);

    $rowID = 4;
    while($row = $objDB->getArray($result)) {
        $columnID = 'A';
        $objPHPExcel->getActiveSheet()->setCellValue($columnID++.$rowID, $row['orderId']);
        $objPHPExcel->getActiveSheet()->setCellValue($columnID++.$rowID, $row['extOrderId']);
        $objPHPExcel->getActiveSheet()->setCellValue($columnID++.$rowID, $row['customerId']);
        $objPHPExcel->getActiveSheet()->setCellValue($columnID++.$rowID, $row['lastname']);
        $objPHPExcel->getActiveSheet()->setCellValue($columnID++.$rowID, $row['city']);
        $objPHPExcel->getActiveSheet()->setCellValue($columnID++.$rowID, $row['status']);
        $objPHPExcel->getActiveSheet()->setCellValue($columnID++.$rowID, $row['totalPrice']);
        $rowID++;
    }
    $rowID++;
    $objPHPExcel->getActiveSheet()->getStyle("A".$rowID.":F".$rowID)->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowID, 'Totaal: ');
    $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowID, "=SUM(G4:G".($rowID-2).")");

    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');

}

exit;