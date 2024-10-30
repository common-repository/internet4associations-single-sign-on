<?php
define('HTTP_URL_REPLACE', 1); 
define('HTTP_URL_JOIN_PATH', 2); 
define('HTTP_URL_JOIN_QUERY', 4); 
define('HTTP_URL_STRIP_USER', 8); 
define('HTTP_URL_STRIP_PASS', 16); 
define('HTTP_URL_STRIP_AUTH', 32); 
define('HTTP_URL_STRIP_PORT', 64); 
define('HTTP_URL_STRIP_PATH', 128); 
define('HTTP_URL_STRIP_QUERY', 256); 
define('HTTP_URL_STRIP_FRAGMENT', 512); 
define('HTTP_URL_STRIP_ALL', 1024); 

function http_build_url($i4aURL, $i4aUrlDataArray = array(), $i4aReplacedUrl = HTTP_URL_REPLACE, &$i4aFixedParseUrl = false) { 
	$i4aUrlComponents = array('user', 'pass', 'port', 'path', 'query', 'fragment'); 
	
	if ($i4aReplacedUrl & HTTP_URL_STRIP_ALL) { 
		$i4aReplacedUrl |= HTTP_URL_STRIP_USER; 
		$i4aReplacedUrl |= HTTP_URL_STRIP_PASS; 
		$i4aReplacedUrl |= HTTP_URL_STRIP_PORT; 
		$i4aReplacedUrl |= HTTP_URL_STRIP_PATH; 
		$i4aReplacedUrl |= HTTP_URL_STRIP_QUERY; 
		$i4aReplacedUrl |= HTTP_URL_STRIP_FRAGMENT; 
	} else { 
		if ($i4aReplacedUrl & HTTP_URL_STRIP_AUTH) { 
			$i4aReplacedUrl |= HTTP_URL_STRIP_USER; 
			$i4aReplacedUrl |= HTTP_URL_STRIP_PASS; 
		} 
	} 
	
	$i4aParsedUrl = parse_url($i4aURL); 
	
	if (isset($i4aUrlDataArray['scheme'])) { 
		$i4aParsedUrl['scheme'] = $i4aUrlDataArray['scheme']; 
	} 
	
	if (isset($i4aUrlDataArray['host'])) { 
		$i4aParsedUrl['host'] = $i4aUrlDataArray['host']; 
	} 
	
	if ($i4aReplacedUrl & HTTP_URL_REPLACE) { 
		foreach ($i4aUrlComponents as $i4aThisUrlComponent) { 
			if (isset($i4aUrlDataArray[$i4aThisUrlComponent])) { 
				$i4aParsedUrl[$i4aThisUrlComponent] = $i4aUrlDataArray[$i4aThisUrlComponent]; 
			} 
		} 
	} else { 
		if (isset($i4aUrlDataArray['path']) && $i4aReplacedUrl & HTTP_URL_JOIN_PATH) { 
			if (isset($i4aParsedUrl['path'])) { 
				$i4aParsedUrl['path'] = rtrim(str_replace(basename($i4aParsedUrl['path']), '', $i4aParsedUrl['path']), '/') . '/' . ltrim($i4aUrlDataArray['path'], '/'); 
			} else { 
				$i4aParsedUrl['path'] = $i4aUrlDataArray['path']; 
			} 
		} 
		if (isset($i4aUrlDataArray['query']) && $i4aReplacedUrl & HTTP_URL_JOIN_QUERY) { 
			if (isset($i4aParsedUrl['query'])) { 
				$i4aParsedUrl['query'] .= '&' . $i4aUrlDataArray['query']; 
			} else { 
				$i4aParsedUrl['query'] = $i4aUrlDataArray['query']; 
			} 
		} 
	} 
	
	foreach ($i4aUrlComponents as $i4aThisUrlComponent) { 
		if ($i4aReplacedUrl & (int) constant('HTTP_URL_STRIP_' . strtoupper($i4aThisUrlComponent))) { 
			unset($i4aParsedUrl[$i4aThisUrlComponent]); 
		} 
	} 
	
	$i4aFixedParseUrl = $i4aParsedUrl; 
	
	return (
			isset($i4aParsedUrl['scheme']) ? $i4aParsedUrl['scheme'] . '://' : '') . 
			(isset($i4aParsedUrl['user']) ? $i4aParsedUrl['user'] . 
			(isset($i4aParsedUrl['pass']) ? ':' . $i4aParsedUrl['pass'] : '') . '@' : '') . 
			(isset($i4aParsedUrl['host']) ? $i4aParsedUrl['host'] : '') . 
			(isset($i4aParsedUrl['port']) ? ':' . $i4aParsedUrl['port'] : '') . 
			(isset($i4aParsedUrl['path']) ? $i4aParsedUrl['path'] : '') . 
			(isset($i4aParsedUrl['query']) ? '?' . $i4aParsedUrl['query'] : '') . 
			(isset($i4aParsedUrl['fragment']) ? '#' . $i4aParsedUrl['fragment'] : ''); 
} 
 


