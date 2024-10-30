<?php
namespace i4aAuth\Views; 
use WP\Views\View; 
class Internet4Associations extends View { 
	
	protected $fields = array(
			'Single Sign-On' => array(
					'desc' => '', 
					'fields' => array(
							'wsdl' => array(
									'title' => 'i4a SSO API WSDL Url', 
									'desc' => 'i4a SSO API WSDL Url.', 
									'required' => true
							),
							'username' => array(
									'title' => 'i4a SSO API Username', 
									'desc' => 'Username to the i4a SSO API user account, format (a-z 0-9 _-)',  
									'required' => true
							),
							'password' => array(
									'title' => 'i4a SSO API Password', 
									'desc' => 'Password to the i4a SSO API user account.', 
									'required' => true, 
									'callback' => 'passwordfield'
							),
							'enable_membercontacttypes' => array(
									'title' => 'Member/Contact Type Sync?',
									'desc' => '',
									'required' => false, 
									'checkbox' => true
							), 
							'enable_nonmembers' => array(
									'title' => 'Enable Non-Member Logins?',
									'desc' => '',
									'required' => false,
									'checkbox' => true
							)
					)
			), 
			'Connection' => array(
					'desc' => '',
					'fields' => array(
							'timeout' => array(
									'title' => 'Connction Reply Timeout',
									'desc' => 'How long to wait to hear a reply from i4a.',
									'validate' => array('\\d{1,2}', 'must be numeric.'),
									'required' => true, 'default' => 9
							),
							'connect_timeout' => array(
									'title' => 'Connection Timeout',
									'desc' => 'How long to wait for the initial connection.',
									'validate' => array('\\d{1,2}', 'must be numeric.'),
									'required' => true,
									'default' => 9
							),
					)
			)
	); 

}