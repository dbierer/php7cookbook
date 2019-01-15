<?php
/**
 * Identifies methods required for hydrator strategy
 */
namespace Application\Generic\Hydrator\Strategy;

interface HydratorInterface
{
	public static function hydrate(array $array, $object);
	public static function extract($object);
}
