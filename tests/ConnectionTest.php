<?php
namespace Dframe\Database\tests;

use PHPUnit\Framework\Constraint\IsType as PHPUnitIsType;
use Dframe\Database\Database;
use Dframe\Database\Pdohelper;
use \PDO;

//@Todo
//@Todo

class ConnectionTest extends TestSetUp
{
    
    public function testEmptyPdoQuery()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SQLSTATE[42000]: Syntax error or access violation: 1065 Query was empty');

        $test = $this->getConnection()->pdoQuery();
    }

}
