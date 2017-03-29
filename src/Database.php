<?php
namespace Dframe\Database;
/**
* Autor: Sławek Kaleta
* Nakładka na PDO_Class_Wrapper_master
*/
include_once(dirname( __FILE__ ) . '/PDO_Class_Wrapper/src/PdoWrapper.php');

class Database extends \PdoWrapper
{
    private $setWhere = null;
    private $setParams = array();
    private $setOrderBy = null;

    public $WhereChunkKey;
    public $WhereChunkValue;
    public $WhereChunkperator;
    public $addWhereEndParams = array();

    function __construct($dsn = array()){
        parent::__construct($dsn);
    }

    public function getWhere(){
        if(!isset($this->setWhere) OR empty($this->setWhere))
            $this->setWhere = null;
        
        return $this->setWhere;
    }

    public function getParams(){
        return $this->setParams;
    }

    public function getOrderBy(){
        return $this->setOrderBy;
    }

    public function getLimit(){
        return $this->setLimit;
    }

    public function getGroupBy(){
    	return $this->setGroupBy;
    }

    public function getQuery(){
        $sql = $this->setQuery;
        $sql .= $this->getWhere();
        $sql .= $this->getGroupBy();
        $sql .= $this->getOrderBy();
        $sql .= $this->getLimit();
        return str_replace('  ', ' ', $sql);
    }

    public function addWhereBeginParams($params){
        array_unshift($this->setParams, $params);
    }

    public function addWhereEndParams($params){
        array_push($this->setParams, $params);
    }

    public function prepareWhere($whereObject, $order = null, $sort = null){
        $where = null;
        $params = null;
        if (!empty($whereObject)) {
            $arr = array();
            /** @var $chunk WhereChunk */
            foreach ($whereObject as $chunk) {
                list($wSQL, $wParams) = $chunk->build();
                $arr[] = $wSQL;
                foreach ($wParams as $k=>$v) {
                    $params[] = $v;
                }
            }
            $this->setWhere = " WHERE ".implode(' AND ', $arr);
            $this->setParams = $params;
        }else{
            $this->setWhere = null;
            $this->setParams = array();
        }



        if(!empty($order))
            $this->prepareOrder($order, $sort);


        return $this;

    }

    public function prepareOrder($order = null, $sort = null){
        if(!in_array($sort, array('ASC', 'DESC'))) 
            $sort = 'DESC';
    
        $this->setOrderBy = ' ORDER BY '.$order.' '.$sort;
        return $this;
    }

    public function prepareQuery($query){
        $this->setQuery = $query;
        return $this;

    }


    public function prepareGroupBy($groupBy){
        $this->setGroupBy = ' GROUP BY '.$groupBy;
        return $this;

    }

     /**
     * @param $start int
     * @param $offset int
     */

   public function prepareLimit($limit, $offset) {
        if($offset) {
            $from = ($limit - 1) * $offset;
            $this->setLimit = ' LIMIT '.$from.', '.$offset.'';
        }else {
            $this->setLimit = ' LIMIT '.$limit.'';
        }

        return $this;
    }

}
