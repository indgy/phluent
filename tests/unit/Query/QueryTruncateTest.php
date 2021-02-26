<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryTruncateTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->truncate());
    }
    public function testShouldHandleCallingInAnyOrder()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->truncate()->table('movies');
        $this->assertEquals("TRUNCATE TABLE `movies`", $q->getSql());
    }
    public function testShouldThrowExceptionIfPreviouslyCalledSelect()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->select('*')->from('movies')->truncate()->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledDelete()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->delete()->truncate()->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledInsert()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->insert(['title'=>2001])->truncate()->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledUpdate()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->where('title', 2001)->update(['title'=>2010])->truncate()->getSql();
    }
}
