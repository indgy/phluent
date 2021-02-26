<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryJoinTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldReturnSelf()
    {
        $q = new Query();
        $this->assertInstanceOf(Query::class, $q->from('actors')->join('movies', 'id', 'movie_id'));
    }
    public function testShouldSetSimpleJoin()
    {
        $q = new Query();

        $q->from('contacts');
        $q->join('emails', 'id', 'contact_id');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetSimpleJoinWithTableNames()
    {
        // not using backticks
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', 'emails.contact_id');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetRightJoinUsingLonghand()
    {
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', '=', 'emails.contact_id');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetRightJoinUsingLonghandNotEqualsOperator()
    {
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', '<>', 'emails.contact_id');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `emails` ON `contacts`.`id`<>`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetSimpleJoinWithTableNamesAndBackticks()
    {
        // using backticks
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', '`contacts`.`id`', '`emails`.`contact_id`');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
        // mismatched backticks
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', '`contacts`.id', '`emails`.contact_id');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
        // mismatched backticks
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.`id`', 'emails.`contact_id`');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetJoinWithSpecificOperator()
    {
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', 'emails.contact_id', '<>');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `emails` ON `contacts`.`id`<>`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetOuterJoinWithSpecificOperator()
    {
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', 'emails.contact_id', '=', 'outer');
        $this->assertEquals("SELECT * FROM `contacts` OUTER JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetInnerJoinWithSpecificOperator()
    {
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', 'emails.contact_id', '=', 'inner');
        $this->assertEquals("SELECT * FROM `contacts` INNER JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetLeftJoinWithSpecificOperator()
    {
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', 'emails.contact_id', '=', 'left');
        $this->assertEquals("SELECT * FROM `contacts` LEFT JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetRightJoinWithSpecificOperator()
    {
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', 'emails.contact_id', '=', 'right');
        $this->assertEquals("SELECT * FROM `contacts` RIGHT JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetRightJoinWithNullOperator()
    {
        $q = new Query();
        $q->from('contacts');
        $q->join('emails', 'contacts.id', 'emails.contact_id', null, 'right');
        $this->assertEquals("SELECT * FROM `contacts` RIGHT JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldSetMultipleJoins()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->from('contacts');
        $q->join('addresses', 'id', 'contact_id');
        $q->join('emails', 'id', 'contact_id');
        $this->assertEquals("SELECT * FROM `contacts` JOIN `addresses` ON `contacts`.`id`=`addresses`.`contact_id`\n JOIN `emails` ON `contacts`.`id`=`emails`.`contact_id`", $q->getSql());
    }
    public function testShouldThrowExceptionIfFromNotSet()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->join('movies', 'id', 'movie_id');
    }
    public function testShouldThrowExceptionIfTableNameInvalid()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->from('actors')->join(';!(DROP TABLES;)movies.genres', 'id', 'movie_id');
    }
    public function testShouldThrowExceptionWithInvalidOperator()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query();
        $q->from('contacts')->join('emails', 'contacts.id', 'emails.contact_id', '!>>');
    }
}
