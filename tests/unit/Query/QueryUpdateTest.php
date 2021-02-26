<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryUpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->update([
            'name' => 'Bob'
        ]));
    }
    public function testShouldUpdateSingleColumn()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->table('movies')->update([
            'name' => 'Inception',
        ]);
        $this->assertEquals("UPDATE `movies` SET `movies`.`name`=?", $q->getSql());

        $p = $r->getProperty('update');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'name' => 'Inception',
            ]
        ], $p->getValue($q));
    }
    public function testShouldUpdateMultipleColumns()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->table('movies')->update([
            'name' => 'Inception',
            'year' => '2018'
        ]);
        $this->assertEquals("UPDATE `movies` SET `movies`.`name`=?,`movies`.`year`=?", $q->getSql());

        $p = $r->getProperty('update');
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
        $q->select('*')->from('movies')->update(['title'=>2001])->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledDelete()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->delete()->update(['title'=>2001])->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledInsert()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->insert('title', 2001)->update(['title'=>2010])->getSql();
    }
    public function testShouldThrowExceptionIfPreviouslyCalledTruncate()
    {
        $this->expectException('BadMethodCallException');
        $q = new Query();
        $q->table('movies')->truncate()->update(['title'=>2010])->getSql();
    }

}
