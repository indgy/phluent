<?php

namespace Tests\Unit;

use Phluent\DB;
use PDOStatement;


class DBTest extends \PHPUnit\Framework\TestCase
{
    protected $config = [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '',
        'database' => 'phluent_test',
        'username' => 'travis',
        'password' => '',
        'charset' => 'utf8mb4',
        // 'socket' => '/opt/local/var/run/mariadb-10.2/mysqld.sock',
    ];


    public function setUp() : Void
    {
        $dsn = ( ! empty($this->config['socket'])) 
            ? sprintf('%s:unix_socket=%s', $this->config['driver'], $this->config['socket']) 
            : sprintf('%s:host=%s;port=%s', $this->config['driver'], $this->config['host'], $this->config['port']);
        $dsn = sprintf("%s;dbname=%s;charset=%s", $dsn, $this->config['database'], $this->config['charset']);

        $pdo = new \PDO($dsn, $this->config['username'], $this->config['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => true,
        ]);

        $this->db = new \Phluent\DB($pdo);
        $this->db->raw(file_get_contents('tests/assets/setup.sql'));
    }
    public function tearDown() : Void
    {

    }

    public function testQueryReturnsSelf()
    {
        $this->assertInstanceOf('Phluent\DB', $this->db->query('SHOW TABLES;'));
    }
    public function testRawReturnsPDOStatement()
    {
        $stmt = $this->db->raw('SHOW TABLES;');
        $this->assertInstanceOf('\PDOStatement', $stmt);
    }
    public function testShouldThrowRuntimeExceptionWhenExecutingInvalidSql()
    {
        $this->expectException('RuntimeException');
        $this->db->query('This is not SQL');
    }
    public function testShouldThrowRuntimeExceptionWhenExecutingRawInvalidSql()
    {
        $this->expectException('RuntimeException');
        $this->db->raw('This is not SQL');
    }
    public function testShouldCountColumns()
    {
        $row = $this->db->table('contacts')->where('id', 1)->getOne();
        $this->assertEquals(10, $this->db->getColumnCount());
        $row = $this->db->table('contacts_addresses')->where('id', 1)->getOne();
        $this->assertEquals(10, $this->db->getColumnCount());
        $row = $this->db->table('contacts_emails')->where('id', 1)->getOne();
        $this->assertEquals(6, $this->db->getColumnCount());
    }
    public function testShouldCountAllRows()
    {
        $this->assertEquals(4, $this->db->table('contacts')->getCount());
    }
    public function testShouldCountFilteredRows()
    {
        $this->assertEquals(2, $this->db->table('contacts')->where('salutation', 'mr')->getCount());
        $this->assertEquals(1, $this->db->table('contacts')->where('salutation', 'mrs')->getCount());
    }
    public function testShouldCountFilteredRowsUsingWhere()
    {
        $this->assertEquals(2, $this->db->table('contacts')->where('salutation', 'mr')->getCount());
        $this->assertEquals(1, $this->db->table('contacts')->where('salutation', 'mrs')->getCount());
        $this->assertEquals(3, $this->db->table('contacts')->where('salutation', '<>', 'mrs')->getCount());
    }
    public function testShouldInsertSingleRow()
    {
        $row = [
            'first_name' => 'Pussy',
            'last_name' => 'Galore',
            'organisation' => 'GoldCo',
        ];
        $this->assertEquals(1, $this->db->table('contacts')->insert($row));
        $this->assertEquals(1, $this->db->table('contacts')->where('organisation', 'GoldCo')->getCount());
    }
    public function testShouldInsertMultipleRows()
    {
        $rows = [
            [
                'first_name' => 'Pussy',
                'last_name' => 'Galore',
                'organisation' => 'GoldCo',
            ],
            [
                'first_name' => 'Honey',
                'last_name' => 'Ryder',
                'organisation' => 'GoldCo',
            ],
            [
                'first_name' => 'Kissy',
                'last_name' => 'Suzuki',
                'organisation' => 'GoldCo',
            ]
        ];
        $this->assertEquals(3, $this->db->table('contacts')->insert($rows));
        $this->assertEquals(3, $this->db->table('contacts')->where('organisation', 'GoldCo')->getCount());
    }
    public function testShouldUpdateRow()
    {
        $row = [
            'first_name' => 'Pussy',
            'last_name' => 'Galore',
            'organisation' => 'GoldCo',
        ];
        $this->assertEquals(4, $this->db->table('contacts')->getCount());
        $this->assertEquals(1, $this->db->table('contacts')->insert($row));
        $this->assertEquals(5, $this->db->table('contacts')->getCount());
        $this->assertEquals(1, $this->db->table('contacts')->where('organisation', 'GoldCo')->getCount());
        $result = $this->db->log()->table('contacts')->where('organisation','GoldCo')->update([
            'organisation' => 'GoldenCo'
        ]);
            // result should be 1 affected row
        $this->assertEquals(1, $result);
        // there should be no matching rows now
        $this->assertEquals(0, $this->db->table('contacts')->where('organisation', 'GoldCo')->getCount());
        // there should one matching rows now
        $this->assertEquals(1, $this->db->table('contacts')->where('organisation', 'GoldenCo')->getCount());
    }
    public function testShouldUpdateRows()
    {
        $rows = [
            [
                'first_name' => 'Pussy',
                'last_name' => 'Galore',
                'organisation' => 'GoldCo',
            ],
            [
                'first_name' => 'Honey',
                'last_name' => 'Ryder',
                'organisation' => 'GoldCo',
            ],
            [
                'first_name' => 'Kissy',
                'last_name' => 'Suzuki',
                'organisation' => 'GoldCo',
            ]
        ];
        $this->assertEquals(3, $this->db->table('contacts')->insert($rows));
        $this->assertEquals(3, $this->db->table('contacts')->where('organisation', 'GoldCo')->getCount());
        $this->assertEquals(3, $this->db->table('contacts')->where('organisation', 'GoldCo')->update([
            'organisation' => 'GoldenCo'
        ]));
        $this->assertEquals(0, $this->db->table('contacts')->where('organisation', 'GoldCo')->getCount());
        $this->assertEquals(3, $this->db->table('contacts')->where('organisation', 'GoldenCo')->getCount());
    }
    public function testShouldDelete()
    {
        $this->assertEquals(1, $this->db->table('contacts')->where('id', 4)->delete());
        $this->assertEquals($this->db->table('contacts')->where('salutation','mrs')->getCount(), $this->db->table('contacts')->where('salutation','mrs')->delete());
        $this->assertEquals($this->db->table('contacts')->where('salutation','mr')->getCount(), $this->db->table('contacts')->where('salutation','mr')->delete());
    }
    public function testShouldInsertGetIdSingleRowFromObject()
    {
        $row = (object) [
            'first_name' => 'Pussy',
            'last_name' => 'Galore',
            'organisation' => 'GoldCo',
        ];
        $this->assertEquals(5, $this->db->table('contacts')->insertGetId($row));
        $this->assertEquals(1, $this->db->table('contacts')->where('organisation', 'GoldCo')->getCount());
    }
    public function testShouldThrowExceptionInsertGetIdSingleRowWithInvalidDataString()
    {
        $this->expectException('InvalidArgumentException');
        $this->assertEquals(1, $this->db->table('contacts')->insertGetId('a string'));
    }
    public function testShouldThrowExceptionInsertGetIdSingleRowWithInvalidDataIndexedArray()
    {
        $this->expectException('InvalidArgumentException');
        $this->assertEquals(1, $this->db->table('contacts')->insertGetId(['Not assoc', 'array']));
    }
    public function testShouldReturnTrueIfRowExists()
    {
        $this->assertTrue(
            $this->db->table('contacts')
                ->where('salutation', 'Ms')
                ->where('first_name', 'Fanny')
                ->getExists()
        );
    }
    public function testShouldReturnFalseIfRowDoesNotExist()
    {
        $this->assertFalse(
            $this->db->table('contacts')
                ->where('salutation', 'Ms')
                ->where('first_name', 'Xaphod')
                ->where('last_name', 'Beeblebrox')
                ->getExists()
        );
    }
    public function testShouldFetchRow()
    {
        $rows = $this->db->table('contacts')->where('salutation', 'mr')->getOne();
        $this->assertEquals(10, $this->db->getColumnCount());
    }
    public function testShouldFetchRows()
    {
        $rows = $this->db->table('contacts')->where('salutation', 'mr')->get();
        $this->assertEquals(2, count($rows));
    }
    public function testShouldLimitReturnedRows()
    {
        $rows = $this->db->table('contacts')->limit(2)->get();
        $this->assertEquals(2, count($rows));
    }
    public function testTakeShouldLimitReturnedRows()
    {
        $rows = $this->db->table('contacts')->take(2)->get();
        $this->assertEquals(2, count($rows));
    }
    public function testShouldSkipAndLimitRows()
    {
        $rows = $this->db->table('contacts')->skip(2)->limit(1)->get();
        $this->assertEquals(1, count($rows));
        $this->assertEquals('Josephine', $rows[0]->first_name);
    }
    public function testShouldSelectSpecificColumns()
    {
        $rows = $this->db->table('contacts')->select(['first_name','last_name'])->get();
        $this->assertEquals(4, count($rows));
        $this->assertEquals(2, $this->db->getColumnCount());
    }
    public function testShouldOrderByColumnAsc()
    {
        $rows = $this->db->table('contacts')->orderBy('first_name', 'asc')->get();
        $this->assertEquals(4, count($rows));
        $this->assertEquals('Bob', $rows[0]->first_name);
        $this->assertEquals('Fanny', $rows[1]->first_name);
        $this->assertEquals('Joseph', $rows[2]->first_name);
        $this->assertEquals('Josephine', $rows[3]->first_name);
    }
    public function testShouldOrderByColumnDesc()
    {
        $rows = $this->db->table('contacts')->orderBy('first_name', 'desc')->get();
        $this->assertEquals(4, count($rows));
        $this->assertEquals('Josephine', $rows[0]->first_name);
        $this->assertEquals('Joseph', $rows[1]->first_name);
        $this->assertEquals('Fanny', $rows[2]->first_name);
        $this->assertEquals('Bob', $rows[3]->first_name);
    }
    public function testShouldLogQuery()
    {
        $rows = $this->db->log()->table('contacts')->where('salutation', 'mr')->get();
        $this->assertEquals(2, count($rows));
        $this->assertIsString($this->db->getLog());
    }
    public function testShouldLogQueryFromRaw()
    {
        $stmt = $this->db->log()->raw('SELECT * FROM `contacts` WHERE `id`=2');
        $this->assertInstanceOf(PDOStatement::class, $stmt);
        $this->assertIsString($this->db->getLog());
    }

