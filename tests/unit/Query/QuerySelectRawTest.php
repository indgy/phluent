<?php

namespace Tests\Unit;

use Phluent\Query;


class QuerySelectRawTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->from('actors')->selectRaw('name, genre'));
    }
    public function testShouldSetFromArray()
    {
        $q = new Query();
        $s = [
            'name',
            'stage_name',
            'genre',
        ];

        $q->from('actors');
        $q->selectRaw($s);
        $this->assertEquals("SELECT name,stage_name,genre FROM `actors`", $q->getSql());
    }
    public function testShouldSetFromString()
    {
        $q = new Query();
        $q->from('actors');
        $q->selectRaw('name, stage_name,genre');
        $this->assertEquals("SELECT name, stage_name,genre FROM `actors`", $q->getSql());
    }
    public function testShouldThrowExceptionWithInvalidInput()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->selectRaw(123);
    }
    public function testShouldNotResetStateIfFromSet()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('actors');
        $q->selectRaw('name, stage_name, genre');

        $p = $r->getProperty('from');
        $p->setAccessible(true);
        $this->assertEquals('actors', $p->getValue($q));
    }
    public function testShouldResetStateIfFromNotSet()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->selectRaw('name, stage_name, genre');

        $p = $r->getProperty('from');
        $p->setAccessible(true);
        $this->assertEquals(null, $p->getValue($q));
    }

    public function testShouldResetIfIsComplete()
    {
        $q = new Query;
        $r = new \ReflectionClass($q);
        $p = $r->getProperty('is_complete');
        $p->setAccessible(true);

        $sql = $q->selectRaw('first_name')->from('contacts')->orderBy('first_name')->limit(10)->getSql();
        $this->assertEquals('SELECT first_name FROM `contacts` ORDER BY `contacts`.`first_name` LIMIT 10', $sql);
        $this->assertTrue($p->getValue($q));
        $q->selectRaw('first_name')->from('contacts');
        $this->assertFalse($p->getValue($q));
        $this->assertEquals('SELECT first_name FROM `contacts`', $q->getSql());
    }
}
