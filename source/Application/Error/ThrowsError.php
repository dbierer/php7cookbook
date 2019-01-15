<?php
namespace Application\Error;

use Error;
class ThrowsError
{
    const NOT_PARSE = 'this will not parse';

    public function divideByZero()
    {
        $this->zero = 1 / 0;
    }
    public function willNotParse()
    {
        eval(self::NOT_PARSE);
	}
}
