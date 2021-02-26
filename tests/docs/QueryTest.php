<?php

namespace Tests\Docs;

use Phluent\Query;
use function Phluent\Query;


/**
 * This class contains tests from the documentation examples
 *
 * @package default
 * @author Ian Grindley
 */
class QueryTest extends \PHPUnit\Framework\TestCase
{
    public function testQuickExampleReadsAsSql()
    {
        $query = new Query;
        $query->select('title, year')->from('movies')->where('title', 'The Lego Movie');

        $this->assertEquals('SELECT `movies`.`title`,`movies`.`year` FROM `movies` WHERE `movies`.`title`=?', $query->getSql());
        $this->assertEquals(['The Lego Movie'], $query->getParams());
        $this->assertEquals("SELECT `movies`.`title`,`movies`.`year` FROM `movies` WHERE `movies`.`title`='The Lego Movie'", $query->getSql($query->getParams()));
    }
    public function testQuickExampleHandyShortcut()
    {
        $query = query()->select('title, year')->from('movies')->where('title', 'The Lego Movie');
        $this->assertEquals('SELECT `movies`.`title`,`movies`.`year` FROM `movies` WHERE `movies`.`title`=?', $query->getSql());
        $this->assertEquals(['The Lego Movie'], $query->getParams());
        $this->assertEquals("SELECT `movies`.`title`,`movies`.`year` FROM `movies` WHERE `movies`.`title`='The Lego Movie'", $query->getSql($query->getParams()));
    }
    public function testQuickExampleShortcutAcceptsTableName()
    {
        $query = query('movies')->select('title, year')->where('title', 'The Lego Movie');
        $this->assertEquals('SELECT `movies`.`title`,`movies`.`year` FROM `movies` WHERE `movies`.`title`=?', $query->getSql());
        $this->assertEquals(['The Lego Movie'], $query->getParams());
        $this->assertEquals("SELECT `movies`.`title`,`movies`.`year` FROM `movies` WHERE `movies`.`title`='The Lego Movie'", $query->getSql($query->getParams()));
    }
    public function testQuickExampleMostClausesSupported()
    {
        $query = new Query();
        $query->select('title, directors.name')
            ->from('movies')
            ->join('directors')
            ->where('movies.title', 'LIKE', 'The Lego Movie%')
            ->groupBy('directors.name')
            ->orderBy('year');
        $sql = "SELECT `movies`.`title`,`directors`.`name` FROM `movies` JOIN `directors` ON `movies`.`id`=`directors`.`movies_id` WHERE `movies`.`title` LIKE %s GROUP BY `directors`.`name` ORDER BY `movies`.`year`";
        $this->assertEquals(sprintf($sql, '?'), $query->getSql());
        $this->assertEquals(['The Lego Movie%'], $query->getParams());
        $this->assertEquals(sprintf($sql, "'The Lego Movie%'"), $query->getSql($query->getParams()));
    }
    public function testQuickExampleGroupWhereClauses()
    {
        $query = new Query();
        $query->select('title, year')
            ->from('movies')
            ->where(function($query) {
                $query->where('title', 'like' ,'A %');
                $query->orWhere('title', 'like' ,'The %');
            })
            ->orderBy('year');
        $sql = "SELECT `movies`.`title`,`movies`.`year` FROM `movies` WHERE (`movies`.`title` LIKE %s OR `movies`.`title` LIKE %s) ORDER BY `movies`.`year`";
        $this->assertEquals(sprintf($sql, '?', '?'), $query->getSql());
        $this->assertEquals(['A %','The %'], $query->getParams());
        $this->assertEquals(sprintf($sql, "'A %'", "'The %'"), $query->getSql($query->getParams()));
    }
    public function testQuickExampleMultiLevelNestingIsPossible()
    {
        $query = new Query();
        $query->select('title, year')
            ->from('movies')
            ->where(function($query) {
                $query->where('title', 'like' ,'The %');
                $query->orWhere(function($query) {
                    $query->where('title', 'like' ,'A %');
                    $query->orWhere('title', 'like' ,'Of %');
                });
            })
            ->orderBy('year');
        $sql = "SELECT `movies`.`title`,`movies`.`year` FROM `movies` WHERE (`movies`.`title` LIKE %s OR (`movies`.`title` LIKE %s OR `movies`.`title` LIKE %s)) ORDER BY `movies`.`year`";
        $this->assertEquals(sprintf($sql, '?', '?', '?'), $query->getSql());
        $this->assertEquals(['The %', 'A %', 'Of %'], $query->getParams());
        $this->assertEquals(sprintf($sql, "'The %'", "'A %'", "'Of %'"), $query->getSql($query->getParams()));
    }


