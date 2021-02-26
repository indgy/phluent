<?php

declare(strict_types=1);

namespace Phluent;

use InvalidArgumentException;


/**
 * This file contains functions that support the DB and Query classes
 *
 * @package Phluent
 * @author Indgy <me@indgy.uk>
 */

/**
 * Checks the provided string is in a valid JSON encoded format
 *
 * @param Mixed $str - The value to be tested
 * @return Boolean
 */
function is_json($str) : Bool
{
	if (is_string($str) && is_array(json_decode($str, true))) {
		return (json_last_error() === JSON_ERROR_NONE);
	}

	return false;
}
/**
 * Checks the provided array is associative, not indexed; i.e. it has string keys
 *
 * @param string $str 
 * @return Boolean
 */
function is_assoc($array) : Bool
{
    if ( ! is_array($array)) {
        return false;
    }

    return count(array_filter(array_keys($array), 'is_string')) > 0;
}
/**
 * Splits a string on capital letters inserting a space before each
 *
 * @param String $str - A string with capital letters
 * @return String 
 */
function str_split_caps(String $str) : String
{
    return trim(join(' ', preg_split('/(?=[A-Z])/', str_replace(' ', '', $str))));
}
/**
 * Shortcut function that returns an instance of Query, optionally setting the table name
 *
 * @param String $table - Sets the table name if provided
 * @return Query
 */
function query(String $table=null)
{
    $q = new Query();
    return ($table) ? $q->table($table) : $q;
}
