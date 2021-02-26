<?php

namespace Tests\Unit;

use Phluent\DB;
use RuntimeException;


class DBAggregatesTest extends \PHPUnit\Framework\TestCase
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
        $mapper = $this->getInstance();
        $mapper->raw(file_get_contents('tests/assets/setup.sql'));
    }
    public function tearDown() : Void
    {

    }
    public function getInstance()
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

        return new \Phluent\DB($pdo);
    }


    /*
     *  getCount()
     */
    public function testGetCountShouldReturnZero()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->where('id', '>', 999)->getCount();
        $this->assertIsNumeric($result);
        $this->assertEquals(0, $result);
    }
    public function testGetCountShouldReturnPositive()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->getCount();
        $this->assertIsNumeric($result);
        $this->assertEquals(4, $result);
    }
    /*
     *  getAvg()
     */
    public function testGetAvgReturnsValue()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->getAvg('id');
        $this->assertIsNumeric($result);
        $this->assertEquals(2.5, $result);
    }
    public function testGetAvgReturnsNull()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->where('id', '>', 999)->getAvg('id');
        $this->assertNull($result);
    }
    public function testGetAvgOnDifferentColumn()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts_emails')->getAvg('contacts_id');
        $this->assertIsNumeric($result);
        $this->assertEquals(2.8, $result);
    }
    /*
     *  getMax()
     */
    public function testGetMaxReturnsValue()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->getMax('id');
        $this->assertIsNumeric($result);
        $this->assertEquals(4, $result);
    }
    public function testGetMaxReturnsNull()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->where('id', '>', 999)->getMax('id');
        $this->assertNull($result);
    }
    public function testGetMaxOnDifferentColumn()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts_emails')->getMax('contacts_id');
        $this->assertIsNumeric($result);
        $this->assertEquals(4, $result);
    }
    /*
     *  getMin()
     */
    public function testGetMinReturnsValue()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->getMin('id');
        $this->assertIsNumeric($result);
        $this->assertEquals(1, $result);
    }
    public function testGetMinReturnsNull()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->where('id', '>', 999)->getMin('id');
        $this->assertNull($result);
    }
    public function testGetMinOnDifferentColumn()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts_addresses')->getMin('contacts_id');
        $this->assertIsNumeric($result);
        $this->assertEquals(2, $result);
    }
    /*
     *  getSum()
     */
    public function testGetSumReturnsValue()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->getSum('id');
        $this->assertIsNumeric($result);
        $this->assertEquals(10, $result);
    }
    public function testGetSumReturnsNull()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts')->where('id', '>', 999)->getSum('id');
        $this->assertNull($result);
    }
    public function testGetSumOnDifferentColumn()
    {
        $db = $this->getInstance();
        $result = $db->table('contacts_emails')->getSum('contacts_id');
        $this->assertIsNumeric($result);
        $this->assertEquals(14, $result);
    }
}
