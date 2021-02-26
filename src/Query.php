<?php

declare(strict_types=1);

namespace Phluent;

use BadMethodCallException;
use InvalidArgumentException;
use Closure;

/**
 * The Query class implements a fluent SQL query builder.
 *
 * @package Phluent
 * @author Indgy <me@indgy.uk>
 */
class Query
{
    /**
     * @var Array<Int,String> - A list of characters allowed in table or column references
     */
    protected $reference_chars = [];
    /**
     * @var String - The character to use when quoting table or column references
     */
    protected $quote_char = '`';
    /**
     * @var Array<Int,String> - The list of columns to select
     */
    private $select = [];
    /**
     * @var String - The name of the table to query on
     */
    private $from = '';
    /**
     * @var Array<Int,String> - A list of join clauses
     */
    private $join = [];
    /**
     * @var Array<Int,String> - A list of where clauses
     */
    private $where = [];
    /**
     * @var Array<Int,String> - A list of group by clauses
     */
    private $group_by = [];
    /**
     * @var Array<Int,String> - A list of having clauses
     */
    private $having = [];
    /**
     * @var Array<Int,String> - A list of having clauses
     */
    private $order_by = [];
    /**
     * @var Int - The limit clause value
     */
    private $limit = 0;
    /**
     * @var Int - The offset clause value
     */
    private $offset = 0;
    /**
     * @var Boolean - Flag set if this is a SELECT DISTINCT query
     */
    private $distinct = false;
    /**
     * @var Boolean - Flag set if this is a DELETE query
     */
    private $delete = false;
    /**
     * @var Boolean - Flag set if getSql has been called
     */
    private $is_complete = false;
    /**
     * @var Boolean - Flag set if this is a TRUNCATE query
     */
    private $truncate = false;
    /**
     * @var Array<Int,Array> - Data to be used in the INSERT query
     */
    private $insert = [];
    /**
     * @var Array<Int,Array> - Data to be used in the UPDATE query
     */
    private $update = [];
    /**
     * @var Array<Int,Query> - The Query instances to be used in the UNION query
     */
    private $union = [];
    /**
     * @var Boolean - Flag set if this is a UNION ALL query
     */
    private $union_all = false;
    /**
     * @var Array<Int,Array> - List of valid logical operators
     */
    private $logicals = ['AND','OR','XOR'];
    /**
     * @var Array<Int,Array> - List of valid comparison operators
     */
    private $operators = ['=','<>','!=','>','>=','<','<=','LIKE'];



    /**
     * Adds to the list of allowed characters used in table and column references
     *
     * @param Array|String $mixed - An array or string of characters 
     * @throws InvalidArgumentException Thrown when the $mixed arg is anything other than an Array or String, or the array contains strings with more than one character 
     * @return Query
     */
    public function addReferenceChars($mixed) : Query
    {
        if (is_string($mixed)) {
            $mixed = array_unique(str_split($mixed, 1));
        }
        if (is_array($mixed)) {
            foreach ($mixed as $c) {
                if (strlen($c) > 1) {
                    throw new InvalidArgumentException('addReferenceChars() only accepts an Array of single chars, a longer string was provided');
                }
                $this->reference_chars[] = $c;
            }
            return $this;
        }

        throw new InvalidArgumentException('addReferenceChars() only accepts an Array or a String');
    }
    /**
     * Sets the character used to quote table column references.
     *
     * @param String $char - The single quote character 
     * @throws InvalidArgumentException Thrown when the $char arg is invalid
     * @return Query
     */
    public function setQuoteChar(String $char=null) : Query
    {
        if (strlen($char) <> 1) {
            throw new InvalidArgumentException('setQuoteChar() only accepts a single character');
        }
        if ( ! in_array($char, ['`', '"'])) {
            throw new InvalidArgumentException('setQuoteChar() only accepts a back-quote or double quote character');
        }
        $this->quote_char = $char;

        return $this;
    }
    /**
     * Fluent query helper to select the table columns to return
     *
     * @param Mixed $columns - The column names to select, either a comma delimited string or array of column names
     * @throws InvalidArgumentException - Thrown if $columns is not an Array or String
     * @return Query
     */
    public function select($columns) : Query
    {
        if ($this->is_complete) {
            $this->reset();
        }
        if (is_string($columns)) {
            $columns = explode(',', $columns);
        }
        if ( ! is_array($columns)) {
            throw new InvalidArgumentException('The select() method accepts a comma delimited string or array of column names');
        }
        foreach ($columns as $column) {
            $this->assertIsReference($column);
            $this->select[] = [
                'type' => 'plain',
                'value' => trim($column)
            ];
        }

        return $this;
    }
    /**
     * Fluent query helper to pass parameters directly to select clause, bypassing any sanity checks
     *
     * @param Mixed $columns - The column names to select, either a comma delimited string or array
     * @throws InvalidArgumentException - Thrown if $columns is not an Array or String
     * @return Query
     */
    public function selectRaw($columns) : Query
    {
        if ($this->is_complete) {
            $this->reset();
        }
        //TODO check operation if we're not 
        if (is_string($columns)) {
            $columns = explode(',', $columns);
        }
        if ( ! is_array($columns)) {
            throw new InvalidArgumentException('The selectRaw() method accepts a comma delimited string or array of column names');
        }
        foreach ($columns as $column) {
            // mark this as a RAW column to be ignored when generating the statement
            $this->select[] = [
                'type' => 'raw',
                'value' => $column
            ];
        }

        return $this;
    }
    /**
     * Fluent query helper to set the table name
     *
     * @param String $table - The name of the table to operate on
     * @throws InvalidArgumentException
     * @return Query
     */
    public function from(String $table) : Query
    {
        if ($this->is_complete) {
            $this->reset();
        }
        $this->assertIsReference($table);
        $this->from = $table;

        return $this;
    }
    /**
     * Fluent query helper to set the group by clause
     *
     * @param Mixed $mixed - A string or array of references to group on
     * @return Query
     */
    public function groupBy($input, String $dir=null) : Query
    {
        $columns = $this->normaliseSortArgs($input, $dir, 'groupBy');
        foreach ($columns as $column=>$dir) {
            $this->group_by[$column] = $dir;
        }

        return $this;
    }
    /**
     * Fluent query helper to set the join clause
     *
     * @param String $table - The name of the table to join
     * @param String $from_column - The column of the `from` table
     * @param String $to_column - The column of the `joined` table
     * @param String $operator - The column join clause, defaults to equals =
     * @param String $type - The join type (INNER|CROSS|OUTER|LEFT|RIGHT)
     * @return Query
     */
    public function join(String $table, String $from_column=null, String $to_column=null, String $operator=null, String $type=null)  : Query
    {
        if (empty($this->from)) {
            throw new InvalidArgumentException('The join() method requires the parent table to be set using the from() or table() methods');
        }
        if (is_null($from_column)) {
            $from_column = sprintf('%s.id', $this->from);
        }
        if (is_null($to_column)) {
            $to_column = sprintf('%s.%s_id', $table, $this->from);
        }
        // swap parameters if called shorthand
        if (in_array($to_column, $this->operators)) {
            $tmp = $operator;
            $operator = $to_column;
            $to_column = $tmp;
        }
        if (is_null($operator)) {
            $operator = '=';
        }
        $this->assertIsOperator($operator, 'join');
        // validate join type
        $type = strtoupper((string) $type);
        $type = (in_array($type, ['INNER','OUTER','LEFT','RIGHT','FULL','SELF','CROSS'])) ? "$type JOIN" : "JOIN";
        // normalise column names by removing table name
        $from_column = preg_replace("/$this->quote_char?$this->from$this->quote_char?\.?/i", '', $from_column);
        $to_column = preg_replace("/$this->quote_char?$table$this->quote_char?\.?/i", '', $to_column);
        // sanity check
        $this->assertIsReference($table);
        $this->assertIsReference($from_column);
        $this->assertIsReference($to_column);
        // populate a new join
        $this->join[] = [
            'type' => $type,
            'table' => $table,
            'from_column' => $from_column,
            'operator' => $operator,
            'to_column' => $to_column,
        ];

        return $this;
    }
    /**
     * Fluent query helper to set the limit value
     *
     * @param Integer $int - The number of rows to return
     * @return Query
     */
    public function limit(Int $int) : Query
    {
        if (preg_match('/[^0-9]/', (string) $int)) {
            throw new InvalidArgumentException("Supplied argument to limit() must be a positive integer greater than 0");
        }
        $this->limit = $int;
        return $this;
    }
    /**
     * Fluent query helper to set the offset value
     *
     * @param Integer $int - The number of rows to skip
     * @return Query
     */
    public function offset(Int $int) : Query
    {
        if (preg_match('/[^0-9]/', (string) $int)) {
            throw new InvalidArgumentException("Supplied argument to limit() must be a positive integer greater than 0");
        }
        $this->offset = $int;
        return $this;
    }
    /**
     * Fluent query helper to set the order by clause
     *
     * @param Mixed $mixed - A string or array of references to order by
     * @param String $dir - The direction to order by when $input is a single column reference
     * @return Query
     */
    public function orderBy($input, String $dir=null) : Query
    {
        $columns = $this->normaliseSortArgs($input, $dir, 'orderBy');
        foreach ($columns as $column=>$dir) {
            $this->order_by[$column] = $dir;
        }

        return $this;
    }
    /**
     * Fluent query helper to set the order by clause to RAND()
     * Note: Results may be unexpected if using with more order by clauses
     *
     * @return Query
     */
    public function orderByRand() : Query
    {
        $this->order_by[] = 'RAND()';
        return $this;
    }
    /**
     * Fluent query helper to set the offset and limit clauses, assumes page 1 is offset 0
     *
     * @param Integer $page - The offset to start from, assumes Page 1 is offset 0
     * @param Integer $limit - The number of rows to show per page
     * @return Query
     */
    public function paginate(?Int $page=0, ?Int $limit=10) : Query
    {
        if ($page <= 1) {
            return $this->limit($limit);
        }

        $page--;
        $this->offset($page * $limit);
        $this->limit($limit);

        return $this;
    }
    /**
     * Fluent query helper to set the offset value
     * Alias of offset()
     *
     * @param Integer $int - The number of rows to skip
     * @return Query
     */
    public function skip(Int $int) : Query
    {
        return $this->offset($int);
    }
    /**
     * Alias of from(), fluent query helper to set the table name
     *
     * @param String $table - The name of the table to operate on
     * @return Query
     */
    public function table(String $table) : Query
    {
        return $this->from($table);
    }
    /**
     * Fluent query helper to set the limit value
     * Alias of limit()
     *
     * @param Integer $int - The number of rows to return
     * @return Query
     */
    public function take(Int $int) : Query
    {
        return $this->limit($int);
    }


