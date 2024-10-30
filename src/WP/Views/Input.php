<?php
namespace WP\Views; 
class Input { 
	
	public static function textfield($i4aFieldObj) { 
		return self::field($i4aFieldObj, $i4aFieldType = 'text'); 
	} 
	
	public static function passwordfield($i4aFieldObj) { 
		return self::field($i4aFieldObj, $i4aFieldType = 'password'); 
	} 
	
	public static function checkbox($i4aFieldObj) { 
		return self::field($i4aFieldObj, $i4aFieldType = 'checkbox', null); 
	} 
	
	public static function textarea($i4aFieldObj) { 
		return self::field($i4aFieldObj, $i4aFieldType = 'textarea', null); 
	} 
	
	protected static function field($i4aFieldObj, $i4aFieldType = 'text', $i4a2b9bc = 'regular-text') { 
		$i4aFieldID = $i4aFieldObj['id']; 
		$i4aFieldGroup = $i4aFieldObj['group']; 
		$i4aFieldSection = $i4aFieldObj['section']; 
		$i4aFieldGroupSectionID = "{$i4aFieldGroup}[{$i4aFieldSection}][{$i4aFieldID}]"; 
		$i4aFieldDefaultVal = self::makeDefaultValue($i4aFieldObj); 
		$i4aFieldIsChecked = ''; 
		if ($i4aFieldType == 'checkbox') { 
			$i4aFieldIsChecked .= checked(1, (bool) $i4aFieldDefaultVal, false); 
			if (!checked(1, (bool) $i4aFieldDefaultVal, false)) { 
				$i4aFieldDefaultVal = checked(1, $i4aFieldDefaultVal, false) ? 0 : 1; 
			} 
		} 
		if (!is_null($i4aFieldObj['js'])) { 
			self::addJS($i4aFieldObj['js'], $i4aFieldGroupSectionID, $i4aFieldDefaultVal, $i4aFieldObj); 
		} 
		$i4aFieldArray = array('class' => $i4a2b9bc, 'type' => $i4aFieldType, 'name' => $i4aFieldGroupSectionID, 'value' => esc_attr($i4aFieldDefaultVal), 'extra' => $i4aFieldIsChecked); 
		switch ($i4aFieldType) { 
			case 'textarea': self::itemTextArea($i4aFieldArray); break; 
			default: self::itemTextField($i4aFieldArray); 
		} 
		self::addDescription($i4aFieldObj['desc']); 
	} 
	
	private static function itemTextArea(array $i4aFieldArray) { 
		printf('<textarea cols=30 rows=4 class=\'%s\' type=\'%s\' name=\'%s\' %s>%s</textarea>', $i4aFieldArray['class'], $i4aFieldArray['type'], $i4aFieldArray['name'], $i4aFieldArray['extra'], $i4aFieldArray['value']); 
	} 
	
	private static function itemTextField(array $i4aFieldArray) { 
		printf('<input class=\'%s\' type=\'%s\' name=\'%s\' value=\'%s\' %s />', $i4aFieldArray['class'], $i4aFieldArray['type'], $i4aFieldArray['name'], $i4aFieldArray['value'], $i4aFieldArray['extra']); 
	} 
	
	private static function addDescription($i4ac610b) { 
		if (is_array($i4ac610b)) { 
			printf('<small>&nbsp; %s</small><br><small>%s</small>', array_shift($i4ac610b), esc_attr(array_pop($i4ac610b))); 
		} else { 
			printf('<br><small>%s</small>', esc_attr($i4ac610b)); 
		} 
	} 
	
	private static function makeDefaultValue(array $i4aFieldObj) { 
		$i4aFieldDefault = $i4aFieldObj['default']; 
		$i4aFieldFilter = $i4aFieldObj['filter']; 
		$i4aFieldOptionValue = self::getOptionValue($i4aFieldObj); 
		if (is_null($i4aFieldOptionValue) && is_array($i4aFieldDefault)) { 
			$i4aFieldDefault = self::handleCallback($i4aFieldDefault); 
		} 
		if (is_array($i4aFieldFilter)) { 
			$i4aFieldOptionValue = self::handleCallback($i4aFieldFilter, array(is_null($i4aFieldOptionValue) ? $i4aFieldDefault : $i4aFieldOptionValue)); 
		} 
		return is_null($i4aFieldOptionValue) ? $i4aFieldDefault : $i4aFieldOptionValue; 
	} 
	
