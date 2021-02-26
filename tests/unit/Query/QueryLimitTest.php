<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->limit(10));
    }
    /**
     *  @dataProvider provideLimits
     */
    public function testShouldSetLimit($limit)
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')->limit($limit);
        $this->assertEquals("SELECT * FROM `movies` LIMIT $limit", $q->getSql());

        $p = $r->getProperty('limit');
        $p->setAccessible(true);
        $this->assertEquals($limit, $p->getValue($q));
    }
    /**
     *  @dataProvider provideLimits
     */
    public function testShouldSetLimitUsingTakeAlias($limit)
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')->take($limit);
        $this->assertEquals("SELECT * FROM `movies` LIMIT $limit", $q->getSql());

        $p = $r->getProperty('limit');
        $p->setAccessible(true);
        $this->assertEquals($limit, $p->getValue($q));
    }
    public function provideLimits()
    {
        return [
            '10' => [10],
            '15' => [15],
            '25' => [25],
            '50' => [50],
            '100' => [100],
            '1000' => [1000],
        ];
    }
    /**
     *  @dataProvider provideInvalidLimits
     */
    public function testShouldThrowExceptionWithInvalidInput($input)
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        
        $q->limit($input);
    }
    public function provideInvalidLimits()
    {
        return [
            '-1' => [-1],
            // '9223372036854775807' => [9223372036854775808],
            // 'E91+10' => ['E91+10'],
        ];
    }
}
