<?php

/**
 * DframeFramework - Database
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/database/blob/master/README.md (MIT)
 */

namespace Dframe\Database;

use Dframe\Database\Chunk\ChunkInterface;

/**
 * new WhereChunk('kolumna', 'test', 'LIKE');
 * Based on https://github.com/Appsco/component-share/blob/9b29a7579c9bdcf9832b94b05ecebc74d771adf9/src/BWC/Share/Data/Select.php.
 */
class WhereChunk implements ChunkInterface
{
    public $key;

    public $value;

    public $operator;

    /**
     * __construct function.
     *
     * @param string $key
     * @param string $value
     * @param string $operator
     */
    public function __construct($key, $value, $operator = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->operator = $operator;
    }

    /**
     * Build function.
     *
     * @return array
     */
    public function build()
    {
        $params = [];
        if ($this->value !== null) {
            $op = !is_null($this->operator) ? $this->operator : '=';

            $paramName = str_replace('.', '_', $this->key);
            if ($op == 'BETWEEN') {
                $sql = "{$this->key} $op ? AND ?";

                $between = explode('AND', $this->value);

                $params[':dateFrom'] = trim($between[0]);
                $params[':dateTo'] = trim($between[1]);
            } else {
                $sql = "{$this->key} $op ?";                                    // $sql = "{$this->key} $op {$paramName}";
                $params[":{$paramName}"] = $this->value;
            }
        } else {
            $sql = $sql = "{$this->key} IS NULL ";
        }

        return [$sql, $params];
    }
}