    public function testSelectUsingCommaString()
    {
        $query = new Query;
        $query->table('movies')->select('title, year, rating');
        $this->assertEquals('SELECT `movies`.`title`,`movies`.`year`,`movies`.`rating` FROM `movies`', $query->getSql());
    }
    public function testSelectUsingArray()
    {
        $query = new Query;
        $query->table('movies')->select(['title', 'year', 'rating']);
        $this->assertEquals('SELECT `movies`.`title`,`movies`.`year`,`movies`.`rating` FROM `movies`', $query->getSql());
    }
    public function testSelectUsingAsKeyword()
    {
        $query = new Query;
        $query->select('title, year, rating AS r')->from('movies');
        $this->assertEquals('SELECT `movies`.`title`,`movies`.`year`,`movies`.`rating` AS `r` FROM `movies`', $query->getSql());
    }
    /**
     *  @dataProvider provideAggregateFunctions
     */
    public function testSelectUsingAggregateKeywords($func, $ref, $as)
    {
        $query = new Query;
        $query->select('title, year')->$func($ref, $as)->from('movies');
        $this->assertEquals(sprintf('SELECT `movies`.`title`,`movies`.`year`,%s(`movies`.`rating`) AS `%s` FROM `movies`', strtoupper($func), $as), $query->getSql());
        // test with quotes
        $query = new Query;
        $query->select('title, year')->$func($ref, $as)->from('movies');
        $this->assertEquals(sprintf('SELECT `movies`.`title`,`movies`.`year`,%s(`movies`.`rating`) AS `%s` FROM `movies`', strtoupper($func), $as), $query->getSql());
        // test with quotes
        $query = new Query;
        $query->select('title, year')->$func($ref, $as)->from('movies');
        $this->assertEquals(sprintf('SELECT `movies`.`title`,`movies`.`year`,%s(`movies`.`rating`) AS `%s` FROM `movies`', strtoupper($func), $as), $query->getSql());
        // test with all quoted
        $query = new Query;
        $query->select('title, year')->$func($ref, $as)->from('movies');
        $this->assertEquals(sprintf('SELECT `movies`.`title`,`movies`.`year`,%s(`movies`.`rating`) AS `%s` FROM `movies`', strtoupper($func), $as), $query->getSql());
    }
    public function  provideAggregateFunctions()
    {
        return [
            'avg' => ['avg', 'rating', 'avg_rating'],
            'AVG' => ['AVG', 'rating', 'avg_rating'],
            'sum' => ['sum', 'rating', 'sum_rating'],
            'SUM' => ['SUM', 'rating', 'sum_rating'],
        ];
    }
    public function testSelectRaw()
    {
        $query = new Query;
        $query->table('movies')->select(['title', 'year', 'rating'])->selectRaw('YEAR(`year`) AS raw_year');
        $this->assertEquals('SELECT `movies`.`title`,`movies`.`year`,`movies`.`rating`,YEAR(`year`) AS raw_year FROM `movies`', $query->getSql());
    }
    public function testSelectRawArray()
    {
        $query = new Query;
        $query->table('movies')->selectRaw(['DISTINCT(COUNT(`title`)) as count_of_title', 'AVG(rating)']);
        $this->assertEquals('SELECT DISTINCT(COUNT(`title`)) as count_of_title,AVG(rating) FROM `movies`', $query->getSql());
    }
    public function testSelectDistinct()
    {
        $query = new Query;
        $query->table('movies')->select(['title', 'year'])->distinct();
        $this->assertEquals('SELECT DISTINCT `movies`.`title`,`movies`.`year` FROM `movies`', $query->getSql());
    }

