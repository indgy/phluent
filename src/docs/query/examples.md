# Query examples
Query reads very much as a hand coded SQL statement would:
```php
$query = new Query;
$query->select('title, year')->from('movies')->where('title', 'The Lego Movie');
```
Query has a handy shortcut which returns a new Query instance:
```php
query()->select('title, year')->from('movies')->where('title', 'The Lego Movie');
```
The shortcut accepts a table name as it's only parameter:
```php
query('movies')->select('title, year')->where('title', 'The Lego Movie');
```
Most clauses are supported, including JOIN, ORDER BY, GROUP BY and WHERE allowing complex queries 
to be created:
```php
$query->select('title, directors.name')
    ->from('movies')
    ->join('directors')
    ->where('movies.title', 'LIKE', 'The Lego Movie%')
    ->groupBy('directors.name')
    ->orderBy('year');
/*
SELECT `movies`.`title`,`directors`.`name` 
FROM `movies`
JOIN `directors` ON `movies`.`id`=`directors`.`movies_id`
WHERE `movies`.`title` LIKE %s
GROUP BY `directors`.`name`
ORDER BY `movies`.`year`
*/
```
*The eagle eyed may notice that directors should really be a many-many relationship.*

It is possible to group where clauses by nesting them:
```php
// pass a lambda function as the first parameter
// it will receive a new instance of Query as it's first parameter
$query->select('title, year')
    ->from('movies')
    ->where(function($query) {
        $query->where('title', 'like' ,'A %');
        $query->orWhere('title', 'like' ,'The %');
    })
    ->orderBy('year');
```
Multi level nesting is possible:
```php
$query->select('title, year')
    ->from('movies')
    ->where(function($query) {
        $query->where('title', 'like' ,'The %');
        $query->orWhere(function($query) {
            $query->where('title', 'like' ,'A %');
            $query->orWhere('title', 'like' ,'Of %');
        });
    })
    ->orderBy('year');
```

