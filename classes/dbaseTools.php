<?php
/**
 * Created and Developped by M Azwar Nurrosat @2014
 * azwar.nrst@gmail.com
 *
 * Ver 2.0
 * php mysql tool with pdo prepared statement
 *
 */

define('_DBHOST', '10.10.10.10');
define('_DB', 'db_name');
define('_UNAME', 'db_uname');
define('_PASS', 'db_pass');

error_reporting(E_ALL);

class dbaseTools {

    /**
     * sql connection
     *
     * @var object
     */
    private $_connection;
    public  $statement;
    private $_dbHost;
    private $_dbName;
    private $_dbUname;
    private $_dbPass;
    private $_debugMode = FALSE;

    public function __construct($host = _DBHOST, $db = _DB, $uname = _UNAME, $pass = _PASS) {
        $this->_dbHost  = $host;
        $this->_dbName  = $db;
        $this->_dbUname = $uname;
        $this->_dbPass  = $pass;

        $this->_connection = new PDO('mysql:dbname=' . $db . ';host=' . $host . ';charset=utf8', $uname, $pass); //mysql_connect(_DBHOST, _UNAME, _PASS);
        if ($this->_connection) {
            $this->_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } else {
            die("Cannot connect to database server : " . "Contact your administrator !<br/>");
        }
    }

