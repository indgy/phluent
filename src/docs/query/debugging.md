# Debugging queries

To get a more accurate representation of the SQL statement pass the parameters into the ``getSql()`` method: 
```php
$sql = $query->getSql($query->getParams());
/*
SELECT 
    `movies`.`title`,`movies`.`year`
FROM `movies`
WHERE 
    `movies`.`title`='The Lego Movie'
*/
```
You may also call the ``debug()`` method which will also output the current SQL statement but also accepts a boolean flag which if true will halt execution after outputting the statement:
```php
$sql = $query->select('*')->from('movies')->where('title', 'The Lego Movie')->debug(true);
/*
Query debug:
SELECT * FROM `movies` WHERE `movies`.`title`='The Lego Movie'
// execution stops as true was passed to debug()
*/

```
!!! note
    Due to the way parameters are escaped the returned statement may not be the actual query that is performed in the database, for further investigate you will need to check the database logs.

!!! Danger
    This feature is provided for debugging only. Do not use the generated SQL to execute queries using untrusted data! The passed parameters are not escaped and may be used to attack the database.
