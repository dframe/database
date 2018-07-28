<?php
// include pdo helper class to use common methods
require_once '../src/Helper/PDOHelper.php';
// include pdo class wrapper
require_once '../src/class.pdowrapper.php';

// database connection setings
$dbConfig = ["host" => "localhost", "dbname" => 'sampledb', "username" => 'root', "password" => ''];
// get instance of PDO Wrapper object
$db = new PdoWrapper($dbConfig);

// get instance of PDO Helper object
$helper = new PDOHelper();

// set error log mode true to show error on screen or false to log in log file
$db->setErrorLog(true);


// Example -1
$selectFields = ['customerNumber', 'customerName', 'contactLastName', 'contactFirstName', 'phone'];
// set where condition
$whereConditions = ['customerNumber' => 103];
// select with where and bind param use select method
$q = $db->select('customers', $selectFields, $whereConditions)->showQuery()->results();
// print array result
PDOHelper::PA($q);

// Example -2
$whereConditions = ['lastname =' => 'bow', 'or jobtitle =' => 'Sales Rep', 'and isactive =' => 1, 'and officecode =' => 1];
$data = $db->select('employees', ['employeenumber', 'lastname', 'jobtitle'], $whereConditions)->showQuery()->results();
// print array result
PDOHelper::PA($q);


// Example -3
$whereConditions = ['lastname =' => 'bow', 'or jobtitle =' => 'Sales Rep', 'and isactive =' => 1, 'and officecode =' => 1];
// select with where and bind param use select method
$q = $db->select('employees', ['employeeNumber', 'lastName', 'firstName'], $whereConditions)->showQuery()->results();
// print array result
PDOHelper::PA($q);


// Example -4
$selectFields = ['customerNumber', 'customerName', 'contactLastName', 'contactFirstName', 'phone'];
// set where condition
$whereConditions = ['customerNumber' => 103, 'contactLastName' => 'Schmitt'];
$array_data = [
    'customerNumber =' => 103,
    'and contactLastName =' => 'Schmitt',
    'and age =' => 30,
    'or contactLastName =' => 'Schmitt',
    'and age <' => 45,
    'or age >' => 65
];
// select with where and bind param use select method
$q = $db->select('customers', $selectFields, $array_data);
// print array result
PDOHelper::PA($q);


// Example -5
$selectFields = ['customerNumber', 'customerName', 'contactLastName', 'contactFirstName', 'phone'];
// set where condition
$whereConditions = [];
// select with where and bind param use select method
$q = $db->select('customers', $selectFields, $whereConditions, 'LIMIT 10')->showQuery()->results();
// print array result
PDOHelper::PA($q);


// Example -6
$selectFields = ['customerNumber', 'customerName', 'contactLastName', 'contactFirstName', 'phone'];
// set where condition
$whereConditions = [];
// select with where and bind param use select method
$q = $db->select('customers', $selectFields, $whereConditions, 'ORDER BY customerNumber DESC LIMIT 5')->showQuery()->results();
// print array result
PA($q);
