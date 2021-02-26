<?php

namespace Tests\Unit;

use Phluent\Query;


class QuerySelectTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->from('actors')->select('name, genre'));
    }
    public function testShouldSetFromArray()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);
        $s = [
            'name',
            'stage_name',
            'genre',
        ];

        $q->from('actors');
        $q->select($s);
        $this->assertEquals("SELECT `actors`.`name`,`actors`.`stage_name`,`actors`.`genre` FROM `actors`", $q->getSql());

        $p = $r->getProperty('select');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'type' => 'plain',
                'value' => 'name',
            ],
            [
                'type' => 'plain',
                'value' => 'stage_name',
            ],
            [
                'type' => 'plain',
                'value' => 'genre',
            ]
        ], $p->getValue($q));
    }
    public function testShouldSetFromString()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);
        $s = [
            'name',
            'stage_name',
            'genre',
        ];

        $q->from('actors');
        $q->select('name, stage_name,genre');
        $this->assertEquals("SELECT `actors`.`name`,`actors`.`stage_name`,`actors`.`genre` FROM `actors`", $q->getSql());

        $p = $r->getProperty('select');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'type' => 'plain',
                'value' => 'name',
            ],
            [
                'type' => 'plain',
                'value' => 'stage_name',
            ],
            [
                'type' => 'plain',
                'value' => 'genre',
            ]
        ], $p->getValue($q));
    }
    public function testShouldSetWithTableNamesFromString()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);
        $s = [
            'name',
            'stage_name',
            'genre',
            'dates.born'
        ];

        $q->from('actors');
        $q->select('name, stage_name,genre, dates.born');
        $this->assertEquals("SELECT `actors`.`name`,`actors`.`stage_name`,`actors`.`genre`,`dates`.`born` FROM `actors`", $q->getSql());

        $p = $r->getProperty('select');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'type' => 'plain',
                'value' => 'name',
            ],
            [
                'type' => 'plain',
                'value' => 'stage_name',
            ],
            [
                'type' => 'plain',
                'value' => 'genre',
            ],
            [
                'type' => 'plain',
                'value' => 'dates.born',
            ]
        ], $p->getValue($q));
    }
    public function testShouldAllowAsAliases()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);
        $s = [
            'name',
            'stage_name AS s_name',
            'genre as g',
        ];

        $q->from('actors');
        $q->select('name, stage_name AS s_name,genre as g');
        $this->assertEquals("SELECT `actors`.`name`,`actors`.`stage_name` AS `s_name`,`actors`.`genre` AS `g` FROM `actors`", $q->getSql());

        $p = $r->getProperty('select');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'type' => 'plain',
                'value' => 'name',
            ],
            [
                'type' => 'plain',
                'value' => 'stage_name AS s_name',
            ],
            [
                'type' => 'plain',
                'value' => 'genre as g',
            ]
        ], $p->getValue($q));
    }
    public function testShouldThrowExceptionWithInvalidInput()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->select(123);
    }
    // public function testShouldThrowExceptionWithWeirdStringInput()
    // {
    //     // How shall we handle this? VaidColumnName, validTableName
    //     $this->expectException('InvalidArgumentException');
    //     $q = new Query();
    //     $q->select('this is a weird string.that is not valid');
    //     echo $q->getSql();
    // }
    public function testShouldThrowExceptionWithInvalidRawInput()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->selectRaw(new \StdClass());
    }
    public function testShouldNotResetStateIfFromSet()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('actors');
        $q->select('name, stage_name, genre');

        $p = $r->getProperty('from');
        $p->setAccessible(true);
        $this->assertEquals('actors', $p->getValue($q));
    }
    public function testShouldResetStateIfFromNotSet()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->select('name, stage_name, genre');

        $p = $r->getProperty('from');
        $p->setAccessible(true);
        $this->assertEquals(null, $p->getValue($q));
    }

    public function testShouldResetIfIsComplete()
    {
        $q = new Query;
        $r = new \ReflectionClass($q);
        $p = $r->getProperty('is_complete');
        $p->setAccessible(true);

        $sql = $q->select('first_name')->from('contacts')->orderBy('first_name')->limit(10)->getSql();
        $this->assertEquals('SELECT `contacts`.`first_name` FROM `contacts` ORDER BY `contacts`.`first_name` LIMIT 10', $sql);
        $this->assertTrue($p->getValue($q));
        $q->select('first_name')->from('contacts');
        $this->assertFalse($p->getValue($q));
        $this->assertEquals('SELECT `contacts`.`first_name` FROM `contacts`', $q->getSql());
    }
}
