<?php

require_once 'class.database.php';

class Backup {

    private $tables = '*';

    public function __construct(DB $db) {
        $this->db = $db;
    }

    public function setTables(array $tables) {
        $this->tables = $tables;
    }

    private function getTablesArray() {
        //get all of the tables
        $tables = array();
        if($this->tables == '*')
        {
            $result = $this->db->sqlExecute('SHOW TABLES');

            while($row = $this->db->getArray($result))
            {
                $tables[] = $row[0];
            }
        } else {
            $tables = $this->tables;
        }

        return $tables;
    }

    public function run() {

        $return = '';
        $tables = $this->getTablesArray();
        //cycle through
        foreach($tables as $table)
        {
            $result = $this->db->sqlExecute('SELECT * FROM '.$table);
            $num_fields = $result->field_count;

            $return.= 'DROP TABLE '.$table.';';
            $row2 = $this->db->getArray($this->db->sqlExecute('SHOW CREATE TABLE '.$table));
            $return.= "\n\n".$row2[1].";\n\n";

            for ($i = 0; $i < $num_fields; $i++)
            {
                while($row = $this->db->getArray($result))
                {
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                    for($j=0; $j<$num_fields; $j++)
                    {
                        $row[$j] = addslashes($row[$j]);
                        if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                        if ($j<($num_fields-1)) { $return.= ','; }
                    }
                    $return.= ");\n";
                }
            }
            $return.="\n\n\n";
        }

        return  $return;
    }
}
