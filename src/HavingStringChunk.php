<?php
namespace Dframe\Database;

/*
 * Klasa wspomagająca warunki wyszukiwania
 * new setHaving('`kolumna` = ?', array('test'));
 *
 *
 */

class HaveStringChunk
{
    public $string;
    public $bindWhere;

    function __construct($string, $bindWhere = null)
    {
        $this->string = $string;
        $this->bindWhere = $bindWhere;
    }

    function build()
    {
        $paramName = str_replace('.', '_', $this->string);
        $column = explode(' ', $paramName);

        $params = array();
        if (is_array($this->bindWhere)) {
            $params[":{$column[0]}"] = $this->bindWhere;
            $params = $this->flatter($params);
        }

        return array($this->string, $params);
    }

    // Bug fix Autor Krzysztof Franek
    function flatter($array)
    {
        $result = array();
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
