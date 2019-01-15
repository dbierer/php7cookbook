<?php
// converts from XML to array
namespace Application\Parse;

use SimpleXMLIterator;
use SimpleXMLElement;

/*
 * This class contains methods to convert from XML to an array
 * 
 * Based on a posting from ratfactor@gmail.com 
 * See: http://php.net/manual/en/class.simplexmliterator.php
 * 
 */
class ConvertXml
{
	/**
	 * Converts a SimpleXMLIterator to an array
	 * Preserves attributes
	 * 
	 * @param SimpleXMLIterator $xml
	 * @return array $a;
	 */
	public function xmlToArray(SimpleXMLIterator $xml) : array
	{
		$a = array();
		for( $xml->rewind(); $xml->valid(); $xml->next() ) {
			if(!array_key_exists($xml->key(), $a)) {
				$a[$xml->key()] = array();
			}
			if($xml->hasChildren()){
				$a[$xml->key()][] = $this->xmlToArray($xml->current());
			}
			else{
				$a[$xml->key()] = (array) $xml->current()->attributes();
				$a[$xml->key()]['value'] = strval($xml->current());
			}
		}
		return $a;
	}
	
	/**
	 * Converts an array to XML
	 * Assumes associative array key/value where key == name of the node
	 * Assumes ['@attributes'] == an array of attributes as key/value pairs
	 * 
	 * @param array $a
	 * @return string $xml;
	 */
	public function arrayToXml(array $a)
	{
		$xml = new SimpleXMLElement('<?xml version="1.0" standalone="yes"?><root></root>');
		$this->phpToXml($a, $xml);
		return $xml->asXML();
	}
		
	protected function phpToXml($value, &$xml)
	{
		$node = $value;
		if (is_object($node)) {
			$node = get_object_vars($node);
		}
		if (is_array($node)) {
			foreach ($node as $k => $v) {
				if (is_numeric($k)) {
					$k = 'number' . $k;
				}
				if (is_array($v)) {
					$newNode = $xml->addChild($k);
					$this->phpToXml($v, $newNode);
				} elseif (is_object($v)) {
					$newNode = $xml->addChild($k);
					$this->phpToXml($v, $newNode);
				} else {
					$xml->addChild($k, $v);
				}
			}
		} else  {
			$xml->addChild(self::UNKNOWN_KEY, $node);
		}
	}
	
}
