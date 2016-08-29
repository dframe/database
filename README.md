Dframe/Database
===================


Hey! This library is a modyfy Wrapper PDO  Original author is **neerajsinghsonu/PDO_Class_Wrapper**.  

----------

Methods
-------------
Name | Method
-------- | ---
MySQL query                         |        pdoQuery()
MySQL select query                  |        select ()
MySQL insert query                  |        insert ()
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

try {
	$dbConfig = array(
		'host' => DB_HOST,
		'dbname' => DB_DATABASE,
		'username' => DB_USER,
		'password' => DB_PASS
	);
	$db = new Database($dbConfig);
	$db->setErrorLog(false); // Debug
	
}catch(DBException $e) {
	echo 'The connect can not create: ' . $e->getMessage(); 
	exit();
}

```


Example - pdoQuery
-------------------
**Return first element array;**
```php
$result = $db->pdoQuery('SELECT * FROM table WHERE id = ?', array($id))->result();

```
> **Note:** result() will select all rows in database, so if you want select only 1 row i query connection add LIMIT 1;


----------

**Return all result array query;**
```php
$results = $db->pdoQuery('SELECT * FROM table')->results();
```

**Update;**
```php
$affectedRows = $db->pdoQuery('UPDATE table SET col_one = ?, col_two = ?', array($col_one, $col_two))->affectedRows();
```
> **Note:** affectedRows() will return numbers modified rows;


**Insert;**
```php
 
$getLastInsertId = $db->pdoQuery('INSERT INTO articles_statistic (col_one, col_two) VALUES (?,?)', array($col_one, $col_two))->getLastInsertId();
```
> **Note:** getLastInsertId() will return insert ID;
> 


### Original author

neerajsinghsonu/PDO_Class_Wrapper [^neerajsinghsonu/PDO_Class_Wrapper]

  [^neerajsinghsonu/PDO_Class_Wrapper]: [neerajsinghsonu/PDO_Class_Wrapper](https://github.com/neerajsinghsonu/PDO_Class_Wrapper)