    /**
     * Fluent query helper to set simple HAVING clauses.
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param String $operator - The operator to compare with, or a value if using the shorthand signature
     * @param String $value - The value to compare on if the operator is specified
     * @param String $logic - The logical operator
     * @param Boolean $negate - Sets the NOT flag if true
     * @return Query
     */
    public function having($reference, $operator=null, $value=null, $logic=null, $negate=false) : Query
    {
        // logic defaults to AND
        $logic = (is_null($logic)) ? 'AND' : $logic;
		// operator and value may be swapped when using short syntax, set default operator to '=' 
		if (is_null($value)) {
			$value = $operator;
			$operator = '=';
		}
        // quick sanity checks
        $this->assertIsOperator($operator, 'having');
        $this->assertIsBoolean($logic, 'having');

		if ($reference instanceof Closure)
		{
            $nested = new Query();
            $nested->table($this->getFromClause());
            call_user_func_array($reference, ['q' => $nested]);
			$this->having[] = [
    			'logic' => (is_null($logic)) ? 'AND' : $logic,
                'negate' => $negate,
				'nested' => $nested,
            ];
			return $this;
		}

        $this->assertIsReference($reference);
		$this->having[] = [
			'logic' => $logic,
            'negate' => $negate,
			'reference' => $reference,
			'operator' => $operator,
			'value' => $value
        ];

		return $this;
    }
    /**
     * Fluent query helper to set simple HAVING NOT clauses.
     * Alias of having()
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param String $operator - The operator to compare with, or a value if using the shorthand signature
     * @param String $value - The value to compare on if the operator is specified
     * @return Query
     */
	public function notHaving($reference, $operator=null, $value=null) : Query
	{
		return $this->having($reference, $operator, $value, 'AND', $negate=true);
	}
    /**
     * Fluent query helper to set simple HAVING x OR y clauses.
     * Alias of having()
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param String $operator - The operator to compare with, or a value if using the shorthand signature
     * @param String $value - The value to compare on if the operator is specified
     * @return Query
     */
	public function orHaving($reference, $operator=null, $value=null) : Query
	{
		return $this->having($reference, $operator, $value, 'OR');
	}
    /**
     * Fluent query helper to set simple HAVING x OR NOT y clauses.
     * Alias of having()
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param String $operator - The operator to compare with, or a value if using the shorthand signature
     * @param String $value - The value to compare on if the operator is specified
     * @return Query
     */
	public function orNotHaving($reference, $operator=null, $value=null) : Query
	{
		return $this->having($reference, $operator, $value, 'OR', $negate=true);
	}


    /**
     * Fluent query helper to set raw WHERE clauses.
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param Array $params - The parameter values
     * @param String $logic - The logical operator
     * @param Boolean $negate - Sets the NOT flag if true
     * @return Query
     */
    public function whereRaw(String $str, Array $params=[], $logic=null, $negate=false) : Query
    {
        // logic defaults to AND
        $logic = (is_null($logic)) ? 'AND' : $logic;
        // quick sanity checks
        $this->assertIsBoolean($logic, 'where');

		$this->where[] = [
            'type' => 'raw',
			'logic' => $logic,
            'negate' => $negate,
			'content' => $str,
            'value' => $params,
        ];

		return $this;
    }
    public function whereNotRaw(String $str, Array $params=[]) : Query
    {
        return $this->whereRaw($str, $params, 'AND', true);
    }
    public function orWhereRaw(String $str, Array $params=[]) : Query
    {
        return $this->whereRaw($str, $params, 'OR');
    }
    public function orWhereNotRaw(String $str, Array $params=[]) : Query
    {
        return $this->whereRaw($str, $params, 'OR', true);
    }

