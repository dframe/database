# Dframe/Database

[![Build Status](https://travis-ci.org/dframe/database.svg?branch=master)](https://travis-ci.org/dframe/database) [![Latest Stable Version](https://poser.pugx.org/dframe/database/v/stable)](https://packagist.org/packages/dframe/database) [![Total Downloads](https://poser.pugx.org/dframe/database/downloads)](https://packagist.org/packages/dframe/database) [![Latest Unstable Version](https://poser.pugx.org/dframe/dframe/v/unstable)](https://packagist.org/packages/dframe/database) [![License](https://poser.pugx.org/dframe/dframe/license)](https://packagist.org/packages/dframe/dframe)

Dframe **[Documentation](https://dframeframework.com/en/page/index)**

### Installation Composer

```sh
$ composer require dframe/database
```


## What's included?
 * [MySQL PDO](https://dframeframework.com/en/docs/database/master/configuration) 
 * [MySQL Query Builder](https://dframeframework.com/en/docs/database/master/query-builder) 
 * [MySQL WHERE Builder](https://dframeframework.com/en/docs/database/master/chunks)
 
Methods
-------------
Description | name
-------- | ---
MySQL query                         |        pdoQuery()
MySQL select query                  |        select()
MySQL insert query                  |        insert()
MySQL insert batch                  |        insertBatch()
MySQL update query                  |        update()
MySQL delete query                  |        delete()
MySQL truncate table                |        truncate()
MySQL drop table                    |        drop()
MySQL describe table                |        describe()
MySQL count records                 |        count()
Show/debug executed query           |        showQuery()
Get last insert id                  |        getLastInsertId()
Get all last insert id              |        getAllLastInsertId()
Get MySQL results                   |        results()
Get MySQL result                    |        result()
Get status of executed query        |        affectedRows()
MySQL begin transactions            |        start()
MySQL commit the transaction        |        end()
MySQL rollback the transaction      |        back()
Debugger PDO Error                  |        setErrorLog()


Init Connection
-------------
```php
<?php 
use Dframe\Database\Database;
use \PDO;

try {

    
    // Debug Config 
    $config = [
        'logDir' => APP_DIR . 'View/logs/',
        'attributes' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
            //PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,  // Set pdo error mode silent
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // If you want to Show Class exceptions on Screen, Uncomment below code 
            PDO::ATTR_EMULATE_PREPARES => true, // Use this setting to force PDO to either always emulate prepared statements (if TRUE), or to try to use native prepared statements (if FALSE). 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Set default pdo fetch mode as fetch assoc
         ]
    ];
    
    $dsn = [
        'host' => DB_HOST,
        'dbname' => DB_DATABASE,
        'dbtype' => 'mysql'
    ];
        
    $db = new Database($dsn, DB_USER, DB_PASS, $config);
    $db->setErrorLog(false); // Debug
    
}catch(\Exception $e) {
    echo 'The connect can not create: ' . $e->getMessage(); 
    exit();
}

```

OR

```php
<?php 
use Dframe\Database\Database;
use \PDO;

try {

    
    // Debug Config 
    $config = [
        'log_dir' => APP_DIR . 'View/logs/',
        'attributes' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
            //PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,  // Set pdo error mode silent
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // If you want to Show Class exceptions on Screen, Uncomment below code 
            PDO::ATTR_EMULATE_PREPARES => true, // Use this setting to force PDO to either always emulate prepared statements (if TRUE), or to try to use native prepared statements (if FALSE). 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Set default pdo fetch mode as fetch assoc
         ]
    ];
    
    $db = new Database('mysql:host='.DB_HOST.';dbname=' . DB_DATABASE . ';port=3306', DB_USER, DB_PASS, $config);
    $db->setErrorLog(false); // Debug
    
}catch(\Exception $e) {
    echo 'The connect can not create: ' . $e->getMessage(); 
    exit();
}

```


Example - pdoQuery
-------------------
**Return first element array;**
```php
$result = $db->pdoQuery('SELECT * FROM table WHERE id = ?', [$id])->result();

```
> **Note:** result() will select all rows in database, so if you want select only 1 row i query connection add LIMIT 1;


----------

**Return all result array query;**
```php
$results = $db->pdoQuery('SELECT * FROM table')->results();
```

**Update;**
```php
$affectedRows = $db->pdoQuery('UPDATE table SET col_one = ?, col_two = ?', [$col_one, $col_two])->affectedRows();
```
> **Note:** affectedRows() will return numbers modified rows;


**Insert;**
```php
 
$getLastInsertId = $db->pdoQuery('INSERT INTO table (col_one, col_two) VALUES (?,?)', [$col_one, $col_two])->getLastInsertId();
```
> **Note:** getLastInsertId() will return insert ID;
> 

----------

WhereChunk
===================

**Return all search result array query;**
```php
$where[] = new Dframe\Database\WhereChunk('col_id', '1'); // col_id = 1
```
WhereStringChunk
===================

**Return search result array query;**
```php
$where[] = new Dframe\Database\WhereStringChunk('col_id > ?', ['1']); // col_id > 1
```

Query builder
===================
```php
$query = $this->baseClass->db->prepareQuery('SELECT * FROM users');
$query->prepareWhere($where);
$query->prepareOrder('col_id', 'DESC');
$results = $this->baseClass->db->pdoQuery($query->getQuery(), $query->getParams())->results();
```

HavingStringChunk
===================
```php
$where[] = new Dframe\Database\HavingStringChunk('col_id > ?', ['1']); // col_id > 1
```

### Original author

neerajsinghsonu/PDO_Class_Wrapper [^neerajsinghsonu/PDO_Class_Wrapper]

  [^neerajsinghsonu/PDO_Class_Wrapper]: [neerajsinghsonu/PDO_Class_Wrapper](https://github.com/neerajsinghsonu/PDO_Class_Wrapper)
