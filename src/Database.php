<?php
namespace Dframe\Database;
/**
* Autor: Sławek Kaleta
* Nakładka na PDO_Class_Wrapper_master
*/
include_once(dirname( __FILE__ ) . '/PdoClassWrapper/src/PdoWrapper.php');

class Database extends \PdoWrapper
{
    private $setWhere = null;
    private $setHaving = null;
    private $setParams = array();
    private $setOrderBy = null;
    private $setGroupBy = null;
    private $setLimit = null;
    protected $config;

    public $WhereChunkKey;
    public $WhereChunkValue;
    public $WhereChunkperator;
    public $addWhereEndParams = array();

    function __construct($dsn = array(), $config = null){
        $this->config = $config;
        if(is_null($this->config))
            $this->config = array(
                'logDir' => appDir.'../app/View/logs/',
            );

        parent::__construct($dsn, $this->config);
    }

    public function getWhere(){
        if(!isset($this->setWhere) OR empty($this->setWhere))
            $this->setWhere = null;
        
        return $this->setWhere;
    }

    public function getHaving(){
        if(!isset($this->setHaving) OR empty($this->setHaving))
            $this->setHaving = null;
        
        return $this->setHaving;
    }

    public function getParams(){
        $setParams = $this->setParams;
        $this->setParams = array();
        return $setParams;
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
        $sql .= $this->getHaving();
        $sql .= $this->getLimit();


        $this->setQuery = null;
        $this->setWhere  = null;
        $this->setHaving = null;
        $this->setOrderBy = null;
        $this->setGroupBy  = null;
        $this->setLimit  = null;

        return str_replace('  ', ' ', $sql);
    }

    public function addWhereBeginParams($params){
        array_unshift($this->setParams, $params);
    }

    public function addWhereEndParams($params){
        array_push($this->setParams, $params);
    }

    public function prepareWhere($whereObject){
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

            if(is_array($this->setParams) AND !empty($this->setParams))
                $this->setParams = array_merge($this->setParams, $params);
            else
                $this->setParams = $params;


        }else{
            $this->setWhere = null;
            //$this->setParams = array();
        }



        //if(!empty($order))
        //    $this->prepareOrder($order, $sort);
        //

        return $this;

    }

    public function prepareHaving($havingObject){
        $where = null;
        $params = null;
        if (!empty($havingObject)) {
            $arr = array();
            /** @var $chunk WhereChunk */
            foreach ($havingObject as $chunk) {
                list($wSQL, $wParams) = $chunk->build();
                $arr[] = $wSQL;
                foreach ($wParams as $k=>$v) {
                    $params[] = $v;
                }
            }

            $this->setHaving = " HAVING ".implode(' AND ', $arr);

            if(is_array($this->setParams) AND !empty($this->setParams))
                $this->setParams = array_merge($this->setParams, $params);
            else
                $this->setParams = $params;


        }else{
            $this->setHaving = null;
            //$this->setParams = array();
        }



        //if(!empty($order))
        //    $this->prepareOrder($order, $sort);
        //

        return $this;

    }

    public function prepareOrder($order = null, $sort = null){

        if($order == null OR $sort == null){
            $this->setOrderBy = '';
            return $this;
        }

        if(!in_array($sort, array('ASC', 'DESC'))) 
            $sort = 'DESC';
    
        $this->setOrderBy = ' ORDER BY '.$order.' '.$sort;
        return $this;
    }

    public function prepareQuery($query, $params = false){

        if(isset($params) AND is_array($params)){
            $this->prepareParms($params);
        }

        if(!isset($this->setQuery))
            $this->setQuery = $query.' ';
        else
            $this->setQuery .= $this->getQuery().' '.$query.' ';
        
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
        if($offset)
            $this->setLimit = ' LIMIT '.$limit.', '.$offset.'';
        else
            $this->setLimit = ' LIMIT '.$limit.'';

        return $this;
    }


    public function prepareParms($params){
        if(is_array($params)){
            foreach ($params as $key => $value) {
                array_push($this->setParams, $value);
            }
        }else
            array_push($this->setParams, $params);
    }

}
