# Unions
The UNION clause combines the rows from multiple SELECT statements into a single result, removing any duplicate rows. Each SELECT statement can be sorted as well as the final result.

!!! Note
    The column names of each SELECT statement must match.

## Union
The ``union()`` method accepts a number of other Queries as it's arguments:
```php
// Create the initial query
$a = new Query();
$a->from('movies')->where('title', 'like', 'a%');

// Create the query to union with
$z = new Query();
$z->from('movies')->where('title', 'like', 'z%');

// Add the queries as arguments to union()
$a->union($z);
/*
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')
*/
```
You may add as many queries as necessary:
```php
// Create the initial query
$a = new Query();
$a->from('movies')->where('title', 'like', 'a%');

// Create the queries to union with
$x = new Query();
$y = new Query();
$z = new Query();
$x->from('movies')->where('title', 'like', 'x%');
$y->from('movies')->where('title', 'like', 'y%');
$z->from('movies')->where('title', 'like', 'z%');

// Add all queries as arguments to union()
$a->union($x, $y, $z);
/*
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'x%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'y%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')
*/
```
You may specify the sort order of the results of the union:
```php
$a = new Query();
$a->from('movies')->where('title', 'like', 'a%');
$z = new Query();
$z->from('movies')->where('title', 'like', 'z%');
// the orderBy clause works on the UNION results as expected
$a->union($z)->orderBy('year desc');
/*
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')
ORDER BY `movies`.`year` DESC
*/
```
And paginate the results of the union:
```php
$a = new Query();
$a->from('movies')->where('title', 'like', 'a%');
$z = new Query();
$z->from('movies')->where('title', 'like', 'z%');
// the orderBy, limit and offset clauses work on the UNION results as expected
$a->union($z)->orderBy('year desc')->limit(10)->offset(40);
/*
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')
ORDER BY `movies`.`year` DESC
LIMIT 10 OFFSET 40
*/
```
## Union All
Calling the ``unionAll()`` method will return all duplicate rows.
```php
$a = query('movies')->where('title', 'like', 'a%');
$z = query('movies')->where('title', 'like', 'z%');
// the orderBy, limit and offset clauses work on the UNION results as expected
$a->unionAll($z);
/*
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION ALL
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')
*/
```
