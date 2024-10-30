<?php
namespace i4aAuth\Views; 
use WP\Views\View; 
use WP\Views\Page; 
class Internet4AssociationsHelp extends View { 
	protected $fields = array(); 
	
	public function __construct() { 
		$this->createHelpPage(); 
	} 

	private function createHelpPage() { 
		include_once Page::getTemplatesPath(__DIR__) . '/help.tpl'; 
	} 
}