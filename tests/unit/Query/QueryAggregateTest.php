<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryAggregateTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldSetAvg()
    {
        $q = new Query();

        $q->table('movies')->avg('rating');
        $this->assertEquals("SELECT AVG(`movies`.`rating`) AS `avg` FROM `movies`", $q->getSql());
    }
    public function testShouldSetAvgWithAlias()
    {
        $q = new Query();

        $q->table('movies')->avg('rating', 'avg_rating');
        $this->assertEquals("SELECT AVG(`movies`.`rating`) AS `avg_rating` FROM `movies`", $q->getSql());
    }
    public function testShouldSetCount()
    {
        $q = new Query();

        $q->table('movies')->count('rating');
        $this->assertEquals("SELECT COUNT(`movies`.`rating`) AS `count` FROM `movies`", $q->getSql());
    }
    public function testShouldSetCountWithAlias()
    {
        $q = new Query();

        $q->table('movies')->count('rating', 'count_rating');
        $this->assertEquals("SELECT COUNT(`movies`.`rating`) AS `count_rating` FROM `movies`", $q->getSql());
    }
    public function testShouldSetMin()
    {
        $q = new Query();

        $q->table('movies')->min('rating');
        $this->assertEquals("SELECT MIN(`movies`.`rating`) AS `min` FROM `movies`", $q->getSql());
    }
    public function testShouldSetMinWithAlias()
    {
        $q = new Query();

        $q->table('movies')->min('rating', 'min_rating');
        $this->assertEquals("SELECT MIN(`movies`.`rating`) AS `min_rating` FROM `movies`", $q->getSql());
    }
    public function testShouldSetMax()
    {
        $q = new Query();

        $q->table('movies')->max('rating');
        $this->assertEquals("SELECT MAX(`movies`.`rating`) AS `max` FROM `movies`", $q->getSql());
    }
    public function testShouldSetMaxWithAlias()
    {
        $q = new Query();

        $q->table('movies')->max('rating', 'max_rating');
        $this->assertEquals("SELECT MAX(`movies`.`rating`) AS `max_rating` FROM `movies`", $q->getSql());
    }
    public function testShouldSetSum()
    {
        $q = new Query();

        $q->table('movies')->sum('rating');
        $this->assertEquals("SELECT SUM(`movies`.`rating`) AS `sum` FROM `movies`", $q->getSql());
    }
    public function testShouldSetSumWithAlias()
    {
        $q = new Query();

        $q->table('movies')->sum('rating', 'sum_rating');
        $this->assertEquals("SELECT SUM(`movies`.`rating`) AS `sum_rating` FROM `movies`", $q->getSql());
    }
}
