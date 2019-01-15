<?php
namespace Application\Filter;

interface CallbackInterface
{
	public function __invoke ($item, $params) : Result;
}
