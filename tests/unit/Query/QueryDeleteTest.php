<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryDeleteTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->delete());
    }
    public function testShouldSetDeleteFlag()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->table('movies')->delete();
        $this->assertEquals("DELETE FROM `movies`", $q->getSql());

        $p = $r->getProperty('delete');
        $p->setAccessible(true);
        $this->assertEquals(true, $p->getValue($q));
    }
    public function testShouldHandleCallingInAnyOrder()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->delete()->table('movies');
        $this->assertEquals("DELETE FROM `movies`", $q->getSql());

        $p = $r->getProperty('delete');
        $p->setAccessible(true);
        $this->assertEquals(true, $p->getValue($q));
    }
    public function testShouldAcceptWhereParams()
    {
        $q = new Query();
        $q->table('movies')->where('rating', '<', 3)->delete();
        $this->assertEquals("DELETE FROM `movies` WHERE `movies`.`rating`<?", $q->getSql());
        $this->assertEquals(['3'], $q->getParams());

        $q = new Query();
        $q->where('rating', '<', 3)->table('movies')->delete();
        $this->assertEquals("DELETE FROM `movies` WHERE `movies`.`rating`<?", $q->getSql());
        $this->assertEquals(['3'], $q->getParams());

        $q = new Query();
        $q->delete()->table('movies')->where('rating', '<', 3);
        $this->assertEquals("DELETE FROM `movies` WHERE `movies`.`rating`<?", $q->getSql());
        $this->assertEquals(['3'], $q->getParams());
    }
    public function testShouldThrowExceptionIfPreviouslyCalledSelect()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->select('*')->from('movies')->delete()->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledInsert()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->insert(['title'=>2001])->delete()->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledUpdate()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->where('title', 2001)->update(['title'=>2010])->delete()->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledTruncate()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->truncate()->delete()->getSql();
    }
    // public function testShouldTHrowExceptionOnInvalidInput()
    // {
    //     $q = new Query();
    //
    //     $this->expectException('InvalidArgumentException');
    //     $q->from('movies')->groupBy('12++34');
    // }
}
