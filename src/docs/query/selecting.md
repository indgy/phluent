# Selecting data

While Query cannot fetch data, it will generate the SQL statements to do so.

## The SELECT clause

Adding a ``select()`` clause will assume you want to start a new query, this resets the internal Query state unless the ``from()`` or ``table()`` methods have been called. If the ``from()`` or ``table()`` methods are called before ``select()`` it is assumed you want to start a new query and the internal state will be reset.
## Specifying columns to select

In the examples below the ``table()``, ``from()`` and ``select()`` clauses are called in different orders, all examples are valid. The ``table()`` and ``from()`` methods can be used interchangeably.

Using a comma separated string:
```php
$query->table('movies')->select('title, year, rating');
// SELECT `movies`.`title`,`movies`.`year`,`movies`.`rating` FROM `movies`;
```
Using an array:
```php
$query->from('movies')->select(['title', 'year', 'rating']);
// SELECT `movies`.`title`,`movies`.`year`,`movies`.`rating` FROM `movies`;
```
Using the 'as' keyword to return column aliases:
```php
$query->select('title, year, rating AS r')->from('movies');
// SELECT `movies`.`title`,`movies`.`year`,`movies`.`rating` AS `r` FROM `movies`;
```

## Raw SELECT parameters
Your database will offer a lot of useful functions, if you need access to them use the ``selectRaw()`` method to pass in references untouched.
```php
$query->table('movies')->select(['title', 'year', 'rating'])->selectRaw('YEAR(`year`) AS raw_year');
// 'SELECT `movies`.`title`,`movies`.`year`,`movies`.`rating`,YEAR(`year`) AS raw_year FROM `movies`
```
Pass in an array of references:
```php
$query->table('movies')->selectRaw(['DISTINCT(COUNT(`title`)) as count_of_title', 'AVG(rating)'])
// SELECT DISTINCT(COUNT(`title`)) as count_of_title, AVG(rating) FROM `movies`
```
