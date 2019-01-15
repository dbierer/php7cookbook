<?php
namespace Application\Web;

/**
 * Defines a filtering/validating mechanism
 */
class Security
{
    
    protected $filter;
    protected $validate;
    
    public function __construct()
    {
        $this->filter = [
            'striptags' => function ($a) { return strip_tags($a); },
            'digits'    => function ($a) { return preg_replace('/[^0-9]/', '', $a); },
            'alpha'     => function ($a) { return preg_replace('/[^A-Z]/i', '', $a); }
        ];
        $this->validate = [
            'alnum'  => function ($a) { return ctype_alnum($a); },
            'digits' => function ($a) { return ctype_digit($a); },
            'alpha'  => function ($a) { return ctype_alpha($a); }
        ];
    }
    
    /**
     * Intercepts calls to non-existent methods
     * Looks at the beginning of $method to see if it's "filter" or "validate"
     * Uses preg_match() to extract the 2nd part of the match, which should produce 
     * a key from $this->filter or $this->validate
     * 
     * @param string $method
     * @param mixed $params
     * @return mixed if $prefix == filter, returns transformed value; otherwise returns (bool)
     */
    public function __call($method, $params)
    {
        preg_match('/^(filter|validate)(.*?)$/i', $method, $matches);
        $prefix   = $matches[1] ?? '';
        $function = strtolower($matches[2] ?? '');
        if ($prefix && $function) {
            return $this->$prefix[$function]($params[0]);
        }
        return $value;
    }
}
