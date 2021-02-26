# Phluent

A dependency free lightweight fluent SQL query builder using PDO. 

There are two classes:

* [Query](query.md) generates SQL and tracks parameter values using fluent methods.
* [DB](db.md) performs SQL queries using PDO.

<!-- * The [Mapper](mapper.md), [Entity](entity.md) and [Collection](collection.md) classes are planned for release at a later date. They follow the data [Mapper](https://martinfowler.com/eaaCatalog/dataMapper.html) principle rather than [Active Record](https://www.martinfowler.com/eaaCatalog/activeRecord.html) so the Entity is not aware of the database and cannot interact with it, instead the storage and retrieval of Entities is handled by the Mapper. -->


*An extension handling relationships using the Data Mapper & Entity paradigm is planned for release at a later date.*

!!! note
    Phluent uses PDO under the hood but has only been tested against MariaDB, MySQL. PostgreSQL and SQLite may be supported but are not currently tested.
