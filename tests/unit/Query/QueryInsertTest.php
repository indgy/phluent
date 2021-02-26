<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryInsertTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->insert([
            'name' => 'Bob'
        ]));
    }
    public function testShouldSetInsertArrayForSingleRowSingleColumn()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->table('movies')->insert([
            'name' => 'Inception',
        ]);
        $this->assertEquals("INSERT INTO `movies` (`name`) VALUES (?)", $q->getSql());
        $this->assertEquals(['Inception'], $q->getParams());

        $p = $r->getProperty('insert');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'name' => 'Inception',
            ]
        ], $p->getValue($q));
    }
    public function testShouldSetInsertArrayForSingleRowSingleMultiColumn()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->table('movies')->insert([
            'name' => 'Inception',
            'year' => '2018'
        ]);
        $this->assertEquals("INSERT INTO `movies` (`name`,`year`) VALUES (?,?)", $q->getSql());
        $this->assertEquals(['Inception', '2018'], $q->getParams());

        $p = $r->getProperty('insert');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'name' => 'Inception',
                'year' => '2018'
            ]
        ], $p->getValue($q));
    }
    public function testShouldSetInsertArrayForSingleRowMultiColumns()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->table('movies')->insert([
            'name' => 'Inception',
            'year' => '2018'
        ]);
        $this->assertEquals("INSERT INTO `movies` (`name`,`year`) VALUES (?,?)", $q->getSql());
        $this->assertEquals(['Inception', '2018'], $q->getParams());

        $p = $r->getProperty('insert');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'name' => 'Inception',
                'year' => '2018'
            ]
        ], $p->getValue($q));
    }
    public function testShouldSetInsertArrayForMultiRowsMultiColumns()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $data = [
            [
                'name' => 'Inception',
                'year' => '2018'
            ],
            [
                'name' => 'Bladerunner',
                'year' => '1986'
            ],
            [
                'name' => 'Bladerunner 2049',
                'year' => '2018'
            ]
        ];

        $q->table('movies')->insert($data);
        $this->assertEquals("INSERT INTO `movies` (`name`,`year`) VALUES (?,?),(?,?),(?,?)", $q->getSql());
        $this->assertEquals(['Inception', '2018', 'Bladerunner', '1986', 'Bladerunner 2049', '2018'], $q->getParams());

        $p = $r->getProperty('insert');
        $p->setAccessible(true);
        $this->assertEquals($data, $p->getValue($q));
    }
    public function testShouldSetInsertArrayOfObjects()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $data = [
            (object) [
                'name' => 'Inception',
                'year' => '2018'
            ],
            (object) [
                'name' => 'Bladerunner',
                'year' => '1986'
            ],
            (object) [
                'name' => 'Bladerunner 2049',
                'year' => '2018'
            ]
        ];

        $q->table('movies')->insert($data);
        $this->assertEquals("INSERT INTO `movies` (`name`,`year`) VALUES (?,?),(?,?),(?,?)", $q->getSql());
        $this->assertEquals(['Inception', '2018', 'Bladerunner', '1986', 'Bladerunner 2049', '2018'], $q->getParams());

        $p = $r->getProperty('insert');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'name' => 'Inception',
                'year' => '2018'
            ],
            [
                'name' => 'Bladerunner',
                'year' => '1986'
            ],
            [
                'name' => 'Bladerunner 2049',
                'year' => '2018'
            ]
        ], $p->getValue($q));
    }
    public function testShouldSetInsertObject()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $data = (object) [
            'name' => 'Inception',
            'year' => '2018'
        ];

        $q->table('movies')->insert($data);
        $this->assertEquals("INSERT INTO `movies` (`name`,`year`) VALUES (?,?)", $q->getSql());
        $this->assertEquals(['Inception', '2018'], $q->getParams());

        $p = $r->getProperty('insert');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'name' => 'Inception',
                'year' => '2018'
            ]
        ], $p->getValue($q));
    }
    public function testShouldThrowExceptionIfPreviouslyCalledSelect()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->select('*')->from('movies')->insert(['title'=>2010])->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledDelete()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->delete()->insert(['title'=>2001])->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledUpdate()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->where('title', 2001)->update(['title'=>2010])->insert(['title'=>2010])->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledTruncate()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->truncate()->insert(['title'=>2010])->getSql();
    }
    // public function testShouldTHrowExceptionOnInvalidInput()
    // {
    //     $q = new Query();
    //
    //     $this->expectException('InvalidArgumentException');
    //     $q->from('movies')->groupBy('12++34');
    // }
}
