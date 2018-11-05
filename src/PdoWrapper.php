<?php

/**
 * DframeFramework - Database
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/database/blob/master/README.md (MIT)
 */

namespace Dframe\Database;

use Dframe\Database\Helper\PDOHelper;
use PDO;

/**
 * PdoWrapper.
 *
 * PdoWrapper for using PDO methods
 *
 *
 * @author   Neeraj Singh <neeraj.singh@lbi.co.in>
 * @author   Sławomir Kaleta <slaszka@gmail.com>
 * @author   Neeraj Singh <neeraj.singh@lbi.co.in>
 * @author   Sławomir Kaleta <slaszka@gmail.com>
 **/
class PdoWrapper extends \PDO
{
    /**
     * PDO Error File.
     *
     * @var string
     */
    const LOG_FILE = 'mysql.error.log';

    /**
     * PDO Object.
     *
     * @var object
     */
    protected static $PDO = null;

    /**
     * PDO SQL Statement.
     *
     * @var string
     */
    public $sql = '';

    /**
     * PDO SQL table name.
     *
     * @var string
     */
    public $table = [];

    /**
     * PDO SQL Where Condition.
     *
     * @var array
     */
    public $arrayWhere = [];

    /**
     * PDO SQL table column.
     *
     * @var array
     */
    public $column = [];

    /**
     * PDO SQL Other condition.
     *
     * @var array
     */
    public $other = [];

    /**
     * PDO Results,Fetch All PDO Results array.
     *
     * @var array
     */
    public $results = [];

    /**
     * PDO Result,Fetch One PDO Row.
     *
     * @var array
     */
    public $result = [];

    /**
     * Get PDO Last Insert ID.
     *
     * @var int
     */
    public $lastId = 0;

    /**
     * PDO last insert di in array
     * using with INSERT BATCH Query.
     *
     * @var array
     */
    public $allLastId = [];

    /**
     * Get PDO Error.
     *
     * @var string
     */
    public $pdoError = '';

    /**
     * Get All PDO Affected Rows.
     *
     * @var int
     */
    public $affectedRows = 0;

    /**
     * Catch temp data.
     *
     * @var null
     */
    public $data = null;

    /**
     * Enable/Disable class debug mode.
     *
     * @var bool
     */
    public $log = false;

    /**
     * Set flag for batch insert.
     *
     * @var bool
     */
    public $batch = false;

    /**
     * PHP Statement Handler.
     *
     * @var object
     */
    private $STH = null;

    /**
     * PDO Config/Settings.
     *
     * @var array
     */
    private $dbInfo = [];

