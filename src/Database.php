<?php
namespace Dframe\Database;

use PDO;
use Dframe\Database\PdoWrapper;

/**
 * Autor: Sławek Kaleta
 * Nakładka na PDO_Class_Wrapper_master
 */

class Database extends PdoWrapper
{
    private $_setWhere = null;
    private $_setHaving = null;
    private $_setParams = array();
    private $_setOrderBy = null;
    private $_setGroupBy = null;
    private $_setLimit = null;
    protected $config;

    public $WhereChunkKey;
    public $WhereChunkValue;
    public $WhereChunkperator;
    public $addWhereEndParams = array();

    function __construct($dsn = array(), $config = null)
    {
        $this->config = $config;
        if (is_null($this->config)) {
            $this->config = array(
                'logDir' => APP_DIR . 'View/logs/',
                'attributes' => array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
                    //PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,  // Set pdo error mode silent
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // If you want to Show Class exceptions on Screen, Uncomment below code 
                    PDO::ATTR_EMULATE_PREPARES => true, // Use this setting to force PDO to either always emulate prepared statements (if TRUE), or to try to use native prepared statements (if FALSE). 
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Set default pdo fetch mode as fetch assoc
                )
            );
        }

        parent::__construct($dsn, $this->config);
    }

    public function getWhere()
    {
        if (!isset($this->_setWhere) or empty($this->_setWhere)) {
            $this->_setWhere = null;
        }

        return $this->_setWhere;
    }

    public function getHaving()
    {
        if (!isset($this->_setHaving) or empty($this->_setHaving)) {
            $this->_setHaving = null;
        }

        return $this->_setHaving;
    }

    public function getParams()
    {
        $_setParams = $this->_setParams;
        $this->_setParams = array();
        return $_setParams;
    }

    public function getOrderBy()
    {
        return $this->_setOrderBy;
    }

    public function getLimit()
    {
        return $this->_setLimit;
    }

    public function getGroupBy()
    {
        return $this->_setGroupBy;
    }

    public function getQuery()
    {
        $sql = $this->setQuery;
        $sql .= $this->getWhere();
        $sql .= $this->getGroupBy();
        $sql .= $this->getOrderBy();
        $sql .= $this->getHaving();
        $sql .= $this->getLimit();


        $this->setQuery = null;
        $this->_setWhere = null;
        $this->_setHaving = null;
        $this->_setOrderBy = null;
        $this->_setGroupBy = null;
        $this->_setLimit = null;

        return str_replace('  ', ' ', $sql);
    }

    public function addWhereBeginParams($params)
    {
        array_unshift($this->_setParams, $params);
    }

    public function addWhereEndParams($params)
    {
        array_push($this->_setParams, $params);
    }

    public function prepareWhere($whereObject)
    {
        $where = null;
        $params = [];
        if (!empty($whereObject)) {
            $arr = array();
            /*** 
             ** @var $chunk WhereChunk 
             */
            foreach ($whereObject as $chunk) {
                list($wSQL, $wParams) = $chunk->build();
                $arr[] = $wSQL;
                foreach ($wParams as $k => $v) {
                    $params[] = $v;
                }
            }
            $this->_setWhere = " WHERE " . implode(' AND ', $arr);

            if (is_array($this->_setParams) and !empty($this->_setParams)) {
                $this->_setParams = array_merge($this->_setParams, $params);
            } else {
                $this->_setParams = $params;
            }


        } else {
            $this->_setWhere = null;
            //$this->_setParams = array();
        }



        //if (!empty($order))
        //    $this->prepareOrder($order, $sort);
        //

        return $this;

    }

    public function prepareHaving($havingObject)
    {
        $where = null;
        $params = [];
        if (!empty($havingObject)) {
            $arr = array();
            /**
             * 
             *
             * @var $chunk WhereChunk 
             */
            foreach ($havingObject as $chunk) {
                list($wSQL, $wParams) = $chunk->build();
                $arr[] = $wSQL;
                foreach ($wParams as $k => $v) {
                    $params[] = $v;
                }
            }

            $this->_setHaving = " HAVING " . implode(' AND ', $arr);

            if (is_array($this->_setParams) and !empty($this->_setParams)) {
                $this->_setParams = array_merge($this->_setParams, $params);
            } else {
                $this->_setParams = $params;
            }


        } else {
            $this->_setHaving = null;
            //$this->_setParams = array();
        }



        //if (!empty($order))
        //    $this->prepareOrder($order, $sort);
        //

        return $this;

    }

    public function prepareOrder($order = null, $sort = null)
    {

        if ($order == null or $sort == null) {
            $this->_setOrderBy = '';
            return $this;
        }

        if (!in_array($sort, array('ASC', 'DESC'))) {
            $sort = 'DESC';
        }

        $this->_setOrderBy = ' ORDER BY ' . $order . ' ' . $sort;
        return $this;
    }

    public function prepareQuery($query, $params = false)
    {

        if (isset($params) and is_array($params)) {
            $this->prepareParms($params);
        }

        if (!isset($this->setQuery)) {
            $this->setQuery = $query . ' ';
        } else {
            $this->setQuery .= $this->getQuery() . ' ' . $query . ' ';
        }

        return $this;

    }


    public function prepareGroupBy($groupBy)
    {
        $this->_setGroupBy = ' GROUP BY ' . $groupBy;
        return $this;

    }

    /**
     * @param $start int
     * @param $offset int
     */

    public function prepareLimit($limit, $offset)
    {
        if ($offset) {
            $this->_setLimit = ' LIMIT ' . $limit . ', ' . $offset . '';
        } else {
            $this->_setLimit = ' LIMIT ' . $limit . '';
        }

        return $this;
    }


    public function prepareParms($params)
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                array_push($this->_setParams, $value);
            }
        } else {
            array_push($this->_setParams, $params);
        }
    }

}
