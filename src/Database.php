<?php

/**
 * DframeFramework - Database
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/database/blob/master/README.md (MIT)
 */

namespace Dframe\Database;

use Exception;
use PDO;

/**
 * Class Database
 *
 * @package Dframe\Database
 */
class Database extends PdoWrapper
{
    /**
     * @var null|array
     */
    protected $config;

    /**
     * @var null|string
     */
    private $setWhere = null;

    /**
     * @var null|string
     */
    private $setHaving = null;

    /**
     * @var array
     */
    private $setParams = [];

    /**
     * @var null|string
     */
    private $setOrderBy = null;

    /**
     * @var null|string
     */
    private $setGroupBy = null;

    /**
     * @var null|string
     */
    private $setLimit = null;

    /**
     * @var null|string
     */
    private $setQuery;

    /**
     * __construct function.
     *
     * @param array $dsn
     * @param string $username
     * @param string $password
     * @param array $config
     */
    public function __construct($dsn, $username, $password, $config = null)
    {
        $this->config = $config;
        if (is_null($this->config)) {
            $this->config = [
                'logDir' => APP_DIR . 'View/logs/',
                'options' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,  // Set pdo error mode silent
                    //PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // If you want to Show Class exceptions on Screen, Uncomment below code
                    PDO::ATTR_EMULATE_PREPARES => false, // Use this setting to force PDO to either always emulate prepared statements (if TRUE), or to try to use native prepared statements (if FALSE).
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Set default pdo fetch mode as fetch assoc
                ],
            ];
        }

