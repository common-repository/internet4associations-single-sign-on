<?php
namespace i4aAuth; 
class RemoveAdminBar { 
	private $menuItems = array('Dashboard'); 
	public function __construct() { 
		add_filter('after_setup_theme', array($this, 'removeAdminBar')); 
		add_action('admin_menu', array($this, 'removeAdminMenuItems')); 
	} 
	private function i4aAdminBarCheckCanManageOptions() { 
		return !current_user_can('manage_options'); 
	} 
	public function removeAdminBar() { 
		if (!$this->i4aAdminBarCheckCanManageOptions()) { 
			return false; 
		} 
		show_admin_bar(false);
		add_filter('show_admin_bar', '__return_false'); 
		add_filter('wp_admin_bar_class', '__return_false'); 
	} 
	public function removeAdminMenuItems() { 
		global $menu; 
		if (!$this->i4aAdminBarCheckCanManageOptions()) { 
			return false; 
		} 
		$i4aAdminBarMenuItems = array_filter($this->menuItems, '__'); 
		end($menu); 
		while (prev($menu)) { 
			$i4aMenuItem = explode(' ', $menu[key($menu)][0]); 
			if (in_array($i4aMenuItem[0] != null ? $i4aMenuItem[0] : '', $i4aAdminBarMenuItems)) { 
				unset($menu[key($menu)]); 
			} 
		} 
		if (preg_match('/src\\s*=\\s*(\'|")(.*?)("|\')/', get_avatar(get_current_user_id(), 20), $i4aAdminUserAvatar)) { 
			$menu[] = array('Log Out', 'read', wp_logout_url(), 'Logout', 'menu-top', 'logout', $i4aAdminUserAvatar[2]); 
		} 
	} 
}