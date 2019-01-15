<?php
// parsing tree structures
namespace Application\Parse;

use Application\Web\Hoover;
use DOMDocument;
use DOMElement;
use Exception;

/*
 * This class contains methods to build and parse a web tree
 * Based on DOMDocument
 * Looks for "href" attributes in initial URL
 * Creates nodes based on DNS name + website links within same domain
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

    /**
     * Populates "vac" which is an instance of Application\Web\Hoover
     * Builds an initial tree adding a "root" node
     */
    public function __construct()
    {
        // pull in "hoover" class
        $this->vac  = new Hoover();
        
        // build initial tree + add root node
        $this->tree = new DOMDocument('1.0', 'utf-8');
        $rootNode   = $this->tree->createElement(self::DEFAULT_ROOT_NODE, self::DEFAULT_ROOT_NODE);
        $rootNode->setAttribute(self::ID_ATTRIBUTE, self::DEFAULT_ROOT_ID);
        $rootNode->setIdAttribute(self::ID_ATTRIBUTE, TRUE);
        $this->tree->appendChild($rootNode);
        
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
        if (isset($parts['path']) && $parts['path']) {
            $requestPath = explode('/', $parts['path']);
            array_walk($requestPath, [$this, 'sanitizeXmlNodeName']);
            $path = array_merge($path, $requestPath);
        }
        $xmlPath->parts = array_filter($path);
        $xmlPath->path  = '/' . implode('/', $xmlPath->parts);
        $xmlPath->name  = substr(strrchr($xmlPath->path, '/'), 1);
        return $xmlPath;
    }

    /**
     * Replaces any non alpha numeric characters with '_'
     * Also makes sure name doesn't start with a number
     * 
     * @param string $name
     * @return string $name
     */
    protected function sanitizeXmlNodeName(&$name)
    {
        $name = preg_replace('/[^A-Za-z0-9]/', '_', $name);
        if (ctype_digit(substr($name, 1, 1))) {
            $name = '_' . $name;
        }
        return $name;
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
                throw new Exception(__METHOD__ . ':' . self::ERROR_NODE_NOT_FOUND);
            }
        }
        $scan = $this->vac->getAttribute($url, 'href', $this->vac->getDomain($url));
        foreach ($scan as $subSite) {
            $subPath = $this->getXmlPathFromUri($subSite);
            $child   = $this->tree->createElement($subPath->name, $subSite);
            $child->setAttribute(self::ID_ATTRIBUTE, $subPath->path);
            $child->setIdAttribute(self::ID_ATTRIBUTE, TRUE);
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

    /**
     * Renders web tree using ASCII graphics
     */
    public function renderTreeAscii()
    {
        $output = '';
        $level  = '';
        $root = $this->tree->getElementById(self::DEFAULT_ROOT_ID);
        if (!$root) {
            throw new Exception(__METHOD__ . ':' . self::ERROR_NODE_NOT_FOUND);
        }
        $current = $root;
        $output .= $this->renderChildAscii($current, $level);
        if ($current->hasChildNodes()) {
            $level = '--';
            foreach ($current->childNodes as $node) {
                $output .= $this->renderChildAscii($node, $level);
            }
        }            
        return $output;
    }

    protected function renderChildAscii($node, $level)
    {
        return sprintf('<br>%s<a href="%s">%s</a>', $level, $node->nodeValue, $node->tagName);
    }
    
}
