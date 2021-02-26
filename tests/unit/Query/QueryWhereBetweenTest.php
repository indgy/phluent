<?php 

namespace Tests\Unit;

use Phluent\Query;

// TODO test where clauses work with JOIN clauses, picking correct tables
// TODO ensure where parameters appear in the correct order, when deeply nested etc.
// TODO ensure parameters appear in the correct order when combined with insert, delete, update

class QueryWhereBetweenTest extends \PHPUnit\Framework\TestCase
{
    public function testWhereBetween()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`date` BETWEEN ? AND ?";

        $q = new Query();
        $q->table('contacts')->whereBetween('date', '2020-01-01', '2020-03-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2020-01-01', '2020-03-01'], $q->getParams());
    }
    public function testWhereBetweenShouldAcceptArray()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`date` BETWEEN ? AND ?";

        $q = new Query();
        $q->table('contacts')->whereBetween('date', ['2020-01-01', '2020-03-01']);

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2020-01-01', '2020-03-01'], $q->getParams());
    }
    public function testWhereNotBetween()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`date` NOT BETWEEN ? AND ?";

        $q = new Query();
        $q->table('contacts')->whereNotBetween('date', '2020-01-01', '2020-03-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2020-01-01', '2020-03-01'], $q->getParams());
    }
    public function testOrWhereBetween()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR `contacts`.`date` BETWEEN ? AND ?";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 1)
            ->orWhereBetween('date', '2020-01-01', '2020-03-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals([1, '2020-01-01', '2020-03-01'], $q->getParams());
    }
    public function testOrWhereNotBetween()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR `contacts`.`date` NOT BETWEEN ? AND ?";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 1)
            ->orWhereNotBetween('date', '2020-01-01', '2020-03-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals([1, '2020-01-01', '2020-03-01'], $q->getParams());
    }
    public function testWhereBetweenShouldThrowExceptionOnSmallArray()
    {
        $this->expectException('InvalidArgumentException');

        $q = new Query();
        $q->table('contacts')->whereBetween('date', ['2020-01-01']);
    }
    public function testWhereBetweenShouldThrowExceptionOnLargeArray()
    {
        $this->expectException('InvalidArgumentException');

        $q = new Query();
        $q->table('contacts')->whereBetween('date', [1,2,3]);
    }
    public function testWhereBetweenShouldThrowExceptionOnAssocArray()
    {
        $this->expectException('InvalidArgumentException');

        $q = new Query();
        $q->table('contacts')->whereBetween('date', ['min'=>'2020-01-01', 'max'=>'2020-01-31']);
    }
    public function testWhereBetweenShouldThrowExceptionOnTooManyParams()
    {
        $this->expectException('InvalidArgumentException');

        $q = new Query();
        $q->table('contacts')->whereBetween('date', ['2020-01-01','2020-02-01'], 'should not be here');
    }
    public function testShouldThrowExceptionWhenProvidedANonBoolean()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->table('contacts')->whereBetween('status', 1, 3, 'NOT A BOOL');
    }
}
