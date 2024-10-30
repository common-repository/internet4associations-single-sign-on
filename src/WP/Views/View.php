<?php
namespace WP\Views; 
use Internet4Associations\Traits\SingletonTrait; 
abstract class View extends Form { 
use SingletonTrait; 
	
	protected $group; 
	
	protected $fields = array(
			'abstract' => array(
					'desc' => 'this is the abstract section.', 
					'fields' => array(
							'field1' => array(
									'title' => 'abstract field 1', 
									'desc' => 'you should extend this abstract class and not use it directly.', '
									validate' => array(
											'[a-zA-Z0-9_]{5,}', 
									'must be minimum 5 characters, format (a-z 0-9 _-)'), 
									'required' => true, 
									'callback' => null, 
									'default' => 'some value', 
									'filter' => 'trim'
							)
					)
			)
	); 
	
	
	public function __construct() { 
		$this->page = Page::getCurrentPage(); 
		$this->group = 'internet4associations';
		if (isset($_POST[$this->page]) && sizeof($_POST[$this->page]) > 0) { 
			!$this->validate() ? $this->flash(true) : $this->store() && $this->flash(); 
		}  
		$this->init(); 
		$this->render(); 
	} 
	
	protected function init() { 
		register_setting($this->page, $this->group, array($this, 'sanitize')); 
		if (sizeof($this->fields) <= 0) { return false; } 
		array_walk($this->fields, function ($i4aField, $i4aSectionObj) { 
			$this->makeSection($i4aSectionObj, $i4aField['desc']); 
			if (isset($i4aField['js'])) { 
				Input::handleCallback($i4aField['js']); 
			} 
			array_walk($i4aField['fields'], function ($i4aSectionArray, $i4aFieldName) 
					use($i4aSectionObj) { 
						$i4aSectionArray += array('key' => $i4aFieldName, 'section' => $i4aSectionObj); 
						$this->makeField($i4aFieldName, $i4aSectionArray); 
					}
			); 
		}); 
	} 
	
	protected function render() { 
		if (sizeof($this->fields) > 0) { 
			settings_fields($this->group); 
			do_settings_sections($this->page); 
			submit_button();
		} 
	} 
	
	protected function makeSection($i4aSectionName, $i4aSectionAltName = null, $i4aUseSectionName = null) { 
		if (is_null($i4aUseSectionName)) { 
			$i4aUseSectionName = function () use($i4aSectionAltName) { print $i4aSectionAltName; }; 
		} 
		return add_settings_section($this->toSlug($i4aSectionName), ucwords($i4aSectionName), $i4aUseSectionName, $this->page); 
	} 
	
	protected function makeField($i4aFieldID, array $i4aField) { 
		
		if (!isset($i4aField['callback']) || (isset($i4aField['callback']) && is_null($i4aField['callback']))) { 
			$i4aField['callback'] = 'textfield'; 
		}
		if (!isset($i4aField['default']) || (isset($i4aField['default']) && is_null($i4aField['default']))) {
			$i4aField['default'] = '';
		}
		if (!isset($i4aField['filter']) || (isset($i4aField['filter']) && is_null($i4aField['filter']))) {
			$i4aField['filter'] = '';
		}
		if (!isset($i4aField['js']) || (isset($i4aField['js']) && is_null($i4aField['js']))) {
			$i4aField['js'] = '';
		}
		
		if (isset($i4aField['checkbox'])) {
			$i4aField['callback'] = 'checkbox'; 
		}
		
		
		if (!isset($i4aField['args']) || (isset($i4aField['args']) && is_null($i4aField['args']))) {
			$i4aField['args'] = array();
		}
		if (!is_array($i4aField['callback'])) { 
			$i4aField['callback'] = array(__NAMESPACE__ . '\\Input', $i4aField['callback']); 
		} 
		$i4aSection = $this->toSlug($i4aField['section']); 
		if (sizeof($i4aField['args']) <= 0) { 
			$i4aField['args'] = array('group' => $this->group, 'section' => $i4aSection, 'id' => $i4aFieldID, 'desc' => $i4aField['desc'], 'default' => $i4aField['default'], 'filter' => $i4aField['filter'], 'js' => $i4aField['js']); 
		} 
		return add_settings_field($i4aFieldID, $i4aField['title'], $i4aField['callback'], $this->page, $i4aSection, $i4aField['args']);
	} 
	
	protected function toSlug($i4aField, $i4aDelimiter = '_') { 
		return preg_replace('/[^\\w]/', $i4aDelimiter, strtolower($i4aField)); 
	} 

}