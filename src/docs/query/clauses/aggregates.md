# Aggregates

The following aggregates are supported, as in SQL you may call more than one in the same query.

## COUNT()
The ``count()`` method generates the SQL to count the number or results in the statement.
```php
$query->from('movies')->where('title', 'like', 'The %')->count();
// SELECT COUNT(*) AS `count` FROM `movies` WHERE `movies`.`title` LIKE ?
```
## AVG()
The ``avg()`` method generates the SQL to return the average value of a column.
```php
$query->from('movies')->where('year', 2020)->avg('rating');
// SELECT AVG(`rating`) AS `avg` FROM `movies` WHERE `movies`.`year` = 2020
```
## MIN()
The ``min()`` method generates the SQL to return the minium value of a column.
```php
$query->from('movies')->where('year', 2020)->min('rating');
// SELECT MIN(`rating`) AS `min` FROM `movies` WHERE `movies`.`year` = 2020
```
## MAX()
The ``max()`` method generates the SQL to return the maxium value of a column.
```php
$query->from('movies')->where('year', 2020)->max('rating');
// SELECT MAX(`rating`) AS `max` FROM `movies` WHERE `movies`.`year` = 2020
```
## SUM()
The ``sum()`` method generates the SQL to return the total of all the values in a column.
```php
$query->from('movies')->where('year', 2020)->sum('attendance');
// SELECT SUM(`attendance`) AS `sum` FROM `movies` WHERE `movies`.`year` = 2020
```