    public function testShouldThrowExceptionCallingSkipWithoutLimit()
    {
        $this->expectException('BadMethodCallException');
        $rows = $this->db->table('contacts')->skip(2)->get();
        $this->assertEquals(2, count($rows));
        $this->assertEquals('Josephine', $rows[0]['first_name']);
        $this->assertEquals('Bob', $rows[1]['first_name']);
    }

    public function testSetQuoteCharWithDblQuote()
    {
        $r = new \ReflectionClass($this->db);

        $p = $r->getProperty('quote_char');
        $p->setAccessible(true);
        $this->db->setQuoteChar('"');
        $this->assertEquals('"', $p->getValue($this->db));
    }    
    public function testSetQuoteCharWithBacktick()
    {
        $r = new \ReflectionClass($this->db);

        $p = $r->getProperty('quote_char');
        $p->setAccessible(true);
        $this->db->setQuoteChar('`');
        $this->assertEquals('`', $p->getValue($this->db));
    }    
    public function testSetQuoteCharShouldThrowExceptionWithUnsupportedVendor()
    {
        $this->expectException('RuntimeException');
        $pdo = $this->getMockBuilder('Tests\Unit\PDO')->getMock();
        $this->db = new \Phluent\DB($pdo);
    }
}

class PDO extends \PDO {
    const ATTR_DRIVER_NAME = 'unknown_vendor';
    public function __construct() {}
}
