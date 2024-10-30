<?php
namespace WP\Views; 
use WP\Traits\Helpers; 
class Page { 
	use Helpers; 
	
	public static $default; 
	
	public static $static; 
	
	public function __construct() { 
		self::set('caller', get_called_class()); $i4aPageSettings = array('i4a_logo' => 'getHeaderLogo', 'i4a_head' => 'getHeader', 'i4a_tabs' => 'getTabs', 'i4a_foot' => 'getFooter'); 
		add_action('admin_menu', array($this, 'makeView')); 
		array_walk($i4aPageSettings, function ($i4aPageSettingActions, $i4aPageAction) { 
			if (method_exists(self::get('caller'), $i4aPageSettingActions)) { 
				add_action($i4aPageAction, array(self::get('caller'), $i4aPageSettingActions)); 
			} 
		}); 
	} 
	
	public static function getTpl() { 
		$i4aTemplate = self::get('namespace') . '\\' . ucfirst(camel_case(self::get('page'))); 
		class_exists($i4aTemplate) ? add_action('i4a_body', array($i4aTemplate, 'getInstance')) : null; include_once self::get('templatePath') . '/main.tpl'; if (!class_exists($i4aTemplate)) { 
			dd('class ' . $i4aTemplate . ' doesn\'t exist.'); 
		} 
	} 
	
	public static function makeView() { 
		self::bootstrap(); 
		$i4aCallingPage = self::get('caller'); 
		array_walk($i4aCallingPage::$plugin, function ($i4aPageSettingActions, $i4aPageAction) use($i4aCallingPage) { 
			if (!self::isMenuItemExists($i4aPageAction)) { 
				add_menu_page(ucwords($i4aPageAction), $i4aPageAction, 'manage_options', $i4aPageSettingActions['uri'], array($i4aCallingPage, 'getTpl'), self::get('assetsUrl') . $i4aPageSettingActions['menu_logo']); 
			} 
			array_walk($i4aPageSettingActions['pages'], 
					function ($i4SubmenuPages, $i4aSubmenuText) use($i4aPageSettingActions, $i4aCallingPage) { 
						add_submenu_page($i4aPageSettingActions['uri'], ucwords($i4aSubmenuText), ucwords($i4aSubmenuText), 'manage_options', $i4SubmenuPages, array($i4aCallingPage, 'getTpl')); 
					}
			); 
		}
		); 
	} 
	
	public static function isMenuItemExists($i4aMenuItem) { 
		global $menu; 
		$i4MenuItemExists = false; 
		array_walk($menu, function ($i4aPageSettingActions) use($i4aMenuItem, &$i4MenuItemExists) { 
			if (preg_match('/^' . trim($i4aMenuItem) . '$/i', $i4aPageSettingActions[0])) { $i4MenuItemExists = true; } 
		}); 
		return $i4MenuItemExists; 
	} 
	
	protected static function makeTab($i4aTabLabel, $i4aSubmenuText, $i4aSubmenuAltText = null) { 
		$i4aPageTab = $i4aSubmenuText == self::getCurrentPage() ? 'nav-tab-active' : ''; 
		printf('<a class=\'nav-tab %s\' href=\'?page=%s\' title=\'%s\'>%s</a>', $i4aPageTab, is_null($i4aSubmenuText) ? self::getCurrentPage() : $i4aSubmenuText, ucfirst($i4aSubmenuAltText), ucwords($i4aTabLabel)); 
	} 
	
	public static function getTabs() { 
		$i4aCallingPage = self::get('caller'); 
		array_walk(array_shift($i4aCallingPage::$plugin)['pages'], function ($i4SubmenuPages, $i4aSubmenuText) {
			self::makeTab($i4aSubmenuText, $i4SubmenuPages); 
		}); 
	} 

}