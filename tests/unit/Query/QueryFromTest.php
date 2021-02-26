<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryFromTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->from('movies'));
    }
    public function testShouldSetFrom()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies');
        $this->assertEquals("SELECT * FROM `movies`", $q->getSql());

        $p = $r->getProperty('from');
        $p->setAccessible(true);
        $this->assertEquals('movies', $p->getValue($q));
    }
    public function testShouldSetFromUsingTableMethod()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->table('movies');
        $this->assertEquals("SELECT * FROM `movies`", $q->getSql());

        $p = $r->getProperty('from');
        $p->setAccessible(true);
        $this->assertEquals('movies', $p->getValue($q));
    }
    public function testShouldThrowExceptionWithoutTableName()
    {
        $this->expectException('InvalidArgumentException');

        $q = new Query();
        $q->select('name')->getSql();
    }
    /**
     *  @dataProvider provideInvalidTableNames
     */
    public function testShouldNotSetInvalidTableName($table)
    {
        $this->expectException('InvalidArgumentException');

        $q = new Query();
        $q->table($table);
    }
    public function provideInvalidTableNames()
    {
        return [
            'Contains invalid chars' => ['muppets+?"in.manhatten"'],
        ];
    }
    public function testShouldResetIfIsComplete()
    {
        $q = new Query;
        $r = new \ReflectionClass($q);
        $p = $r->getProperty('is_complete');
        $p->setAccessible(true);

        $sql = $q->from('contacts')->orderBy('first_name')->limit(10)->getSql();
        $this->assertEquals('SELECT * FROM `contacts` ORDER BY `contacts`.`first_name` LIMIT 10', $sql);
        $this->assertTrue($p->getValue($q));
        $q->from('contacts');
        $this->assertFalse($p->getValue($q));
        $this->assertEquals('SELECT * FROM `contacts`', $q->getSql());
    }
}
