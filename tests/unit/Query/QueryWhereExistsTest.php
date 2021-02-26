<?php 

namespace Tests\Unit;

use Phluent\Query;

// TODO test where clauses work with JOIN clauses, picking correct tables
// TODO ensure where parameters appear in the correct order, when deeply nested etc.
// TODO ensure parameters appear in the correct order when combined with insert, delete, update

class QueryWhereExistsTest extends \PHPUnit\Framework\TestCase
{
    public function testWhereExists()
    {
        $query = new Query();
        $query->from('movies')
        ->whereExists(function($query) {
                $query->from('tags')
                    ->join('movies__tags')
                    ->whereColumn('movies.id', '=', 'movies__tags.movies_id')
                    ->where('name', 'Must see');
            });

        $this->assertEquals('SELECT * FROM `movies` WHERE EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`=?)', $query->getSql());
        $this->assertEquals(['Must see'], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`='Must see')", $query->getSql($query->getParams()));

    }
    public function testWhereNotExists()
    {
        $query = new Query();
        $query->from('movies')
            ->whereNotExists(function($query) {
                $query->from('tags')
                    ->join('movies__tags')
                    ->whereColumn('movies.id', '=', 'movies__tags.movies_id')
                    ->where('name', 'Must see');
            });

        $this->assertEquals('SELECT * FROM `movies` WHERE NOT EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`=?)', $query->getSql());
        $this->assertEquals(['Must see'], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE NOT EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`='Must see')", $query->getSql($query->getParams()));

    }
    public function testAndWhereExists()
    {
        $query = new Query();
        $query->from('movies')
            ->where('year', 2020)
            ->whereExists(function($query) {
                $query->from('tags')
                    ->join('movies__tags')
                    ->whereColumn('movies.id', '=', 'movies__tags.movies_id')
                    ->where('name', 'Must see');
            });

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`year`=? AND EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`=?)', $query->getSql());
        $this->assertEquals([2020, 'Must see'], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`year`='2020' AND EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`='Must see')", $query->getSql($query->getParams()));

    }
    public function testAndWhereNotExists()
    {
        $query = new Query();
        $query->from('movies')
            ->where('year', 2020)
            ->whereNotExists(function($query) {
                $query->from('tags')
                    ->join('movies__tags')
                    ->whereColumn('movies.id', '=', 'movies__tags.movies_id')
                    ->where('name', 'Must see');
            });

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`year`=? AND NOT EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`=?)', $query->getSql());
        $this->assertEquals([2020, 'Must see'], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`year`='2020' AND NOT EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`='Must see')", $query->getSql($query->getParams()));

    }
    public function testOrWhereExists()
    {
        $query = new Query();
        $query->from('movies')
            ->where('year', 2020)
            ->orWhereExists(function($query) {
                $query->from('tags')
                    ->join('movies__tags')
                    ->whereColumn('movies.id', '=', 'movies__tags.movies_id')
                    ->where('name', 'Must see');
            });

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`year`=? OR EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`=?)', $query->getSql());
        $this->assertEquals([2020, 'Must see'], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`year`='2020' OR EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`='Must see')", $query->getSql($query->getParams()));

    }
    public function testOrWhereNotExists()
    {
        $query = new Query();
        $query->from('movies')
            ->where('year', 2020)
            ->orWhereNotExists(function($query) {
                $query->from('tags')
                    ->join('movies__tags')
                    ->whereColumn('movies.id', '=', 'movies__tags.movies_id')
                    ->where('name', 'Must see');
            });

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`year`=? OR NOT EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`=?)', $query->getSql());
        $this->assertEquals([2020, 'Must see'], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`year`='2020' OR NOT EXISTS (SELECT * FROM `tags` JOIN `movies__tags` ON `tags`.`id`=`movies__tags`.`tags_id` WHERE `movies`.`id`=`movies__tags`.`movies_id` AND `tags`.`name`='Must see')", $query->getSql($query->getParams()));

    }
}
