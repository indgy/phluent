# SELECT DISTINCT

Use the ``distinct()`` method to return unique rows:
```php
$query->table('movies')->select(['title', 'year'])->distinct();
// SELECT DISTINCT `movies`.`title`,`movies`.`year` FROM `movies`
```
