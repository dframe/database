<?php
namespace Dframe\Database\tests;

use Dframe\Database\Database;
use PHPUnit\Framework\TestCase;

abstract class TestSetUp extends TestCase
{

    public function getConnection()
    {
        try {
            $dbConfig = [
                'dbtype' => 'mysql',
                'host' => 'localhost',
                'dbname' => 'test',
                'username' => 'root',
                'password' => ''
            ];
            $this->db = new Database($dbConfig);

        } catch (\PDOException $e) {
            echo $e->getMessage();
        }

        return $this->db;
    }

}
