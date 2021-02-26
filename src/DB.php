<?php

declare(strict_types=1);

namespace Phluent;

use PDO;
use PDOException;
use PDOStatement;
use Exception;
use RuntimeException;
use Throwable;
use InvalidArgumentException;


/**
 * The DB class extends the fluent query builder Query to interact the database.
 *
 * @package Phluent
 * @author Indgy <me@indgy.uk>
 */
class DB extends Query
{
    /**
     * @var PDO - The PDO connection
     */
    protected $conn;
    /**
     * @var Array - Stores the query log if enabled
     */
    protected $log = [];
    /**
     * @var Boolean - Flag to set logging on or off
     */
    protected $log_enabled;
    /**
     * @var PDOStatement - The current query instance
     */
    protected $stmt;
    /**
     * @var Integer - The current transaction nesting level
     */
    protected $transaction;


    /**
     * The DB class requires a PDO instance to your database
     *
     * @param PDO $conn 
     */
    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
        $this->reset();
        $this->setQuoteChar();
    }
    /**
     * Set the log flag to enable query logging
     *
     * @param Bool $flag 
     * @return DB
     */
    public function log() : DB
    {
        $this->log_enabled = true;
        return $this;
    }
    /**
     * Log the query
     *
     * @param string $sql 
     * @param string $params 
     * @return Void
     */
    private function logQuery($sql, $params)
    {
        if ($this->log_enabled) {
            $ts = date('Y-m-d H:i:s');
            $this->log[] = sprintf("\n%s: %s\n%s: %s", $ts, $sql, $ts, json_encode($params));
        }
    }
    /**
     * Reset to the initial state
     *
     * @return Self
     */
    protected function reset() : Query
    {
        parent::reset();

        return $this;
    }
    /**
     * Sets the quote character used in Query statements
     * Inspired by Idiorm - https://github.com/j4mie/idiorm/blob/master/idiorm.php 
     * @throws RuntimeException If the current database is not supported 
     * @return Query
     */
    public function setQuoteChar(String $char=null) : Query
    {
        if ( ! is_null($char)) {
            return parent::setQuoteChar($char);
        }

        $quote_chars = [
            // 'dblib' => '"',
            // 'firebird' => '"',
            // 'mssql' => '"',
            'mysql' => '`',
            'pgsql' => '"',
            'sqlite' => '`',
            // 'sqlite2' => '`',
            // 'sqlsrv' => '"',
            // 'sybase' => '"',
        ];
        $driver = $this->conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ( ! isset($quote_chars[$driver])) {
            throw new RuntimeException(sprintf('Unsupported driver %s', $driver));
        }

        return parent::setQuoteChar($quote_chars[$driver]);
    }
    /**
     * Returns the column count from the internal PDOStatement pointer
     *
     * @return Integer
     */
    public function getColumnCount() : Int
    {
        return $this->stmt->columnCount();
    }
    /**
     * Returns and removes the current log disabling further logging
     *
     * @return String
     */
    public function getLog() : ?String
    {
        $out = $this->log;
        $this->log = [];
        $this->log_enabled = false;
        return join("\n", $out);
    }
    /**
     * Returns the row count from the internal PDOStatement pointer
     *
     * @return Integer
     */
    public function getRowCount() : Int
    {
        return $this->stmt->rowCount();
    }
    /**
     * Returns the last insert id from the internal PDOStatement pointer
     *
     * @return Integer
     */
    public function getLastInsertId() : Int
    {
        return (int) $this->conn->lastInsertId();
    }
    /**
     * Executes a SELECT query and returning an array of rows from the internal PDOStatement pointer
     *
     * @return Array
     */
    public function get()
    {
        $this->query($this->getSql(), $this->getParams());
        return $this->stmt->fetchAll();
    }
    /**
     * Executes a SELECT query returning a single row from the internal PDOStatement pointer
     *
     * @return Object
     */
    public function getOne()
    {
        $this->limit(1);
        $this->query($this->getSql(), $this->getParams());
        return $this->stmt->fetch();
    }


    /**
     * Executes an INSERT query inserting one or more rows
     *
     * @return Integer - The number of inserted rows
     */
    public function insert($data)
    {
        $return_id = false;
        if (is_assoc($data)) {
            $return_id = true;
        }
        parent::insert($data);
        $this->query($this->getSql(), $this->getParams());

        return $this->getRowCount();
    }
    /**
     * Executes an UPDATE query, can be preceded by the where(), orderBy() and limit() methods
     *
     * @param Array<String,Mixed> $data - An array of data to update rows with
     * @return Integer - The number of affected rows
     */
    public function update(Array $data)
    {
        parent::update($data);
        $this->query($this->getSql(),  $this->getParams());

        return $this->getRowCount();
    }
    /**
     * Executes a DELETE query, can be preceded by the where(), orderBy() and limit() methods
     *
     * @return Integer - The number of affected rows
     */
    public function delete()
    {
        parent::delete();
        $this->query($this->getSql(), $this->getParams());

        return $this->getRowCount();
    }
    /**
     * Executes an INSERT query inserting one row and returning the new id
     *
     * @return Integer - The id of the inserted row
     * @throws InvalidArgumentException - If data is not object or an associative array
     */
    public function insertGetId($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }
        if ( ! is_assoc($data)) {
            throw new InvalidArgumentException('insertGetId() cannot insert non associative data, pass an object or key=>value array');
        }
        parent::insert($data);
        $this->query($this->getSql(), $this->getParams());

        return $this->getLastInsertId();
    }


    /**
     * Executes an aggregate query, can be preceded by the where() method
     * returns an Integer representing the number of matched rows
     *
     * @param String $sql - The parameterised SQL query
     * @param Array<String,Mixed> $params - Optional parameters used in where clause
     * @return Integer - The number of matched rows
     */
    protected function aggregate(String $function, String $ref=null, String $as=null)
    {
        $ref = (empty($ref)) ? '*' : $ref;
        $as = (empty($as)) ? strtolower($function) : $as;

        parent::aggregate($function, $ref, $as);

        $this->query($this->getSql(), $this->getParams());
        $row = $this->stmt->fetch();

        return (isset($row->$as)) ? $row->$as : null;
    }
    /**
     * Returns the average value of the table/column reference
     *
     * @param String $ref - The table column reference to average
     * @return Int|Float|null
     */
    public function getAvg(String $ref=null)
    {
        return $this->aggregate('avg', $ref);
    }
    /**
     * Returns the count of returned rows
     *
     * @return Int
     */
    public function getCount() : Int
    {
        return (int) $this->aggregate('count');
    }
    /**
     * Returns the distinct values from the specified column
     *
     * @return Array
     */
    public function getDistinct(String $column) : Array
    {
        return (int) $this->select[] = 'DISTINCT()';
    }
    /**
     * Returns the highest value from the table/column reference
     *
     * @param String $ref - The table column reference to check
     * @return Int|Float|null
     */
    public function getMax(String $ref=null)
    {
        return $this->aggregate('max', $ref);
    }
    /**
     * Returns the lowest value from the table/column reference
     *
     * @param String $ref - The table column reference to check
     * @return Int|Float|null
     */
    public function getMin(String $ref=null)
    {
        return $this->aggregate('min', $ref);
    }
    /**
     * Returns the total of the table/column reference
     *
     * @param String $ref - The table column reference to total
     * @return Int|Float|null
     */
    public function getSum(String $ref=null)
    {
        return $this->aggregate('sum', $ref);
    }


    /**
     * Returns true if any rows are returned, alias of getCount() returning Boolean
     * See whereExists() for EXISTS support
     *
     * @return Boolean
     */
    public function getExists() : Bool
    {
        return (bool) $this->getCount() > 0;
    }

    /**
     * Execute an SQL query binding the $params, sets the internal PDOStatement property
     * @param String $sql - The parameterised SQL query
     * @param Array<String,Mixed> $params - The parameter values to be escaped
     * @throws RuntimeException
     * @return DB
     */
    public function query(String $sql, ?Array $params=[]) : DB
    {
        try {
            if ($this->log_enabled) {
                $this->logQuery($sql, $params);
            }
            $this->stmt = $this->conn->prepare($sql);
            $this->stmt->execute($params);
            $this->stmt->setFetchMode(PDO::FETCH_OBJ);
            $this->reset();

            return $this;

        } catch (PDOException $e) {
            $this->log('error', $sql, $params, $this->conn->errorCode(), $e->getMessage());
            throw new RuntimeException(sprintf("Cannot execute query '%s', %s", $sql, $e->getMessage()));
        }
    }
    /**
     * Execute an SQL query binding the $params returning the PDOStatement class
     *
     * @param String $sql - The parameterised SQL query
     * @param Array<String,Mixed> $params - The parameter values to be escaped
     * @throws RuntimeException
     * @return PDOStatement
     */
    public function raw(String $sql, ?Array $params=[]) : PDOStatement
    {
        try {
            if ($this->log_enabled) {
                $this->logQuery($sql, $params);
            }
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            // are we sure this should be reset?
            $this->reset();

            return $stmt;

        } catch (PDOException $e) {
            $this->log('error', $sql, $params, $this->conn->errorCode(), $e->getMessage());
            throw new RuntimeException(sprintf("Cannot execute raw query '%s', %s", $sql, $e->getMessage()));
        }
    }
    /**
     * Execute the function inside a transaction
     *
     * @param Closure $function - The unit of work to carry out
     * @return Mixed - The response from the unit of work
     */
    public function transaction(Callable $function)
    {
        $this->beginTransaction();

        try {
            $result = $function($this);
            $this->commit();

            return $result;

        } catch (PDOException|Exception|Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
    /**
     * Begin a new transaction
     *
     * @return DB
     */
    public function beginTransaction() : DB
    {
        $this->transaction++;
        if ($this->transaction == 1) {
            $this->conn->beginTransaction();
        }

        return $this;
    }
    /**
     * Commit the current transaction
     *
     * @return DB
     */
    public function commit() : DB
    {
        if ($this->transaction == 1) {
            $this->conn->commit();
        }
        $this->transaction--;
        
        return $this;
    }
    /**
     * Cancel the current transaction
     *
     * @return DB
     */
    public function rollback() : DB
    {
        if ($this->transaction == 1) {
            $this->conn->rollback();
        }
        $this->transaction--;
        
        return $this;
    }
}