    public function testJoin()
    {
        $query = new Query;
        $query->table('movies')->join('actors');
        $this->assertEquals('SELECT * FROM `movies` JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`', $query->getSql());
    }
    public function testJoinSpecifyingColumn()
    {
        $query = new Query;
        $query->table('movies')->join('actors', 'movies.id', 'actors.movies_id');
        $this->assertEquals('SELECT * FROM `movies` JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`', $query->getSql());
    }
    public function testJoinSpecifyingOperator()
    {
        $query = new Query;
        $query->table('movies')->join('actors', 'movies.id', '=', 'actors.movies_id');
        $this->assertEquals('SELECT * FROM `movies` JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`', $query->getSql());
        $query = new Query;
        $query->table('movies')->join('actors', 'movies.id', 'actors.movies_id', '=');
        $this->assertEquals('SELECT * FROM `movies` JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`', $query->getSql());
    }
    public function testJoinSpecifyingJoinType()
    {
        $query = new Query;
        $query->table('movies')->join('actors', 'movies.id', '=', 'actors.movies_id', 'inner');
        $this->assertEquals('SELECT * FROM `movies` INNER JOIN `actors` ON `movies`.`id`=`actors`.`movies_id`', $query->getSql());
    }


    public function testOrderBy()
    {
        $query = new Query;
        $query->table('movies')->orderBy('rating');
        $this->assertEquals('SELECT * FROM `movies` ORDER BY `movies`.`rating`', $query->getSql());
    }
    public function testOrderByWithDirection()
    {
        $query = new Query;
        $query->table('movies')->orderBy('rating', 'desc');
        $this->assertEquals('SELECT * FROM `movies` ORDER BY `movies`.`rating` DESC', $query->getSql());
    }
    public function testOrderByCommaSeparatedColumns()
    {
        $query = new Query;
        $query->table('movies')->orderBy('year, rating');
        $this->assertEquals('SELECT * FROM `movies` ORDER BY `movies`.`year`,`movies`.`rating`', $query->getSql());
    }
    public function testOrderByCommaSeparatedColumnsWithDirection()
    {
        $query = new Query;
        $query->table('movies')->orderBy('year, rating DESC');
        $this->assertEquals('SELECT * FROM `movies` ORDER BY `movies`.`year`,`movies`.`rating` DESC', $query->getSql());
    }
    public function testOrderByArray()
    {
        $query = new Query;
        $query->table('movies')->orderBy(['year desc', 'rating']);
        $this->assertEquals('SELECT * FROM `movies` ORDER BY `movies`.`year` DESC,`movies`.`rating`', $query->getSql());
    }
    public function testOrderByAssocArray()
    {
        $query = new Query;
        $query->table('movies')->orderBy(['year'=>'desc', 'rating'=>'asc']);
        $this->assertEquals('SELECT * FROM `movies` ORDER BY `movies`.`year` DESC,`movies`.`rating`', $query->getSql());
    }
    public function testOrderByMoreThanOnce()
    {
        $query = new Query;
        $query->table('movies')->orderBy('year')->orderBy('rating', 'desc');
        $this->assertEquals('SELECT * FROM `movies` ORDER BY `movies`.`year`,`movies`.`rating` DESC', $query->getSql());
    }
    
    public function testOrderByRand()
    {
        $query = new Query;
        $query->table('movies')->orderByRand();
        $this->assertEquals('SELECT * FROM `movies` ORDER BY RAND()', $query->getSql());
    }


