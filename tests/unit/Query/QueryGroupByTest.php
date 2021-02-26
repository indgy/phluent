<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryGroupByTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->groupBy('id'));
    }
    public function testShouldSetGroupBy()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')->groupBy('title');
        $this->assertEquals("SELECT * FROM `movies` GROUP BY `movies`.`title`", $q->getSql());

        $p = $r->getProperty('group_by');
        $p->setAccessible(true);
        $this->assertEquals(['title'=>null], $p->getValue($q));
    }
    public function testShouldSetGroupByMultipleTimes()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('movies')
            ->groupBy('title')
            ->groupBy('year', 'DESC')
            ->groupBy('actors.film');
        $this->assertEquals("SELECT * FROM `movies` GROUP BY `movies`.`title`,`movies`.`year` DESC,`actors`.`film`", $q->getSql());

        $p = $r->getProperty('group_by');
        $p->setAccessible(true);
        
        // TODO this should match orderBY data structure, asc is null
        
        $this->assertEquals(['title'=>null,'year'=>'DESC','actors.film'=>null], $p->getValue($q));
    }
    public function testShouldTHrowExceptionOnInvalidInput()
    {
        $q = new Query();

        $this->expectException('InvalidArgumentException');
        $q->from('movies')->groupBy('12++34');
    }
    //TODO groupBy should accept csv string or array
}
