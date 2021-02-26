<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryOrderByTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->orderBy('id'));
    }
    public function testShouldSetOrderByDefaults()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')->orderBy('id');

        $p = $r->getProperty('order_by');
        $p->setAccessible(true);
        $this->assertEquals(['id'=>null], $p->getValue($q));

        $this->assertEquals("SELECT * FROM `movies` ORDER BY `movies`.`id`", $q->getSql());

    }
    public function testShouldSetOrderByDefaultsRepeated()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')
            ->orderBy('id')
            ->orderBy('name');

        $p = $r->getProperty('order_by');
        $p->setAccessible(true);
        $this->assertEquals(['id'=>null,'name'=>null], $p->getValue($q));

        $this->assertEquals("SELECT * FROM `movies` ORDER BY `movies`.`id`,`movies`.`name`", $q->getSql());

    }
    /**
     *  @dataProvider provideSortDirections
     */
    public function testShouldSetOrderByDir($expected, $dir)
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')->orderBy('id', $dir);

        $p = $r->getProperty('order_by');
        $p->setAccessible(true);
        $this->assertEquals(['id'=>$expected], $p->getValue($q));

        $this->assertEquals(trim("SELECT * FROM `movies` ORDER BY `movies`.`id` $expected"), $q->getSql());
    }
    public function provideSortDirections()
    {
        return [
            'asc' => [null, 'asc'],
            'ASC' => [null, 'ASC'],
            'ascending' => [null, 'ascending'],
            'dsc' => ['DESC', 'dsc'],
            'DSC' => ['DESC', 'DSC'],
            'desc' => ['DESC', 'desc'],
            'DESC' => ['DESC', 'DESC'],
            'descending' => ['DESC', 'descending'],
        ];
    }
    public function testShouldSetOrderByRand()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')->orderByRand();

        $p = $r->getProperty('order_by');
        $p->setAccessible(true);
        $this->assertEquals([0=>'RAND()'], $p->getValue($q));

        $this->assertEquals("SELECT * FROM `movies` ORDER BY RAND()", $q->getSql());

    }
    /**
     *  @dataProvider provideInvalidSortDirections
     */
    public function testShouldThrowExceptionOnInvalidOrderByDir($expected, $dir)
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->from('movies')->orderBy('id', $dir);
    }
    public function provideInvalidSortDirections()
    {
        return [
            'backwards' => [null, 'backwards'],
            '9908345' => [null, '9908345'],
        ];
    }
    
    public function testOrderByThrowsExceptionWithInvalidInputType()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->from('movies')->orderBy(new \StdClass());
        
    }
    public function testOrderByThrowsExceptionWithInvalidDirType()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->from('movies')->orderBy(new \StdClass());
        
    }
}