	public static function handleCallback($i4aFieldCallback, array $i4aFieldOptionValue = array()) { 
		if (!is_array($i4aFieldCallback)) { return $i4aFieldOptionValue; } 
		$i4aFieldShiftArray = array_shift($i4aFieldCallback); 
		$i4aFieldArray = is_array(end($i4aFieldCallback)) ? is_null($i4aFieldOptionValue) ? end($i4aFieldCallback) : array_merge(end($i4aFieldCallback), $i4aFieldOptionValue) : $i4aFieldOptionValue; 
		return call_user_func_array($i4aFieldShiftArray, $i4aFieldArray); 
	} 
	
	/* removed, fails with PHP 8.1
	private static function getOptionValue(array $i4aFieldObj) { 
		$i4aFieldID = $i4aFieldObj['id']; 
		$i4aFieldGroup = $i4aFieldObj['group']; 
		$i4aFieldSection = $i4aFieldObj['section']; 
		if (isset($_REQUEST[$i4aFieldGroup][$i4aFieldSection][$i4aFieldID])) { 
			return $_REQUEST[$i4aFieldGroup][$i4aFieldSection][$i4aFieldID]; 
		} 
		$i4aFieldGroupOptions = get_option($i4aFieldGroup);
		return isset($i4aFieldGroupOptions[$i4aFieldID]) && !empty($i4aFieldGroupOptions[$i4aFieldID]) ? $i4aFieldGroupOptions[$i4aFieldID] : isset($i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID]) && !empty($i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID]) ? $i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID] : null; 
	}
	*/
	
	private static function getOptionValue(array $i4aFieldObj) { 
		$i4aFieldID = $i4aFieldObj['id']; 
		$i4aFieldGroup = $i4aFieldObj['group']; 
		$i4aFieldSection = $i4aFieldObj['section']; 
		$return_value = null;
	
		if (isset($_REQUEST[$i4aFieldGroup][$i4aFieldSection][$i4aFieldID])) { 
			return $_REQUEST[$i4aFieldGroup][$i4aFieldSection][$i4aFieldID]; 
		} 
		$i4aFieldGroupOptions = get_option($i4aFieldGroup);
	
		// original return
		//return isset($i4aFieldGroupOptions[$i4aFieldID]) && !empty($i4aFieldGroupOptions[$i4aFieldID]) ? $i4aFieldGroupOptions[$i4aFieldID] : isset($i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID]) && !empty($i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID]) ? $i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID] : null; 
	
		// new return
		if ( isset($i4aFieldGroupOptions[$i4aFieldID]) && !empty($i4aFieldGroupOptions[$i4aFieldID]) ) {
			$return_value = $i4aFieldGroupOptions[$i4aFieldID];
		} elseif ( isset($i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID]) && !empty($i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID]) ) {
			$return_value = $i4aFieldGroupOptions[$i4aFieldSection][$i4aFieldID];
		}
	
		return $return_value;
	}
	
	public static function addJS($i4aJavascript = '', $i4aJSLocation = '', $i4aFieldDefaultVal = '', $i4aFieldObj = '') { 
		$i4aFieldGroup = $i4aFieldSection = null; 
		is_array($i4aFieldObj) ? extract($i4aFieldObj) : null; $i4aJSObj = function ($i4aJSLocation, $i4aJSNum = '') { 
			return sprintf('$(\'input[name%s="%s"]\')', $i4aJSNum, $i4aJSLocation); 
		}; 
		printf(
				'<script>jQuery(document).ready(function($) { %s });</script>', 
				preg_replace(array('/%field:(.*?)%/i', '/%field/', '/%value/'), 
				array($i4aJSObj('[$1]', '*'), 
				$i4aJSObj($i4aJSLocation), 
				esc_attr($i4aFieldDefaultVal)), 
				self::compress($i4aJavascript)
				)
		); 
	} 
	
	protected static function compress($i4aStringToCompress) { 
		$i4aStringToCompress = preg_replace('/((?:\\/\\*(?:[^*]|(?:\\*+[^*\\/]))*\\*+\\/)|(?:\\/\\/.*))/', '', $i4aStringToCompress); 
		$i4aStringToCompress = str_replace(array('', '', '	', '', '  ', '    ', '     '), '', $i4aStringToCompress); 
		$i4aStringToCompress = preg_replace(array('(( )+\\))', '(\\)( )+)'), ')', $i4aStringToCompress); 
		return $i4aStringToCompress; 
	} 

}