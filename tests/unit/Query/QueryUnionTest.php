<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryUnionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->union(new Query));
    }
    public function testShouldUnionTwoQueries()
    {
        $expected = "(SELECT * FROM `contacts` WHERE `contacts`.`first_name` LIKE ?)\nUNION\n(SELECT * FROM `contacts` WHERE `contacts`.`first_name` LIKE ?)\nUNION\n(SELECT * FROM `contacts` WHERE `contacts`.`first_name` LIKE ?)";

        $a = new Query();
        $a->from('contacts')->where('first_name', 'like', 'a%');
        $b = new Query();
        $b->from('contacts')->where('first_name', 'like', 'b%');
        $c = new Query();
        $c->from('contacts')->where('first_name', 'like', 'c%');

        $a->union($b, $c);

        $this->assertEquals($expected, $a->getSql());
        $this->assertEquals(['a%','b%','c%'], $a->getParams());
    }
    public function testShouldUnionAllTwoQueries()
    {
        $expected = "(SELECT * FROM `contacts` WHERE `contacts`.`first_name` LIKE ?)\nUNION ALL\n(SELECT * FROM `contacts` WHERE `contacts`.`first_name` LIKE ?)\nUNION ALL\n(SELECT * FROM `contacts` WHERE `contacts`.`first_name` LIKE ?)";

        $a = new Query();
        $a->from('contacts')->where('first_name', 'like', 'a%');
        $b = new Query();
        $b->from('contacts')->where('first_name', 'like', 'b%');
        $c = new Query();
        $c->from('contacts')->where('first_name', 'like', 'c%');

        $a->unionAll($b, $c);

        $this->assertEquals($expected, $a->getSql());
        $this->assertEquals(['a%','b%','c%'], $a->getParams());
    }
    public function testShouldThrowExceptionIfArgumentIsNotQueryInstances()
    {
        $this->expectException('InvalidArgumentException');

        $q = new Query();
        $a = new Query();
        $b = new \StdClass();

        $q->union($a, $b);
    }
    public function testShouldThrowExceptionIfCalledTwice()
    {
        $this->expectException('BadMethodCallException');

        $q = new Query();
        $a = new Query();
        $b = new Query();
        $c = new Query();

        $q->union($a, $b)->union($c);
    }
    public function testShouldThrowExceptionIfPreviouslyCalledSelect()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->select('*')->from('movies')->union(new Query)->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledDelete()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->delete()->union(new Query)->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledInsert()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->where('title', 2001)->insert(['title'=>2010])->union(new Query)->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledUpdate()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->where('title', 2001)->update(['title'=>2010])->union(new Query)->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledTruncate()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->truncate()->union(new Query)->getSql();
    }
}
