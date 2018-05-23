<?php
namespace Dframe\Database\tests;

use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;

use Dframe\Database\Database;
use Dframe\Database\Pdohelper;


class QueryTest extends \Dframe\Database\tests\TestSetUp
{
    public function setUp()
    {
        $this->dataSetup = array(
            'data1' => array(
                'username' => 'Jack_' . uniqid()
            ),
            'data2' => array(
                array('username' => 'Eli_' . uniqid()),
                array('username' => 'Mat_' . uniqid()),
                array('username' => 'Andre_' . uniqid())
            )
        );
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
        $dataArray = array('username' => $this->dataSetup['data1']['username']);
        $insert = $this->getConnection()->insert('users', $dataArray)->getLastInsertId();
        $this->assertTrue(is_numeric($insert));
    }

    public function testInsertBatch()
    {
        $insert = $this->getConnection()->insertBatch('users', $this->dataSetup['data2'])->getAllLastInsertId();
        $this->assertCount(3, $insert);
    }

    public function testUpdate(){
        $dataArray = array('phone' => rand(100, 999).'-'. rand(100, 999).'-'. rand(100, 999));
        $where = array('`users`.`user_id`' => rand(1, 1000));
        $update = $this->getConnection()->update('users', $dataArray, $where)->affectedRows();
        $this->assertTrue(is_numeric($update));
    }
}
