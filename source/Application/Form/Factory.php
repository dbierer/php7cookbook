<?php
namespace Application\Form;

use RuntimeException;
use Application\Filter\ { Filter, Validator };
use Application\Form\Element\Form;

class Factory
{
	
	const DATA_NOT_FOUND = 'Data not found. Run setData()';
	const FILTER_NOT_FOUND = 'Filter not found. Run setFilter()';
	const VALIDATOR_NOT_FOUND = 'Validator not found. Run setValidator()';
	
	protected $elements;
	protected $filter;
	protected $validator;
	protected $data;
	
	/**
	 * Builds instance of this class
	 * 
	 * @param array $config => [
			'element_name' => [
				'class'		=> 'Application\Form\Generic' | 'Application\Form\Element\*',
				'type' 		=> Generic::TYPE_*, 
				'label'		=> 'some_label',
				'wrappers'	=> [array_of_wrappers],
				'attributes'=> ['html_attrib' => 'setting'],
				'errors'    => [array_of_error_messages],
				'options'   => [option_1, option_2, etc.],	
			],
			etc.
		];
		* @return Factory $form
	*/
	public static function generate(array $config)
	{
		$form = new self();
		foreach ($config as $key => $p) {
			// check for optional params
			$p['errors'] = $p['errors'] ?? array();
			$p['wrappers'] = $p['wrappers'] ?? array();
			$p['attributes'] = $p['attributes'] ?? array();
			$form->elements[$key] = new $p['class']
				(
					$key, 
					$p['type'],
					$p['label'],
					$p['wrappers'],
					$p['attributes'],
					$p['errors']
				);
			if (isset($p['options'])) {
				list($a,$b,$c,$d) = $p['options'];
				switch ($p['type']) {
					case Generic::TYPE_RADIO    :
					case Generic::TYPE_CHECKBOX :
						$form->elements[$key]->setOptions($a,$b,$c,$d);
						break;
					case Generic::TYPE_SELECT   :
						$form->elements[$key]->setOptions($a,$b);
						break;
					default                     :
						$form->elements[$key]->setOptions($a,$b);
						break;
				}
			}
		}
		return $form;
	}

	public static function render($form, $formConfig)
	{
		// get contents of form
		$rowPattern = $form->getWrapperPattern($formConfig['row_wrapper']);
		$contents   = '';
		foreach ($form->getElements() as $element) {
			$contents .= sprintf($rowPattern, $element->render()) . PHP_EOL;
		}
		// create form tag
		$formTag = new Form($formConfig['name'], 
							Generic::TYPE_FORM, 
							'', 
							array(), 
							$formConfig['attributes']); 
		// place form tag inside wrapper?
		$formPattern = $form->getWrapperPattern($formConfig['form_wrapper']);
		if (isset($formConfig['form_tag_inside_wrapper']) && !$formConfig['form_tag_inside_wrapper']) {
			$formPattern = '%s' . $formPattern . '%s';
			return sprintf($formPattern, $formTag->getInputOnly(), $contents, $formTag->closeTag());
		} else {
			return sprintf($formPattern, $formTag->getInputOnly() . $contents . $formTag->closeTag());
		}
	}
	
	protected function getWrapperPattern($wrapper)
	{
		$type = $wrapper['type'];
		unset($wrapper['type']);
		$pattern = '<' . $type;
		foreach ($wrapper as $key => $value) {
			$pattern .= ' ' . $key . '="' . $value . '"';
		}
		$pattern .= '>%s</' . $type . '>';
		return $pattern;
	}

	public function getElements()
	{
		return $this->elements;
	}
	
	//********* Lets Filter/Validator Tie to Form *********//
	public function getFilter()
	{
		return $this->filter;
	}

	public function setFilter(Filter $filter)
	{
		$this->filter = $filter;
	}

	public function setValidator(Validator $validator)
	{
		$this->validator = $validator;
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function filter()
	{
		if (!$this->data)
			throw new RuntimeException(self::DATA_NOT_FOUND);
			
		if (!$this->filter)
			throw new RuntimeException(self::FILTER_NOT_FOUND);
		
		$this->filter->process($this->data);
		// apply filtered data to form elements
		foreach ($this->filter->getResults() as $key => $result) {
			if (isset($this->elements[$key])) {
				$this->elements[$key]->setSingleAttribute('value', $result->item);
				if (isset($result->messages) && count($result->messages)) {
					foreach ($result->messages as $message) {
						$this->elements[$key]->addSingleError($message);
					}
				}
			}			
		}
	}

	public function validate()
	{
		if (!$this->data)
			throw new RuntimeException(self::DATA_NOT_FOUND);
			
		if (!$this->validator)
			throw new RuntimeException(self::VALIDATOR_NOT_FOUND);
			
		// perform validation
		$valid = $this->validator->process($this->data);

		// tie validation messages into form elements
		foreach ($this->elements as $element) {
			if (isset($this->validator->getResults()[$element->getName()])) {
				$element->setErrors($this->validator->getResults()[$element->getName()]->messages);
			}
		}
		return $valid;
	}
	
}
