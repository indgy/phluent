# Using Query

Query is a simple fluent query builder, it has no connection to a database and only aims to produce correct SQL statements while tracking any required parameters. 

Query follows the syntax of SQL wherever possible aiming to offer lots of shorthand methods to make writing SQL statements as easy as possible.

Query has two return methods, [``getSql()``](query.md#getsql) which returns the parameterised SQL statement and [``getParams()``](query.md#getparams) which returns the parameters to be escaped.

!!! note 
    Query aims to support the majority of SQL clauses but is not comprehensive. If you need more control use the [``raw()``](/db#raw-queries) and [``query()``](/db#raw-queries) methods on the DB class.

## Retrieving the SQL statement and parameters

Query only generates SQL tracking the required query parameters. It has two methods which provide access, ``getSql()`` which returns the SQL statement and ``getParams()`` which returns an indexed array of parameters.

#### getSql()
Returns the parameterised SQL statement.
```php
$sql = $query->getSql();
/*
SELECT 
    `movies`.`title`,`movies`.`year`
FROM `movies`
WHERE 
    `movies`.`title`=?
*/
```
#### getParams()
Returns the parameter values as an indexed array.
```php
$params = $query->getParams();
// ['The Lego Movie']
```