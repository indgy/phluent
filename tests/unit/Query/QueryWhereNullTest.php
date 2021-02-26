<?php 

namespace Tests\Unit;

use Phluent\Query;

// TODO test where clauses work with JOIN clauses, picking correct tables
// TODO ensure where parameters appear in the correct order, when deeply nested etc.
// TODO ensure parameters appear in the correct order when combined with insert, delete, update

class QueryWhereNullTest extends \PHPUnit\Framework\TestCase
{
    public function testWhereNull()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`name` IS NULL";

        $q = new Query();
        $q->table('contacts')->whereNull('name');

        $this->assertEquals($expected, $q->getSql());
    }
    public function testWhereNotNull()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`name` IS NOT NULL";

        $q = new Query();
        $q->table('contacts')->whereNotNull('name');

        $this->assertEquals($expected, $q->getSql());
    }
    public function testOrWhereNull()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR `contacts`.`name` IS NULL";

        $q = new Query();
        $q->table('contacts')->where('status', 1)->orWhereNull('name');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals([1], $q->getParams());
    }
    public function testOrWhereNotNull()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR `contacts`.`name` IS NOT NULL";

        $q = new Query();
        $q->table('contacts')->where('status', 1)->orWhereNotNull('name');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals([1], $q->getParams());
    }
    public function testShouldThrowExceptionWhenProvidedANonBoolean()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->table('contacts')->whereNull('status', 'NOT A BOOL');
    }
}
