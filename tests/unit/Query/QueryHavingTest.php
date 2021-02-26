<?php 

namespace Tests\Unit;

use Phluent\Query;

// TODO test where clauses work with JOIN clauses, picking correct tables
// TODO ensure where parameters appear in the correct order, when deeply nested etc.
// TODO ensure parameters appear in the correct order when combined with insert, delete, update

class QueryHavingTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $q = new Query();

        $this->assertInstanceOf(Query::class, $q);
    }
    public function testHaving()
    {
        $expected = "SELECT * FROM `contacts` GROUP BY `contacts`.`email` HAVING `contacts`.`email` LIKE ?";

        $q = new Query();
        $q->table('contacts')->groupBy('email')->having('email', 'LIKE', '%@gmail.com');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['%@gmail.com'], $q->getParams());
    }
    public function testHavingShortSyntax()
    {
        $expected = "SELECT * FROM `contacts` GROUP BY `contacts`.`email` HAVING `contacts`.`last_contact`=?";

        $q = new Query();
        $q->table('contacts')->groupBy('email')->having('last_contact', '2020-01-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2020-01-01'], $q->getParams());
    }
    public function testHavingAndHaving()
    {
        $expected = "SELECT * FROM `contacts` GROUP BY `contacts`.`email` HAVING `contacts`.`email` LIKE ? AND `contacts`.`last_contact`<?";

        $q = new Query();
        $q->table('contacts')
            ->groupBy('email')
            ->having('email', 'LIKE', '%@gmail.com')
            ->having('last_contact', '<', '2019-01-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['%@gmail.com', '2019-01-01'], $q->getParams());
    }
    public function testHavingOrHaving()
    {
        $expected = "SELECT * FROM `contacts` GROUP BY `contacts`.`email` HAVING `contacts`.`email` LIKE ? OR `contacts`.`last_contact`<?";

        $q = new Query();
        $q->table('contacts')
            ->groupBy('email')
            ->having('email', 'LIKE', '%@gmail.com')
            ->orHaving('last_contact', '<', '2019-01-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['%@gmail.com', '2019-01-01'], $q->getParams());
    }
    public function testHavingNotHaving()
    {
        $expected = "SELECT * FROM `contacts` GROUP BY `contacts`.`email` HAVING `contacts`.`email` LIKE ? AND NOT `contacts`.`last_contact`<?";

        $q = new Query();
        $q->table('contacts')
            ->groupBy('email')
            ->having('email', 'LIKE', '%@gmail.com')
            ->notHaving('last_contact', '<', '2019-01-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['%@gmail.com', '2019-01-01'], $q->getParams());
    }
    public function testHavingOrNotHaving()
    {
        $expected = "SELECT * FROM `contacts` GROUP BY `contacts`.`email` HAVING `contacts`.`email` LIKE ? OR NOT `contacts`.`last_contact`<?";

        $q = new Query();
        $q->table('contacts')
            ->groupBy('email')
            ->having('email', 'LIKE', '%@gmail.com')
            ->orNotHaving('last_contact', '<', '2019-01-01');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['%@gmail.com', '2019-01-01'], $q->getParams());
    }
    public function testHavingCallable()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`last_contact`<? GROUP BY `contacts`.`status` HAVING (`contacts`.`name` LIKE ? OR `contacts`.`name` LIKE ?)";

        $q = new Query();
        $q->table('contacts')
            ->where('last_contact', '<', '2020-01-01')
            ->groupBy('status')
            ->having(function($q){
                $q->having('name', 'LIKE', 'Bob');
                $q->orHaving('name', 'LIKE', 'Jill');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['2020-01-01', 'Bob','Jill'], $q->getParams());
    }
    public function testNotHaving()
    {
        $expected = "SELECT * FROM `contacts` GROUP BY `contacts`.`email` HAVING NOT `contacts`.`email` LIKE ?";

        $q = new Query();
        $q->table('contacts')->groupBy('email')->notHaving('email', 'LIKE', '%@gmail.com');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['%@gmail.com'], $q->getParams());
    }

}