    /**
     * Set PDO valid Query operation.
     *
     * @var array
     */
    private $validOperation = ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];

    /**
     * Auto Start on Object init.
     *
     * @param array $dsn
     *
     * @param array $settings
     */
    public function __construct($dsn = [], $settings = ['attributes' => []])
    {
        // if isset $dsn and it is array
        if (is_array($dsn) && count($dsn) > 0) {
            // check valid array key name
            if (!isset($dsn['host']) || !isset($dsn['dbname']) || !isset($dsn['username']) || !isset($dsn['password'])) {
                die("Dude!! You haven't pass valid db config array key.");
            }
            $this->dbInfo = $dsn;
        } else {
            if (count($this->dbInfo) > 0) {
                $dsn = $this->dbInfo;
                // check valid array key name
                if (!isset($dsn['host']) || !isset($dsn['dbname']) || !isset($dsn['username']) || !isset($dsn['password'])) {
                    die("Dude!! You haven't set valid db config array key.");
                }
            } else {
                die("Dude!! You haven't set valid db config array.");
            }
        }

        if (!isset($dsn['dbtype'])) {
            $dsn['dbtype'] = 'mysql';
        }

        // Okay, everything is clear. now connect
        // spilt array key in php variable
        extract($this->dbInfo);
        // try catch block start
        try {

            // use native pdo class and connect
            parent::__construct(
                $dsn['dbtype'] . ":host=$host; dbname=$dbname",
                $username,
                $password,
                $settings['attributes']
            );
        } catch (\PDOException $e) {
            // get pdo error and pass on error method
            die('ERROR in establish connection: ' . $e->getMessage());
        }
    }

    /**
     * Get Instance of PDO Class as Singleton Pattern.
     *
     * @param array $dsn
     *
     * @return object $PDO
     */
    public static function getPDO($dsn = [])
    {
        // if not set self pdo object property or pdo set as null
        if (!isset(self::$PDO) || (self::$PDO !== null)) {
            self::$PDO = new self($dsn); // set class pdo property with new connection
        }

        // return class property object
        return self::$PDO;
    }

    /**
     * Return PDO Query result by index value.
     *
     * @param int $row
     *
     * @return array|bool
     */
    public function result($row = 0)
    {
        return isset($this->results[$row]) ? $this->results[$row] : false;
    }

    /**
     * Get Affected rows by PDO Statement.
     *
     * @return int|bool
     */
    public function affectedRows()
    {
        return is_numeric($this->affectedRows) ? $this->affectedRows : false;
    }

    /**
     * Get Last Insert id by Insert query.
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->lastId;
    }

    /**
     * Get all last insert id by insert batch query.
     *
     * @return array
     */
    public function getAllLastInsertId()
    {
        return $this->allLastId;
    }

    /**
     * Execute PDO Query.
     *
     * @param string $sql
     * @param array  $bindWhereParam Bind Param Value
     *
     * @return self|void
     */
    public function pdoQuery($sql = '', $bindWhereParam = [])
    {
        // Check empty query
        if (empty($sql)) {
            self::error('Query is empty..');
        }

        // clean query from white space
        $sql = trim($sql);
        // get operation type
        $operation = explode(' ', $sql);
        // make first word in uppercase
        $operation[0] = strtoupper($operation[0]);

        // set class property with pass value
        $this->sql = $sql;
        // set class statement handler
        $this->STH = $this->prepare($this->sql);

        // check valid sql operation statement
        if (!in_array($operation[0], $this->validOperation)) {
            self::error('invalid operation called in query. use only ' . implode(', ', $this->validOperation) . ' You can have NO SPACE be between ' . implode(', ', $this->validOperation) . ' AND parms');
        }

        // sql query pass with no bind param
        if (count($bindWhereParam) <= 0) {

            // try catch block start
            try {
                // execute pdo statement
                if ($this->STH->execute()) {
                    // get affected rows and set it to class property
                    $this->affectedRows = $this->STH->rowCount();
                    // set pdo result array with class property
                    $this->results = $this->STH->fetchAll();
                    // close pdo cursor
                    $this->STH->closeCursor();
                    // return pdo result
                    return $this;
                } else {
                    self::error($this->STH->errorInfo()); // if not run pdo statement sed error
                }
            } catch (\PDOException $e) {
                self::error($e->getMessage() . ': ' . __LINE__);
            } // end try catch block
        } elseif (count($bindWhereParam) > 0) {  // if query pass with bind param

            $this->data = $bindWhereParam;
            // start binding fields
            // bind pdo param
            $this->_bindPdoParam($bindWhereParam);
            // use try catch block to get pdo error
            try {
                // run pdo statement with bind param
                if ($this->STH->execute()) {
                    // check operation type
                    switch ($operation[0]) {
                        case 'SELECT':
                            // get affected rows by select statement
                            $this->affectedRows = $this->STH->rowCount();
                            // get pdo result array
                            $this->results = $this->STH->fetchAll();
                            // return PDO instance
                            return $this;
                            break;
                        case 'INSERT':
                            // return last insert id
                            $this->lastId = $this->lastInsertId();
                            // return PDO instance
                            return $this;
                            break;
                        case 'UPDATE':
                            // get affected rows
                            $this->affectedRows = $this->STH->rowCount();
                            // return PDO instance
                            return $this;
                            break;
                        case 'DELETE':
                            // get affected rows
                            $this->affectedRows = $this->STH->rowCount();
                            // return PDO instance
                            return $this;
                            break;
                    }
                    // close pdo cursor
                    $this->STH->closeCursor();
                } else {
                    self::error($this->STH->errorInfo());
                }
            } catch (\PDOException $e) {
                self::error($e->getMessage() . ': ' . __LINE__);
            } // end try catch block to get pdo error
        } else {
            self::error('Error Query');
        }
    }

    /**
     * Catch Error in txt file.
     *
     * @param mixed $msg
     */
    public function error($msg)
    {
        file_put_contents($this->config['logDir'] . self::LOG_FILE, date('Y-m-d h:m:s') . ' :: ' . $msg . "\n", FILE_APPEND);

        // log set as true
        if ($this->log) {
            // show executed query with error
            $this->showQuery();
            // die code
            $this->helper()->errorBox($msg);
        }

        throw new \PDOException($msg);
    }

    /**
     * Show executed query on call.
     *
     * @param bool $logfile set true if wanna log all query in file
     *
     * @return self
     */
    public function showQuery($logfile = false)
    {
        if (!$logfile) {
            echo "<div style='color:#990099; border:1px solid #777; padding:2px; background-color: #E5E5E5;'>";
            echo " Executed Query -> <span style='color:#008000;'> ";
            echo $this->helper()->formatSQL($this->interpolateQuery());
            echo '</span></div>';
        }

        file_put_contents($this->config['logDir'] . self::LOG_FILE, date('Y-m-d h:m:s') . ' :: ' . $this->interpolateQuery() . "\n", FILE_APPEND);

        return $this;
    }

    /**
     * Get Helper Object.
     *
     * @return PDOHelper
     */
    public function helper()
    {
        return new PDOHelper();
    }

    /**
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from.
     *
     * @return mixed
     */
    protected function interpolateQuery()
    {
        $sql = $this->STH->queryString;
        // handle insert batch data
        if (!$this->batch) {
            $params = ((is_array($this->data)) && (count($this->data) > 0)) ? $this->data : $this->sql;
            if (is_array($params)) {
                // build a regular expression for each parameter
                $keys = [];
                foreach ($params as $key => $value) {
                    if (strstr($key, ' ')) {
                        $real_key = $this->getFieldFromArrayKey($key);
                        // update param value with quotes, if string value
                        $params[$key] = is_string($value) ? '"' . $value . '"' : $value;
                        // make replace array
                        $keys[] = is_string($real_key) ? '/:s_' . $real_key . '/' : '/[?]/';
                    } else {
                        // update param value with quotes, if string value
                        $params[$key] = is_string($value) ? '"' . $value . '"' : $value;
                        // make replace array
                        $keys[] = is_string($key) ? '/:s_' . $key . '/' : '/[?]/';
                    }
                }
                $sql = preg_replace($keys, $params, $sql, 1, $count);

                if (strstr($sql, ':s_')) {
                    foreach ($this->arrayWhere as $key => $value) {
                        if (strstr($key, ' ')) {
                            $real_key = $this->getFieldFromArrayKey($key);
                            // update param value with quotes, if string value
                            $params[$key] = is_string($value) ? '"' . $value . '"' : $value;
                            // make replace array
                            $keys[] = is_string($real_key) ? '/:s_' . $real_key . '/' : '/[?]/';
                        } else {
                            // update param value with quotes, if string value
                            $params[$key] = is_string($value) ? '"' . $value . '"' : $value;
                            // make replace array
                            $keys[] = is_string($key) ? '/:s_' . $key . '/' : '/[?]/';
                        }
                    }
                    $sql = preg_replace($keys, $params, $sql, 1, $count);
                }

                return $sql;
                // trigger_error('replaced '.$count.' keys');
            }

            return $params;
        } else {
            $params_batch = ((is_array($this->data)) && (count($this->data) > 0)) ? $this->data : $this->sql;
            $batch_query = '';

            if (is_array($params_batch)) {
                // build a regular expression for each parameter
                $array_keys = [];
                foreach ($params_batch as $keys => $params) {
                    echo $params;
                    foreach ($params as $key => $value) {
                        if (strstr($key, ' ')) {
                            $real_key = $this->getFieldFromArrayKey($key);
                            // update param value with quotes, if string value
                            $params[$key] = is_string($value) ? '"' . $value . '"' : $value;
                            // make replace array
                            $array_keys[] = is_string($real_key) ? '/:s_' . $real_key . '/' : '/[?]/';
                        } else {
                            // update param value with quotes, if string value
                            $params[$key] = is_string($value) ? '"' . $value . '"' : $value;
                            // make replace array
                            $array_keys[] = is_string($key) ? '/:s_' . $key . '/' : '/[?]/';
                        }
                    }
                    $batch_query .= '<br />' . preg_replace($array_keys, $params, $sql, 1, $count);
                }

                return $batch_query;
                // trigger_error('replaced '.$count.' keys');
            }

            return $params_batch;
        }
    }

    /**
     * Return real table column from array key.
     *
     * @param string $array_key
     *
     * @return mixed
     */
    public function getFieldFromArrayKey($array_key)
    {
        // get table column from array key
        $key_array = explode(' ', $array_key);
        // check no of chunk
        return (count($key_array) == '2') ? $key_array[0] : ((count($key_array) > 2) ? $key_array[1] : $key_array[0]);
    }

    /**
     * Bind PDO Param without :namespace.
     *
     * @param array $array
     */
    private function _bindPdoParam($array = [])
    {
        // bind array data in pdo
        foreach ($array as $f => $v) {
            // check pass data type for appropriate field
            switch (gettype($array[$f])) {
                // is string found then pdo param as string
                case 'string':
                    $this->STH->bindParam($f + 1, $array[$f], PDO::PARAM_STR);
                    break;
                // if int found then pdo param set as int
                case 'integer':
                    $this->STH->bindParam($f + 1, $array[$f], PDO::PARAM_INT);
                    break;
                // if boolean found then set pdo param as boolean
                case 'boolean':
                    $this->STH->bindParam($f + 1, $array[$f], PDO::PARAM_BOOL);
                    break;
            }
        } // end for each here
    }

    /**
     * MySQL SELECT Query/Statement.
     *
     * @param string $table
     * @param array  $column
     * @param array  $arrayWhere
     * @param string $other
     *
     * @return self type: array/error
     */
    public function select($table = '', $column = [], $arrayWhere = [], $other = '')
    {
        // handle column array data
        if (!is_array($column)) {
            $column = [];
        }
        // get field if pass otherwise use *
        $sField = count($column) > 0 ? implode(', ', $column) : '*';
        // check if table name not empty
        if (!empty($table)) {
            // if more then 0 array found in where array
            if (count($arrayWhere) > 0 && is_array($arrayWhere)) {
                // set class where array
                $this->data = $arrayWhere;
                // parse where array and get in temp var with key name and val
                if (strstr(key($arrayWhere), ' ')) {
                    $tmp = $this->customWhere($this->data);
                    // get where syntax with namespace
                    $where = $tmp['where'];
                } else {
                    $tmp = [];
                    foreach ($arrayWhere as $k => $v) {
                        $tmp[] = "$k = :s_$k";
                    }
                    // join temp array with AND condition
                    $where = implode(' AND ', $tmp);
                }
                // unset temp var
                unset($tmp);
                // set class sql property
                $this->sql = "SELECT $sField FROM `$table` WHERE $where $other;";
            } else {
                $this->sql = "SELECT $sField FROM `$table` $other;";  // if no where condition pass by user
            }

            // pdo prepare statement with sql query
            $this->STH = $this->prepare($this->sql);
            // if where condition has valid array number

            if (count($arrayWhere) > 0 && is_array($arrayWhere)) {
                $this->_bindPdoNameSpace($arrayWhere); // bind pdo param
            }

            // use try catch block to get pdo error
            try {
                // check if pdo execute
                if ($this->STH->execute()) {
                    // set class property with affected rows
                    $this->affectedRows = $this->STH->rowCount();
                    // set class property with sql result
                    $this->results = $this->STH->fetchAll();
                    // close pdo
                    $this->STH->closeCursor();
                    // return self object
                    return $this;
                } else {
                    self::error($this->STH->errorInfo());  // catch pdo error
                }
            } catch (\PDOException $e) {
                // get pdo error and pass on error method
                self::error($e->getMessage() . ': ' . __LINE__);
            } // end try catch block to get pdo error
        } else { // if table name empty
            self::error('Table name not found..');
        }
    }

    /**
     * @param array $array_data
     *
     * @return array
     */
    public function customWhere($array_data = [])
    {
        $syntax = '';
        $tmp = [];
        foreach ($array_data as $key => $value) {
            $key = trim($key);
            if (strstr($key, ' ')) {
                $array = explode(' ', $key);
                if (count($array) == '2') {
                    $random = ''; //"_".rand(1,100);
                    $field = $array[0];
                    $operator = $array[1];
                    $tmp[] = "$field $operator :s_$field" . "$random";
                    $syntax .= " $field $operator :s_$field" . "$random ";
                } elseif (count($array) == '3') {
                    $random = ''; //"_".rand(1,100);
                    $condition = $array[0];
                    $field = $array[1];
                    $operator = $array[2];
                    $tmp[] = "$condition $field $operator :s_$field" . "$random";
                    $syntax .= " $condition $field $operator :s_$field" . "$random ";
                }
            }
        }

        return [
            'where' => $syntax,
            'bind' => implode(' ', $tmp),
        ];
    }

    /**
     * PDO Bind Param with :namespace.
     *
     * @param array $array
     */
    private function _bindPdoNameSpace($array = [])
    {
        if (strstr(key($array), ' ')) {
            // bind array data in pdo
            foreach ($array as $f => $v) {
                // get table column from array key
                $field = $this->getFieldFromArrayKey($f);
                // check pass data type for appropriate field
                switch (gettype($array[$f])) {
                    // is string found then pdo param as string
                    case 'string':
                        $this->STH->bindParam(':s' . '_' . "$field", $array[$f], PDO::PARAM_STR);
                        break;
                    // if int found then pdo param set as int
                    case 'integer':
                        $this->STH->bindParam(':s' . '_' . "$field", $array[$f], PDO::PARAM_INT);
                        break;
                    // if boolean found then set pdo param as boolean
                    case 'boolean':
                        $this->STH->bindParam(':s' . '_' . "$field", $array[$f], PDO::PARAM_BOOL);
                        break;
                };
            } // end for each here
        } else {

            // bind array data in pdo
            foreach ($array as $f => $v) {
                // check pass data type for appropriate field
                switch (gettype($array[$f])) {
                    // is string found then pdo param as string
                    case 'string':
                        $this->STH->bindParam(':s' . '_' . "$f", $array[$f], PDO::PARAM_STR);
                        break;
                    // if int found then pdo param set as int
                    case 'integer':
                        $this->STH->bindParam(':s' . '_' . "$f", $array[$f], PDO::PARAM_INT);
                        break;
                    // if boolean found then set pdo param as boolean
                    case 'boolean':
                        $this->STH->bindParam(':s' . '_' . "$f", $array[$f], PDO::PARAM_BOOL);
                        break;
                }
            } // end for each here
        }
    }

    /**
     * Execute PDO Insert.
     *
     * @param string $table
     * @param array  $data
     *
     * @return self|void
     */
    public function insert($table, $data = [])
    {
        // check if table name not empty
        if (!empty($table)) {
            // and array data not empty
            if (count($data) > 0 && is_array($data)) {
                // get array insert data in temp array
                $tmp = [];
                foreach ($data as $f => $v) {
                    $tmp[] = ":s_$f";
                }
                // make name space param for pdo insert statement
                $sNameSpaceParam = implode(',', $tmp);
                // unset temp var
                unset($tmp);
                // get insert fields name
                $sFields = implode(',', array_keys($data));
                // set pdo insert statement in class property
                $this->sql = "INSERT INTO `$table` ($sFields) VALUES ($sNameSpaceParam);";
                // pdo prepare statement
                $this->STH = $this->prepare($this->sql);
                // set class where property with array data
                $this->data = $data;
                // bind pdo param
                $this->_bindPdoNameSpace($data);
                // use try catch block to get pdo error
                try {
                    // execute pdo statement
                    if ($this->STH->execute()) {
                        // set class property with last insert id
                        $this->lastId = $this->lastInsertId();
                        // close pdo
                        $this->STH->closeCursor();
                        // return this object
                        return $this;
                    } else {
                        self::error($this->STH->errorInfo());
                    }
                } catch (\PDOException $e) {
                    // get pdo error and pass on error method
                    self::error($e->getMessage() . ': ' . __LINE__);
                }
            } else {
                self::error('Data not in valid format..');
            }
        } else {
            self::error('Table name not found..');
        }
    }

    /**
     * Execute PDO Insert as Batch Data.
     *
     * @param string $table          mysql table name
     * @param array  $data           mysql insert array data
     * @param bool   $safeModeInsert set true if want to use pdo bind param
     *
     * @return self last insert ID
     */
    public function insertBatch($table, $data = [], $safeModeInsert = true)
    {

        // PDO transactions start
        $this->start();
        // check if table name not empty
        if (!empty($table)) {
            // and array data not empty
            if (count($data) > 0 && is_array($data)) {
                // get array insert data in temp array
                $tmp = [];
                foreach ($data[0] as $f => $v) {
                    $tmp[] = ":s_$f";
                }
                // make name space param for pdo insert statement
                $sNameSpaceParam = implode(', ', $tmp);
                // unset temp var
                unset($tmp);
                // get insert fields name
                $sFields = implode(', ', array_keys($data[0]));
                // handle safe mode. If it is set as false means user not using bind param in pdo
                if (!$safeModeInsert) {
                    // set pdo insert statement in class property
                    $this->sql = "INSERT INTO `$table` ($sFields) VALUES ";
                    foreach ($data as $key => $value) {
                        $this->sql .= '(' . "'" . implode("', '", array_values($value)) . "'" . '), ';
                    }
                    $this->sql = rtrim($this->sql, ', ');
                    // return this object
                    // return $this;
                    // pdo prepare statement
                    $this->STH = $this->prepare($this->sql);
                    // start try catch block
                    try {
                        // execute pdo statement
                        if ($this->STH->execute()) {
                            // store all last insert id in array
                            $this->allLastId[] = $this->lastInsertId();
                        } else {
                            self::error($this->STH->errorInfo());
                        }
                    } catch (\PDOException $e) {
                        // get pdo error and pass on error method
                        self::error($e->getMessage() . ': ' . __LINE__);
                        // PDO Rollback
                        $this->back();
                    }// end try catch block

                    // PDO Commit
                    $this->end();
                    // close pdo
                    $this->STH->closeCursor();
                    // return this object
                    return $this;
                }

                // end here safe mode
                // set pdo insert statement in class property
                $this->sql = "INSERT INTO `$table` ($sFields) VALUES ($sNameSpaceParam);";
                // pdo prepare statement
                $this->STH = $this->prepare($this->sql);
                // set class property with array
                $this->data = $data;
                // set batch insert flag true
                $this->batch = true;
                // parse batch array data
                foreach ($data as $key => $value) {
                    // bind pdo param
                    $this->_bindPdoNameSpace($value);

                    try {
                        // execute pdo statement
                        if ($this->STH->execute()) {
                            // set class property with last insert id as array
                            $this->allLastId[] = $this->lastInsertId();
                        } else {
                            self::error($this->STH->errorInfo());
                            // on error PDO Rollback
                            $this->back();
                        }
                    } catch (\PDOException $e) {
                        // get pdo error and pass on error method
                        self::error($e->getMessage() . ': ' . __LINE__);
                        // on error PDO Rollback
                        $this->back();
                    }
                }
                // fine now PDO Commit
                $this->end();
                // close pdo
                $this->STH->closeCursor();
                // return this object
                return $this;
            } else {
                self::error('Data not in valid format..');
            }
        } else {
            self::error('Table name not found..');
        }
    }

    /**
     * Start PDO Transaction.
     */
    public function start()
    {
        // begin the transaction
        $this->beginTransaction();
    }

    /**
     * Start PDO Rollback.
     */
    public function back()
    {
        // roll back the transaction if we fail
        $this->rollback();
    }

    /**
     * Start PDO Commit.
     */
    public function end()
    {
        // commit the transaction
        $this->commit();
    }

    /**
     * Execute PDO Update Statement
     * Get No OF Affected Rows updated.
     *
     * @param string $table
     * @param array  $data
     * @param array  $arrayWhere
     * @param string $other
     *
     * @return self|void
     */
    public function update($table = '', $data = [], $arrayWhere = [], $other = '')
    {
        // if table name is empty
        if (!empty($table)) {
            // check if array data and where array is more then 0
            if (count($data) > 0 && count($arrayWhere) > 0) {
                // parse array data and make a temp array
                $tmp = [];
                foreach ($data as $k => $v) {
                    $tmp[] = "$k = :s_$k";
                }
                // join temp array value with ,
                $sFields = implode(', ', $tmp);
                // delete temp array from memory
                unset($tmp);

                $tmp = [];
                // parse where array and store in temp array
                foreach ($arrayWhere as $k => $v) {
                    $tmp[] = "$k = :s_$k";
                }

                $this->data = $data;
                $this->arrayWhere = $arrayWhere;
                // join where array value with AND operator and create where condition
                $where = implode(' AND ', $tmp);
                // unset temp array
                unset($tmp);
                // make sql query to update
                $this->sql = "UPDATE `$table` SET $sFields WHERE $where $other;";
                // on PDO prepare statement
                $this->STH = $this->prepare($this->sql);
                // bind pdo param for update statement
                $this->_bindPdoNameSpace($data);
                // bind pdo param for where clause
                $this->_bindPdoNameSpace($arrayWhere);
                // try catch block start
                try {
                    // if PDO run
                    if ($this->STH->execute()) {
                        // get affected rows
                        $this->affectedRows = $this->STH->rowCount();
                        // close PDO
                        $this->STH->closeCursor();
                        // return self object
                        return $this;
                    } else {
                        self::error($this->STH->errorInfo());
                    }
                } catch (\PDOException $e) {
                    // get pdo error and pass on error method
                    self::error($e->getMessage() . ': ' . __LINE__);
                } // try catch block end
            } else {
                self::error('update statement not in valid format..');
            }
        } else {
            self::error('Table name not found..');
        }
    }

    /**
     * Execute PDO Delete Query.
     *
     * @param string $table
     * @param array  $arrayWhere
     * @param string $other
     *
     * @return self|void
     */
    public function delete($table, $arrayWhere = [], $other = '')
    {
        // if table name not pass
        if (!empty($table)) {
            // check where condition array length
            if (count($arrayWhere) > 0 && is_array($arrayWhere)) {
                // make an temp array from where array data
                $tmp = [];
                foreach ($arrayWhere as $k => $v) {
                    $tmp[] = "$k = :s_$k";
                }
                // join array values with AND Operator
                $where = implode(' AND ', $tmp);
                // delete temp array
                unset($tmp);
                // set DELETE PDO Statement
                $this->sql = "DELETE FROM `$table` WHERE $where $other;";
                // Call PDo Prepare Statement
                $this->STH = $this->prepare($this->sql);
                // bind delete where param
                $this->_bindPdoNameSpace($arrayWhere);
                // set array data
                $this->data = $arrayWhere;
                // Use try Catch

                try {
                    if ($this->STH->execute()) {
                        // get affected rows
                        $this->affectedRows = $this->STH->rowCount();
                        // close pdo
                        $this->STH->closeCursor();
                        // return this object
                        return $this;
                    } else {
                        self::error($this->STH->errorInfo());
                    }
                } catch (\PDOException $e) {
                    // get pdo error and pass on error method
                    self::error($e->getMessage() . ': ' . __LINE__);
                } // end try catch here
            } else {
                self::error('Not a valid where condition..');
            }
        } else {
            self::error('Table name not found..');
        }
    }

    /**
     * Return PDO Query results array/json/xml type.
     *
     * @param string $type
     *
     * @return mixed
     */
    public function results($type = 'array')
    {
        switch ($type) {
            case 'array':
                // return array data
                return $this->results;
                break;
            case 'xml':
                //send the xml header to the browser
                header('Content-Type:text/xml');
                // return xml content
                return $this->helper()->arrayToXml($this->results);
                break;
            case 'json':
                // set header as json
                header('Content-type: application/json; charset="utf-8"');
                // return json encoded data
                return json_encode($this->results);
                break;
        }
    }

    /**
     * Get Total Number Of Records in Requested Table.
     *
     * @param string $table
     * @param string $where
     *
     * @return number
     */
    public function count($table = '', $where = '')
    {
        // if table name not pass
        if (!empty($table)) {
            if (empty($where)) {
                $this->sql = "SELECT COUNT(*) AS NUMROWS FROM `$table`;"; // create count query
            } else {
                $this->sql = "SELECT COUNT(*) AS NUMROWS FROM `$table` WHERE $where;";  // create count query
            }

            // pdo prepare statement
            $this->STH = $this->prepare($this->sql);

            try {
                if ($this->STH->execute()) {
                    // fetch array result
                    $this->results = $this->STH->fetch();
                    // close pdo
                    $this->STH->closeCursor();
                    // return number of count
                    return $this->results['NUMROWS'];
                } else {
                    self::error($this->STH->errorInfo());
                }
            } catch (\PDOException $e) {
                // get pdo error and pass on error method
                self::error($e->getMessage() . ': ' . __LINE__);
            }
        } else {
            self::error('Table name not found..');
        }
    }

    /**
     * Truncate a MySQL table.
     *
     * @param string $table
     *
     * @return bool
     */
    public function truncate($table = '')
    {
        // if table name not pass
        if (!empty($table)) {
            // create count query
            $this->sql = "TRUNCATE TABLE `$table`;";
            // pdo prepare statement
            $this->STH = $this->prepare($this->sql);

            try {
                if ($this->STH->execute()) {
                    // close pdo
                    $this->STH->closeCursor();
                    // return number of count
                    return true;
                } else {
                    self::error($this->STH->errorInfo());
                }
            } catch (\PDOException $e) {
                // get pdo error and pass on error method
                self::error($e->getMessage() . ': ' . __LINE__);
            }
        } else {
            self::error('Table name not found..');
        }
    }

    /**
     * Drop a MySQL table.
     *
     * @param string $table
     *
     * @return bool
     */
    public function drop($table = '')
    {
        // if table name not pass
        if (!empty($table)) {
            // create count query
            $this->sql = "DROP TABLE `$table`;";
            // pdo prepare statement
            $this->STH = $this->prepare($this->sql);

            try {
                if ($this->STH->execute()) {
                    // close pdo
                    $this->STH->closeCursor();
                    // return number of count
                    return true;
                } else {
                    self::error($this->STH->errorInfo());
                }
            } catch (\PDOException $e) {
                // get pdo error and pass on error method
                self::error($e->getMessage() . ': ' . __LINE__);
            }
        }

        self::error('Table name not found..');
    }

    /**
     * Return Table Fields of Requested Table.
     *
     * @param string $table
     *
     * @return array Field Type and Field Name
     */
    public function describe($table = '')
    {
        $this->sql = $sql = "DESC $table;";
        $this->STH = $this->prepare($sql);
        $this->STH->execute();
        $colList = $this->STH->fetchAll();

        $field = [];
        $type = [];
        foreach ($colList as $key) {
            $field[] = $key['Field'];
            $type[] = $key['Type'];
        }

        return array_combine($field, $type);
    }

    /**
     * Set PDO Error Mode to get an error log file or true to show error on screen.
     *
     * @param bool $mode
     */
    public function setErrorLog($mode = false)
    {
        $this->log = $mode;
    }

    /**
     * prepare PDO Query.
     *
     * @param string $statement
     * @param array  $options Value
     *
     * @return self
     */
    public function pdoPrepare($statement, $options = [])
    {
        $this->STH = $this->prepare($statement, $options);

        return $this;
    }

    /**
     * Execute PDO Query.
     *
     * @param array Bind Param Value
     *
     * @return self|int
     */
    public function execute($bindWhereParam = [])
    {

        // clean query from white space
        $sql = trim($this->STH->queryString);
        // get operation type
        $operation = explode(' ', $sql);
        // make first word in uppercase
        $operation[0] = strtoupper($operation[0]);

        if (!empty($bindWhereParam)) {
            $this->_bindPdoParam($bindWhereParam);
        }

        // use try catch block to get pdo error
        try {
            // run pdo statement with bind param
            if ($this->STH->execute()) {
                // check operation type
                switch ($operation[0]) {
                    case 'SELECT':
                        // get affected rows by select statement
                        $this->affectedRows = $this->STH->rowCount();
                        // get pdo result array
                        $this->results = $this->STH->fetchAll();
                        // return PDO instance
                        return $this;
                        break;
                    case 'INSERT':
                        // return last insert id
                        $this->lastId = $this->lastInsertId();
                        // return PDO instance
                        return $this;
                        break;
                    case 'UPDATE':
                        // get affected rows
                        $this->affectedRows = $this->STH->rowCount();
                        // return PDO instance
                        return $this;
                        break;
                    case 'DELETE':
                        // get affected rows
                        $this->affectedRows = $this->STH->rowCount();
                        // return PDO instance
                        return $this;
                        break;
                }
                // close pdo cursor
                $this->STH->closeCursor();
            } else {
                self::error($this->STH->errorInfo());
            }
        } catch (\PDOException $e) {
            // get pdo error and pass on error method
            self::error($e->getMessage() . ': ' . __LINE__);
        }
    }

    /**
     * Unset The Class Object PDO.
     */
    public function __destruct()
    {
        self::$PDO = null;
    }
}
