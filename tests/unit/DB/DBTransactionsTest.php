<?php

namespace Tests\Unit;

use Phluent\DB;
use RuntimeException;


class DBTransactionsTest extends \PHPUnit\Framework\TestCase
{
    protected $config = [
        'driver' => 'mysql',
        'host' => '',
        'port' => '',
        'database' => 'test_db',
        'username' => 'local',
        'password' => 'password',
        'charset' => 'utf8mb4',
        'socket' => '/opt/local/var/run/mariadb-10.2/mysqld.sock',
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

    public function testShouldCommitTransaction()
    {
        $db = $this->getInstance();
        $this->assertEquals(4, $db->table('contacts')->getCount());
        $db->transaction(function() use ($db) {
            $db->table('contacts')->insert([
                'first_name' => 'Pussy',
                'last_name' => 'Galore',
                'organisation' => 'GoldCo',
            ]);
            $db->table('contacts')->insert([
                'first_name' => 'Suzuki',
                'last_name' => 'Lovely',
                'organisation' => 'GoldCo',
            ]);
        });
        $this->assertEquals(6, $db->table('contacts')->getCount());
    }
    public function testShouldRollbackTransactionOnException()
    {
        $db = $this->getInstance();
        $this->assertEquals(4, $db->table('contacts')->getCount());
        try {
            $db->transaction(function() use ($db) {
                $db->table('contacts')->insert([
                    'first_name' => 'Pussy',
                    'last_name' => 'Galore',
                    'organisation' => 'GoldCo',
                ]);
                $db->table('contacts')->insert([
                    'first_name' => 'Suzuki',
                    'last_name' => 'Lovely',
                    'organisation' => 'GoldCo',
                ]);
                throw new RuntimeException('Some error');
            });
        } catch (RuntimeException $e) {
            // do app handling here
        }

        $this->assertEquals(4, $db->table('contacts')->getCount());
    }
    public function testShouldCommitNestedTransaction()
    {
        $db = $this->getInstance();
        $this->assertEquals(4, $db->table('contacts')->getCount());
        $db->transaction(function() use ($db) {
            $db->transaction(function() use ($db) {
                $db->table('contacts')->insert([
                    'first_name' => 'Pussy',
                    'last_name' => 'Galore',
                    'organisation' => 'GoldCo',
                ]);
            });

            $db->table('contacts')->insert([
                'first_name' => 'Suzuki',
                'last_name' => 'Lovely',
                'organisation' => 'GoldCo',
            ]);

        });
        $this->assertEquals(6, $db->table('contacts')->getCount());
    }
    public function testShouldRollbackNestedTransactionOnException()
    {
        $db = $this->getInstance();
        $this->assertEquals(4, $db->table('contacts')->getCount());
        try {
            $db->transaction(function() use ($db) {
                $db->table('contacts')->insert([
                    'first_name' => 'Suzuki',
                    'last_name' => 'Lovely',
                    'organisation' => 'GoldCo',
                ]);

                $db->transaction(function() use ($db) {
                    $db->table('contacts')->insert([
                        'first_name' => 'Pussy',
                        'last_name' => 'Galore',
                        'organisation' => 'GoldCo',
                    ]);

                    $db->transaction(function() use ($db) {
                        $db->table('contacts')->insert([
                            'first_name' => 'Suzuki',
                            'last_name' => 'Lovely',
                            'organisation' => 'GoldCo',
                        ]);
                        $db->table('not_a_table')->insert([
                            'first_name' => 'Pussy',
                            'last_name' => 'Galore',
                            'organisation' => 'GoldCo',
                        ]);
                        throw new RuntimeException('Some error');
                    });
                });


            });
        } catch (RuntimeException $e) {
            
        }
        $this->assertEquals(4, $db->table('contacts')->getCount());
    }
}
