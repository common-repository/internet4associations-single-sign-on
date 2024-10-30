<?php
namespace Internet4Associations\Traits; 
trait SingletonTrait { 
	protected static $instance = null; 
	protected function __clone() { } 
	protected function __construct() { } 
	
	public static function getInstance() { 
		if (static::$instance === null) { 
			static::$instance = new static(); 
		} 
		return static::$instance; 
	} 
	
	public static function setInstance($i4aSingletonObj) { 
		return static::$instance = $i4aSingletonObj; 
	} 
}