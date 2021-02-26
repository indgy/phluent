<?php 

namespace Tests\Unit;

use Phluent\Query;

// TODO test where clauses work with JOIN clauses, picking correct tables
// TODO ensure where parameters appear in the correct order, when deeply nested etc.
// TODO ensure parameters appear in the correct order when combined with insert, delete, update

class QueryWhereColumnTest extends \PHPUnit\Framework\TestCase
{
    public function testWhereColumn()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`id`=`emails`.`contact_id`";

        $q = new Query();
        $q->table('contacts')->whereColumn('id', 'emails.contact_id');

        $this->assertEquals($expected, $q->getSql());
    }
    public function testWhereColumnSHouldAcceptOperatorInSignature()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`id`<>`emails`.`contact_id`";

        $q = new Query();
        $q->table('contacts')->whereColumn('id', '<>', 'emails.contact_id');

        $this->assertEquals($expected, $q->getSql());
    }
}
