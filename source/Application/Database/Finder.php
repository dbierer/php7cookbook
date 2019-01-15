<?php
namespace Application\Database;
// example uses PHP 7 context sensitive lexer
// see: https://wiki.php.net/rfc/context_sensitive_lexer

/**
 * Builds an SQL query using OOP approach
 * To create a prepared statement w/ placeholders, just supply the placeholders instead of values
 * 
 */
class Finder
{
	public static $sql      = '';
	public static $instance = NULL;
	public static $prefix   = '';
	public static $where    = array();
	public static $control  = ['', ''];
	
    // $a == name of table
    // $cols = column names
    public static function select($a, $cols = NULL)
    {
        self::$instance  = new Finder();
        if ($cols) {
			self::$prefix = 'SELECT ' . $cols . ' FROM ' . $a;
		} else {
			self::$prefix = 'SELECT * FROM ' . $a;
		}
		return self::$instance;
    }
    
    public static function where($a = NULL)
    {
        self::$where[0] = ' WHERE ' . $a;
		return self::$instance;
    }
    
    public static function like($a, $b)
    {
        self::$where[] = trim($a . ' LIKE ' . $b);
		return self::$instance;
    }
    
    public static function and($a = NULL)
    {
        self::$where[] = trim('AND ' . $a);
		return self::$instance;
    }
    
    public static function or($a = NULL)
    {
        self::$where[] = trim('OR ' . $a);
		return self::$instance;
    }
    
    public static function in(array $a)
    {
        self::$where[] = 'IN ( ' . implode(',', $a) . ' )';
		return self::$instance;
    }
    
    public static function not($a = NULL)
    {
        self::$where[] = trim('NOT ' . $a);
		return self::$instance;
    }
    
    public static function limit($limit)
    {
        self::$control[0] = 'LIMIT ' . $limit;
		return self::$instance;
    }
    
    public static function offset($offset)
    {
        self::$control[1] = 'OFFSET ' . $offset;
		return self::$instance;
    }

	public static function getSql()
	{
		self::$sql = self::$prefix
				. implode(' ', self::$where)
				. ' '
				. self::$control[0]
				. ' '
				. self::$control[1];
		preg_replace('/  /', ' ', self::$sql);
		return trim(self::$sql);
	}

}

