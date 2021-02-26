<?php 

namespace Tests\Unit;

use Phluent\Query;

// TODO test where clauses work with JOIN clauses, picking correct tables
// TODO ensure where parameters appear in the correct order, when deeply nested etc.
// TODO ensure parameters appear in the correct order when combined with insert, delete, update

class QueryWhereTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $q = new Query();

        $this->assertInstanceOf(Query::class, $q);
    }
    public function testWhere()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`name`=?";
        
        $q = new Query();
        $q->table('contacts')->where('name', 'Bob');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['Bob'], $q->getParams());
    }
    public function testWhereCallable()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? AND (`contacts`.`name` LIKE ?)";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 1)
            ->where(function($q){
                $q->where('name', 'LIKE', 'Bob');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['1','Bob'], $q->getParams());
    }
    public function testWhereNot()
    {
        $expected = "SELECT * FROM `contacts` WHERE NOT `contacts`.`name`=?";

        $q = new Query();
        $q->table('contacts')->whereNot('name', 'Bob');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['Bob'], $q->getParams());
    }
    public function testOrWhere()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`name`=? OR `contacts`.`name`=?";

        $q = new Query();
        $q->table('contacts')->where('name', 'Bob')->orWhere('name', 'Jill');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['Bob','Jill'], $q->getParams());
    }
    public function testOrWhereNot()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`name`=? OR NOT `contacts`.`name`=?";

        $q = new Query();
        $q->table('contacts')->where('name', 'Bob')->orWhereNot('name', 'Jill');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['Bob','Jill'], $q->getParams());
    }


    public function testWhereOrWhere()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`name`=? OR `contacts`.`name`=?";

        $q = new Query();
        $q->table('contacts')
            ->where('name', 'Bob')
            ->orWhere('name', 'Jill');

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['Bob','Jill'], $q->getParams());
    }

    public function testWhereOrWhereWithNest()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? AND (`contacts`.`name`=? OR `contacts`.`name`=?)";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 'active')
            ->where(function($q) {
                $q->where('name', 'Bob');
                $q->orWhere('name', 'Jill');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['active','Bob','Jill'], $q->getParams());
    }
    public function testWhereOrWhereWithOrNest()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR (`contacts`.`name`=? OR `contacts`.`name`=?)";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 'active')
            ->orWhere(function($q) {
                $q->where('name', 'Bob');
                $q->orWhere('name', 'Jill');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['active','Bob','Jill'], $q->getParams());
    }

    public function testTwoNestedWheres()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`active`=? AND (`contacts`.`name`=? AND `contacts`.`email`=?) AND (`contacts`.`name`=? AND `contacts`.`email`=?) AND `contacts`.`last_seen`<?";
        $data = [
            '1', 'Bob', 'bob@example.com', 'Jill', 'jill@example.com', '25'
        ];
        $q = new Query();
        $q->table('contacts')
            ->where('active', 1)
            ->where(function($q){
                $q->where('name', 'Bob');
                $q->where('email', 'bob@example.com');
            })
            ->where(function($q){
                $q->where('name', 'Jill');
                $q->where('email', 'jill@example.com');
            })
            ->where('last_seen', '<', 25);

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals($data, $q->getParams());
    }
    public function testNestedWheresTwoDeep()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`active`=? AND (`contacts`.`name`=? OR (`contacts`.`name`=? OR (`contacts`.`name`=? OR `contacts`.`name`=?))) AND `contacts`.`last_seen`<?";
        $values = ['1', 'Bob', 'Jill', 'Jack', 'John', '25'];

        $q = new Query();
        $q->table('contacts')
            ->where('active', 1)
            ->where(function($q){
                $q->where('name', 'Bob');
                $q->orWhere(function($q){
                    $q->where('name', 'Jill');
                    $q->orWhere(function($q){
                        $q->where('name', 'Jack');
                        $q->orWhere('name', 'John');
                    });
                });
            })
            ->where('last_seen', '<', 25);

        $sql = $q->getSql();
        $values = $q->getParams();

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals($values, $q->getParams());
    }

    public function testTwoNestedWheresOrderBy()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`active`=? AND (`contacts`.`name`=? AND `contacts`.`email`=?) AND (`contacts`.`name`=? AND `contacts`.`email`=?) AND `contacts`.`last_seen`<? ORDER BY `contacts`.`name`";
        $data = [
            '1', 'Bob', 'bob@example.com', 'Jill', 'jill@example.com', '25'
        ];

        $q = new Query();
        $q->table('contacts')
            ->orderBy('name')
            ->where('active', 1)
            ->where(function($q){
                $q->where('name', 'Bob');
                $q->where('email', 'bob@example.com');
            })
            ->where(function($q){
                $q->where('name', 'Jill');
                $q->where('email', 'jill@example.com');
            })
            ->where('last_seen', '<', 25);

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals($data, $q->getParams());
    }
    public function testNestedWhereInBetween()
    {
        $expected = "SELECT * FROM `contacts` WHERE (`contacts`.`name`=? AND `contacts`.`status` IN (?,?,?,?) AND `contacts`.`last_login` BETWEEN ? AND ?)";

        $q = new Query();
        $q->table('contacts')
        ->where(function($q){
            $q->where('name', 'Bob');
            $q->whereIn('status', [10,14,19,21]);
            $q->whereBetween('last_login', '2020-01-01', '2020-03-31');
        });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['Bob',10,14,19,21,'2020-01-01','2020-03-31'], $q->getParams());
    }


    public function testWhereNotCallable()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? AND NOT (`contacts`.`name` LIKE ?)";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 1)
            ->whereNot(function($q) {
                $q->where('name', 'LIKE', 'Bob');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['1','Bob'], $q->getParams());
    }
    public function testWhereCallable2Levsls()
    {
        $expected = "SELECT * FROM `contacts` WHERE (`contacts`.`name` LIKE ? AND (`contacts`.`name`=? OR `contacts`.`name`=?))";

        $q = new Query();
        $q->table('contacts')->where(function($q){
            $q->where('name', 'LIKE', 'Bob');
            $q->where(function($q){
                $q->where('name', 'Jack');
                $q->orWhere('name', 'Jill');
            });
        });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['Bob','Jack','Jill'], $q->getParams());
    }
    public function testOrWhereCallable()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR (`contacts`.`name` LIKE ? AND `contacts`.`name` LIKE ?)";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 1)
            ->orWhere(function($q){
                $q->where('name', 'LIKE', 'Bob');
                $q->where('name', 'LIKE', 'Jill');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['1','Bob','Jill'], $q->getParams());
    }
    public function testOrWhereNotCallable()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR NOT (`contacts`.`name` LIKE ? AND `contacts`.`name` LIKE ?)";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 1)
            ->orWhereNot(function($q){
                $q->where('name', 'LIKE', 'Bob');
                $q->where('name', 'LIKE', 'Jill');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['1','Bob','Jill'], $q->getParams());
    }
    public function testShouldThrowExceptionWhenProvidedANonBoolean()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->table('contacts')->where('status', '=', 1, 'NOT A BOOL');
    }
}