    /**
     * Fluent query helper to set simple WHERE clauses.
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param String $operator - The operator to compare with, or a value if using the shorthand signature
     * @param String $value - The value to compare on if the operator is specified
     * @param String $logic - The logical operator
     * @param Boolean $negate - Sets the NOT flag if true
     * @return Query
     */
    public function where($reference, $operator=null, $value=null, $logic=null, $negate=false) : Query
    {
        // logic defaults to AND
        $logic = (is_null($logic)) ? 'AND' : $logic;
		// operator and value may be swapped when using short syntax, set default operator to '=' 
		if (is_null($value)) {
			$value = $operator;
			$operator = '=';
		}
        // quick sanity checks
        $this->assertIsOperator($operator, 'where');
        $this->assertIsBoolean($logic, 'where');

		if ($reference instanceof Closure)
		{
            $nested = new Query();
            $nested->table($this->getFromClause());
            call_user_func_array($reference, ['q' => $nested]);
			$this->where[] = [
    			'logic' => (is_null($logic)) ? 'AND' : $logic,
                'type' => "",
                'negate' => $negate,
				'nested' => $nested,
            ];
			return $this;
		}

        $this->assertIsReference($reference);
		$this->where[] = [
            'type' => "",
			'logic' => $logic,
            'negate' => $negate,
			'reference' => $reference,
			'operator' => $operator,
			'value' => $value
        ];

		return $this;
    }
    /**
     * Fluent query helper to set simple WHERE NOT clauses.
     * Alias of where()
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param String $operator - The operator to compare with, or a value if using the shorthand signature
     * @param String $value - The value to compare on if the operator is specified
     * @return Query
     */
	public function whereNot($reference, $operator=null, $value=null) : Query
	{
		return $this->where($reference, $operator, $value, 'AND', $negate=true);
	}
    /**
     * Fluent query helper to set simple WHERE x OR y clauses.
     * Alias of where()
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param String $operator - The operator to compare with, or a value if using the shorthand signature
     * @param String $value - The value to compare on if the operator is specified
     * @return Query
     */
	public function orWhere($reference, $operator=null, $value=null) : Query
	{
		return $this->where($reference, $operator, $value, 'OR');
	}
    /**
     * Fluent query helper to set simple WHERE x OR NOT y clauses.
     * Alias of where()
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param String $operator - The operator to compare with, or a value if using the shorthand signature
     * @param String $value - The value to compare on if the operator is specified
     * @return Query
     */
	public function orWhereNot($reference, $operator=null, $value=null) : Query
	{
		return $this->where($reference, $operator, $value, 'OR', $negate=true);
	}



    /**
     * Fluent query helper to set simple WHERE x BETWEEN a AND b clauses
     *
     * @param string $reference - The database column to operate on
     * @param string $min - The lower bound
     * @param string $max - The upper bound
     * @param string $logic - The logical operator
     * @param string $negate - Sets the NOT flag if true
     * @return Query
     */
	public function whereBetween($reference, $min, $max=null, $logic=null, $negate=false) : Query
	{
		if (is_array($min)) {
		    if ($max !== null OR count($min) !== 2 OR is_assoc($min)) {
    		    throw new InvalidArgumentException('The whereBetween() method requires an array with two elements');
    		}
			$max = $min[1];
			$min = $min[0];
		}
        // logic defaults to AND
        $logic = (is_null($logic)) ? 'AND' : $logic;
        // quick sanity checks
        $this->assertIsBoolean($logic, 'whereBetween');
        $this->assertIsReference($reference);

        $this->where[] = [
            'type' => 'between',
            'logic' => $logic,
            'negate' => $negate,
            'reference' => $reference,
            'operator' => null,
            'value' => [$min, $max],
        ];

		return $this;
	}
    /**
     * Fluent query helper to set simple WHERE x NOT BETWEEN a AND b clauses
     *
     * @param string $reference - The database column to operate on
     * @param string $min - The lower bound
     * @param string $max - The upper bound
     * @return Query
     */
	public function whereNotBetween($reference, $min, $max=null) : Query
	{
		return $this->whereBetween($reference, $min, $max, 'AND', true);
	}
    /**
     * Fluent query helper to set simple WHERE x OR BETWEEN a AND b clauses
     *
     * @param string $reference - The database column to operate on
     * @param string $min - The lower bound
     * @param string $max - The upper bound
     * @return Query
     */
	public function orWhereBetween($reference, $min, $max=null) : Query
	{
		return $this->whereBetween($reference, $min, $max, 'OR');
	}
    /**
     * Fluent query helper to set simple WHERE x OR NOT BETWEEN a AND b clauses
     *
     * @param string $reference - The database column to operate on
     * @param string $min - The lower bound
     * @param string $max - The upper bound
     * @return Query
     */
	public function orWhereNotBetween($reference, $min, $max=null) : Query
	{
		return $this->whereBetween($reference, $min, $max, 'OR', true);
	}



    /**
     * Fluent query helper to set matching column clauses
     *
     * @param String $ref_1 - The reference to compare
     * @param String $operator - The operator to compare with
     * @param String $ref_2 - The reference to compare
     * @return Query
     */
    public function whereColumn($ref_1, $operator=null, $ref_2=null) : Query
    {
        // handle short syntax whereColumn('a','b'), becomes whereColumn('a','=','b')
        if (is_null($ref_2)) {
            $ref_2 = $operator;
            $operator = '=';
        }
        // quick sanity checks
        $this->assertIsOperator($operator, 'whereColumn');
        $this->assertIsReference($ref_1);
        $this->assertIsReference($ref_2);

		$this->where[] = [
            'type' => 'column',
            'logic' => 'AND',
            'negate' => false,
			'ref_1' => $ref_1,
			'operator' => $operator,
			'ref_2' => $ref_2,
        ];
        
        return $this;
    }



    public function whereExists(Callable $callable, $logic=null, $negate=false) : Query
    {
        // logic defaults to AND
        $logic = (is_null($logic)) ? 'AND' : $logic;
        // quick sanity checks
        $this->assertIsBoolean($logic, 'whereExists');

        $nested = new Query();
        $nested->table($this->getFromClause());
        call_user_func_array($callable, ['q' => $nested]);
		$this->where[] = [
			'logic' => (is_null($logic)) ? 'AND' : "$logic",
            'type' => 'exists',
            'negate' => $negate,
			'nested' => $nested,
        ];

		return $this;
    }
    public function whereNotExists(Callable $callable) : Query
    {
		return $this->whereExists($callable, 'AND', true);
    }
    public function orWhereExists(Callable $callable) : Query
    {
		return $this->whereExists($callable, 'OR');
    }
    public function orWhereNotExists(Callable $callable) : Query
    {
		return $this->whereExists($callable, 'OR', true);
    }



