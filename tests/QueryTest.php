<?php

namespace Dframe\Database\tests;

class QueryTest extends TestSetUp
{
    public function setUp()
    {
        $this->dataSetup = [
            'data1' => [
                'username' => 'Jack_' . uniqid(),
            ],
            'data2' => [
                ['username' => 'Eli_' . uniqid()],
                ['username' => 'Mat_' . uniqid()],
                ['username' => 'Andre_' . uniqid()],
            ],
        ];
    }

//     public function testSelectUser()
//     {
//         $select = $this->getConnection()->select('users')->results();
//         $this->assertArrayHasKey('user_id', $select[0]);

//         // Collects all rows
//         $select = $this->getConnection()->select('users', '*')->result();
//         $this->assertArrayHasKey('user_id', $select);
//     }

    public function testInsert()
    {
        $dataArray = ['username' => $this->dataSetup['data1']['username']];
        $insert = $this->getConnection()->insert('users', $dataArray)->getLastInsertId();
        $this->assertTrue(is_numeric($insert));
    }

    public function testInsertBatch()
    {
        $insert = $this->getConnection()->insertBatch('users', $this->dataSetup['data2'])->getAllLastInsertId();
        $this->assertCount(3, $insert);
    }

    public function testUpdate()
    {
        $dataArray = ['phone' => '123-123-123'];
        $aWhere = ['id' => 23];
        $update = $this->getConnection()->update('users', $dataArray, $aWhere)->affectedRows();
        $this->assertTrue(is_numeric($update));
    }
}