function colorize($i4aColorizeMsg, $i4aColorizeColor = 'blue') { 
	$i4aColorizeInitMsg = ''; 
	switch ($i4aColorizeColor) { 
		case 'green': case 'SUCCESS': $i4aColorizeInitMsg = '[42m'; break; 
		case 'red': case 'FAILURE': $i4aColorizeInitMsg = '[41m'; break; 
		case 'yellow': case 'WARNING': $i4aColorizeInitMsg = '[43m'; break; 
		case 'blue': case 'NOTE': $i4aColorizeInitMsg = '[44m'; break; 
		default: throw new Exception('Invalid status: ' . $i4aColorizeColor); 
	} 
	return chr(27) . "{$i4aColorizeInitMsg}" . "{$i4aColorizeMsg}" . chr(27) . '[0m'; 
} 



function prettyXML($i4aXmlString) { 
	if (!$i4aXmlString || !class_exists('DomDocument')) { 
		return $i4aXmlString; 
	} 
	$i4aDomDocument = new \DomDocument('1.0'); $i4aDomDocument->preserveWhiteSpace = false; $i4aDomDocument->formatOutput = true; $i4aDomDocument->loadXML($i4aXmlString); return $i4aDomDocument->saveXML(); 
} 
 


function autoload_psr4($i4aAutoLoadURL) { 
	$i4aAutoLoadUrlSubstr = $i4aFinalAutoLoadPath = ''; 
	$i4aAutoLoadFileTypes = array('.php', '.class.php', '.inc'); 
	$i4aDirName = dirname(__FILE__); 
	if (false !== ($i4aBackslashPos = strripos($i4aAutoLoadURL, '\\'))) { 
		$i4aAutoLoadUrlSubstr = substr($i4aAutoLoadURL, 0, $i4aBackslashPos); 
		$i4aAutoLoadURL = substr($i4aAutoLoadURL, $i4aBackslashPos + 1); 
		$i4aFinalAutoLoadPath = str_replace('\\', DIRECTORY_SEPARATOR, $i4aAutoLoadUrlSubstr) . DIRECTORY_SEPARATOR; } $i4aFinalAutoLoadPath .= str_replace('_', DIRECTORY_SEPARATOR, $i4aAutoLoadURL); 
		$i4aDirectoryPath = $i4aDirName . DIRECTORY_SEPARATOR . $i4aFinalAutoLoadPath; 
		foreach ($i4aAutoLoadFileTypes as $i4aAutoLoadFileType) { 
			if (file_exists($i4aDirectoryPath . $i4aAutoLoadFileType)) { 
				require_once $i4aDirectoryPath . $i4aAutoLoadFileType; 
			} 
		} 
} 


function autoload_psr0($i4aAutoLoadURL) { 
	$i4aAutoLoadFileTypes = array('.php', '.class.php', '.inc'); 
	$i4aReplacedPath = str_replace(__NAMESPACE__ . '\\', '', __CLASS__); 
	$i4aRealPath = realpath(__DIR__) . DIRECTORY_SEPARATOR; 
	if (substr($i4aRealPath, -strlen($i4aReplacedPath)) === $i4aReplacedPath) { 
		$i4aRealPath = substr($i4aRealPath, 0, -strlen($i4aReplacedPath)); 
	} 
	$i4aAutoLoadURL = ltrim($i4aAutoLoadURL, '\\'); 
	$i4aFinalAutoLoadPath = $i4aRealPath; 
	$i4aAutoLoadUrlSubstr = ''; 
	if ($i4aBackslashPos = strripos($i4aAutoLoadURL, '\\')) { 
		$i4aAutoLoadUrlSubstr = substr($i4aAutoLoadURL, 0, $i4aBackslashPos); 
		$i4aAutoLoadURL = substr($i4aAutoLoadURL, $i4aBackslashPos + 1); 
		$i4aFinalAutoLoadPath .= str_replace('\\', DIRECTORY_SEPARATOR, $i4aAutoLoadUrlSubstr) . DIRECTORY_SEPARATOR; } $i4aFinalAutoLoadPath .= str_replace('_', DIRECTORY_SEPARATOR, $i4aAutoLoadURL); 
		foreach ($i4aAutoLoadFileTypes as $i4aAutoLoadFileType) { 
			if (file_exists($i4aFinalAutoLoadPath . $i4aAutoLoadFileType)) { 
				require_once $i4aFinalAutoLoadPath . $i4aAutoLoadFileType; 
			} 
		} 
} 


 
function registerAutoloader($i4aAutoLoaderType = 'psr4') { 
	spl_autoload_register('autoload_' . $i4aAutoLoaderType); 
} 