    /**
     * Fluent query helper to set WHERE IN clauses.
     *
     * @param String $reference - The database column to operate on, or a callable nested query
     * @param Array<Int,Mixed>|Callable $values - The values or a subquery to match against
     * @param String $logic - The logical operator
     * @param Boolean $negate - Sets the NOT flag if true
     * @return Query
     */
	public function whereIn(String $reference, $values, $logic=null, $negate=false) : Query
	{
        // logic defaults to AND
        $logic = (is_null($logic)) ? 'AND' : $logic;
        // quick sanity checks
        if ( ! is_array($values) && ! $values instanceof Closure) {
            throw new InvalidArgumentException('whereIn() expects a callable or an array of values to match against');
        }
        $this->assertIsBoolean($logic, sprintf('whereIn'));

        // if values is callable, attach the Query object from callable
        if ($values instanceof Closure) {
            $sub = new Query();
            call_user_func_array($values, ['q' => $sub]);
            $values = $sub;
        }
        $this->where[] = array(
            'type' => 'in',
            'logic' => $logic,
            'negate' => $negate,
            'reference' => $reference,
            'operator' => null,
            'value' => $values
        );

        return $this;
    }
    /**
     * Fluent query helper to set WHERE NOT IN clauses.
     * Alias of whereIn()
     *
     * @param String $reference - The database column to operate on
     * @param Array<Int,Mixed>|String $values - The values to operate with
     * @return Query
     */
    public function whereNotIn($reference, $array) : Query
    {
		return $this->whereIn($reference, $array, 'AND', true);
	}
    /**
     * Fluent query helper to set WHERE x OR IN clauses.
     * Alias of whereIn()
     *
     * @param String $reference - The database column to operate on
     * @param Array<Int,Mixed>|String $values - The values to operate with
     * @return Query
     */
	public function orWhereIn($reference, $array) : Query
	{
		return $this->whereIn($reference, $array, 'OR');
	}
    /**
     * Fluent query helper to set WHERE x OR NOT IN clauses.
     * Alias of whereIn()
     *
     * @param String $reference - The database column to operate on
     * @param Array<Int,Mixed>|String $values - The values to operate with
     * @return Query
     */
	public function orWhereNotIn($reference, $array) : Query
	{
		return $this->whereIn($reference, $array, 'OR', true);
	}



    /**
     * Fluent query helper to set WHERE x IS NULL clauses.
     *
     * @param String $reference - The database column to operate on
     * @param String $logic - The logical operator
     * @param Boolean $negate - Sets the NOT flag if true
     * @return Query
     */
	public function whereNull($reference, $logic=null, $negate=false) : Query
	{
        // logic defaults to AND
        $logic = (is_null($logic)) ? 'AND' : $logic;
        // quick sanity checks
        $this->assertIsBoolean($logic, 'where');

        // Boolean, field, operator
        // (null|AND|OR) (field) IS (NULL|NOT NULL)
        //TODO this should handle closures
		$this->where[] = array(
            'type' => 'null',
			'logic' => $logic,
            'negate' => $negate,
			'reference' => $reference,
			'operator' => 'NULL',
		);

		return $this;
	}
    /**
     * Fluent query helper to set AND WHERE x IS NOT NULL clauses.
     * Alias of whereNull
     *
     * @param String $reference - The database column to operate on
     * @return Query
     */
	public function whereNotNull($reference) : Query
	{
		return $this->whereNull($reference, "AND", true);
	}
    /**
     * Fluent query helper to set WHERE .. OR x IS NULL clauses.
     * Alias of whereNull
     *
     * @param String $reference - The database column to operate on
     * @return Query
     */
	public function orWhereNull($reference) : Query
	{
		return $this->whereNull($reference, 'OR');
	}
    /**
     * Fluent query helper to set WHERE .. OR x IS NOT NULL clauses.
     * Alias of whereNull
     *
     * @param String $reference - The database column to operate on
     * @return Query
     */
	public function orWhereNotNull($reference) : Query
	{
		return $this->whereNull($reference, 'OR', true);
	}


    /**
     * Sets a where clause to a function()
     *
     * @param String $function - The name of the database function 
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @param String $logic - The logical operator
     * @return Query
     */
    private function whereFunc(String $function, String $reference, String $operator, String $value=null, String $logic=null) : Query
    {
        // logic defaults to AND
        $logic = (is_null($logic)) ? 'AND' : $logic;
        // sanity check
        $this->assertIsReference($reference);
        $this->assertIsBoolean($logic, sprintf('where%s', ucfirst($function))); //TODO missing logic
		// operator and value may be swapped when using short syntax, set default operator to '=' 
		if (is_null($value)) {
			$value = $operator;
			$operator = '=';
		}
		$this->where[] = [
            'type' => 'function',
			'function' => $function,
            'logic' => $logic,
            'reference' => $reference,
			'operator' => $operator,
			'value' => $value
        ];

        return $this;
    }
    /**
     * Shortcut to the TIME() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereTime(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('time', $reference, $operator, $value);
    }
    /**
     * Shortcut to the TIME() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereTime(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('time', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the HOUR() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereHour(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('hour', $reference, $operator, $value);
    }
    /**
     * Shortcut to the HOUR() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereHour(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('hour', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the MINUTE() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereMinute(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('minute', $reference, $operator, $value);
    }
    /**
     * Shortcut to the MINUTE() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereMinute(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('minute', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the DATE() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereDate(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('date', $reference, $operator, $value);
    }
    /**
     * Shortcut to the DATE() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereDate(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('date', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the DAY() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereDay(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('day', $reference, $operator, $value);
    }
    /**
     * Shortcut to the DAY() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereDay(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('day', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the MONTH() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereMonth(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('month', $reference, $operator, $value);
    }
    /**
     * Shortcut to the MONTH() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereMonth(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('month', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the YEAR() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereYear(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('year', $reference, $operator, $value);
    }
    /**
     * Shortcut to the YEAR() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereYear(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('year', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the WEEK() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereWeek(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('week', $reference, $operator, $value);
    }
    /**
     * Shortcut to the WEEK() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereWeek(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('week', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the WEEKDAY() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereWeekday(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('weekday', $reference, $operator, $value);
    }
    /**
     * Shortcut to the WEEKDAY() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereWeekday(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('weekday', $reference, $operator, $value, 'OR');
    }
    /**
     * Shortcut to the QUARTER() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function whereQuarter(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('quarter', $reference, $operator, $value);
    }
    /**
     * Shortcut to the QUARTER() function
     *
     * @param String $reference - The table/column reference to operate on
     * @param String $operator - The comparison operator
     * @param String $value - The value to match
     * @return Query
     */
    public function orWhereQuarter(String $reference, String $operator, String $value=null) : Query
    {
        return $this->whereFunc('quarter', $reference, $operator, $value, 'OR');
    }


    /*
     *  Fluent aggregates
     */


