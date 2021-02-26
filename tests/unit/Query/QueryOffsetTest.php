<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryOffsetTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->offset(10));
    }
    public function testShouldSetWithLimit()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')->limit(10)->offset(40);
        $this->assertEquals("SELECT * FROM `movies` LIMIT 10 OFFSET 40", $q->getSql());

        $p = $r->getProperty('offset');
        $p->setAccessible(true);
        $this->assertEquals(40, $p->getValue($q));
    }
    public function testShouldSetUsingSkipAlias()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')->limit(10)->skip(40);
        $this->assertEquals("SELECT * FROM `movies` LIMIT 10 OFFSET 40", $q->getSql());

        $p = $r->getProperty('offset');
        $p->setAccessible(true);
        $this->assertEquals(40, $p->getValue($q));
    }
    public function testShouldThrowExceptionWithoutLimit()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->from('movies')->offset(10);
        $q->getSql();
    }
    /**
     *  @dataProvider provideInvalidOffsets
     */
    public function testShouldThrowExceptionWithInvalidInput($input)
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        
        $q->offset($input);
    }
    public function provideInvalidOffsets()
    {
        return [
            '-1' => [-1],
            // '9223372036854775807' => [9223372036854775808],
            // 'E91+10' => ['E91+10'],
        ];
    }
}