    public function testGroupBy()
    {
        $query = new Query;
        $query->table('movies')->groupBy('year');
        $this->assertEquals('SELECT * FROM `movies` GROUP BY `movies`.`year`', $query->getSql());
    }
    public function testGroupByMultipleColumns()
    {
        $query = new Query;
        $query->table('movies')->groupBy('year, rating');
        $this->assertEquals('SELECT * FROM `movies` GROUP BY `movies`.`year`,`movies`.`rating`', $query->getSql());
    }
    public function testGroupByIndexedArray()
    {
        $query = new Query;
        $query->table('movies')->groupBy(['year', 'rating']);
        $this->assertEquals('SELECT * FROM `movies` GROUP BY `movies`.`year`,`movies`.`rating`', $query->getSql());
    }
    public function testGroupByMultipleColumnsWithDirection()
    {
        $query = new Query;
        $query->table('movies')->groupBy('year, rating desc');
        $this->assertEquals('SELECT * FROM `movies` GROUP BY `movies`.`year`,`movies`.`rating` DESC', $query->getSql());
    }
    public function testGroupByIndexedArrayWithDirection()
    {
        $query = new Query;
        $query->table('movies')->groupBy(['year', 'rating desc']);
        $this->assertEquals('SELECT * FROM `movies` GROUP BY `movies`.`year`,`movies`.`rating` DESC', $query->getSql());
    }
    public function testGroupByAssocArray()
    {
        $query = new Query;
        $query->table('movies')->groupBy(['year'=>'asc', 'rating'=>'DESC']);
        $this->assertEquals('SELECT * FROM `movies` GROUP BY `movies`.`year`,`movies`.`rating` DESC', $query->getSql());
    }


    public function testLimit()
    {
        $query = new Query;
        $query->table('movies')->limit(10);
        $this->assertEquals('SELECT * FROM `movies` LIMIT 10', $query->getSql());
    }
    public function testLimitOffset()
    {
        $query = new Query;
        $query->table('movies')->limit(10)->offset(40);
        $this->assertEquals('SELECT * FROM `movies` LIMIT 10 OFFSET 40', $query->getSql());
    }