        parent::__construct($dsn, $username, $password, $this->config);
    }

    /**
     * GetParams function.
     *
     * @return array
     */
    public function getParams()
    {
        $setParams = $this->setParams;
        $this->setParams = [];

        return $setParams;
    }

    /**
     * AddWhereBeginParams function.
     *
     * @param array $params
     */
    public function addWhereBeginParams($params)
    {
        array_unshift($this->setParams, $params);
    }

    /**
     * addWhereEndParams function.
     *
     * @param array $params
     */
    public function addWhereEndParams($params)
    {
        array_push($this->setParams, $params);
    }

    /**
     * PrepareWhere function.
     *
     * @param array $whereObject
     *
     * @return Database
     */
    public function prepareWhere($whereObject)
    {
        $where = null;
        $params = [];
        if (!empty($whereObject)) {
            $arr = [];

            /** @var \Dframe\Database\Chunk\ChunkInterface $chunk */
            foreach ($whereObject as $chunk) {
                list($wSQL, $wParams) = $chunk->build();
                $arr[] = $wSQL;
                foreach ($wParams as $k => $v) {
                    $params[] = $v;
                }
            }
            $this->setWhere = ' WHERE ' . implode(' AND ', $arr);

            if (is_array($this->setParams) and !empty($this->setParams)) {
                $this->setParams = array_merge($this->setParams, $params);
            } else {
                $this->setParams = $params;
            }
        } else {
            $this->setWhere = null;
            //$this->setParams = [];
        }

        //if (!empty($order))
        //    $this->prepareOrder($order, $sort);
        //

        return $this;
    }

    /**
     * PrepareHaving function.
     *
     * @param \Dframe\Database\WhereChunk[] $havingObject
     *
     * @return Database
     */
    public function prepareHaving($havingObject)
    {
        $where = null;
        $params = [];
        if (!empty($havingObject)) {
            $arr = [];

            /** @var \Dframe\Database\Chunk\ChunkInterface $chunk */
            foreach ($havingObject as $chunk) {
                list($wSQL, $wParams) = $chunk->build();
                $arr[] = $wSQL;
                foreach ($wParams as $k => $v) {
                    $params[] = $v;
                }
            }

            $this->setHaving = ' HAVING ' . implode(' AND ', $arr);

            if (is_array($this->setParams) and !empty($this->setParams)) {
                $this->setParams = array_merge($this->setParams, $params);
            } else {
                $this->setParams = $params;
            }
        } else {
            $this->setHaving = null;
            //$this->setParams = [];
        }

        //if (!empty($order))
        //    $this->prepareOrder($order, $sort);
        //

        return $this;
    }

    /**
     * prepareOrder function.
     *
     * @param string $order
     * @param string $sort
     *
     * @return Database
     */
    public function prepareOrder($order = null, $sort = null)
    {
        if ($order == null or $sort == null) {
            $this->setOrderBy = '';

            return $this;
        }

        if (!in_array($sort, ['ASC', 'DESC'])) {
            $sort = 'DESC';
        }

        $this->setOrderBy = ' ORDER BY ' . $order . ' ' . $sort;

        return $this;
    }

    /**
     * Undocumented function.
     *
     * @param string     $query
     * @param bool|array $params
     *
     * @return Database
     */
    public function prepareQuery($query, $params = false)
    {
        if (is_array($params)) {
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
     * PrepareParms function.
     *
     * @param array|string $params
     *
     * @return void
     */
    public function prepareParms($params)
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                array_push($this->setParams, $value);
            }
        } else {
            array_push($this->setParams, $params);
        }
    }

    /**
     * GetQuery function.
     *
     * @return string
     */
    public function getQuery()
    {
        $sql = $this->setQuery;
        $sql .= $this->getWhere();
        $sql .= $this->getGroupBy();
        $sql .= $this->getHaving();
        $sql .= $this->getOrderBy();
        $sql .= $this->getLimit();
        
        $this->setQuery = null;
        $this->setWhere = null;
        $this->setGroupBy = null;
        $this->setHaving = null;
        $this->setOrderBy = null;
        $this->setLimit = null;
        return str_replace('  ', ' ', $sql);
    }

    /**
     * GetWhere function.
     *
     * @return null|string
     */
    public function getWhere()
    {
        if (!isset($this->setWhere) or empty($this->setWhere)) {
            $this->setWhere = null;
        }

        return $this->setWhere;
    }

    /**
     * GetGroupBy function.
     *
     * @return null|string
     */
    public function getGroupBy()
    {
        return $this->setGroupBy;
    }

    /**
     * GetOrderBy function.
     *
     * @return null|string
     */
    public function getOrderBy()
    {
        return $this->setOrderBy;
    }

    /**
     * GetHaving function.
     *
     * @return null|string
     */
    public function getHaving()
    {
        if (!isset($this->setHaving) or empty($this->setHaving)) {
            $this->setHaving = null;
        }

        return $this->setHaving;
    }

    /**
     * GetLimit function.
     *
     * @return null|string
     */
    public function getLimit()
    {
        return $this->setLimit;
    }

    /**
     * PrepareGroupBy function.
     *
     * @param string $groupBy
     *
     * @return Database
     */
    public function prepareGroupBy($groupBy)
    {
        $this->setGroupBy = ' GROUP BY ' . $groupBy;

        return $this;
    }

    /**
     * PrepareLimit function.
     *
     * @param int      $limit
     * @param int|null $offset
     *
     * @return Database
     */
    public function prepareLimit($limit, $offset = null)
    {
        if ($offset) {
            $this->setLimit = ' LIMIT ' . $offset . ', ' . $limit . '';
        } else {
            $this->setLimit = ' LIMIT ' . $limit . '';
        }

        return $this;
    }

    /**
     * @param $query
     *
     * @return void
     */
    protected function setQuery($query)
    {
        $this->setQuery = $query;
    }

    /**
     * @param $params
     *
     * @return void
     */
    protected function setParams($params)
    {
        $this->setParams = $params;
    }

    /**
     * @param string     $sql
     * @param array      $values
     * @param null|array $updateCols
     *
     * @throws \Exception
     */
    public function prepareBatchInsert(string $sql, array $values, ?array $updateCols = null): bool
    {
        $rowPlaces = [];

        foreach ($values as $value) {
            $count = count($value) - 1;
            $rowPlaces[] = '(' . str_repeat('?,', $count) . '?)';
        }

        // (optional) setup the ON DUPLICATE column names
        $colsToUpdate = [];

        if (!is_null($updateCols)) {
            foreach ($updateCols as $curCol) {
                $colsToUpdate[] = $curCol . " = VALUES($curCol)";
            }
        }

        $allPlaces = implode(', ', $rowPlaces);

        if (!empty($updateCols)) {
            $sql .= ' VALUES ' . $allPlaces . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $colsToUpdate);
        } else {
            $sql .= ' VALUES ' . $allPlaces;
        }

        $dataToInsert = [];

        foreach ($values as $val) {
            foreach ($val as $v) {
                $dataToInsert[] = $v;
            }
        }

        $this->setQuery($sql);
        $this->setParams($dataToInsert);
    }
}
