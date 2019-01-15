<?php
// XML to array
// see http://php.net/manual/en/class.simplexmliterator.php
$path = '/home/ed/Desktop/Repos/apache-fundamentals/pptx_xml/*.xml';

// setup class autoloading
require __DIR__ . '/../Application/Autoload/Loader.php';

// add current directory to the path
Application\Autoload\Loader::init(__DIR__ . '/..');

// classes to use
use Application\Parse\ConvertXml;
$convert = new ConvertXml();

foreach (glob($path) as $fn) {
	echo $fn;
	$xml = new SimpleXMLElement(file_get_contents($fn));
	scanXml($xml);
	break;
}

function scanXml($xml)
{
	foreach ($xml as $node) {
		if ($node->count()) {
			scanXml($node);
		} else {
			echo $node;
		}
	}
}
