<?php

namespace Dframe\Database\Tests;

//@Todo
//@Todo

class ConnectionTest extends TestSetUp
{
    public function testEmptyPdoQuery()
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('SQLSTATE[42000]: Syntax error or access violation: 1065 Query was empty');

        $test = $this->getConnection()->pdoQuery();
    }
}
