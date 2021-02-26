<?php

namespace Tests\Unit;

use Phluent\Query;


class QuerySelectDistinctTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->from('actors')->select('name, genre'));
    }
    public function testShouldSetDistinct()
    {
        $q = new Query();
        $q->select('name, stage_name')->from('actors')->distinct();
        $this->assertEquals("SELECT DISTINCT `actors`.`name`,`actors`.`stage_name` FROM `actors`", $q->getSql());
    }


}
