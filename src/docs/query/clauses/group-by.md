# Group By

The argument signature of the ``groupBy()`` method is the same as the ``orderBy()`` method.

The ``groupBy()`` method sets the GROUP BY clause:
```php
$query->table('movies')->groupBy('year');
// SELECT * FROM `movies` GROUP BY `movies`.`year`
```
You can group on multiple columns:
```php
$query->table('movies')->groupBy('year, rating');
// SELECT * FROM `movies` GROUP BY `movies`.`year`
```
You can pass in an array of columns:
```php
$query->table('movies')->groupBy(['year', 'rating']);
// SELECT * FROM `movies` GROUP BY `movies`.`year`
```
## Specifying the direction

Passing a string:
```php
$query->table('movies')->groupBy('year, rating desc');
// SELECT * FROM `movies` GROUP BY `movies`.`year`,`movies`.`rating` DESC
```
Passing an indexed array:
```php
$query->table('movies')->groupBy(['year', 'rating desc']);
// SELECT * FROM `movies` GROUP BY `movies`.`year`,`movies`.`rating` DESC
```
Using an associative array:
```php
$query->table('movies')->groupBy(['year'=>'asc', 'rating'=>'desc']);
// SELECT * FROM `movies` GROUP BY `movies`.`year`,`movies`.`rating` DESC
```