    /**
     * Genereate an insert sql query - <i><b>updated by M. Azwar Nurrosat</b></i>
     *
     * <pre>
     * $table = table name<br/>
     * $value  = array("column_name"=>$val) <br/>or $value["column_name"] = $val;
     *
     * example:<br/>
     * $tool                = new dbaseTools();<br/>
     * $data["id"]          = "USER01";<br/>
     * $data["username"]    = "xander";<br/>
     * $data["pass"]        = "nothing";<br/>
     * $tool->insert("user_table", $data);<br/>
     * </pre>
     * @access	public
     * @param 	string  the table to insert data into
     * @param 	array   an associative array of insert values
     * @return 	boolean
     */
    public function insert($table = "", $value = array()) {
        try {
            $val = "";
            $col = "";
            if (count($value) != 0) {
                foreach ($value as $column => $vl) {
                    $col .= "," . $column;
                    if ($vl != 'NOW()' && $vl != 'CURDATE()' && $vl != 'CURRENT_DATE()' && $vl != 'LAST_INSERT_ID()') {
                        $val .= ",:" . $column . "";
                    } else {
                        $val .= ", " . $vl;
                        unset($value[$column]);
                    }
                }
                $col = "(" . substr($col, 1) . ")";
                $val = "(" . substr($val, 1) . ")";

                $query = "insert into {$table}{$col} value {$val}";
                $res = $this->_connection->prepare($query);
                foreach ($value as $column => $vl) {
                    $res->bindParam(':' . $column, $vl);
                }
                $res->execute($value);
                $this->closeConnection();
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            $this->closeConnection();
            return false;
        }
    }

    /**
     * Genereate an update sql query - <i><b>updated by M. Azwar Nurrosat</b></i>
     *
     * <pre>
     * $table = table name<br/>
     * $value  = array("column_name"=>$val) <br/>or $value["column_name"] = $val;
     *
     * example:<br/>
     * $tool                = new dbaseTools();<br/>
     * $data["id"]          = "USER01";<br/>
     * $data["username"]    = "xander";<br/>
     * $data["pass"]        = "nothing";<br/>
     * $tool->update("user_table", $data, $where);<br/>
     * </pre>
     * @access	public
     * @param 	string  the table to insert data into
     * @param 	array   an associative array of update values
     * @param 	array   an associative array of where condition
     * @return 	boolean
     */
    public function update($table = "", $value = array(), $where = array()) {
        try {
            $val = "";
            $col = "";
            if (count($value) != 0) {
                foreach ($value as $column => $vl) {
                    $col .= "," . $column;
                    if ($vl != 'NOW()' && $vl != 'CURDATE()' && $vl != 'CURRENT_DATE()' && $vl != 'LAST_INSERT_ID()') {
                        //$val .= ",:" . $column . "";
                        $val .= ",$column = :$column";
                    } else {
                        //$val .= ", " . $vl;
                        $val .= ",$column = $vl";
                        unset($value[$column]);
                    }
                }
                $set = substr($val, 1);
                $query = "update {$table} set $set where {$where["where"]}";
                $res = $this->_connection->prepare($query);

                $ar = array();
                foreach ($value as $column => $vl) {
                    $ar[":$column"] = $vl;
                }
                foreach ($where["param"] as $column => $vl) {
                    $ar[":$column"] = $vl;
                }
                $result = $res->execute($ar);
                $this->closeConnection();
                return $result;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            $this->closeConnection();
            return false;
        }
    }

    /**
     * Generate an update sql query by primary key - <i><b>updated by M. Azwar Nurrosat</b></i>
     *
     * example:<br/>
     * <pre>
     * $tool                = new dbaseTools();<br/>
     * $id                  = 'USER01';<br/>
     * $data["username"]    = "xander";<br/>
     * $data["pass"]        = "new_pas";<br/>
     * $tool->updateByPK("user_table", $data, $id);<br/>
     * </pre>
     *
     * @access	public
     * @param	string the table to retrieve the results from
     * @param	array an associative array of update values
     * @param	mixed the primary key id
     * @return	void
     */
    public function updateByPK($table = "", $value = array(), $id = "") {
        try {
            $set = "";
            $colName = $this->getPrimaryColumn($table);
            if (count($value) != 0) {
                foreach ($value as $column => $val) {
                    if ($val != 'NOW()' && $val != 'CURRENT_DATE()') {
                        $set .= ",$column = :$column";
                    } else {
                        $set .= ",$column = $val";
                        unset($value[$column]);
                    }
                }
                $set = substr($set, 1);
            }
            $query = "update {$table} set {$set} where $colName = :$colName";
            //// echo $query;
            $stmt = $this->_connection->prepare($query);
            $ar = array();
            foreach ($value as $column => $vl) {
                $ar[":$column"] = $vl;
            }
            $ar[":$colName"] = $id;
            $stmt->execute($ar);
            $this->closeConnection();
            return true;
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
//            // echo json_encode($e);

            $this->closeConnection();
            return false;
        }
    }

    /**
     * Delete a row from database by primary key id - <i><b>updated by M. Azwar Nurrosat</b></i>
     *
     * example:<br/>
     * <pre>
     * $tool    = new dbaseTools();<br/>
     * $id      = 'user01';<br/>
     * $tool->deleteByPK("user_table", $id);<br/>
     * </pre>
     * @access	public
     * @param 	string  the table to delete from
     * @param 	string  the primary key id
     * @return 	void
     */
    public function deleteByPK($table = "", $id = "") {
        try {
            $column = $this->getPrimaryColumn($table);
            $query = "delete from {$table} where $column = :$column";
            $stmt = $this->_connection->prepare($query);
            $stmt->bindParam(':' . $column, $id);
            $stmt->execute();
            $this->closeConnection();
            return true;
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            $this->closeConnection();
            return false;
        }
    }

    /**
     * Generate mysql query<br/>
     * <i><b>Updated by M. Azwar Nurrosat</b></i><br/><br/>
     * example:<br/>
     * <pre>
     * $tool    = new dbaseTools();<br/>
     * $query   = "select * from user_table where id = :id and status = :status";<br/>
     * $param   = array("id"=>2, "status"=>0);
     * $tool->query($query, $param);<br/>
     * </pre>
     *
     * @access	public
     * @param	string	the query string
     * @param	array	the where parameter
     * @return	object
     */
    public function query($query = "", $param = array()) {
        try{
            $this->statement  = $this->_connection->prepare($query);
            return $this->executeQuery($param);
        }catch(PDOException $e){
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            return false;
        }
    }

    /**
     * Generate select query - <i><b>updated by M. Azwar Nurrosat</b></i><br/>
     * select $column from $table where $where<br/>
     * <br/>
     * example:<br/>
     * <pre>
     * $tool    = new dbaseTools();<br/>
     * $where   = "id = :id and status = :status";<br/>
     * $param   = array("id"=>2, "status"=>0);
     * $tool->selectTable("user_table", $where, $param);<br/>
     * </pre>
     *
     * @access	public
     * @param	string	the table to retrieve the results from
     * @param	string	the where clause
     * @param	Array	the where parameter
     * @return	object
     */
    public function selectTable($table = "", $where = "", $param = array()) {
        try {
            $cond = "";
            if (!empty($where)) {
                $cond = " where " . $where;
            }
            $query = "select * from $table $cond";
            $this->statement = $this->_connection->prepare($query);

            if (empty($where)) {
                $this->statement->execute();
                $results = $this->statement->fetchAll(PDO::FETCH_OBJ);
                $this->closeConnection();
                return $results;
            } else {
                return $this->executeQuery($param);
            }
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            return false;
        }
    }

    private function executeQuery($param = array()) {
        try {
            $pr = array();
            if ($this->statement != null) {
                if (count($param) > 0) {
                    foreach ($param as $column => $vl) {
                        $pr[":$column"] = $vl;
                    }
                }

                $result = $this->statement->execute($pr);
//                $rowCount = $this->statement->rowCount();

                try{
                    $result = $this->statement->fetchAll(PDO::FETCH_OBJ);
                }catch(PDOException $s){
//                    $results = $this->statement->fetch();
                }
                $this->closeConnection();
                return $result;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            return false;
        }
    }

    /**
     * Generate single select query - <i><b>updated by M. Azwar Nurrosat</b></i><br/>
     * select * from $table where primary_key = $id<br/>
     * example:<br/>
     * <pre>
     * $tool    = new dbaseTools();<br/>
     * $result  = $tool->getByPK("user_table", $userID);<br/>
     * </pre>
     *
     * @access	public
     * @param	string	the table to retrieve the results from
     * @param	mixed	the primary_key
     * @return	object
     */
    public function getByPK($table = "", $id = "") {
        try {
            if ($table != "") {
                $colName = $this->getPrimaryColumn($table);
                if ($colName != "") {
                    $query = "select * from $table where $colName = :$colName";
                    $stmt = $this->_connection->prepare($query);
                    $stmt->bindParam(':' . $colName, $id);
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_OBJ);
                    $this->closeConnection();
                    return $results;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            return false;
        }
    }

    /**
     * get primary key column name
     *
     * @access  private
     * @param	String	Table name
     * @return	String
     */
    private function getPrimaryColumn($table = "") {
        $colName = "";
        if ($table != "") {
            $query = "show KEYS from $table WHERE Key_name = 'PRIMARY'";
            $stmt = $this->_connection->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            $colName = $results[0]->Column_name;
        }
        $this->closeConnection();
        return $colName;
    }

    /**
     * Get the last inserted primary key<br/>
     * <i><b>Updated by M. Azwar Nurrosat</b></i><br/>
     * <br/>
     * @access public
     * @return int
     *
     */
    public function getLastPK() {
        return $this->_connection->lastInsertId();
    }

    /**
     * Call mysql stored procedure
     *
     * @access	public
     * @param	String	stored procedure
     * @param	Array	parameter (if exist)
     * @return	object
     */
    public function callProcedure($storedProcedure = "", $param = array()) {
        try {
            $query = "call $storedProcedure";
            $col = "";
            if (count($param) != 0) {
                foreach ($param as $column => $value) {
                    $col .= ",:" . $column;
                }
                $col = "(" . substr($col, 1) . ")";
                $query .= $col;
                $stmt = $this->_connection->prepare($query);
                foreach ($param as $column => $vl) {
                    $stmt->bindParam(':' . $column, $vl);
                }
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_OBJ);
                $this->closeConnection();
                return $results;
            } else {
                $query .= "()";
                $stmt = $this->_connection->prepare($query);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_OBJ);
                $this->closeConnection();
                return $results;
            }
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            return false;
        }
    }

    /**
     * Retrieve mysql database structure
     *
     * @access	public
     * @return	object  table structure
     */
    public function fetchDatabaseStructure() {
        try {
            //fetch all table name from current database
            $query = "select table_name from information_schema.TABLES where table_schema = '" . $this->_dbName . "'";
            $this->statement = $this->_connection->prepare($query);
            $tableRaw = $this->executeQuery();
            $rawData = array();
            foreach ($tableRaw as $rd) {
                $tableName = $rd->table_name;
                $query = "desc $tableName";
                $this->statement = $this->_connection->prepare($query);
                $rawObject = $this->executeQuery();
                //generate object structure
                $className = $this->generateClassName($tableName);
                //generate class content
                $rawText = "<?php\n";
                $rawText .= 'require_once "../dbaseTools.php";';
                $rawText .= "\n\n\nclass $className {\n";
                foreach ($rawObject as $ro) {
                    $columnName = $ro->Field;
                    $varName = $this->generateClassName($columnName);
                    $rawText .= "\n\tpublic $$varName;";
                }

                //generate constructor
                $rawText .= "\n\tpublic function __construct() {";
                $rawText .= "\n\n\n\n\n";
                $rawText .= "\n\t}";
                //generate insert query
                $rawText .= "\n\tpublic function insert(){";
                $rawText .= "\n\t\t" . '$tool = new dbaseTools();';

                foreach ($rawObject as $ro) {
                    $columnName = $ro->Field;
                    $varName = $this->generateClassName($columnName);
                    $rawText .= "\n\t\t" . '$data["' . $columnName . '"] = $this->' . $varName . ';';
                }
                $rawText .= "\n\t\t" . 'return $tool->insert("' . $tableName . ', $data");';
                $rawText .= "\n\t}";
                //generate update query
                $rawText .= "\n\tpublic function update(){";
                $rawText .= "\n\t\treturn false;";
                $rawText .= "\n\t}";
                //generate delete query
                $rawText .= "\n\tpublic function delete(){";
                $rawText .= "\n\t\treturn false;";
                $rawText .= "\n\t}";
                //generate select query
                $rawText .= "\n\tpublic function select(){";
                $rawText .= "\n\t\treturn false;";
                $rawText .= "\n\t}";
                $rawText .= "\n";
                $rawText .= "\n}";

                $file = "models/$className.php";
                $fh = fopen($file, 'w');
                fwrite($fh, $rawText);
                fclose($fh);
                // echo "generate $file<br/>";
            }
            return "oke";
        } catch (PDOException $e) {
            if($this->_debugMode){
                echo "<pre>";
                print_r($e);
                echo "</pre>";
            }
            return false;
        }
    }

    private function generateClassName($tableName = "") {
        if ($tableName != "") {
            $rawName = explode("_", $tableName);
            $index = 0;
            $result = "";
            foreach ($rawName as $rn) {
                if ($index > 0) {
                    $result .= ucfirst($rn);
                } else {
                    $result .= $rn;
                }
                $index++;
            }
            return $result;
        } else {
            return "";
        }
    }

    public function fetchTable() {
        $query = "select table_name from information_schema.TABLES where table_schema = '" . $this->_dbName . "'";
        $this->statement = $this->_connection->prepare($query);
        return $this->executeQuery();
    }

    public function generateClassFromTableName($tbName = "", $outputPath = "classes"){

        if (!empty($outputPath) && !file_exists($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        if(!empty($tbName)) {
            $tableName = $tbName;
            $query = "desc $tableName";
            $this->statement = $this->_connection->prepare($query);
            $rawObject = $this->executeQuery();
            if(count($rawObject) > 0 && $rawObject != "") {
                $className = $this->generateClassName($tableName);
                $rawText = "<?php\n";
                $rawText .= "/**\n* auto generate class name $className\n* from table $tableName\n* db helper class generator - by M Azwar Nurrosat\n*/";
                $rawText .= "\nclass $className {\n";
                foreach ($rawObject as $ro) {
                    $columnName = $ro->Field;
                    $varName = $this->generateClassName($columnName);
                    $rawText .= "\n\tpublic $$varName;";
                }

                //generate constructor
                $rawText .= "\n\tpublic function __construct() {";
                $rawText .= "\n";
                $rawText .= "\n\t}";
                //generate insert query
                $rawText .= "\n\tpublic function insert(){";
                $rawText .= "\n\t\t" . '$tool = new dbaseTools();';

                foreach ($rawObject as $ro) {
                    $columnName = $ro->Field;
                    $varName = $this->generateClassName($columnName);
                    $rawText .= "\n\t\t" . '$data["' . $columnName . '"] = $this->' . $varName . ';';
                }
                $rawText .= "\n\t\t" . 'return $tool->insert("' . $tableName . ', $data");';
                $rawText .= "\n\t}";
                //generate update query
                $rawText .= "\n\tpublic function update(){";
                $rawText .= "\n\t\treturn false;";
                $rawText .= "\n\t}";
                //generate delete query
                $rawText .= "\n\tpublic function delete(){";
                $rawText .= "\n\t\treturn false;";
                $rawText .= "\n\t}";
                //generate select query
                $rawText .= "\n\tpublic function select(){";
                $rawText .= "\n\t\treturn false;";
                $rawText .= "\n\t}";
                $rawText .= "\n";
                $rawText .= "\n}";


                $file = "$outputPath/$className.php";
                $fh = fopen($file, 'w');
                fwrite($fh, $rawText);
                fclose($fh);
                echo "generate $file \tfrom table $tableName\n";
            }else{
                echo "table name  $tableName not found ...\n";
            }
        }

    }

    public function generateClassFromTables($tableNameList = [], $outputPath = "classes") {

        foreach ($tableNameList as $rd) {
            $this->generateClassFromTableName($rd, $outputPath);
        }
        return "done";
    }

    public function generateClassFromAllTable($outputPath = "classes") {
        $res = $this->fetchTable();
        foreach($res as $rs){
            $this->generateClassFromTableName($rs->table_name, $outputPath);
        }
        return "done";
    }

    public function getUID() {
        $uid = md5(date("YmdHis") . round(microtime(true) * 1000));
        return $uid;
    }

    public function getUniqID(){
        $query  ="select uuid() as uid";
        $uid    = $this->query($query);
        return $uid[0]->uid;
    }

    public function getTableInfo($tableName = ""){
        if(!empty($tableName)){
            $query = "SHOW TABLE STATUS FROM ".$this->_dbName." LIKE '$tableName'";
            //// echo $query;
            return $this->query($query);
        }else{
            return false;
        }
    }

    private function closeConnection(){
//        $this->_connection = null;
    }

    public function clearConnection(){
        $this->_connection = null;
    }

    public function generateRandomID($prefix){
        list($usec, $sec) = explode(" ", microtime());
        $randomID = $prefix . (10000 * ((float)$usec + (float)$sec));
        return $randomID;
    }
}
