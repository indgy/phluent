# Join

The ``join()`` method adds related tables, you must set the table name using ``from()`` or ``table()`` before calling ``join()``. The following shortcut methods are available; ``innerJoin()``, ``outerJoin()``, ``crossJoin()``, ``leftJoin()`` and ``rightJoin()``.

```php
$query->table('movies')->join('actors');
// SELECT * FROM `movies` JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`
```
You may specify the join columns:
```php
$query->table('movies')->join('actors', 'movies.id', 'actors.movies_id');
// SELECT * FROM `movies` JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`
```
You may also specify the operator as necessary, note the position:
```php
$query->table('movies')->join('actors', 'movies.id', '=', 'actors.movies_id');
// or add the operate after the foreign table reference
$query->table('movies')->join('actors', 'movies.id', 'actors.movies_id', '=');
// SELECT * FROM `movies` JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`
```
And you may also specify join type:
```php
$query->table('movies')->join('actors', 'movies.id', '=', 'actors.movies_id', 'inner');
// SELECT * FROM `movies` INNER JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`
```