function dd($i4aTextString, $i4aQuitAfterDDFunction = false) { 
	$i4aWebServerType = php_sapi_name() == 'cli' ? '' : '<br>'; 
	if (is_string($i4aTextString)) { 
		echo $i4aTextString . $i4aWebServerType; 
		if ($i4aQuitAfterDDFunction) { die; } 
		return; 
	} 
	if (php_sapi_name() == 'cli') { 
		print_r($i4aTextString); 
	} else { 
		echo '<pre>'; 
		print_r($i4aTextString); 
		echo '</pre>'; 
	} 
	if ($i4aQuitAfterDDFunction) { die; } 
} 


function printfa($i4aFunctionArg, $i4aArrayArg) { 
	return call_user_func_array('printf', array_merge((array) $i4aFunctionArg, $i4aArrayArg)); 
} 
 

 
function camel_case($i4aStringToFix) { 
	$i4aFixedStringArray = array(); 
	if (isset($i4aFixedStringArray[$i4aStringToFix])) { 
		return $i4aFixedStringArray[$i4aStringToFix]; 
	} 
	return $i4aFixedStringArray[$i4aStringToFix] = lcfirst(studly($i4aStringToFix)); 
} 



function studly($i4aStringToFix) { 
	$i4aStudlyRetArray = array(); 
	$i4aThisUrlComponent = $i4aStringToFix; 
	if (isset($i4aStudlyRetArray[$i4aThisUrlComponent])) { 
		return $i4aStudlyRetArray[$i4aThisUrlComponent]; 
	} 
	$i4aStringToFix = ucwords(str_replace(array('-', '_'), ' ', $i4aStringToFix)); 
	return $i4aStudlyRetArray[$i4aThisUrlComponent] = str_replace(' ', '', $i4aStringToFix); 
} 



function snake_case($i4aStringToFix, $i4aDelimeter = '_') { 
	$i4aReturnArr = array(); 
	$i4aThisUrlComponent = $i4aStringToFix . $i4aDelimeter; 
	if (isset($i4aReturnArr[$i4aThisUrlComponent])) { 
		return $i4aReturnArr[$i4aThisUrlComponent]; 
	} 
	if (!ctype_lower($i4aStringToFix)) { 
		$i4aStringToFix = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $i4aDelimeter, $i4aStringToFix)); 
	} 
	return $i4aReturnArr[$i4aThisUrlComponent] = $i4aStringToFix; 
} 


 
function starts_with($i4aStringArg1, $i4aSeachArray) { 
	foreach ((array) $i4aSeachArray as $i4aCurrentArrayItem) { 
		if ($i4aCurrentArrayItem != '' && strpos($i4aStringArg1, $i4aCurrentArrayItem) === 0) { 
			return true; 
		} 
	} 
	return false; 
} 
 

 
function contains($i4aStringArg1, $i4aSeachArray) { 
	foreach ((array) $i4aSeachArray as $i4aCurrentArrayItem) { 
		if ($i4aCurrentArrayItem != '' && strpos($i4aStringArg1, $i4aCurrentArrayItem) !== false) { 
			return true; 
		} 
	} 
	return false; 
} 
 


function ends_with($i4aStringArg1, $i4aSeachArray) { 
	foreach ((array) $i4aSeachArray as $i4aCurrentArrayItem) { 
		if ((string) $i4aCurrentArrayItem === substr($i4aStringArg1, -strlen($i4aCurrentArrayItem))) { 
			return true; 
		} 
	} 
	return false; 
} 
 

function finish($i4aStringToFix, $i4aInputStr) { 
	$i4aBackslashStr = preg_quote($i4aInputStr, '/'); 
	return preg_replace('/(?:' . $i4aBackslashStr . ')+$/', '', $i4aStringToFix) . $i4aInputStr; 
}  


function is($i4aCompareVal, $i4aStringToFix) { 
	if ($i4aCompareVal == $i4aStringToFix) { 
		return true; 
	} 
	$i4aCompareVal = preg_quote($i4aCompareVal, '#'); 
	$i4aCompareVal = str_replace('\\*', '.*', $i4aCompareVal) . '\\z'; 
	return (bool) preg_match('#^' . $i4aCompareVal . '#', $i4aStringToFix); 
} 



// new functions added for bidirectional SSO
// ------------------------------------------------------------

function i4a_getCookieRootDomain(){
	$i4a_WpSiteURL = $_SERVER['HTTP_HOST'];
	$i4a_hostParts = explode('.', $i4a_WpSiteURL);
	$i4a_hostParts = array_reverse($i4a_hostParts);
	$i4a_host = $i4a_hostParts[1] . '.' . $i4a_hostParts[0];
	$i4a_cookieRootDomain = '.' . $i4a_host;

	return $i4a_cookieRootDomain;
}
