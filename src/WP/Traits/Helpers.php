<?php
namespace WP\Traits; 
trait Helpers { 
	
	public static function set($i4aCalledClassName, $i4aCalledClassNameVal, $i4aExternalClass = null) { 
		$i4aCalledClass = is_null($i4aExternalClass) ? get_called_class() : $i4aExternalClass; 
		self::$static[$i4aCalledClass][$i4aCalledClassName] = $i4aCalledClassNameVal; 
	} 
	
	public static function get($i4aCalledClassName, $i4aExternalClass = null) { 
		$i4aCalledClass = is_null($i4aExternalClass) ? get_called_class() : $i4aExternalClass; 
		return isset(self::$static[$i4aCalledClass][$i4aCalledClassName]) ? self::$static[$i4aCalledClass][$i4aCalledClassName] : null; 
	} 
	
	public static function whoami() { 
		return get_called_class(); 
	} 
	
	public static function bootstrap() { 
		$i4aCalledClass = get_called_class(); 
		$i4aPluginFileName = new \ReflectionObject(new $i4aCalledClass()); $i4aPluginFile = dirname($i4aPluginFileName->getFileName()); $i4aPluginFile = dirname(dirname($i4aPluginFile)); 
		self::set('pluginPath', plugin_dir_path($i4aPluginFile)); 
		self::set('pluginUrl', plugin_dir_url($i4aPluginFile)); 
		self::set('page', self::getCurrentPage()); 
		self::set('caller', get_called_class()); 
		self::set('namespace', self::getNamespace(get_called_class())); 
		self::set('assetsUrl', self::get('pluginUrl') . 'assets'); 
		self::set('templatePath', self::get('pluginPath') . 'assets/templates'); 
		self::set('jsPath', self::get('pluginPath') . 'assets/javascripts'); 
	} 
	
	public static function getCurrentPage() { 
		return isset($_GET['page']) ? sanitize_title($_GET['page']) : self::getDefaultPage(); 
	} 
	
	public static function getJsPath($i4aPluginFileAssets = null) { 
		$i4aPluginPath = is_null($i4aPluginFileAssets) ? self::get('pluginPath') : dirname($i4aPluginFileAssets); 
	} 
	
	public static function getAssetsUrl($i4aPluginFileAssets = null) { 
		$i4aPluginPath = is_null($i4aPluginFileAssets) ? self::get('pluginUrl') : plugin_dir_url($i4aPluginFileAssets); 
		$i4aAssetsUrl = self::get('assetsUrl'); 
		return empty($i4aAssetsUrl) ? esc_url_raw($i4aPluginPath . 'assets') : esc_url_raw($i4aAssetsUrl); 
	}
	
	public static function getTemplatesPath($i4aPluginFileAssets = null) { 
		$i4aPluginPath = is_null($i4aPluginFileAssets) ? self::get('pluginPath') : dirname(dirname(dirname($i4aPluginFileAssets))); 
		$i4aTemplatePath = self::get('templatePath'); 
		return empty($i4aTemplatePath) ? $i4aPluginPath . '/assets/templates' : $i4aTemplatePath; 
	} 
	
	public static function getDefaultPage() { 
		return self::$default; 
	} 
	
	public static function getPluginInfo() { 
		return get_plugin_data(self::get('pluginPath') . '/' . self::getPluginFileName()); 
	} 
	
	public static function getPluginVersion() { 
		return self::getPluginInfo()['Version']; 
	} 
	
	public static function getPluginFileName($i4aPluginFileNameHasExtension = true) { 
		return plugin_basename(self::get('pluginPath')) . ($i4aPluginFileNameHasExtension ? '.php' : ''); 
	} 
	
	public static function getNamespace($i4aPluginNamespace) { 
		return '\\' . substr($i4aPluginNamespace, 0, strrpos($i4aPluginNamespace, '\\')); 
	} 

}