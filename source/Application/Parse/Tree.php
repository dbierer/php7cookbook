<?php
// parsing tree structures
namespace Application\Parse;

use Application\Web\Hoover;
use DOMDocument;
use DOMElement;
use Exception;

/*
 * This class contains methods to build and parse tree structures
 * Based on DOMDocument
 */

class WebTree
{

    const DEFAULT_ROOT_NODE    = 'root';
    const DEFAULT_ROOT_ID      = '/';
    const ID_ATTRIBUTE         = 'id';
    const ERROR_NODE_NOT_FOUND = 'Node Not Found';   
    
    // tree structure == DOMDocument
    protected $tree = NULL;

    // instance of Application\Web\Hoover
    protected $vac  = NULL;

    public function __construct()
    {
        $this->vac  = new Hoover();
        $this->tree = new DOMDocument('1.0', 'utf-8');
        $element    = $this->tree->createElement(self::DEFAULT_ROOT_NODE, self::DEFAULT_ROOT_NODE);
        $element->setAttribute(self::ID_ATTRIBUTE, self::DEFAULT_ROOT_ID);
        $element->setIdAttribute(self::ID_ATTRIBUTE, TRUE);
        $this->tree->appendChild($element);
    }
    
    /**
     * Returns XML from tree
     * 
     * @return string $xml
     */
    public function getXml()
    {
        return $this->tree->saveXml();
    }

    /**
     * Converts URI into XML path
     * 
     * NOTE: uses PHP 7 return data typing
     * 
     * @param string $uri
     * @return Application\Parse\XmlPath $xmlPath
     */
    public function getXmlPathFromUri($uri) : XmlPath
    {
        $xmlPath = new XmlPath();
        $parts   = parse_url($uri);
        $path    = array_reverse(explode('.', $parts['host']));
        if (isset($parts['path'])) {
            $path = array_merge($path, explode('/', $parts['path']));
        }
        $xmlPath->parts = array_filter($path);
        $xmlPath->path  = implode('/', $xmlPath->parts);
        return $xmlPath;
    }
    
    /**
     * Builds tree from XML path
     * 
     * @param Application\Parse\XmlPath $path
     * @return TRUE [populates $this->tree]
     */
    public function buildTreeFromPath(XmlPath $path)
    {
        $id = '';
        $current = $this->tree->getElementById(self::DEFAULT_ROOT_ID);
        foreach ($path->parts as $node) {
            $id .= '/' . $node;
            $element = $this->tree->getElementById($id);
            if ($element) {
                $current = $element;
                continue;
            }
            $element = $this->tree->createElement($node, $node);
            $element->setAttribute(self::ID_ATTRIBUTE, $id);
            $element->setIdAttribute(self::ID_ATTRIBUTE, TRUE);
            $current->appendChild($element);
            $current = $element;
        }
        return TRUE;
    }
    
    /**
     * Adds a level to the tree
     * 
     * Builds one child node for each array element
     * 
     * @param string $url
     * @param XmlPath $path = XML path to parent node
     * @return SimpleXMLElement $tree
     */
    public function addLevelToTreeFromUrl($url, XmlPath $path)
    {
        $node = $this->tree->getElementById($path->path); 
        if (!$node) {
            $this->buildTreeFromPath($path);
            $node = $this->tree->getElementById($path->path); 
            if (!$node) {
                throw new Exception(self::ERROR_NODE_NOT_FOUND);
            }
        }
        $scan = $this->vac->getAttribute($url, 'href', $vac->getDomain($url));
        foreach ($scan as $subSite) {
            $subPath = $this->getXmlPathFromUri($subSite);
            $name    = $subPath->parts[count($subPath->parts) - 1];
            $child = new DOMElement($name, $name);
            $child->setAttribute('id', $subPath->path);
            $node->appendChild($child);
        }
        return TRUE;
    }

    /**
     * Builds a tree from $url
     * $levels == how deep to go into the website
     * 
     * @param string $url
     * @param int $levelsl
     * @return SimpleXMLElement $tree
     */
    public function buildWebTree($url, $level)
    {
        // add initial level
        try {
            $this->addLevelToTreeFromUrl($url, $this->getXmlPathFromUri($url));
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }

}
