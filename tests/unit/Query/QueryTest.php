<?php

namespace Tests\Unit;

use Phluent\Query;


class QueryTest extends \PHPUnit\Framework\TestCase
{
    // /**
    // *  @dataProvider provideSanitiseData
    // */
    // public function testSanitiseShouldAllowValidChars($input)
    // {
    //     $q = new Query();
    //     $r = new ReflectionMethod(Query::class, 'sanitise');
    //     $r->setAccessible('sanitise');
    //     $this->assertEquals($input, $r->invoke(new Query, $input));
    // }
    // public function provideSanitiseData()
    // {
    //     return [
    //         'Allowed qualified' => ['table.column_1234567890'],
    //         'Allowed unqualified' => ['column_1234567890'],
    //     ];
    // }

    public function testShouldAddReferenceCharsFromString()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->addReferenceChars(':-');
        $p = $r->getProperty('reference_chars');
        $p->setAccessible(true);
        $this->assertEquals([':','-'], $p->getValue($q));
    }
    public function testSanitiseShouldAddReferenceCharsFromArray()
    {
        $q = new Query();
        $r = new \ReflectionClass($q);

        $q->addReferenceChars([':','-']);
        $p = $r->getProperty('reference_chars');
        $p->setAccessible(true);
        $this->assertEquals([':','-'], $p->getValue($q));
    }
    /**
     * @dataProvider provideValidAllowedChars
     */
    public function testShouldAllowAddedChars($chars, $reference)
    {
        $q = new Query();
        $r = new \ReflectionClass($q);
        $q->addReferenceChars($chars);
        $q->select($reference);

        $p = $r->getProperty('select');
        $p->setAccessible(true);
        $this->assertEquals([
            [
                'type' => 'plain',
                'value' => $reference
            ]
        ], $p->getValue($q));
    }
    public function provideValidAllowedChars()
    {
        return [
            'accents' => ['ñáéõô', 'movies.refñáéõôerence'],
            'colon' => [':', 'movies::translations.movies_id'],
        ];
    }
    /**
     *  @dataProvider provideInvalidAllowedChars
     */
    public function testShouldThrowExceptionWhenAllowAddedCharsGivenInvalidInput($input)
    {
        $q = new Query;
        $this->expectException('InvalidArgumentException');
        $q->addReferenceChars($input);
    }
    public function provideInvalidAllowedChars()
    {
        return [
            'array with multichars' => [[':','-','abc']],
            'integer' => [1234],
            'float' => [123.456],
            'object' => [new \StdClass()],
        ];
    }
    
    public function testSetQuoteCharToDoubleQuote()
    {
        $q = new Query;
        $r = new \ReflectionClass($q);

        $p = $r->getProperty('quote_char');
        $p->setAccessible(true);
        $q->setQuoteChar('"');
        $this->assertEquals('"', $p->getValue($q));
    }
    public function testSetQuoteCharToBacktick()
    {
        $q = new Query;
        $r = new \ReflectionClass($q);
        
        $p = $r->getProperty('quote_char');
        $p->setAccessible(true);
        $q->setQuoteChar('`');
        $this->assertEquals('`', $p->getValue($q));
    }
    public function testSetQuoteCharShouldThrowExceptionWithInvalidChar()
    {
        $q = new Query;

        $this->expectException('InvalidArgumentException');
        $q->setQuoteChar('!');
    }
    public function testSetQuoteCharShouldThrowExceptionWithMultipleChars()
    {
        $q = new Query;

        $this->expectException('InvalidArgumentException');
        $q->setQuoteChar('this is not a quote');
    }
    
    public function testQueryShouldResetAfterGetSql()
    {
        $q = new Query;
        $expected = 'SELECT * FROM `movies` WHERE `movies`.`year`=? AND `movies`.`rating`>? ORDER BY `movies`.`rating` DESC';
        $sql = $q->table('movies')->where('year', 2020)->where('rating', '>', 7)->orderBy('rating', 'desc')->getSql();
        $this->assertEquals($expected, $sql);
        $sql = $q->table('movies')->getSql();
        $this->assertEquals('SELECT * FROM `movies`', $sql);
    }
    
    public function testQueryDebugOutputsSql()
    {
        $q = new Query;
        $expected = "SELECT * FROM `movies` WHERE `movies`.`year`='2020' AND `movies`.`rating`>'7' ORDER BY `movies`.`rating` DESC";

        $this->expectOutputString("\nQuery debug:\n$expected\n");
        $q->table('movies')->where('year', 2020)->where('rating', '>', 7)->orderBy('rating', 'desc')->debug();
        
    }
    public function testQueryDebugHaltsExecution()
    {
        $q = new Query;
        $this->expectException('Error');
        $q->table('movies')->where('year', 2020)->where('rating', '>', 7)->orderBy('rating', 'desc')->debug(true);
    }
    
    public function testGetColumnNamesThrowsExceptionWithMismatchedColumnNames()
    {
        $this->expectException('InvalidArgumentException');
        $q = new Query;
        $data = [
            [
                'first_name' => 'Bob',
            ],
            [
                'last_name' => 'Smith',
            ],
        ];
        $sql = $q->table('contacts')->insert([
            [
                'first_name' => 'Bob',
            ],
            [
                'last_name' => 'Smith',
            ],
        ])->getSql();
        
    }

    /**
     *  @dataProvider provideInvalidSanitiseData
     */
    // public function testSanitiseShouldThrowExceptionWithInvalidChars($input)
    // {
    //     $q = new Query();
    //     $r = new ReflectionMethod(Query::class, 'sanitise');
    //     $r->setAccessible('sanitise');
    //
    //     $this->expectException('InvalidArgumentException');
    //     $r->invoke(new Query, $input);
    // }
    // public function provideInvalidSanitiseData()
    // {
    //     return [
    //         'Removes invalid char' => ['column-name'],
    //         'Removes invalid chars' => ['table.column-name+'],
    //     ];
    // }
    /**
     *  @dataProvider provideQuoteData
     */
    // public function testQuoteShouldAddBackticksCorrectly($expected, $input)
    // {
    //     $q = new Query();
    //     $r = new ReflectionMethod(Query::class, 'quote');
    //     $r->setAccessible('quote');
    //     $this->assertEquals($expected, $r->invoke(new Query, $input));
    // }
    // public function provideQuoteData()
    // {
    //     return [
    //         'Single column' => ['`column_name`', 'column_name'],
    //         'Fully qualified' => ['`table_name`.`column_name`', 'table_name.column_name'],
    //         'Triple qualified' => ['`schema_name`.`table_name`.`column_name`', 'schema_name.table_name.column_name'],
    //         'Fully qualified, quoted table' => ['`table_name`.`column_name`', '`table_name`.column_name'],
    //         'Fully qualified, quoted column' => ['`table_name`.`column_name`', 'table_name.`column_name`'],
    //     ];
    // }
    /**
     *  @dataProvider provideQualifySingleData
     */
    // public function testQualifyShouldAddTableToSingleReference($expected, $input, $from, $table)
    // {
    //     $q = new Query();
    //     $r = new ReflectionMethod(Query::class, 'qualify');
    //     $r->setAccessible('qualify');
    //     $this->assertEquals($expected, $r->invoke($q->from($from), $input, $table));
    // }
    // public function provideQualifySingleData()
    // {
    //     return [
    //         'Single column' => ['table_name.column_name', 'column_name', 'table_name', null],
    //         'Single column, with spaces' => ['table_name.column_name', ' column_name ', ' table_name ', null],
    //         'Other table, single column' => ['other_table.column_name', 'column_name', 'table_name', 'other_table'],
    //     ];
    // }
    /**
     *  @dataProvider provideQualifyWithTableData
     */
    // public function testQualifyShouldIgnoreTableWithTableColumnReference($expected, $input, $from, $table)
    // {
    //     $q = new Query();
    //     $r = new ReflectionMethod(Query::class, 'qualify');
    //     $r->setAccessible('qualify');
    //     $this->assertEquals($expected, $r->invoke($q->from($from), $input, $table));
    // }
    // public function provideQualifyWithTableData()
    // {
    //     return [
    //         'Fully qualified' => ['table_name.column_name', 'table_name.column_name', 'table_name', null],
    //         'Fully qualified, other table' => ['other_table_name.column_name', 'other_table_name.column_name', 'table_name', null],
    //         'Fully qualified, quoted table' => ['table_name.column_name', '`table_name`.column_name', 'table_name', null],
    //         'Fully qualified, quoted column' => ['table_name.column_name', 'table_name.`column_name`', 'table_name', null],
    //         'Fully qualified, with spaces' => ['table_name.column_name', 'table_name . column_name', 'table_name', null],
    //     ];
    // }
    //
    // public function provideQualifyWithSchemaTableData()
    // {
    //     return [
    //         'Single column' => ['table_name.column_name', 'column_name', 'table_name', null],
    //         'Fully qualified' => ['table_name.column_name', 'table_name.column_name', 'table_name', null],
    //         'Fully qualified, with Schema' => ['schema_name.table_name.column_name', 'schema_name.table_name.column_name', 'table_name', null],
    //         'Fully qualified, other table' => ['other_table_name.column_name', 'other_table_name.column_name', 'table_name', null],
    //         'Fully qualified, quoted table' => ['table_name.column_name', '`table_name`.column_name', 'table_name', null],
    //         'Fully qualified, quoted column' => ['table_name.column_name', 'table_name.`column_name`', 'table_name', null],
    //         'Single column, with spaces' => ['table_name.column_name', ' column_name ', ' table_name ', null],
    //         'Fully qualified, with spaces' => ['table_name.column_name', 'table_name . column_name', 'table_name', null],
    //
    //         'Other table, single column' => ['other_table.column_name', 'column_name', 'table_name', 'other_table'],
    //     ];
    // }
    //
    // public function testShouldThrowExceptionWhenFromNotSet()
    // {
    //     $this->expectException('InvalidArgumentException');
    //     $q = new Query();
    //     $q->getSql();
    // }
    //
    // public function testShouldMergeInParametersWhenGeneratingSql()
    // {
    //     $q = new Query();
    //     $q->select('name, email')->from('contacts')->where('name', 'Jo');
    //
    //     // using string paramaters
    //     $sql = 'SELECT `contacts`.`name`,`contacts`.`email` FROM `contacts` WHERE `contacts`.`name`';
    //     $this->assertEquals("$sql=?", $q->getSql());
    //     $this->assertEquals(['Jo'], $q->getParams());
    //     $this->assertEquals("$sql='Jo'", $q->getSql($q->getParams()));
    //     // using array parameters
    //
    // }
}
