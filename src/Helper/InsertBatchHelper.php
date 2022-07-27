<?php

/**
 * DframeFramework - Database
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/database/blob/master/README.md (MIT)
 */

namespace Dframe\Database\Helper;

class InsertBatchHelper
{
    /**
     * @var array
     */
    protected $queries = [];

    /**
     * @var array
     */
    protected $queriesBatchInsert = [];

    /**
     * @var array
     */
    protected $value = [];

    /**
     * @var array
     */
    protected $colsForUpdate = [];

    /**
     * @var array
     */
    protected $updateCols = [];

    /**
     * @param string $table
     * @param array  $columns
     * @param array  $updateCols
     */
    public function prepareInsert(string $table, array $columns, array $updateCols): void
    {
        $sql = "INSERT INTO " . $table . " (" . trim(
            implode(
                    ', ',
                    $this->arrayMapAssoc(
                        function ($k, $v) {
                            return ltrim($k, ':');
                        },
                        $columns
                    )
                )
        ) . ")";
        /**
         * Search if query string already exist
         */
        $arrayQueryKey = array_search($sql, $this->queries, true);

        /**
         * If query string not exist add Query Key to for Insert Batch
         */
        if ($arrayQueryKey === false) {
            $this->queries[] = $sql;
            $arrayQueryKey = array_search($sql, $this->queries, true);
        }

        /**
         * Add data to query Insert Bach
         */
        if (!isset($this->queriesBatchInsert[$arrayQueryKey])) {
            $this->queriesBatchInsert[$arrayQueryKey] = [];
        }

        $this->queriesBatchInsert[$arrayQueryKey][] = ['columns' => $columns];
        $this->updateCols[$arrayQueryKey] = $updateCols;
    }

    /**
     * @return array
     */
    public function getQueriesBatchInsert(): array
    {
        $array = [];

        foreach ($this->queries as $key => $sql) {
            if (!isset($array[$key])) {
                $array[$key] = [];
                $array[$key]['sql'] = $sql;
                $array[$key]['updateCols'] = $this->updateCols[$key] ?? [];
            }

            $array[$key]['data'] = [];

            foreach ($this->queriesBatchInsert[$key] as $query) {
                $array[$key]['data'][] = $query['columns'];
            }
        }

        return $array;
    }

    /**
     * @param $callback
     * @param $array
     *
     * @return array
     */
    protected function arrayMapAssoc($callback, $array): array
    {
        $r = [];

        foreach ($array as $key => $value) {
            $r[$key] = $callback($key, $value);
        }

        return $r;
    }

    /**
     * @param string $field
     * @param        $value
     * @param bool   $colsForUpdate
     *
     * @return $this
     */
    public function addField(string $field, $value, $colsForUpdate = false)
    {
        if (!is_null($value)) {
            $this->value[':' . $field] = (is_bool($value) ? (int)$value : $value);

            if ($colsForUpdate === true) {
                $this->colsForUpdate[$field] = $field;
            }
        }

        return $this;
    }

    /**
     * @param string $field
     * @param        $value
     * @param bool   $colsForUpdate
     *
     * @return $this
     */
    public function isCondition(string $field, $value, $colsForUpdate = false)
    {
        if (!is_null($value)) {
            $this->value[':' . $field] = (is_bool($value) ? (int)$value : $value);

            if ($colsForUpdate === true) {
                $this->colsForUpdate[$field] = $field;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        $value = $this->value;
        $this->value = [];

        return $value;
    }

    /**
     * @return array
     */
    public function getColsForUpdate(): array
    {
        $value = $this->colsForUpdate;
        $this->colsForUpdate = [];

        return $value;
    }

    /**
     * @param array $array
     *
     * @return $this
     */
    public function addRequireFields(array $array): self
    {
        foreach ($array as $field => $value) {
            $this->value[':' . $field] = $value;
        }

        return $this;
    }
}
