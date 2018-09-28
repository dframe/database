<?php

namespace Dframe\Database\Tests;

//@Todo
//@Todo

class ConnectionTest extends TestSetUp
{
    public function testEmptyPdoQuery()
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('Query is empty..');

        $test = $this->getConnection()->pdoQuery();
    }
}
