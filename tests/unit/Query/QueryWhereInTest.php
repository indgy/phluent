<?php 

namespace Tests\Unit;

use Phluent\Query;

// TODO test where clauses work with JOIN clauses, picking correct tables
// TODO ensure where parameters appear in the correct order, when deeply nested etc.
// TODO ensure parameters appear in the correct order when combined with insert, delete, update

class QueryWhereInTest extends \PHPUnit\Framework\TestCase
{
    public function testWhereIn()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`id` IN (?,?,?,?)";

        $q = new Query();
        $q->table('contacts')
            ->whereIn('id', [99,199,299,999]);

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals([99,199,299,999], $q->getParams());
    }
    public function testWhereNotIn()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`id` NOT IN (?,?,?,?)";

        $q = new Query();
        $q->table('contacts')
            ->whereNotIn('id', [4678,3344,2,1]);

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals([4678,3344,2,1], $q->getParams());
    }
    public function testOrWhereIn()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR `contacts`.`id` IN (?,?,?,?)";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 1)
            ->orWhereIn('id', [99,33,903458435,3544]);

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals([1,99,33,903458435,3544], $q->getParams());
    }
    public function testOrWhereNotIn()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`status`=? OR `contacts`.`id` NOT IN (?,?,?,?)";

        $q = new Query();
        $q->table('contacts')
            ->where('status', 1)
            ->orWhereNotIn('id', [69,87,658,11]);

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals([1,69,87,658,11], $q->getParams());
    }
    
    public function testWhereInCallable()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`id` IN (SELECT `emails`.`id` FROM `emails` WHERE `emails`.`address` LIKE ?)";

        $q = new Query();
        $q->table('contacts')
            ->whereIn('id', function($q){
                $q->select('id')
                    ->from('emails')
                    ->where('address', 'like', '%@gmail.%');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['%@gmail.%'], $q->getParams());
    }
    public function testAndWhereInCallable()
    {
        $expected = "SELECT * FROM `contacts` WHERE `contacts`.`name` LIKE ? AND `contacts`.`id` IN (SELECT `emails`.`id` FROM `emails` WHERE `emails`.`address` LIKE ?)";

        $q = new Query();
        $q->table('contacts')
            ->where('name', 'like', 'jo%')
            ->whereIn('id', function($q){
                $q->select('id')
                    ->from('emails')
                    ->where('address', 'like', '%@gmail.%');
            });

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['jo%', '%@gmail.%'], $q->getParams());
    }
    
    public function testWhereInUpdateParamsOrder()
    {
        $expected = "UPDATE `emails` SET `emails`.`address`=? WHERE `emails`.`id` IN (SELECT `emails`.`id` FROM `emails` WHERE `emails`.`address` LIKE ?)";

        $q = new Query();
        $q->table('emails')
            ->whereIn('id', function($q){
                $q->select('id')
                    ->from('emails')
                    ->where('address', 'like', '%@gmail.com%');
            })
            ->update([
                'address' => 'gmail.com'
            ]);

        $this->assertEquals($expected, $q->getSql());
        $this->assertEquals(['gmail.com', '%@gmail.com%'], $q->getParams());
    }
    
    public function testShouldThrowExceptionWithInvalidValue()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->table('contacts')->whereIn('status', new \StdClass());
    }
    public function testShouldThrowExceptionWhenProvidedANonBoolean()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->table('contacts')->whereIn('status', [1,2,3], 'NOT A BOOL');
    }

}
