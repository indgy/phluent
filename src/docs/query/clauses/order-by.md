# Order By

The ``orderBy()`` method sets the ORDER BY clause.
```php
$query->table('movies')->orderBy('rating');
// SELECT * FROM `movies` ORDER BY `movies`.`rating`
```
You can specify the direction, defaults to ascending:
```php
$query->table('movies')->orderBy('rating', 'desc');
// SELECT * FROM `movies` ORDER BY `movies`.`rating` DESC
```
You can set multiple columns separated by commas:
```php
$query->table('movies')->orderBy('year, rating');
// SELECT * FROM `movies` ORDER BY `movies`.`year`,`movies`.`rating`
```
Or call ``orderBy()`` more than once:
```php
$query->table('movies')->orderBy('year')->orderBy('rating', 'desc');
// SELECT * FROM `movies` ORDER BY `movies`.`year`,`movies`.`rating` DESC
```
## Specifying the direction

You can set multiple columns specifying a direction for each:
```php
$query->table('movies')->orderBy('year, rating DESC');
// SELECT * FROM `movies` ORDER BY `movies`.`year`,`movies`.`rating` DESC
```
You can pass in an array:
```php
$query->table('movies')->orderBy(['year desc', 'rating']);
// SELECT * FROM `movies` ORDER BY `movies`.`year` DESC,`movies`.`rating`
```
Or an associative array where the keys are the column references and the value the direction:
```php
$query->table('movies')->orderBy(['year'=>'desc', 'rating'=>'asc']);
// SELECT * FROM `movies` ORDER BY `movies`.`year` DESC,`movies`.`rating`
```

## Random ordering
Use the ``orderByRand()`` method to order the rows at random:
```php
$query->table('movies')->orderByRand();
// SELECT * FROM `movies` ORDER BY RAND()
```
!!! note
    The orderByRand() method will will probably not work as expected if combined with additional orderBy clauses 
