<?php

// Required includes
require_once ($_SERVER['DOCUMENT_ROOT'].'/includes/php/dc_connect.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.database.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/_classes/class.backup.php');

require_once ($_SERVER['DOCUMENT_ROOT'].'/beheer/includes/php/dc_session.php');

$DB = new DB();
$backup = new Backup($DB);

if(is_array($_POST['tables'])) {
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename=db-backup-'.time().'.sql');
    $backup->setTables($_POST['tables']);

    echo $backup->run();
}

