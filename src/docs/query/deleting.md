# Deleting

The ``delete()`` method generates SQL to remove rows from a table, it can be combined with any of the ``where()`` methods, the ``orderBy()`` and ``limit()`` methods.

!!! Important
    The ``delete()`` method should be called last.

## Delete queries
#### Deleting all rows in a table
```php
$query->table('movies')->delete();
// DELETE FROM `movies`
```
#### Deleting a subset of rows in a table
```php
$query->table('movies')->where('year', '<', 1984)->delete();
// DELETE FROM `movies` WHERE `year` < 1984
```
#### Limiting the number of deleted rows
```php
$query->table('movies')->where('year', '<', 1984)->limit(10)->delete();
// DELETE FROM `movies` WHERE `movies`.`year` < 1984 LIMIT 10
```
#### Ordering the rows for deletion
```php
$query->table('movies')->where('year', '<', 1984)->orderBy('year', 'DESC')->limit(10)->delete();
// DELETE FROM `movies` WHERE `movies`.`year` < 1984 ORDER BY `movies`.`year` DESC LIMIT 10
```
## Truncate queries
The truncate() method generates SQL to reset a table removing all rows and resetting any increment columns back to zero.

!!! Important
    The ``truncate()`` method should be called last.

#### Truncating a table
```php
$query->table('movies')->truncate();
// TRUNCATE TABLE `movies`
```

