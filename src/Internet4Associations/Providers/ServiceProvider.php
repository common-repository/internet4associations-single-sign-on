<?php
namespace Internet4Associations\Providers; 
use Internet4Associations\Request; 
class ServiceProvider { 
	public function __construct(array $config) { 
		$i4aWsdl = $config['wsdl']; 
		$this->simple = new Request($i4aWsdl, $config); 
	} 
}