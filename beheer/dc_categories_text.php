<?php
/**
 * Created by PhpStorm.
 * User: dmitri
 * Date: 20-7-15
 * Time: 13:02
 */

// Required includes
require_once (__DIR__.'/../includes/php/dc_connect.php');
require_once (__DIR__.'/../_classes/class.database.php');
$objDB = new DB();
require_once (__DIR__.'/../beheer/includes/php/dc_config.php');

// Page specific includes
require_once (__DIR__.'/../beheer/includes/php/dc_functions.php');

// For mollie paymethods
require_once (__DIR__.'/../libraries/Mollie/API/Autoloader.php');

$objDB 		= new DB();

/* Checkbox names */
$checkBoxes = array(
    'parse_markdown',
    'parse_boilerplate'
);


function redirectBack($success = false){
    $url = SITE_URL.'/beheer/dc_content_admin.php';

    if( $success )
        $url.= '?success=1';

    $url.='#categories';

    header('Location: '.$url);
}

/**
 * @author Dmitri Chebotarev <dmitri.chebotarev@gmail.com>
 * @param $objDB
 * @return array
 */
function getAllowedColumnNames($objDB)
{
    $sqlColumns = "SHOW COLUMNS FROM " . DB_PREFIX . "content_boilerplate";
    $resultColumns = $objDB->sqlExecute($sqlColumns);

    while ($row = $objDB->getObject($resultColumns)) {
        $allowed[] = $row->Field;
    }
    return $allowed;
}

function categoryExists($db, $category_id){

    $sql = "SELECT id FROM ".DB_PREFIX."content_boilerplate WHERE category_id = {$category_id}";
    $result = $db->sqlExecute($sql);

    return $db->getNumRows($result) !== 0;
}

function extractData($data, $key){
    $extracted = array();
    foreach($data as $item => $values) {
       if( array_key_exists($key, $values) ){
        $extracted[] = $values[$key];
       }
    }
    return $extracted;
}

function insertCheckboxValues($data, $checkBoxes){


    /* Search checkboxes */
    foreach($data as $key => $value){

        foreach($checkBoxes as $key => $checkbox){
            /* Remove checkbox from array if exists */
            if( $value['col'] == $checkbox){
                unset($checkBoxes[$key]);
            }
        }
    }

    /* Add the checkbox values that are still missing */
    foreach($checkBoxes as $checkbox){
        $data[] = array(
            'col' => $checkbox,
            'value' => 0
        );
    }

    return $data;
}

function getCreateQuery($category_id, $data){
    $colNames = extractData($data, 'col');
    $colNames[] = 'category_id';
    $values = extractData($data, 'value');
    $values[] = $category_id;

    $sql = "INSERT INTO ".DB_PREFIX."content_boilerplate
    (".implode($colNames, ", ").") VALUES ('".implode($values, "', '")."')";

    return $sql;
}

/* Check if the users didn't fill out the fields, but did check atleast one checkbox */
function onlyCheckBoxes($data, $checkboxes){
    $count = 0;
    foreach($data as $key => $value){
        if( !in_array($value['col'], $checkboxes) )
            $count++;
    }

    return $count === 0;
}

function getUpdateQuery($category_id, $data){
    $sql = "UPDATE ".DB_PREFIX."content_boilerplate SET ";

    $count = count($data);
    foreach($data as $key => $value){
        $sql.= " {$value['col']} = '{$value['value']}'";
        if( $key < ( $count - 1) ){
            $sql.= ',';
        }
        $sql.= ' ';
    }

    $sql.= "WHERE category_id = {$category_id}";

    return $sql;
}

function pushChanges($db, $category_id, $data, $exists){
    // Check if exists

    $sql = null;

    if( $exists ){
        $sql = getUpdateQuery($category_id, $data);
    }
    else{
        $sql = getCreateQuery($category_id, $data);
    }

    if( !is_null($sql))
        $db->sqlExecute($sql);
}

/* START Handler */
$allowed = getAllowedColumnNames($objDB);

if( isset($_GET['reset']) && is_numeric($_GET['reset'])){
    $category_id = sanitize($_GET['reset']);
    $deleteQuery = "DELETE FROM ".DB_PREFIX."content_boilerplate WHERE category_id = '{$category_id}'";
    $objDB->sqlExecute($deleteQuery);

    redirectBack();
}

if( !isset($_POST['categories'] ) ){
    redirectBack();
}

foreach( $_POST['categories'] as $category_id => $input ){

    $tmpInsertData = array();
    $exists = categoryExists($objDB, $category_id);


    $category_id = $objDB->escapeString($category_id);
    foreach($input as $key => $value){

        if( empty($value) ){

            if( $exists ){
                $value = NULL;
            }
            else {
                continue;
            }
        }


        if( !in_array($key, $allowed) )
            continue;




        $tmpInsertData[] = array(
            'col' => $key,
            'value' => $objDB->escapeString($value)
        );

    }


    if( count($tmpInsertData) === 0 )
        continue;

    if( onlyCheckBoxes($tmpInsertData, $checkBoxes) )
        continue;

    $tmpInsertData = insertCheckboxValues($tmpInsertData, $checkBoxes);
    pushChanges($objDB, $category_id, $tmpInsertData, $exists);
}

redirectBack(true);