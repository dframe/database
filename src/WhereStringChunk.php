<?php

/**
 * DframeFramework - Database
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/database/blob/master/README.md (MIT)
 */


namespace Dframe\Database;

/**
 * Klasa wspomagająca warunki wyszukiwania
 * new WhereStringChunk('`kolumna` LIKE ?', ['test']);
 * 
 */

class WhereStringChunk
{
    public $string;
    public $bindWhere;

    /**
     * __construct function
     *
     * @param string $string
     * @param array $bindWhere
     */
    function __construct($string, $bindWhere = null)
    {
        $this->string = $string;
        $this->bindWhere = $bindWhere;
    }

    /**
     * Build function
     *
     * @return array
     */
    function build()
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
     * Flatter function
     *
     * @param array $array
     * @return void
     */
    function flatter($array)
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
