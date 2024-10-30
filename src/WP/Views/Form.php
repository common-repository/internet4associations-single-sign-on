<?php
namespace WP\Views; 
abstract class Form { 
	protected $wp; 
	
	// save plugin settings from wordpress admin plugin page
	protected function store() { 
		$i4aPluginSettings = $_POST[$this->page]; 
		// add or remove i4a non-member role from WP 
		if ( isset($i4aPluginSettings['single_sign_on']['enable_nonmembers']) ) {
			add_role('i4a_nonmember', 'i4a: Non-Member');
		} else {
			remove_role('i4a_nonmembers');
		}
		return add_option($this->page, $i4aPluginSettings) || update_option($this->page, $i4aPluginSettings); 
	} 
	
	// validate form fields from wordpress admin plugin settings page
	protected function validate() { 		 
		if (!isset($_POST[$this->page])) { 
			return false; 
		} 
		$i4aPluginSettings = $_POST[$this->page]; 
		$this->wp = new \WP_Error(); 
		foreach ($this->fields as $i4aPluginField => $i4aPluginSettingsForm) { 
			$i4aPluginField = $this->toSlug($i4aPluginField); 
			foreach ($i4aPluginSettingsForm['fields'] as $i4aPluginSettingsFormField => $i4aPluginSettingsFormFieldProperties) { 
				$i4aPluginSettingsFormField = $this->toSlug($i4aPluginSettingsFormField); 
				if (isset($i4aPluginSettingsFormFieldProperties['required']) && $i4aPluginSettingsFormFieldProperties['required']) { 
					if (!is_null($i4aPluginSettings[$i4aPluginField][$i4aPluginSettingsFormField]) && trim($i4aPluginSettings[$i4aPluginField][$i4aPluginSettingsFormField]) == '') { 
						$this->wp->add($i4aPluginSettingsFormField, __($i4aPluginSettingsFormFieldProperties['title'] . ' cannot be left empty.')); continue; 
					} 
					if (isset($i4aPluginSettingsFormFieldProperties['validate']) && !is_null($i4aPluginSettingsFormFieldProperties['validate'])) { 
						if (!is_array($i4aPluginSettingsFormFieldProperties['validate'])) { continue; } 
						if (!preg_match('/^' . current($i4aPluginSettingsFormFieldProperties['validate']) . '$/is', $i4aPluginSettings[$i4aPluginField][$i4aPluginSettingsFormField])) { 
							$i4aPluginSettingsFormFieldValidateMsg = end($i4aPluginSettingsFormFieldProperties['validate']) != '' ? end($i4aPluginSettingsFormFieldProperties['validate']) : 'must be valid characters.'; 
							$this->wp->add($i4aPluginSettingsFormField, __($i4aPluginSettingsFormFieldProperties['title'] . ' ' . $i4aPluginSettingsFormFieldValidateMsg)); 
							continue; 
						} 
					} 
				} 
			} 
			return sizeof($this->wp->get_error_codes()) <= 0; }
	} 
			
	protected function flash($i4aPluginErrorMsg = false, $i4aPluginSettingsFormFieldValidateMsg = null) { 
		if ($i4aPluginErrorMsg) { 
			return printf('<div id="message" class="%s"><p><strong>%s</strong></p><p>%s</p></div>', $i4aPluginErrorMsg ? 'error' : 'updated', $i4aPluginErrorMsg ? __('Error!') : __('Success!'), __(implode('<br>', $this->wp->get_error_messages()))); 
		} 
		return printf('<div id="message" class="%s"><p><strong>%s</strong></p><p>%s</p></div>', 'updated', 'Success!', is_null($i4aPluginSettingsFormFieldValidateMsg) ? __('Your request has been processed successfully.') : __($i4aPluginSettingsFormFieldValidateMsg)); 
	} 
			
	protected function sanitize(&$i4aTextString) { 
		return $i4aTextString = trim(stripslashes(sanitize_text_field($i4aTextString))); 
	} 
}