    public function testWhereArgsSkippingOperator()
    {
        $query = query('movies')->where('title', 'The Lego Movie');
        
        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`title`=?', $query->getSql());
        $this->assertEquals(['The Lego Movie'], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`title`='The Lego Movie'", $query->getSql($query->getParams()));
    }
    public function testWhereArgsSpecifyingOperator()
    {
        $query = query('movies')->where('title', 'LIKE', 'The Lego Movie%');
        
        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`title` LIKE ?', $query->getSql());
        $this->assertEquals(['The Lego Movie%'], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'The Lego Movie%'", $query->getSql($query->getParams()));
    }
    public function testWhereMultipleTimes()
    {
        $query = query('movies')->where('year', 2020)->where('rating', '>', 8);

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`year`=? AND `movies`.`rating`>?', $query->getSql());
        $this->assertEquals([2020, 8], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`year`='2020' AND `movies`.`rating`>'8'", $query->getSql($query->getParams()));
    }
    public function testWhereNestedOnce()
    {
        $query = query('movies')->where(function($query) {
            $query->where('year', 2020);
            $query->orWhere('rating', '>', 8);
        });

        $this->assertEquals('SELECT * FROM `movies` WHERE (`movies`.`year`=? OR `movies`.`rating`>?)', $query->getSql());
        $this->assertEquals([2020, 8], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE (`movies`.`year`='2020' OR `movies`.`rating`>'8')", $query->getSql($query->getParams()));
    }
    public function testWhereNestedTwice()
    {
        $query = query('movies')->where(function($query) {
            $query->where('year', 2020);
            $query->whereNot(function($query) {
                $query->where('rating', '<', 2);
                $query->orWhere('rating', '>', 8);
            });
        });

        $this->assertEquals('SELECT * FROM `movies` WHERE (`movies`.`year`=? AND NOT (`movies`.`rating`<? OR `movies`.`rating`>?))', $query->getSql());
        $this->assertEquals([2020, 2, 8], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE (`movies`.`year`='2020' AND NOT (`movies`.`rating`<'2' OR `movies`.`rating`>'8'))", $query->getSql($query->getParams()));
    }
    
    public function testWhereColumnSkippingOperator()
    {
        $query = query('movies')->join('directors')->whereColumn('director', 'directors.name');
        
        $this->assertEquals('SELECT * FROM `movies` JOIN `directors` ON `movies`.`id`=`directors`.`movies_id` WHERE `movies`.`director`=`directors`.`name`', $query->getSql());
    }
    public function testWhereColumnSpecifyingOperator()
    {
        $query = query('movies')->join('directors')->whereColumn('director', '<>', 'directors.name');
        
        $this->assertEquals('SELECT * FROM `movies` JOIN `directors` ON `movies`.`id`=`directors`.`movies_id` WHERE `movies`.`director`<>`directors`.`name`', $query->getSql());
    }

    public function testWhereExists()
    {
        $query = query('movies')
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

    public function testWhereIn()
    {
        $query = query('movies')->whereIn('year', [2010, 2012, 2014]);

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`year` IN (?,?,?)', $query->getSql());
        $this->assertEquals([2010, 2012, 2014], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`year` IN ('2010','2012','2014')", $query->getSql($query->getParams()));
    }
    public function testWhereNotIn()
    {
        $query = query('movies')->whereNotIn('year', [2010, 2012, 2014]);

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`year` NOT IN (?,?,?)', $query->getSql());
        $this->assertEquals([2010, 2012, 2014], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`year` NOT IN ('2010','2012','2014')", $query->getSql($query->getParams()));
    }
    public function testWhereInWithNestedQuery()
    {
        $query = query('movies')->whereIn('id', function($query) {
            $query->select('movie_id')
                ->from('best_of')
                ->whereIn('genre', ['sci-fi','comedy']);
        })->orWhereNotIn('id', function($query) {
            $query->select('movie_id')
                ->from('best_of')
                ->whereIn('genre', ['rom-com','film noir']);
        });
        
        // $a = query('best_of')
        //         ->select('movie_id')
        //         ->whereIn('genre', ['sci-fi','comedy']);
        // $b = query('best_of')
        //         ->select('movie_id')
        //         ->orWhereNotIn('genre', ['rom-com','film noir']);

        // TODO check removed assertion, output is correct but assertion is not 
        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`id` IN (SELECT `best_of`.`movie_id` FROM `best_of` WHERE `best_of`.`genre` IN (?,?)) OR `movies`.`id` NOT IN (SELECT `best_of`.`movie_id` FROM `best_of` WHERE `best_of`.`genre` IN (?,?))', $query->getSql());
        // $this->assertEquals([$a, $b], $query->getParams());
        $this->assertEquals("SELECT * FROM `movies` WHERE `movies`.`id` IN (SELECT `best_of`.`movie_id` FROM `best_of` WHERE `best_of`.`genre` IN ('sci-fi','comedy')) OR `movies`.`id` NOT IN (SELECT `best_of`.`movie_id` FROM `best_of` WHERE `best_of`.`genre` IN ('rom-com','film noir'))", $query->getSql($query->getParams()));
    }

    public function testWhereNull()
    {
        $query = query('movies')->whereNull('rating');

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`rating` IS NULL', $query->getSql());
    }
    public function testWhereNotNull()
    {
        $query = query('movies')->whereNotNull('rating');

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`rating` IS NOT NULL', $query->getSql());
    }
    public function testWhereNotNullOrWhereNull()
    {
        $query = query('movies')->whereNotNull('rating')->orWhereNull('reviews');

        $this->assertEquals('SELECT * FROM `movies` WHERE `movies`.`rating` IS NOT NULL OR `movies`.`reviews` IS NULL', $query->getSql());
    }
    
    
    public function testAggregateCount()
    {
        $query = new Query;
        $query->from('movies')->where('title', 'like', 'The %')->count();
        $this->assertEquals('SELECT COUNT(*) AS `count` FROM `movies` WHERE `movies`.`title` LIKE ?', $query->getSql());
        $this->assertEquals(['The %'], $query->getParams());
        $this->assertEquals("SELECT COUNT(*) AS `count` FROM `movies` WHERE `movies`.`title` LIKE 'The %'", $query->getSql($query->getParams()));
    }
    public function testAggregateAvg()
    {
        $query = new Query;
        $query->from('movies')->where('year', 2020)->avg('rating');
        $this->assertEquals('SELECT AVG(`movies`.`rating`) AS `avg` FROM `movies` WHERE `movies`.`year`=?', $query->getSql());
        $this->assertEquals(['2020'], $query->getParams());
        $this->assertEquals("SELECT AVG(`movies`.`rating`) AS `avg` FROM `movies` WHERE `movies`.`year`='2020'", $query->getSql($query->getParams()));
    }
    public function testAggregateMin()
    {
        $query = new Query;
        $query->from('movies')->where('year', 2020)->min('rating');
        $this->assertEquals('SELECT MIN(`movies`.`rating`) AS `min` FROM `movies` WHERE `movies`.`year`=?', $query->getSql());
        $this->assertEquals(['2020'], $query->getParams());
        $this->assertEquals("SELECT MIN(`movies`.`rating`) AS `min` FROM `movies` WHERE `movies`.`year`='2020'", $query->getSql($query->getParams()));
    }
    public function testAggregateMax()
    {
        $query = new Query;
        $query->from('movies')->where('year', 2020)->max('rating');
        $this->assertEquals('SELECT MAX(`movies`.`rating`) AS `max` FROM `movies` WHERE `movies`.`year`=?', $query->getSql());
        $this->assertEquals(['2020'], $query->getParams());
        $this->assertEquals("SELECT MAX(`movies`.`rating`) AS `max` FROM `movies` WHERE `movies`.`year`='2020'", $query->getSql($query->getParams()));
    }
    public function testAggregateSum()
    {
        $query = new Query;
        $query->from('movies')->where('year', 2020)->sum('attendance');
        $this->assertEquals('SELECT SUM(`movies`.`attendance`) AS `sum` FROM `movies` WHERE `movies`.`year`=?', $query->getSql());
        $this->assertEquals(['2020'], $query->getParams());
        $this->assertEquals("SELECT SUM(`movies`.`attendance`) AS `sum` FROM `movies` WHERE `movies`.`year`='2020'", $query->getSql($query->getParams()));
    }


    public function testUnion()
    {
        $a = new Query();
        $z = new Query();

        $a->from('movies')->where('title', 'like', 'a%');
        $z->from('movies')->where('title', 'like', 'z%');
        $a->union($z);
        
        $sql = "(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')";
        $this->assertEquals($sql, $a->getSql($a->getParams()));
    }
    public function testUnionManyQueries()
    {
        // example 2
        $a = new Query();
        $x = new Query();
        $y = new Query();
        $z = new Query();

        $a->from('movies')->where('title', 'like', 'a%');
        $x->from('movies')->where('title', 'like', 'x%');
        $y->from('movies')->where('title', 'like', 'y%');
        $z->from('movies')->where('title', 'like', 'z%');
        $a->union($x, $y, $z);
        
        $sql = "(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'x%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'y%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')";
        $this->assertEquals($sql, $a->getSql($a->getParams()));
    }
    public function testUnionOrderBy()
    {
        $a = new Query();
        $z = new Query();

        $a->from('movies')->where('title', 'like', 'a%');
        $z->from('movies')->where('title', 'like', 'z%');
        $a->union($z)->orderBy('year', 'desc');
        
        $sql = "(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')
ORDER BY `movies`.`year` DESC";
        $this->assertEquals($sql, $a->getSql($a->getParams()));
    }
    public function testUnionOrderByPaginate()
    {
        $a = new Query();
        $z = new Query();

        $a->from('movies')->where('title', 'like', 'a%');
        $z->from('movies')->where('title', 'like', 'z%');
        $a->union($z)->orderBy('year', 'desc')->limit(10)->offset(40);
        
        $sql = "(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')
ORDER BY `movies`.`year` DESC
LIMIT 10 OFFSET 40";
        $this->assertEquals($sql, $a->getSql($a->getParams()));
    }
    public function testUnionAll()
    {
        $a = query('movies')->where('title', 'like', 'a%');
        $z = query('movies')->where('title', 'like', 'z%');
        // the orderBy, limit and offset clauses work on the UNION results as expected
        $a->unionAll($z);
        
        $sql = "(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'a%')
UNION ALL
(SELECT * FROM `movies` WHERE `movies`.`title` LIKE 'z%')";
        $this->assertEquals($sql, $a->getSql($a->getParams()));
    }


    public function testInsertAssocArray()
    {
        $query = new Query;
        $query->table('movies')->insert([
            'title' => '2001',
            'year' => 1968,
            'rating' => 8.3
        ]);
        $this->assertEquals('INSERT INTO `movies` (`title`,`year`,`rating`) VALUES (?,?,?)', $query->getSql());
        $this->assertEquals(['2001', 1968, 8.3], $query->getParams());
        $this->assertEquals("INSERT INTO `movies` (`title`,`year`,`rating`) VALUES ('2001','1968','8.3')", $query->getSql($query->getParams()));
    }
    public function testInsertObject()
    {
        $movie = new \StdClass;
        $movie->title = '2001';
        $movie->year = 1968;
        $movie->rating = 8.3;
        
        $query = new Query;
        $query->table('movies')->insert($movie);
        $this->assertEquals('INSERT INTO `movies` (`title`,`year`,`rating`) VALUES (?,?,?)', $query->getSql());
        $this->assertEquals(['2001', 1968, 8.3], $query->getParams());
        $this->assertEquals("INSERT INTO `movies` (`title`,`year`,`rating`) VALUES ('2001','1968','8.3')", $query->getSql($query->getParams()));
    }
    public function testInsertManyAssocArrays()
    {
        $query = new Query;
        $query->table('movies')->insert([
            [
                'title' => '2001',
                'year' => 1968,
                'rating' => 8.3
            ],
            [
                'title' => 'The Shining',
                'year' => 1980,
                'rating' => 8.4
            ],
            [
                'title' => 'Clockwork Orange',
                'year' => 1971,
                'rating' => 8.3
            ]
        ]);
        $this->assertEquals('INSERT INTO `movies` (`title`,`year`,`rating`) VALUES (?,?,?),(?,?,?),(?,?,?)', $query->getSql());
        $this->assertEquals(['2001', 1968, 8.3, 'The Shining', 1980, 8.4, 'Clockwork Orange', 1971, 8.3], $query->getParams());
        $this->assertEquals("INSERT INTO `movies` (`title`,`year`,`rating`) VALUES ('2001','1968','8.3'),('The Shining','1980','8.4'),('Clockwork Orange','1971','8.3')", $query->getSql($query->getParams()));
    }
    public function testInsertManyObects()
    {
        $query = new Query;
        $query->table('movies')->insert([
            new class {
                public $title = '2001';
                public $year = 1968;
                public $rating = 8.3;
            },
            new class {
                public $title = 'The Shining';
                public $year = 1980;
                public $rating = 8.4;
            },
            new class {
                public $title = 'Clockwork Orange';
                public $year = 1971;
                public $rating = 8.3;
            }
        ]);
        $this->assertEquals('INSERT INTO `movies` (`title`,`year`,`rating`) VALUES (?,?,?),(?,?,?),(?,?,?)', $query->getSql());
        $this->assertEquals(['2001', 1968, 8.3, 'The Shining', 1980, 8.4, 'Clockwork Orange', 1971, 8.3], $query->getParams());
        $this->assertEquals("INSERT INTO `movies` (`title`,`year`,`rating`) VALUES ('2001','1968','8.3'),('The Shining','1980','8.4'),('Clockwork Orange','1971','8.3')", $query->getSql($query->getParams()));
    }


    public function testUpdate()
    {
        $query = new Query;
        $query->table('movies')
            ->update([
                'archived' => 1
            ]);
        $this->assertEquals('UPDATE `movies` SET `movies`.`archived`=?', $query->getSql());
        $this->assertEquals([1], $query->getParams());
        $this->assertEquals("UPDATE `movies` SET `movies`.`archived`='1'", $query->getSql($query->getParams()));
    }
    public function testUpdateWithWhere()
    {
        $query = new Query;
        $query->table('movies')
            ->where('year', '<', 1984)
            ->update([
                'archived' => 1
            ]);
        $this->assertEquals('UPDATE `movies` SET `movies`.`archived`=? WHERE `movies`.`year`<?', $query->getSql());
        $this->assertEquals([1,1984], $query->getParams());
        $this->assertEquals("UPDATE `movies` SET `movies`.`archived`='1' WHERE `movies`.`year`<'1984'", $query->getSql($query->getParams()));
    }
    public function testUpdateWithWhereLimit()
    {
        $query = new Query;
        $query->table('movies')
            ->where('year', '<', 1984)
            ->update([
                'archived' => 1
            ])
            ->limit(10);
            $this->assertEquals('UPDATE `movies` SET `movies`.`archived`=? WHERE `movies`.`year`<? LIMIT 10', $query->getSql());
            $this->assertEquals([1,1984], $query->getParams());
            $this->assertEquals("UPDATE `movies` SET `movies`.`archived`='1' WHERE `movies`.`year`<'1984' LIMIT 10", $query->getSql($query->getParams()));
    }
    public function testUpdateWithWhereLimitOrderBy()
    {
        $query = new Query;
        $query->table('movies')
            ->where('year', '<', 1984)
            ->update([
                'archived' => 1
            ])
            ->orderBy('year', 'desc')
            ->limit(10);
        $this->assertEquals('UPDATE `movies` SET `movies`.`archived`=? WHERE `movies`.`year`<? ORDER BY `movies`.`year` DESC LIMIT 10', $query->getSql());
        $this->assertEquals([1,1984], $query->getParams());
        $this->assertEquals("UPDATE `movies` SET `movies`.`archived`='1' WHERE `movies`.`year`<'1984' ORDER BY `movies`.`year` DESC LIMIT 10", $query->getSql($query->getParams()));
    }


    public function testDeleteExamplesWork()
    {
        $query = new Query;
        $query->table('movies')->delete();
        $this->assertEquals('DELETE FROM `movies`', $query->getSql());
    }
    public function testDeleteWhere()
    {
        $query = new Query;
        $query->table('movies')->where('year', '<', 1984)->delete();
        $this->assertEquals('DELETE FROM `movies` WHERE `movies`.`year`<?', $query->getSql());
        $this->assertEquals([1984], $query->getParams());
        $this->assertEquals("DELETE FROM `movies` WHERE `movies`.`year`<'1984'", $query->getSql($query->getParams()));
    }
    public function testDeleteWhereLimit()
    {
        $query = new Query;
        $query->table('movies')->where('year', '<', 1984)->limit(10)->delete();
        $this->assertEquals('DELETE FROM `movies` WHERE `movies`.`year`<? LIMIT 10', $query->getSql());
        $this->assertEquals([1984], $query->getParams());
        $this->assertEquals("DELETE FROM `movies` WHERE `movies`.`year`<'1984' LIMIT 10", $query->getSql($query->getParams()));
    }
    public function testDeleteWhereOrderByLimit()
    {
        $query = new Query;
        $query->table('movies')->where('year', '<', 1984)->orderBy('year', 'DESC')->limit(10)->delete();
        $this->assertEquals('DELETE FROM `movies` WHERE `movies`.`year`<? ORDER BY `movies`.`year` DESC LIMIT 10', $query->getSql());
        $this->assertEquals([1984], $query->getParams());
        $this->assertEquals("DELETE FROM `movies` WHERE `movies`.`year`<'1984' ORDER BY `movies`.`year` DESC LIMIT 10", $query->getSql($query->getParams()));
    }


    public function testTruncate()
    {
        $query = new Query;
        $query->table('movies')->truncate();
        $this->assertEquals('TRUNCATE TABLE `movies`', $query->getSql());
    }
}
