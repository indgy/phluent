# Transactions
The DB class supports two different ways of handling transactions. Manually by calling the ``beginTransaction()`` and ``commit()`` or ``rollback()`` methods. Or automatically by using the ``transaction()`` method which wraps a closure defining the set of operations to carry out. 

## Automatic transaction handling
Using the ``transaction()`` method is the simplest way to handle transactions. If the operations are successful the ``commit()`` method wil be called, if any of the operations fail the ``rollback()`` method will be called and any Exceptions thrown.
```php
$db->transaction(function() {
    $db->table('movies')->where('title', 2001)->update([
        'rating' => 8.5
    ]);
    $db->table('movies')->where('title', 2010)->delete();
});
```
## Manual transaction handling
If you want more fine grained control you may call the transaction methods manually, these are just wrappers around the PDO transaction methods.
```php
$db->beginTransaction();
```
Rollback the transaction if necessary:
```php
$db->rollBack();
```
Or commit to storage:
```php
$db->commit();
```
