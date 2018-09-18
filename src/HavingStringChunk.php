<?php

/**
 * DframeFramework - Database
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/database/blob/master/README.md (MIT)
 */

namespace Dframe\Database;

/**
 * Klasa wspomagająca warunki wyszukiwania
 * new setHaving('`kolumna` = ?', [test']);.
 */
class HavingStringChunk
{
    /**
     * String variable.
     *
     * @var string
     */
    public $string;

    /**
     * BindWhere variable.
     *
     * @var array
     */
    public $bindWhere;

    /**
     * HavingStringChunk constructor.
     *
     * @param      $string
     * @param null $bindWhere
     */
    public function __construct($string, $bindWhere = null)
    {
        $this->string = $string;
        $this->bindWhere = $bindWhere;
    }

    /**
     * @return array
     */
    public function build()
    {
        $paramName = str_replace('.', '_', $this->string);
        $column = explode(' ', $paramName);

        $params = [];
        if (is_array($this->bindWhere)) {
            $params[":{$column[0]}"] = $this->bindWhere;
            $params = $this->flatter($params);
        }

        return [$this->string, $params];
    }

    /**
     * Flatter function.
     *
     * @param array $array
     *
     * @return void
     */
    public function flatter($array)
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                $result = array_merge($result, $this->flatter($item));
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }
}
