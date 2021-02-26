# Logging queries
DB will log all queries executed after the ``log()`` method is called
```php
$db->log()->table('movies')->delete();
```
To retrieve the log and stop logging call the ``getLog()`` method.
```php
echo $db->getLog();
// DELETE FROM `movies`
```
Logging continues until the ``getLog()`` method is called. To continue call the ``log()`` method again.
```php
$db->log()->table('movies')->delete();
$db->table('directors')->delete();

echo $db->getLog();
// DELETE FROM `movies`
// DELETE FROM `directors`
```
