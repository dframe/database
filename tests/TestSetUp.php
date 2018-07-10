<?php
namespace Dframe\Database\tests;

use Dframe\Database\Database;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') and class_exists('\PHPUnit_Framework_TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

abstract class TestSetUp extends \PHPUnit\Framework\TestCase
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
