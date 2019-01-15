<?php
namespace Application\Web\Menu;

class Factory
{
	
	const TYPE_NORMAL              = 'normal';
	const TYPE_BUTTON              = 'button';
	const DEFAULT_LI_TEMPLATE      = '<li %s><a href="%s">%s</a></li>' . PHP_EOL;
	const DEFAULT_UL_TEMPLATE      = '<ul class="dropdown-menu">' . PHP_EOL;
	const DEFAULT_TOGGLE_TEMPLATE  = '<a href="%s" data-toggle="dropdown" class="dropdown-toggle">%s <b class="caret"></b></a>' . PHP_EOL;
	const DEFAULT_DROPDOWN_WRAPPER = '<div class="dropdown">' . PHP_EOL;
	const DEFAULT_TOGGLE_BUTTON    = '<button type="button" data-toggle="dropdown" class="btn btn-default dropdown-toggle">%s <span class="caret"></span></button>' . PHP_EOL;
	
	protected $type;
	protected $items;
	protected $ulTag;
	protected $liTag;
	protected $toggleTag; 
	protected $loop = 0;
	protected $output = '';
	protected $bootstrap;		// bootstrap + jquery config
	protected $dropdownWrapper;
	
	public function __construct($menu, $bootstrap, $type = NULL, array $templates = NULL)
	{
		$this->setItems($menu);
		$this->setBootstrap($bootstrap);
		$this->type  = $type ?? self::TYPE_NORMAL;
		$this->liTag = $templates['liTag'] ?? self::DEFAULT_LI_TEMPLATE;
		$this->ulTag = $templates['ulTag'] ?? self::DEFAULT_UL_TEMPLATE;
		$this->dropdownWrapper = $templates['dropdownWrapper'] ?? self::DEFAULT_DROPDOWN_WRAPPER;
		// set toggle tag
		if (isset($templates['toggleTag'])) {
			$this->toggleTag = $templates['toggleTag'];
		} else {
			switch ($this->type) {
				case self::TYPE_BUTTON :
					$this->toggleTag = self::DEFAULT_TOGGLE_BUTTON;
					break;
				case self::TYPE_NORMAL :
				default :
					$this->toggleTag = self::DEFAULT_TOGGLE_TEMPLATE;
			}
		}
	}
		
	public function render()
	{
		$this->renderList($this->getItems());
		$this->output .= '</' . trim(substr($this->dropdownWrapper, 1, 3)) . '>' . PHP_EOL;
		return $this->output;
	}
	
	protected function renderList(array $list)
	{
		// we could use a recursive iterator here, but recursive functions are faster
		foreach ($list as $item) {
			if (isset($item['dropDown'])) {
				if ($this->loop++) {
					$this->output .= '</' . trim(substr($this->dropdownWrapper, 1, 3)) . '>' . PHP_EOL;
				}
				$this->output .= $this->dropdownWrapper;
			}
			$this->renderItem($item);
			if (isset($item['dropDown'])) {
				$this->renderList($item['dropDown']);
				$this->output .= '</ul>' . PHP_EOL;
			}
		}
	}
	
	protected function renderItem(array $item)
	{
		$attribs = (isset($item['li'])) ? $this->getAttribs($item['li']) : '';
		if (isset($item['dropDown']) && count($item['dropDown'])) {
			$params = array();
			if (isset($item['url'])) {
				$this->output .= sprintf($this->toggleTag, $item['url'], $item['label']);
			} else {
				$this->output .= sprintf($this->toggleTag, $item['label']);
			}
			$this->output .= $this->ulTag;
		} else {
			$this->output .= sprintf($this->liTag, $attribs, $item['url'], $item['label']);
		}
		$this->output;
	}
	
	public function getAttribs($config)
	{
		foreach ($config as $key => $value) {
			$key = strtolower($key);
			if ($value) {
				if ($key == 'value') {
					if (is_array($value)) {
						foreach ($value as $k => $i) 
							$value[$k] = htmlspecialchars($i);
					} else {
						$value = htmlspecialchars($value);
					}
				} elseif ($key == 'href') {
					$value = urlencode($value);
				}
				$attribs .= $key . '="' . $value . '" ';
			} else {
				$attribs .= $key . ' ';
			}
		}
		return trim($attribs);
	}

	public function header()
	{
		$header = '';
		foreach ($this->bootstrap['css'] as $link) 
			$header .= sprintf('<link rel="stylesheet" href="%s">', $link) . PHP_EOL;
		foreach ($this->bootstrap['js'] as $js) 
			$header .= sprintf('<script src="%s"></script>', $js) . PHP_EOL;
		return $header;
	}
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function setToggleTag($toggleTag)
	{
		$this->toggleTag = $toggleTag;
	}
	
	public function setLiTag($liTag)
	{
		$this->liTag = $liTag;
	}
	
	public function setUlTag($ulTag)
	{
		$this->ulTag = $ulTag;
	}
	
	public function setDropdownWrapper($wrapper)
	{
		$this->dropdownWrapper = $wrapper;
	}
	
	public function setOuterWrapper($wrapper)
	{
		$this->outerWrapper = $wrapper;
	}
	
	public function getBootstrap()
	{
		return $this->bootstrap;
	}
	
	public function setBootstrap(array $bootstrap)
	{
		$this->bootstrap = $bootstrap;
	}
	
	public function getItems()
	{
		return $this->items;
	}
	
	public function setItems(array $items)
	{
		$this->items = $items;
	}

}


