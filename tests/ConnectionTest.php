<?php
namespace Dframe\Database\tests;

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
