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
    protected static $oPDO = null;

    /**
     * PDO SQL Statement.
     *
     * @var string
     */
    public $sSql = '';

    /**
     * PDO SQL table name.
     *
     * @var string
     */
    public $sTable = [];

    /**
     * PDO SQL Where Condition.
     *
     * @var array
     */
    public $aWhere = [];

    /**
     * PDO SQL table column.
     *
     * @var array
     */
    public $aColumn = [];

    /**
     * PDO SQL Other condition.
     *
     * @var array
     */
    public $sOther = [];

    /**
     * PDO Results,Fetch All PDO Results array.
     *
     * @var array
     */
    public $aResults = [];

    /**
     * PDO Result,Fetch One PDO Row.
     *
     * @var array
     */
    public $aResult = [];

    /**
     * Get PDO Last Insert ID.
     *
     * @var int
     */
    public $iLastId = 0;

    /**
     * PDO last insert di in array
     * using with INSERT BATCH Query.
     *
     * @var array
     */
    public $iAllLastId = [];

    /**
     * Get PDO Error.
     *
     * @var string
     */
    public $sPdoError = '';

    /**
     * Get All PDO Affected Rows.
     *
     * @var int
     */
    public $iAffectedRows = 0;

    /**
     * Catch temp data.
     *
     * @var null
     */
    public $aData = null;

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
    private $oSTH = null;

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
    private $aValidOperation = ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];

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
     * @return object $oPDO
     */
    public static function getPDO($dsn = [])
    {
        // if not set self pdo object property or pdo set as null
        if (!isset(self::$oPDO) || (self::$oPDO !== null)) {
            self::$oPDO = new self($dsn); // set class pdo property with new connection
        }

        // return class property object
        return self::$oPDO;
    }

    /**
     * Return PDO Query result by index value.
     *
     * @param int $iRow
     *
     * @return array|bool
     */
    public function result($iRow = 0)
    {
        return isset($this->aResults[$iRow]) ? $this->aResults[$iRow] : false;
    }

    /**
     * Get Affected rows by PDO Statement.
     *
     * @return int|bool
     */
    public function affectedRows()
    {
        return is_numeric($this->iAffectedRows) ? $this->iAffectedRows : false;
    }

    /**
     * Get Last Insert id by Insert query.
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->iLastId;
    }

    /**
     * Get all last insert id by insert batch query.
     *
     * @return array
     */
    public function getAllLastInsertId()
    {
        return $this->iAllLastId;
    }

    /**
     * Execute PDO Query.
     *
     * @param string $sSql
     * @param array  $aBindWhereParam Bind Param Value
     *
     * @return self|void
     */
    public function pdoQuery($sSql = '', $aBindWhereParam = [])
    {
        // Check empty query
        if (empty($sSql)) {
            self::error('Query is empty..');
        }

        // clean query from white space
        $sSql = trim($sSql);
        // get operation type
        $operation = explode(' ', $sSql);
        // make first word in uppercase
        $operation[0] = strtoupper($operation[0]);

        // set class property with pass value
        $this->sSql = $sSql;
        // set class statement handler
        $this->oSTH = $this->prepare($this->sSql);

        // check valid sql operation statement
        if (!in_array($operation[0], $this->aValidOperation)) {
            self::error('invalid operation called in query. use only ' . implode(', ', $this->aValidOperation) . ' You can have NO SPACE be between ' . implode(', ', $this->aValidOperation) . ' AND parms');
        }

        // sql query pass with no bind param
        if (count($aBindWhereParam) <= 0) {

            // try catch block start
            try {
                // execute pdo statement
                if ($this->oSTH->execute()) {
                    // get affected rows and set it to class property
                    $this->iAffectedRows = $this->oSTH->rowCount();
                    // set pdo result array with class property
                    $this->aResults = $this->oSTH->fetchAll();
                    // close pdo cursor
                    $this->oSTH->closeCursor();
                    // return pdo result
                    return $this;
                } else {
                    self::error($this->oSTH->errorInfo()); // if not run pdo statement sed error
                }
            } catch (\PDOException $e) {
                self::error($e->getMessage() . ': ' . __LINE__);
            } // end try catch block
        } elseif (count($aBindWhereParam) > 0) {  // if query pass with bind param

            $this->aData = $aBindWhereParam;
            // start binding fields
            // bind pdo param
            $this->_bindPdoParam($aBindWhereParam);
            // use try catch block to get pdo error
            try {
                // run pdo statement with bind param
                if ($this->oSTH->execute()) {
                    // check operation type
                    switch ($operation[0]) :
                        case 'SELECT':
                            // get affected rows by select statement
                            $this->iAffectedRows = $this->oSTH->rowCount();
                            // get pdo result array
                            $this->aResults = $this->oSTH->fetchAll();
                            // return PDO instance
                            return $this;
                            break;
                        case 'INSERT':
                            // return last insert id
                            $this->iLastId = $this->lastInsertId();
                            // return PDO instance
                            return $this;
                            break;
                        case 'UPDATE':
                            // get affected rows
                            $this->iAffectedRows = $this->oSTH->rowCount();
                            // return PDO instance
                            return $this;
                            break;
                        case 'DELETE':
                            // get affected rows
                            $this->iAffectedRows = $this->oSTH->rowCount();
                            // return PDO instance
                            return $this;
                            break;
                    endswitch;
                    // close pdo cursor
                    $this->oSTH->closeCursor();
                } else {
                    self::error($this->oSTH->errorInfo());
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
        $sql = $this->oSTH->queryString;
        // handle insert batch data
        if (!$this->batch) {
            $params = ((is_array($this->aData)) && (count($this->aData) > 0)) ? $this->aData : $this->sSql;
            if (is_array($params)) {
                // build a regular expression for each parameter
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
                    foreach ($this->aWhere as $key => $value) {
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
            $params_batch = ((is_array($this->aData)) && (count($this->aData) > 0)) ? $this->aData : $this->sSql;
            $batch_query = '';

            if (is_array($params_batch)) {
                // build a regular expression for each parameter
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
            switch (gettype($array[$f])) :
                // is string found then pdo param as string
                case 'string':
                    $this->oSTH->bindParam($f + 1, $array[$f], PDO::PARAM_STR);
                    break;
                // if int found then pdo param set as int
                case 'integer':
                    $this->oSTH->bindParam($f + 1, $array[$f], PDO::PARAM_INT);
                    break;
                // if boolean found then set pdo param as boolean
                case 'boolean':
                    $this->oSTH->bindParam($f + 1, $array[$f], PDO::PARAM_BOOL);
                    break;
            endswitch;
        } // end for each here
    }

    /**
     * MySQL SELECT Query/Statement.
     *
     * @param string $sTable
     * @param array  $aColumn
     * @param array  $aWhere
     * @param string $sOther
     *
     * @return self type: array/error
     */
    public function select($sTable = '', $aColumn = [], $aWhere = [], $sOther = '')
    {
        // handle column array data
        if (!is_array($aColumn)) {
            $aColumn = [];
        }
        // get field if pass otherwise use *
        $sField = count($aColumn) > 0 ? implode(', ', $aColumn) : '*';
        // check if table name not empty
        if (!empty($sTable)) {
            // if more then 0 array found in where array
            if (count($aWhere) > 0 && is_array($aWhere)) {
                // set class where array
                $this->aData = $aWhere;
                // parse where array and get in temp var with key name and val
                if (strstr(key($aWhere), ' ')) {
                    $tmp = $this->customWhere($this->aData);
                    // get where syntax with namespace
                    $sWhere = $tmp['where'];
                } else {
                    foreach ($aWhere as $k => $v) {
                        $tmp[] = "$k = :s_$k";
                    }
                    // join temp array with AND condition
                    $sWhere = implode(' AND ', $tmp);
                }
                // unset temp var
                unset($tmp);
                // set class sql property
                $this->sSql = "SELECT $sField FROM `$sTable` WHERE $sWhere $sOther;";
            } else {
                $this->sSql = "SELECT $sField FROM `$sTable` $sOther;";  // if no where condition pass by user
            }

            // pdo prepare statement with sql query
            $this->oSTH = $this->prepare($this->sSql);
            // if where condition has valid array number

            if (count($aWhere) > 0 && is_array($aWhere)) {
                $this->_bindPdoNameSpace($aWhere); // bind pdo param
            }

            // use try catch block to get pdo error
            try {
                // check if pdo execute
                if ($this->oSTH->execute()) {
                    // set class property with affected rows
                    $this->iAffectedRows = $this->oSTH->rowCount();
                    // set class property with sql result
                    $this->aResults = $this->oSTH->fetchAll();
                    // close pdo
                    $this->oSTH->closeCursor();
                    // return self object
                    return $this;
                } else {
                    self::error($this->oSTH->errorInfo());  // catch pdo error
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
                switch (gettype($array[$f])) :
                    // is string found then pdo param as string
                    case 'string':
                        $this->oSTH->bindParam(':s' . '_' . "$field", $array[$f], PDO::PARAM_STR);
                        break;
                    // if int found then pdo param set as int
                    case 'integer':
                        $this->oSTH->bindParam(':s' . '_' . "$field", $array[$f], PDO::PARAM_INT);
                        break;
                    // if boolean found then set pdo param as boolean
                    case 'boolean':
                        $this->oSTH->bindParam(':s' . '_' . "$field", $array[$f], PDO::PARAM_BOOL);
                        break;
                endswitch;
            } // end for each here
        } else {

            // bind array data in pdo
            foreach ($array as $f => $v) {
                // check pass data type for appropriate field
                switch (gettype($array[$f])) :
                    // is string found then pdo param as string
                    case 'string':
                        $this->oSTH->bindParam(':s' . '_' . "$f", $array[$f], PDO::PARAM_STR);
                        break;
                    // if int found then pdo param set as int
                    case 'integer':
                        $this->oSTH->bindParam(':s' . '_' . "$f", $array[$f], PDO::PARAM_INT);
                        break;
                    // if boolean found then set pdo param as boolean
                    case 'boolean':
                        $this->oSTH->bindParam(':s' . '_' . "$f", $array[$f], PDO::PARAM_BOOL);
                        break;
                endswitch;
            } // end for each here
        }
    }

    /**
     * Execute PDO Insert.
     *
     * @param string $sTable
     * @param array  $aData
     *
     * @return self|void
     */
    public function insert($sTable, $aData = [])
    {
        // check if table name not empty
        if (!empty($sTable)) {
            // and array data not empty
            if (count($aData) > 0 && is_array($aData)) {
                // get array insert data in temp array
                foreach ($aData as $f => $v) {
                    $tmp[] = ":s_$f";
                }
                // make name space param for pdo insert statement
                $sNameSpaceParam = implode(',', $tmp);
                // unset temp var
                unset($tmp);
                // get insert fields name
                $sFields = implode(',', array_keys($aData));
                // set pdo insert statement in class property
                $this->sSql = "INSERT INTO `$sTable` ($sFields) VALUES ($sNameSpaceParam);";
                // pdo prepare statement
                $this->oSTH = $this->prepare($this->sSql);
                // set class where property with array data
                $this->aData = $aData;
                // bind pdo param
                $this->_bindPdoNameSpace($aData);
                // use try catch block to get pdo error
                try {
                    // execute pdo statement
                    if ($this->oSTH->execute()) {
                        // set class property with last insert id
                        $this->iLastId = $this->lastInsertId();
                        // close pdo
                        $this->oSTH->closeCursor();
                        // return this object
                        return $this;
                    } else {
                        self::error($this->oSTH->errorInfo());
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
     * @param string $sTable         mysql table name
     * @param array  $aData          mysql insert array data
     * @param bool   $safeModeInsert set true if want to use pdo bind param
     *
     * @return self last insert ID
     */
    public function insertBatch($sTable, $aData = [], $safeModeInsert = true)
    {

        // PDO transactions start
        $this->start();
        // check if table name not empty
        if (!empty($sTable)) {
            // and array data not empty
            if (count($aData) > 0 && is_array($aData)) {
                // get array insert data in temp array
                foreach ($aData[0] as $f => $v) {
                    $tmp[] = ":s_$f";
                }
                // make name space param for pdo insert statement
                $sNameSpaceParam = implode(', ', $tmp);
                // unset temp var
                unset($tmp);
                // get insert fields name
                $sFields = implode(', ', array_keys($aData[0]));
                // handle safe mode. If it is set as false means user not using bind param in pdo
                if (!$safeModeInsert) {
                    // set pdo insert statement in class property
                    $this->sSql = "INSERT INTO `$sTable` ($sFields) VALUES ";
                    foreach ($aData as $key => $value) {
                        $this->sSql .= '(' . "'" . implode("', '", array_values($value)) . "'" . '), ';
                    }
                    $this->sSql = rtrim($this->sSql, ', ');
                    // return this object
                    // return $this;
                    // pdo prepare statement
                    $this->oSTH = $this->prepare($this->sSql);
                    // start try catch block
                    try {
                        // execute pdo statement
                        if ($this->oSTH->execute()) {
                            // store all last insert id in array
                            $this->iAllLastId[] = $this->lastInsertId();
                        } else {
                            self::error($this->oSTH->errorInfo());
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
                    $this->oSTH->closeCursor();
                    // return this object
                    return $this;
                }

                // end here safe mode
                // set pdo insert statement in class property
                $this->sSql = "INSERT INTO `$sTable` ($sFields) VALUES ($sNameSpaceParam);";
                // pdo prepare statement
                $this->oSTH = $this->prepare($this->sSql);
                // set class property with array
                $this->aData = $aData;
                // set batch insert flag true
                $this->batch = true;
                // parse batch array data
                foreach ($aData as $key => $value) {
                    // bind pdo param
                    $this->_bindPdoNameSpace($value);

                    try {
                        // execute pdo statement
                        if ($this->oSTH->execute()) {
                            // set class property with last insert id as array
                            $this->iAllLastId[] = $this->lastInsertId();
                        } else {
                            self::error($this->oSTH->errorInfo());
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
                $this->oSTH->closeCursor();
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
     * @param string $sTable
     * @param array  $aData
     * @param array  $aWhere
     * @param string $sOther
     *
     * @return self|void
     */
    public function update($sTable = '', $aData = [], $aWhere = [], $sOther = '')
    {
        // if table name is empty
        if (!empty($sTable)) {
            // check if array data and where array is more then 0
            if (count($aData) > 0 && count($aWhere) > 0) {
                // parse array data and make a temp array
                foreach ($aData as $k => $v) {
                    $tmp[] = "$k = :s_$k";
                }
                // join temp array value with ,
                $sFields = implode(', ', $tmp);
                // delete temp array from memory
                unset($tmp);
                // parse where array and store in temp array
                foreach ($aWhere as $k => $v) {
                    $tmp[] = "$k = :s_$k";
                }

                $this->aData = $aData;
                $this->aWhere = $aWhere;
                // join where array value with AND operator and create where condition
                $sWhere = implode(' AND ', $tmp);
                // unset temp array
                unset($tmp);
                // make sql query to update
                $this->sSql = "UPDATE `$sTable` SET $sFields WHERE $sWhere $sOther;";
                // on PDO prepare statement
                $this->oSTH = $this->prepare($this->sSql);
                // bind pdo param for update statement
                $this->_bindPdoNameSpace($aData);
                // bind pdo param for where clause
                $this->_bindPdoNameSpace($aWhere);
                // try catch block start
                try {
                    // if PDO run
                    if ($this->oSTH->execute()) {
                        // get affected rows
                        $this->iAffectedRows = $this->oSTH->rowCount();
                        // close PDO
                        $this->oSTH->closeCursor();
                        // return self object
                        return $this;
                    } else {
                        self::error($this->oSTH->errorInfo());
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
     * @param string $sTable
     * @param array  $aWhere
     * @param string $sOther
     *
     * @return self|void
     */
    public function delete($sTable, $aWhere = [], $sOther = '')
    {
        // if table name not pass
        if (!empty($sTable)) {
            // check where condition array length
            if (count($aWhere) > 0 && is_array($aWhere)) {
                // make an temp array from where array data
                foreach ($aWhere as $k => $v) {
                    $tmp[] = "$k = :s_$k";
                }
                // join array values with AND Operator
                $sWhere = implode(' AND ', $tmp);
                // delete temp array
                unset($tmp);
                // set DELETE PDO Statement
                $this->sSql = "DELETE FROM `$sTable` WHERE $sWhere $sOther;";
                // Call PDo Prepare Statement
                $this->oSTH = $this->prepare($this->sSql);
                // bind delete where param
                $this->_bindPdoNameSpace($aWhere);
                // set array data
                $this->aData = $aWhere;
                // Use try Catch

                try {
                    if ($this->oSTH->execute()) {
                        // get affected rows
                        $this->iAffectedRows = $this->oSTH->rowCount();
                        // close pdo
                        $this->oSTH->closeCursor();
                        // return this object
                        return $this;
                    } else {
                        self::error($this->oSTH->errorInfo());
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
                return $this->aResults;
                break;
            case 'xml':
                //send the xml header to the browser
                header('Content-Type:text/xml');
                // return xml content
                return $this->helper()->arrayToXml($this->aResults);
                break;
            case 'json':
                // set header as json
                header('Content-type: application/json; charset="utf-8"');
                // return json encoded data
                return json_encode($this->aResults);
                break;
        }
    }

    /**
     * Get Total Number Of Records in Requested Table.
     *
     * @param string $sTable
     * @param string $sWhere
     *
     * @return number
     */
    public function count($sTable = '', $sWhere = '')
    {
        // if table name not pass
        if (!empty($sTable)) {
            if (empty($sWhere)) {
                $this->sSql = "SELECT COUNT(*) AS NUMROWS FROM `$sTable`;"; // create count query
            } else {
                $this->sSql = "SELECT COUNT(*) AS NUMROWS FROM `$sTable` WHERE $sWhere;";  // create count query
            }

            // pdo prepare statement
            $this->oSTH = $this->prepare($this->sSql);

            try {
                if ($this->oSTH->execute()) {
                    // fetch array result
                    $this->aResults = $this->oSTH->fetch();
                    // close pdo
                    $this->oSTH->closeCursor();
                    // return number of count
                    return $this->aResults['NUMROWS'];
                } else {
                    self::error($this->oSTH->errorInfo());
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
     * @param string $sTable
     *
     * @return bool
     */
    public function truncate($sTable = '')
    {
        // if table name not pass
        if (!empty($sTable)) {
            // create count query
            $this->sSql = "TRUNCATE TABLE `$sTable`;";
            // pdo prepare statement
            $this->oSTH = $this->prepare($this->sSql);

            try {
                if ($this->oSTH->execute()) {
                    // close pdo
                    $this->oSTH->closeCursor();
                    // return number of count
                    return true;
                } else {
                    self::error($this->oSTH->errorInfo());
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
     * @param string $sTable
     *
     * @return bool
     */
    public function drop($sTable = '')
    {
        // if table name not pass
        if (!empty($sTable)) {
            // create count query
            $this->sSql = "DROP TABLE `$sTable`;";
            // pdo prepare statement
            $this->oSTH = $this->prepare($this->sSql);

            try {
                if ($this->oSTH->execute()) {
                    // close pdo
                    $this->oSTH->closeCursor();
                    // return number of count
                    return true;
                } else {
                    self::error($this->oSTH->errorInfo());
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
     * @param string $sTable
     *
     * @return array Field Type and Field Name
     */
    public function describe($sTable = '')
    {
        $this->sSql = $sSql = "DESC $sTable;";
        $this->oSTH = $this->prepare($sSql);
        $this->oSTH->execute();
        $aColList = $this->oSTH->fetchAll();

        foreach ($aColList as $key) {
            $aField[] = $key['Field'];
            $aType[] = $key['Type'];
        }

        return array_combine($aField, $aType);
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
        $this->oSTH = $this->prepare($statement, $options);

        return $this;
    }

    /**
     * Execute PDO Query.
     *
     * @param array Bind Param Value
     *
     * @return self|int
     */
    public function execute($aBindWhereParam = [])
    {

        // clean query from white space
        $sSql = trim($this->oSTH->queryString);
        // get operation type
        $operation = explode(' ', $sSql);
        // make first word in uppercase
        $operation[0] = strtoupper($operation[0]);

        if (!empty($aBindWhereParam)) {
            $this->_bindPdoParam($aBindWhereParam);
        }

        // use try catch block to get pdo error
        try {
            // run pdo statement with bind param
            if ($this->oSTH->execute()) {
                // check operation type
                switch ($operation[0]) {
                    case 'SELECT':
                        // get affected rows by select statement
                        $this->iAffectedRows = $this->oSTH->rowCount();
                        // get pdo result array
                        $this->aResults = $this->oSTH->fetchAll();
                        // return PDO instance
                        return $this;
                        break;
                    case 'INSERT':
                        // return last insert id
                        $this->iLastId = $this->lastInsertId();
                        // return PDO instance
                        return $this;
                        break;
                    case 'UPDATE':
                        // get affected rows
                        $this->iAffectedRows = $this->oSTH->rowCount();
                        // return PDO instance
                        return $this;
                        break;
                    case 'DELETE':
                        // get affected rows
                        $this->iAffectedRows = $this->oSTH->rowCount();
                        // return PDO instance
                        return $this;
                        break;
                }
                // close pdo cursor
                $this->oSTH->closeCursor();
            } else {
                self::error($this->oSTH->errorInfo());
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
        self::$oPDO = null;
    }
}
