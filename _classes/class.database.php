<?php
class DB {
    //setting vars
    var $db;
    var $strSQL;

    //opening database connection
    function DB() {
        $strHost = DB_HOST;
        $strDB = DB_NAME;
        $strUser = DB_USER;
        $strPass = DB_PASS;

        //connecting to database
        $this->db = new mysqli($strHost, $strUser, $strPass, $strDB);

        //check connection
        if (mysqli_connect_errno()) {
            $strError = 'Connection to database failed: ' . mysqli_connect_error();
        }

    }

    //closing database
    function closeDB() {
        $this->db->close();
    }

    function escapeString($strString) {

        $strString = $this->db->real_escape_string($strString);

        return $strString;

    }

    //executing query
    function sqlExecute($strQuery) {

        $timeStart = microtime();

        //executing query
        $result = $this->db->query($strQuery);

        return $result;
    }

    function multi_query($strQuery) {

        $timeStart = microtime();

        $result = $this->db->multi_query($strQuery);
        if ($result) {
            do {
            } while ($this->db->next_result());
        }

        //error reporting
        if (!$result) {
            //getting error message
            $message = $strErrorMsg = $this->db->error;
        }

        return array(
            'result' => $result,
            'message' => $message,
        );
    }

    //executing DELETE statement
    function sqlDelete($strTable, $strColumn, $intId, $strExtraSQL) {
        $strSQL = "DELETE FROM " . DB_PREFIX . "" . $strTable . " WHERE " . $strColumn . "=" . $intId . " " . $strExtraSQL;
        $result = $this->sqlExecute($strSQL);
    }

    //inserted id
    function getInsertedId() {
        $intRowId = $this->db->insert_id;

        return $intRowId;
    }

    //getting num rows
    function getNumRows($resResult) {
        $intNumRows = $resResult->num_rows;

        return $intNumRows;
    }

    //fetch row
    function getRow($resResult) {
        $arrRow = $resResult->fetch_row();

        return $arrRow;
    }

    //fetch object
    function getObject($resResult) {
        $objResult = null;
        if (is_object($resResult)) {
            $objResult = $resResult->fetch_object();
        }
        return $objResult;
    }

    //fetch array
    function getArray($resResult) {
        $arrRow = $resResult->fetch_array();

        return $arrRow;
    }

    //getting recordcount
    function getRecordCount($strTable, $strColumn, $strWhereSQL) {
        $strSQL = "SELECT COUNT(" . $strColumn . ") FROM " . DB_PREFIX . "" . $strTable . " " . $strWhereSQL;
        $result = $this->sqlExecute($strSQL);
        list($intCount) = $this->getRow($result);

        return $intCount;
    }

    //moving pointer in result set
    function moveTo($resResult, $intPoint) {
        $resResult->data_seek($intPoint);

        return $resResult;
    }

    //error function
    function raiseError($strError, $strErrorMsg = '') {
        echo "<pre>" . print_r(debug_backtrace(), true) . "</pre>";

        if (!headers_sent() && $strErrorMsg != '') {
            header('HTTP/1.1 500 Internal Server Error');
        }

        exit('<pre>' . $strError . '</pre>');
    }
}
?>
