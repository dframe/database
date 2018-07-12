<?php
namespace Dframe\Database\tests;

use PHPUnit\Framework\Constraint\IsType as PHPUnitIsType;
use Dframe\Database\Database;
use Dframe\Database\Pdohelper;
use \PDO;

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

            $config = [
                'logDir' => APP_DIR . 'View/logs/',
                'attributes' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // If you want to Show Class exceptions on Screen, Uncomment below code 
                    PDO::ATTR_EMULATE_PREPARES => true, // Use this setting to force PDO to either always emulate prepared statements (if TRUE), or to try to use native prepared statements (if FALSE). 
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Set default pdo fetch mode as fetch assoc
                 ]
            ];

            $this->db = new Database($dbConfig, $config);

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
