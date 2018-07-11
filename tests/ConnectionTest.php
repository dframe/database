<?php
namespace Dframe\Database\tests;

use PHPUnit\Framework\Constraint\IsType as PHPUnitIsType;
use Dframe\Database\Database;
use Dframe\Database\Pdohelper;

//@Todo
//@Todo

class ConnectionTest extends \PHPUnit\Framework\TestCase
{

    private $db = null;

    public function getConnection()
    {

        try {
            $dbConfig = [
                'dbtype' => 'mysql',
                'host' => 'localhost',
                'dbname' => 'test',
                'username' => 'root',
                'password' => '',
            ];
            $this->db = new Database($dbConfig);

        } catch (\PDOException $e) {
            echo $e->getMessage();
        }

        return $this->db;
    }

    public function testEmptyPdoQuery()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SQLSTATE[42000]: Syntax error or access violation: 1065 Query was empty');

        $test = $this->getConnection()->pdoQuery();
    }

}
