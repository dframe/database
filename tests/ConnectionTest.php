<?php
use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;

use Dframe\Database\Database;
use Dframe\Database\Pdohelper;
use \PDO;


//@Todo
//@Todo

class ConnectionTest extends PHPUnit_Framework_TestCase
{

    protected $backupGlobals = false;
    private $conn = null;

    public function getConnection()
    {
        if ($this->conn === null) {
            try {
                $dbConfig = array(
                    'dbtype' => 'mysql',
                    'host' => '',
                    'dbname' => 'test',
                    'username' => 'root',
                    'password' => '',
                );
                $this->db = new Database($dbConfig);

            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        return $this->db;
    }

    public function testEmptyPdoQuery()
    {
        try {
            $test = $this->getConnection()->pdoQuery();
        } catch(Exception $e) {
            $this->assertEquals($e->getMessage(), 'SQLSTATE[42000]: Syntax error or access violation: 1065 Query was empty');
        }

    }

}
