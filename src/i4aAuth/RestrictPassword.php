<?php
namespace i4aAuth; 
class RestrictPassword { 
	public function __construct() { 
		add_filter('show_password_fields', array($this, 'disable')); 
		add_filter('allow_password_reset', array($this, 'disable')); 
		add_filter('gettext', array($this, 'remove')); 
	} 
	
	public function disable() { 
		if (is_admin()) { 
			$i4aCurrentWPUser = wp_get_current_user(); 
			$i4aNewWPUser = new \WP_User($i4aCurrentWPUser->ID); 
			return !empty($i4aNewWPUser->roles) && is_array($i4aNewWPUser->roles) && array_shift($i4aNewWPUser->roles) == 'administrator'; 
		} 
		return false; 
	} 
	
	public function remove($i4aTextString) { 
		return preg_replace('/lost your password\\??/is', '', $i4aTextString); 
	} 
}