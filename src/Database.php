<?php

/**
 * DframeFramework - Database
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/database/blob/master/README.md (MIT)
 */

namespace Dframe\Database;

use \PDO;
use Dframe\Database\PdoWrapper;

class Database extends PdoWrapper
{
    private $_setWhere = null;
    private $_setHaving = null;
    private $_setParams = [];
    private $_setOrderBy = null;
    private $_setGroupBy = null;
    private $_setLimit = null;
    protected $config;

    public $WhereChunkKey;
    public $WhereChunkValue;
    public $WhereChunkperator;
    public $addWhereEndParams = [];

    /**
     * __construct function
     *
     * @param array $dsn
     * @param array $config
     */
    function __construct($dsn = [], $config = null)
    {
        $this->config = $config;
        if (is_null($this->config)) {
            $this->config = [
                'logDir' => APP_DIR . 'View/logs/',
                'attributes' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,  // Set pdo error mode silent
                    //PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // If you want to Show Class exceptions on Screen, Uncomment below code 
                    PDO::ATTR_EMULATE_PREPARES => false, // Use this setting to force PDO to either always emulate prepared statements (if TRUE), or to try to use native prepared statements (if FALSE). 
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Set default pdo fetch mode as fetch assoc
                ]
            ];
        }

        parent::__construct($dsn, $this->config);
    }

    /**
     * GetWhere function
     *
     * @return void
     */
    public function getWhere()
    {
        if (!isset($this->_setWhere) or empty($this->_setWhere)) {
            $this->_setWhere = null;
        }

        return $this->_setWhere;
    }

    /**
     * GetHaving function
     *
     * @return void
     */
    public function getHaving()
    {
        if (!isset($this->_setHaving) or empty($this->_setHaving)) {
            $this->_setHaving = null;
        }

        return $this->_setHaving;
    }

    /**
     * GetParams function
     *
     * @return void
     */
    public function getParams()
    {
        $_setParams = $this->_setParams;
        $this->_setParams = [];
        return $_setParams;
    }

    /**
     * GetOrderBy function
     *
     * @return void
     */
    public function getOrderBy()
    {
        return $this->_setOrderBy;
    }

    /**
     * GetLimit function
     *
     * @return void
     */
    public function getLimit()
    {
        return $this->_setLimit;
    }

    /**
     * GetGroupBy function
     *
     * @return void
     */
    public function getGroupBy()
    {
        return $this->_setGroupBy;
    }

    /**
     * GetQuery function
     *
     * @return void
     */
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

    /**
     * AddWhereBeginParams function
     *
     * @param array $params
     */
    public function addWhereBeginParams($params)
    {
        array_unshift($this->_setParams, $params);
    }

    /**
     * addWhereEndParams function
     *
     * @param array $params
     */
    public function addWhereEndParams($params)
    {
        array_push($this->_setParams, $params);
    }

    /**
     * PrepareWhere function
     *
     * @param Dframe\WhereChunk|Dframe\WhereChunkArray $whereObject
     * @return void
     */
    public function prepareWhere($whereObject)
    {
        $where = null;
        $params = [];
        if (!empty($whereObject)) {
            $arr = [];
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
            //$this->_setParams = [];
        }

        //if (!empty($order))
        //    $this->prepareOrder($order, $sort);
        //

        return $this;

    }

    /**
     * PrepareHaving function
     *
     * @param Dframe\WhereChunk $havingObject
     * @return void
     */
    public function prepareHaving($havingObject)
    {
        $where = null;
        $params = [];
        if (!empty($havingObject)) {
            $arr = [];
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
            //$this->_setParams = [];
        }

        //if (!empty($order))
        //    $this->prepareOrder($order, $sort);
        //

        return $this;

    }

    /**
     * prepareOrder function
     *
     * @param string $order
     * @param string $sort
     * @return void
     */
    public function prepareOrder($order = null, $sort = null)
    {

        if ($order == null or $sort == null) {
            $this->_setOrderBy = '';
            return $this;
        }

        if (!in_array($sort, ['ASC', 'DESC'])) {
            $sort = 'DESC';
        }

        $this->_setOrderBy = ' ORDER BY ' . $order . ' ' . $sort;
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $query
     * @param array $params
     * @return void
     */
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

    /**
     * PrepareGroupBy function
     *
     * @param string $groupBy
     * @return void
     */
    public function prepareGroupBy($groupBy)
    {
        $this->_setGroupBy = ' GROUP BY ' . $groupBy;
        return $this;

    }

    /**
     * PrepareLimit function
     *
     * @param int $limit
     * @param int $offset
     * @return void
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

    /**
     * PrepareParms function
     *
     * @param array|string $params
     * @return void
     */
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