    protected function aggregate(String $function, String $ref=null, String $as=null)
    {
        $ref = (is_null($ref)) ? '*' : $ref;
        $as = (is_null($as)) ? strtolower($function) : $as;
        $this->assertIsReference($ref);
        $this->assertIsReference($as);

        $this->select[] = [
            'type' => 'aggregate',
            'function' => $function,
            'ref' => $ref,
            'as' => $as
        ];

        return $this;
    }
    public function avg(String $ref=null, String $as=null)
    {
        return $this->aggregate('avg', $ref, $as);
    }
    public function count(String $ref=null, String $as=null)
    {
        return $this->aggregate('count', $ref, $as);
    }
    public function max(String $ref=null, String $as=null)
    {
        return $this->aggregate('max', $ref, $as);
    }
    public function min(String $ref=null, String $as=null)
    {
        return $this->aggregate('min', $ref, $as);
    }
    public function sum(String $ref=null, String $as=null)
    {
        return $this->aggregate('sum', $ref, $as);
    }


    public function distinct() : Query
    {
        $this->distinct = true;
        return $this;
    }


    /**
     * Fluent query method to create an DELETE query.
     *
     * @return Query
     */
    public function delete()
    {
        $this->delete = true;

        return $this;
    }
    /**
     * Fluent query method to create an INSERT query.
     *
     * @param Array|Object $data - The data to insert, either an associative array, object or array of multiple instances of either
     * @return Query
     */
    public function insert($data)
    {
        // handle array of object by casting all to array
        if (is_array($data) && isset($data[0]) && is_object($data[0])) {
            $data = array_map(function($item) {
                return (array) $item;
            }, $data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (is_assoc($data)) {
            $data = [$data];
        }
        $this->insert = $data;

        return $this;
    }
    /**
     * Fluent query method to create a TRUNCATE query.
     *
     * @return Query
     */
    public function truncate()
    {
        $this->truncate = true;

        return $this;
    }
    /**
     * Fluent query method to create an UPDATE query.
     *
     * @param Array $data - The data to update
     * @return Query
     */
    public function update(Array $data)
    {
        if (is_assoc($data)) {
            $data = [$data];
        }
        $this->update = $data;

        return $this;
    }
    /**
     * Fluent query method to create a UNION query.
     *
     * @return Query
     * @throws BadMethodCallException If called more than once
     * @throws InvalidArgumentException If argument is not instance of Query
     */
    public function union()
    {
        if ( ! empty($this->union)) {
            throw new BadMethodCallException('Cannot call union() more than once, instead pass your Query instances in as arguments');
        }
        // best to check this here rather than getType()
        foreach (['select', 'insert', 'update', 'delete', 'truncate'] as $method) {
            if ( ! empty($this->$method)) {
                throw new BadMethodCallException("Cannot mix query types, union() called but $method() was called previously");
            }
        } 
        
        $this->union[] = clone $this;
        foreach (func_get_args() as $arg) {
            if ( ! $arg instanceof Query) {
                throw new InvalidArgumentException('Arguments to union() must be instances of Query');
            }
            $this->union[] = $arg;
        }

        return $this;
    }
    /**
     * Fluent query method to create a UNION ALL query.
     *
     * @return Query
     * @throws BadMethodCallException If called more than once
     * @throws InvalidArgumentException If argument is not instance of Query
     */
    public function unionAll()
    {
        $this->union_all = true;
        return call_user_func_array([$this, 'union'], func_get_args());
    }


    /**
     * Returns the SQL representing the query, optionally populated with the contents of $params
     * Note: The provided params are unescaped and insecure, best used for debugging only 
     *
     * @param Array $params - An optional array of parameters to include in the SQL statement
     * @return String
     */
    public function getSql(Array $params=[]) : String
    {
        switch ($this->getType())
        {
            case 'delete':
            $str = sprintf(
                'DELETE FROM %s%s%s%s',
                $this->getFromClause(),
                $this->getWhereClause(),
                $this->getOrderByClause(),
                $this->getLimitClause()
            );
            break;

            case 'truncate':
            $str = sprintf(
                'TRUNCATE TABLE %s',
                $this->getFromClause()
            );
            break;

            case 'insert':
            $str = sprintf(
                'INSERT INTO %s (%s) VALUES %s',
                $this->getFromClause(),
                join(',', $this->getColumnNames($this->insert)),
                join(',', $this->getPlaceChars($this->insert))
            );
            break;

            case 'update':
            $str = sprintf(
                'UPDATE %s SET %s%s%s%s',
                $this->getFromClause(),
                join(',', $this->getSetClause($this->update)),
                $this->getWhereClause(),
                $this->getOrderByClause(),
                $this->getLimitClause()
            );
            break;

            case 'union':
            $clause = ($this->union_all) ? 'UNION ALL' : 'UNION';
            $str = trim(sprintf(
                "%s\n%s\n%s",
                join("\n$clause\n", array_map(function($q){
                    return sprintf('(%s)', $q->getSql());
                }, $this->union)),
                (is_string($this->getOrderByClause())) ? trim($this->getOrderByClause()) : null,
                (is_string($this->getLimitClause())) ? trim($this->getLimitClause()) : null
            ));
            break;

            case 'select':
            $str = sprintf(
                'SELECT %s%s FROM %s%s%s%s%s%s%s',
                (true === $this->distinct) ? 'DISTINCT ' : null,
                $this->getSelectClause(),
                $this->getFromClause(),
                $this->getJoinClause(),
                $this->getWhereClause(),
                $this->getGroupByClause(),
                $this->getHavingClause(),
                $this->getOrderByClause(),
                $this->getLimitClause()
            );
            break;
        }

        // If params are provided then replace placeholders with values, used with debug methods
        if ( ! empty($params))
        {
            $out = '';
            // explode the SQL statement on placeholders
            $parts = explode('?', $str);
            
            // join the SQL statement back together
            for ($i=0; $i<count($parts); $i++) {
                $out.= (isset($params[$i])) ? sprintf("%s'%s'", $parts[$i], $params[$i]) : $parts[$i];
            }

            return $out;
        }

        // set complete flag
        $this->is_complete = true;

        return $str;
    }
    /**
     * Returns the query parameters indexed and ready to be passed to PDO
     *
     * @return Array<Int,Mixed>
     */
    public function getParams() : Array
    {
        $out = [];
        switch ($this->getType())
        {
            case 'delete':
                $out = array_merge($out, $this->getWhereParams());
                break;

            case 'insert':
                $out = array_merge($out, $this->getInsertParams());
                break;

            case 'update':
                $out = array_merge($out, $this->getUpdateParams());
                $out = array_merge($out, $this->getWhereParams());
                break;

            case 'union':
                foreach ($this->union as $query) {
                    $out = array_merge($out, $query->getWhereParams());
                    $out = array_merge($out, $query->getHavingParams());
                }
                break;

            default:
                $out = array_merge($out, $this->getWhereParams());
                $out = array_merge($out, $this->getHavingParams());
                break;
        }

        return $out;
    }


    /**
     * Resets the internal variables back to empty state
     *
     * @return Query
     */
    protected function reset() : Query
    {
        $this->select = [];
        if ( ! $this instanceof Mapper) {
            // when using the Mapper we keep the Entity table name
            $this->from = null;
        }
        $this->join = [];
        $this->where = [];
        $this->group_by = [];
        $this->having = [];
        $this->order_by = [];
        $this->limit = null;
        $this->offset = null;

        $this->distinct = false;
        $this->delete = false;
        $this->insert = [];
        $this->update = [];
        $this->union = [];
        $this->union_all = false;

        $this->is_complete = false;

        return $this;
    }
    /**
     * Outputs the current SQL statement, optionally stopping execution
     *
     * @param Boolean $stop - If true execution  will stop after outputting the SQL statement
     * @return Query
     */
    public function debug(Bool $stop=false) : Query
    {
        $out = sprintf("\nQuery debug:\n%s\n", $this->getSql($this->getParams()));
        if ($stop) {
            throw new \Error($out);
        }
        echo $out;

        return $this;
    }


    private function getInsertParams() : Array
    {
        $out = [];
        foreach ($this->insert as $a) {
            $out = array_merge($out, array_values($a));
        }

        return $out;
    }
    private function getUpdateParams() : Array
    {
        $out = [];
        foreach ($this->update as $a) {
            $out = array_merge($out, array_values($a));
        }

        return $out;
    }
    private function getWhereParams() : Array
    {
        $out = [];
        foreach ($this->where as $clause) {
            if (isset($clause['nested'])) {
                $out = array_merge($out, $clause['nested']->getParams());
            } elseif (isset($clause['value'])) {
                if (is_array($clause['value'])) {
                    $out = array_merge($out, array_values($clause['value']));
                } elseif ($clause['value'] instanceof Query) {
                    // TODO FIXME this may cause a bug where the parameter order may be wrong
                    // though not found in testing
                    $out = array_merge($out, $clause['value']->getParams());
                } else {
                    $out[] = $clause['value'];
                }
            }
        }

        return $out;
    }
    private function getHavingParams() : Array
    {
        $out = [];
        foreach ($this->having as $clause) {
            if (isset($clause['nested'])) {
                $out = array_merge($out, $clause['nested']->getParams());
            } elseif (isset($clause['value'])) {
                $out[] = $clause['value'];
            }
        }

        return $out;
    }
    /**
     * Returns the type of SQL statement to generate
     *
     * @return String
     */
    private function getType() : String
    {
        if ($this->delete) {
            foreach (['select', 'insert', 'update', 'truncate'] as $method) {
                if ( ! empty($this->$method)) {
                    throw new BadMethodCallException("Cannot mix query types, delete() called but $method() was called previously");
                }
            } 
            return 'delete';
        }
        if ($this->truncate) {
            foreach (['select', 'insert', 'update', 'delete'] as $method) {
                if ( ! empty($this->$method)) {
                    throw new BadMethodCallException("Cannot mix query types, truncate() called but $method() was called previously");
                }
            } 
            return 'truncate';
        }
        if ( ! empty($this->insert)) {
            foreach (['select', 'update', 'delete', 'truncate'] as $method) {
                if ( ! empty($this->$method)) {
                    throw new BadMethodCallException("Cannot mix query types, insert() called but $method() was called previously");
                }
            } 
            return 'insert';
        }
        if ( ! empty($this->update)) {
            foreach (['select', 'insert', 'delete', 'truncate'] as $method) {
                if ( ! empty($this->$method)) {
                    throw new BadMethodCallException("Cannot mix query types, update() called but $method() was called previously");
                }
            } 
            return 'update';
        }
        if ( ! empty($this->union)) {
            // moved checks into union() method
            return 'union';
        }

        return 'select';
    }
    /**
     * Returns the columns to SELECT or *
     *
     * @return String
     */
    private function getSelectClause() : String
    {
        if (empty($this->select)) {
            return '*';
        }
        $out = [];
        foreach ($this->select as $column)
        {
            switch ($column['type'])
            {
                case 'plain':
                    $out[] = $this->quote($this->qualify($column['value']));
                    break;
                case 'raw':
                    $out[] = $column['value'];
                    break;
                case 'aggregate':
                    $out[] = sprintf(
                        '%s(%s) AS %s', 
                        strtoupper($column['function']),
                        ($column['ref'] == '*') ? '*' : $this->quote($this->qualify($column['ref'])),
                        $this->quote($column['as'])
                    );
                    break;
            }
        }

        return join(',', $out);
    }
    /**
     * Returns the FROM clause
     *
     * @return String
     */
    private function getFromClause() : String
    {
        if (empty($this->from)) {
            throw new InvalidArgumentException('Cannot construct query without a table name, please set using the from() or table() methods');
        }
        return $this->quote($this->from);
    }
    /**
     * Returns the JOIN clause(s)
     *
     * @return String|null
     */
    private function getJoinClause() : ?String
    {
        if (empty($this->join)) {
            return null;
        }

        $joins = [];
        foreach ($this->join as $join)
        {
            $joins[] = sprintf(
                ' %s %s ON %s%s%s',
                $join['type'],
                $this->quote($join['table']),
                $this->quote($this->qualify($join['from_column'])),
                $join['operator'],
                $this->quote($this->qualify($join['to_column'], $join['table']))
            );
        }

        return join("\n", $joins);
    }
    /**
     * Returns the WHERE clause(s)
     *
     * @return String|null
     */
    private function getWhereClause($nested=false) : ?String
    {
        if (empty($this->where)) {
            return null;
        }

        $out = [];
        $first = true;  // track first loop as we do not add logic to the first clause
        foreach ($this->where as $clause)
        {
            if (isset($clause['nested']))
            {
                switch (strtoupper($clause['type']))
                {
                    case 'EXISTS':
                    $out[] = sprintf(
                        '%s%s%s (%s)',
                        ($first) ? null : $clause['logic'],
                        (true === $clause['negate']) ? ' NOT' : null,
                        ($clause['type']) ? sprintf(' %s', strtoupper($clause['type'])) : null,
                        $clause['nested']->getSql()
                    );
                    break;

                    // case 'SUBQUERY':
                    // $out[] = sprintf(
                    //     '%s%s (%s)',
                    //     ($first) ? null : $clause['logic'],
                    //     (true === $clause['negate']) ? ' NOT' : null,
                    //     $clause['nested']->getSql()
                    // );
                    // break;

                    default:
                    $out[] = sprintf(
                        '%s%s (%s)',
                        ($first) ? null : $clause['logic'],
                        (true === $clause['negate']) ? ' NOT' : null,
                        $clause['nested']->getWhereClause(true)
                    );
                }
            }
            else
            {
                switch($clause['type'])
                {
                    case 'null':
                    $out[] = trim(sprintf(
                        '%s %s IS %s%s',
                        ($first) ? null : $clause['logic'],
                        $this->quote($this->qualify($clause['reference'])),
                        (true === $clause['negate']) ? 'NOT ' : null,
                        $clause['operator']
                    ));
                    break;

                    case 'between':
                    $out[] = trim(sprintf(
                        '%s%s%s BETWEEN ? AND ?',
                        ($first) ? null : $clause['logic'].' ',
                        $this->quote($this->qualify($clause['reference'])),
                        (true === $clause['negate']) ? ' NOT' : null,
                        $this->quote($this->qualify($clause['reference']))
                    ));
                    break;

                    case 'column':
                    $out[] = trim(sprintf(
                        '%s%s%s%s',
                        ($first) ? null : $clause['logic'].' ',
                        $this->quote($this->qualify($clause['ref_1'])),
                        (strlen($clause['operator']) > 2) ? ' '.strtoupper($clause['operator']).' ' : $clause['operator'],
                        $this->quote($this->qualify($clause['ref_2']))
                    ));
                    break;

                    case 'function':
                    $out[] = trim(sprintf(
                        '%s %s(%s)%s%s',
                        ($first) ? null : $clause['logic'],
                        strtoupper($clause['function']),
                        $this->quote($this->qualify($clause['reference'])),
                        (strlen($clause['operator']) > 2) ? ' '.strtoupper($clause['operator']).' ' : $clause['operator'],
                        '?'
                    ));
                    break;

                    case 'in':
                    // the clause value may contain an array or a Query object
                    $value = null;
                    if (is_array($clause['value'])) {
                        $value = join(',', $this->getPlaceChars($clause['value']));
                    } else {
                        $value = $clause['value']->getSql();
                    }
                    $out[] = trim(sprintf(
                        '%s%s%s IN (%s)',
                        ($first) ? null : $clause['logic'].' ',
                        $this->quote($this->qualify($clause['reference'])),
                        (true === $clause['negate']) ? ' NOT' : null,
                        $value
                    ));
                    break;

                    case 'raw':
                    $out[] = trim(sprintf(
                        '%s%s%s',
                        ($first) ? null : $clause['logic'].' ',
                        (true === $clause['negate']) ? 'NOT ' : null,
                        $clause['content']
                    ));
                    break;

                    default:
                    $out[] = trim(sprintf(
                        '%s%s%s%s%s',
                        ($first) ? null : $clause['logic'].' ',
                        (true === $clause['negate']) ? 'NOT ' : null,
                        $this->quote($this->qualify($clause['reference'])),
                        (strlen($clause['operator']) > 2) ? ' '.strtoupper($clause['operator']).' ' : $clause['operator'],
                        '?'
                    ));
                }
            }
            $first = false;
        }

        return ($nested) ? join(' ', $out) : sprintf(' WHERE %s', trim(join(' ', $out)));
    }
    /**
     * Returns the GROUP BY clause 
     *
     * @return String|null
     */
    private function getGroupByClause() : ?String
    {
        if (empty($this->group_by)) {
            return null;
        }
        // format clauses
        $out = [];
        foreach ($this->group_by as $k=>$v) {
            $out[] = trim(sprintf(
                // e.g. table.column ASC
                '%s %s', 
                $this->quote($this->qualify($k)),
                $v
            ));
        }

        return sprintf(' GROUP BY %s', join(',', $out));
    }
    /**
     * Returns the HAVING clause(s)
     *
     * @return String|null
     */
    private function getHavingClause($nested=false) : ?String
    {
        if (empty($this->having)) {
            return null;
        }

        $out = [];
        $first = true;  // track first loop as we do not add logic to the first clause
        foreach ($this->having as $clause)
        {
            if (isset($clause['nested']))
            {
                $out[] = sprintf(
                    '%s%s (%s)',
                    ($first) ? null : $clause['logic'],
                    (true === $clause['negate']) ? ' NOT' : null,
                    $clause['nested']->getHavingClause(true)
                );
            }
            else
            {
                $out[] = trim(sprintf(
                    '%s%s%s%s%s',
                    ($first) ? null : $clause['logic'].' ',
                    (true === $clause['negate']) ? 'NOT ' : null,
                    $this->quote($this->qualify($clause['reference'])),
                    (strlen($clause['operator']) > 2) ? ' '.strtoupper($clause['operator']).' ' : $clause['operator'],
                    '?'
                ));
            }
            $first = false;
        }

        return ($nested) ? join(' ', $out) : sprintf(' HAVING %s', trim(join(' ', $out)));
    }
    /**
     * Returns the ORDER BY clause 
     *
     * @return String|null
     */
    private function getOrderByClause() : ?String
    {
        if (empty($this->order_by)) {
            return null;
        }
        // format clauses
        $out = [];
        foreach ($this->order_by as $k=>$v) {
            if ($v == 'RAND()') {
                $out[] = 'RAND()';
            } else {
                $out[] = trim(sprintf(
                    // e.g. table.column ASC
                    '%s %s', 
                    $this->quote($this->qualify($k)),
                    $v
                ));
            }
        }

        return sprintf(' ORDER BY %s', join(',', $out));
    }
    /**
     * Returns the LIMIT and OFFSET clauses 
     *
     * @throws BadMethodCallException
     * @return String|null
     */
    private function getLimitClause() : ?String
    {
        // MariaDB syntax - 
        // LIMIT $limit
        // LIMIT $offset, $limit
        // LIMIT $limit OFFSET $offset
        if ($this->offset && ! $this->limit) {
            throw new BadMethodCallException('Cannot set OFFSET without LIMIT, please set limit() to use offset()');
        }
        if ($this->limit && $this->offset) {
            return sprintf(' LIMIT %d OFFSET %d', $this->limit, $this->offset);
        } elseif ($this->limit) {
            return sprintf(' LIMIT %d', $this->limit);
        }

        return null;
    }
    /**
     * Returns an array representing the SET clause
     *
     * @param Array $data - The data used to create the clause
     * @return Array<Int,String>
     */
    private function getSetClause(Array $data) : Array
    {
        $out = [];
        foreach ($this->getColumnNames($data) as $column) {
            $out[] = sprintf('%s=?', $this->quote($this->qualify($column)));
        }

        return $out;
    }


    /**
     * Returns the quoted column names, without values from the provided array
     *
     * @param Array<Int,Array> $params - An array of associative arrays containing columns=>values
     * @return Array<Int,String> - The quoted column names
     * @throws InvalidArgumentException - If the column names are not consistent within the provided array 
     */
    private function getColumnNames(Array $params) : Array
    {
        // sanity check that all array keys (columns) are consistent
        foreach ($params as $a) {
            if (count(array_diff(array_keys($params[0]), array_keys($a)))) {
                throw new InvalidArgumentException('Cannot get column names, ensure the provided columns are consistent');
            }
        }

        $columns = [];
        foreach (array_keys($params[0]) as $v) {
            $columns[] = $this->quote($v);
        }

        return $columns;
    }
    /**
     * Returns the placeholder characters required for the data
     *
     * @param Array $params
     * @return Array
     */
    private function getPlaceChars(Array $params) : Array
    {
        if (is_array($params[0]))
        {
            // Inserts are an array of item params.
            // Each set of params needs wrapping in brackets
            $chars = [];
            $count = count($params[0]);
            foreach ($params as $k=>$a) {
                $chars[] = sprintf('(%s)', join(',', array_fill(0, $count, '?')));
            }

            return $chars;
        }

        // just a flat array, like WHERE IN 
        return array_fill(0, count($params), '?');
    }


    /**
     * Returns a normalised where clause, accepts any input format and converts into something more manageable
     * TODO centralise the WHERE and HAVING argument signatures
     *
     * @param Mixed $input - The input to normalise
     * @return Array - The normalised array
     */
    /*
    private function normaliseWhereArgs($mixed) : Array
    {
        // accepts tble.column references in indexed array, assoc array, string or csv formats and returns 
    }
    */
    /**
     * Returns a normalised array of sort references used by the orderBy and groupBy methods
     *
     * @param Mixed $input - The input to normalise
     * @return Array - The normalised array
     */
    private function normaliseSortArgs($input, String $dir=null, String $method) : Array
    {
        /* input signatures
        $input='column', $dir=null 
        $input='column', $dir='asc'
        $input='column, column dir', $dir='asc' - not valid to use dir when input is csv
        $input=[..], $dir='asc' - not valid to use dir when input is array
        $input=['column', 'column']
        $input=['column dir', 'column dir']
        $input=['column'=>'dir', 'column'=>'dir']
        
        
        */
        $out = [];

        if ( ! is_string($input) AND ! is_array($input)) {
            throw new InvalidArgumentException(sprintf('Invalid data type passed to %s()', $method));
        }
        // handle string input by turning it into array and calling this method again
        if (is_string($input)) {
            if (stripos($input, ',')) {
                if ( ! is_null($dir)) {
                    throw new InvalidArgumentException(sprintf('Cannot use a comma separated list of references and pass direction argument in %s()', $method));
                }

                return $this->normaliseSortArgs(array_values(explode(',', $input)), $dir, $method);

            } else {
                // sanitise dir if provided
                $input = trim($input);
                $this->assertIsReference($input);
                $this->assertIsSortDir($dir, $method);
                $out[$input] = $this->normaliseSortDir($dir);

                return $out;
            }
        }

        // handle arrays
        if ( ! is_null($dir)) {
            throw new InvalidArgumentException(sprintf('Cannot use a comma separated list of references and pass direction argument in %s()', $method));
        }
        // assoc arrays already have key values
        if (is_assoc($input)) {
            foreach ($input as $k=>$v) {
                $this->assertIsReference($k);
                $this->assertIsSortDir($v, $method);
                $out[$k] = $this->normaliseSortDir($v);
            }

            return $out;
        }

        // indexed array 'column dir','column dir'
        foreach ($input as $param) {
            $parts = explode(' ', trim($param), 2);
            $this->assertIsReference($parts[0]);
            if (isset($parts[1])) {
                $this->assertIsSortDir($parts[1], $method);
                $out[$parts[0]] = $this->normaliseSortDir($parts[1]);
            } else {
                $out[$parts[0]] = null;
            }
        } 

        return $out;
    }
    /**
     * Returns a normalised sort direction statement, converts asc|ascending to null, dsc, desc or descending to DESC
     *
     * @param Mixed $input - The input to normalise
     * @return String - The normalised sort direction DESC|null
     */
    private function normaliseSortDir(String $dir=null) : ?String
    {
        if (is_null($dir)) {
            return null;
        }

        return (in_array(strtolower(substr($dir,0,3)), ['dsc','des'])) ? 'DESC' : null;
    }
    /**
     * Returns a fully qualified reference to a table or column where possible, unquoted
     *
     * @param String $str 
     * @param String $table 
     * @return String
     */
    private function qualify(String $str, String $table=null) : String
    {
        if (empty($table)) {
            $table = $this->from;
        }
        if (stripos($str, '.')) {
            $parts = explode('.', $str);
        } else {
            $parts = [$table, $str];
        }
        for ($i=0; $i < count($parts); $i++) {
            $parts[$i] = $this->unquote(trim($parts[$i]));
        }

        return join('.', $parts);
    }
    /**
     * Returns the provided string quoted with the character defined in the quote_char property
     *
     * @param String $str - The string to be quoted
     * @return String
     */
    private function quote(String $str) : String
    {
        $str = $this->quote_char.$this->unquote($str).$this->quote_char;
        // replace period . with quote.quote
        $str = str_replace('.', "$this->quote_char.$this->quote_char", $str);
        // replace as with quote.quote
        $str = str_replace(' as ', ' AS ', $str);
        $str = str_replace(' AS ', "$this->quote_char AS $this->quote_char", $str);

        return $str;
    }
    /**
     * Removes the quote character from the provided string
     *
     * @param String $str - The string to be unquoted
     * @return String
     */
    private function unquote(String $str) : String
    {
        return str_replace($this->quote_char, '', $str);
    }


    /**
     * Asserts that $str is a valid database logical operator (AND|OR|XOR)
     *
     * @param String $str - The string to test
     * @param String $method - The name of the calling method to be returned in the exception message
     * @throws InvalidArgumentException If the string is not a valid logical operator
     * @return Void
     */
    private function assertIsBoolean(String $str, String $method) : Void
    {
        if ( ! in_array($str, $this->logicals)) {
            throw new InvalidArgumentException(sprintf('The %s() logic must be one of: %s', $method, join(' ', $this->logicals)));
        }
    }
    /**
     * Asserts that $str is a valid sort direction ASC|DSC|DESC
     *
     * @param String $str - The string to test
     * @param String $method - The name of the calling method to be returned in the exception message
     * @throws InvalidArgumentException If the string is not a valid direction
     * @return Void
     */
    private function assertIsSortDir(String $str=null, String $method) : Void
    {
        if (is_null($str)) {
            return;
        }
        $dir = substr(strtolower($str), 0, 3);
        if ( ! in_array($dir, ['asc','dsc','des'])) {
            throw new InvalidArgumentException(sprintf('Cannot set unknown sort direction %s in %s()', $str, $method));
        }
    }
    /**
     * Asserts that $str is a valid database operator (=,<,>,<>...)
     *
     * @param String $str - The string to test
     * @param String $method - The name of the calling method to be returned in the exception message
     * @throws InvalidArgumentException If the string is not a valid comparison operator
     * @return Void
     */
    private function assertIsOperator(String $str, String $method) : Void
    {
        if ( ! in_array(strtoupper($str), $this->operators)) {
            throw new InvalidArgumentException(sprintf('The %s() operator must be one of: %s', $method, join(' ', $this->operators)));
        }
    }
    /**
     * Asserts that $str is a valid table/column reference.
     * By default the permitted chars are a-z, 0-9, underscore and period, additional chars can be 
     * added using the addReferenceChars() method
     *
     * @param String $str 
     * @return Void
     */
    private function assertIsReference(String $str) : Void
    {
        if ($str == '*') {
            return;
        }
        // Handle ' as ' and ' AS ' by spliting and processing separately
        if (stristr($str, ' AS ')) {
            $str = str_replace(' as ', ' AS ', $str);
            $parts = explode(' AS ', $str, 2);
            $this->assertIsReference($parts[0]);
            $this->assertIsReference($parts[1]);

            return;
        }
        
        $str = trim($this->unquote($str));
        if (preg_match(sprintf('/[^a-z0-9\_\.%s]/i', join($this->reference_chars)), $str)) {
            throw new InvalidArgumentException(sprintf("Cannot set reference '%s' as it contains invalid characters", $str));
        }
    }
}
