# Where

Use the WHERE clauses to filter results, there are various helper methods available including ``whereNot()``, ``orWhere()``, ``orWhereNot()``, ``whereColumn()``

## Method arguments

All where methods have a similar very flexible argument signature, they can be called with strings and some methods accept a callable which is used when nesting where clauses.

Skipping the comparison operator assumes '='
```php
query('movies')->where('title', 'The Lego Movie');
// SELECT * FROM `movies` WHERE `movies`.`title` = 'The Lego Movie'
```
Any of the normal comparison operators may be used including =, <>, !=, >, >=, <, <= or LIKE:
```php
query('movies')->where('title', 'LIKE', 'The Lego Movie%');
// SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'The Lego Movie%'
```
You may call ``where()`` multiple times, they will produce AND where statements:
```php
query('movies')->where('year', 2020)->where('rating', '>', 8);
// SELECT * FROM `movies` WHERE `movies`.`year` = 2020 AND `movies`.`rating` > 8
```
You may nest ``where()`` clauses:
```php
query('movies')->where(function($query) {
    $query->where('year', 2020);
    $query->orWhere('rating', '>', 8);
});
// SELECT * FROM `movies` WHERE (`movies`.`year` = 2020 OR `movies`.`rating` > 8)
```
As deep as necessary:
```php
query('movies')->where(function($query) {
    $query->where('year', 2020);
    $query->whereNot(function($query) {
        $query->where('rating', '<', 2);
        $query->orWhere('rating', '>', 8);
    });
});
/*
SELECT * 
FROM `movies` 
WHERE 
(
    `movies`.`year`='2020' AND NOT
    (
        `movies`.`rating`<'2' OR 
        `movies`.`rating`>'8'
    )
)
```


## Where Between
Use the WHERE BETWEEN clause to check if a column is between two other values. The helper methods available include ``whereNotBetween()``, ``orWhereBetween()`` and ``orWhereNotBetween()``

```php
query('movies')->whereBetween('year', [2010, 2020]);
// SELECT * FROM `movies` WHERE `movies`.`year` BETWEEN 2010 AND 2020
```
```php
query('movies')->whereBetween('year', [2010, 2020])->orWhereBetween('year', [1990, 2000]);
// SELECT * FROM `movies` WHERE `movies`.`year` BETWEEN 2010 AND 2020 OR `movies`.`year` BETWEEN 1990 AND 2000
```



## Where Column
This is not an SQL construct, however we use it in Query to compare two columns to enable the sanity checking of both table/column references.

Skipping the comparison operator assumes '=':
```php
query('movies')->join('directors')->whereColumn('director', 'directors'.'name');
// SELECT * FROM `movies` JOIN `directors` ON `movies`.`id`=`directors`.`movie_id` WHERE `movies`.`director` = `directors`.`name`
```
Any of the normal comparison operators may be used including =, <>, !=, >, >=, <, <= or LIKE:
```php
query('movies')->join('directors')->whereColumn('director', '<>', 'directors'.'name');
// SELECT * FROM `movies` JOIN `directors` ON `movies`.`id`=`directors`.`movie_id` WHERE `movies`.`director` <> `directors`.`name`
```

## Where Exists
The WHERE EXISTS clause will return results from the query if the ``whereExists()`` subquery returns one or more rows.

```php
query('movies')
    ->where('year', 2020)
    ->whereExists(function($query) {
        $query->from('tags')
            ->join('movies__tags')
            ->whereColumn('movies.id', '=', 'movies__tags.movies_id')
            ->where('name', 'Must see');
    });
/*
-- A slightly convoluted example
SELECT * FROM `movies` WHERE `movies`.`year` = 2020 AND EXISTS (
    SELECT *
    FROM `tags`
    JOIN `movies__tags` ON `movies`.`id`=`movies__tags`.`movies_id`
    WHERE
        `movies`.`id`=`movies__tags`.`movies_id` AND
        `tags`.`name`='Must see'
);
```

## Where In
Use the WHERE IN clause to check if a column contains any value in a range of values. The shortcut methods available include ``whereNotIn()``, ``orWhereIn()`` and ``orWhereNotIn()``

```php
query('movies')->whereIn('year', [2010, 2012, 2014]);
// SELECT * FROM `movies` WHERE `movies`.`year` IN (2010, 2012, 2014)
```
The ``whereIn()`` method also supports nested queries:
```php
query('movies')->whereIn('id', function($query) {
    $query->select('movie_id')
        ->from('best_of')
        ->whereIn('genre', ['sci-fi','comedy']);
});
// SELECT * FROM `movies` WHERE `movies`.`id` IN (SELECT `best_of`.`movie_id` FROM `best_of` WHERE `best_of`.`genre` IN ('sci-fi', 'comedy'));
```


## Where Null
Use the WHERE NULL clause to check if a column value is null. The shortcut methods available include ``whereNotNull()``, ``orWhereNull()`` and ``orWhereNotNull()``

```php
query('movies')->whereNull('rating');
// SELECT * FROM `movies` WHERE `movies`.`rating` IS NULL
```
Using the helper methods: 
```php
query('movies')->whereNotNull('rating');
// SELECT * FROM `movies` WHERE `movies`.`rating` IS NOT NULL
```
You may chain methods together: 
```php
query('movies')->whereNotNull('rating')->orWhereNull('reviews');
// SELECT * FROM `movies` WHERE `movies`.`rating` IS NOT NULL OR `movies`.`reviews` IS NULL
```