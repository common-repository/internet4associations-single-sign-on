<?php
/**
 * Plugin Name: i4a Single Sign On
 * Description: Authenticate users to sign in using i4a credentials.
 * Version: 2.0
 * Author: Internet4Associations
 * Author URI: http://www.i4a.com/
 * License: GPLv3
 */
if( !function_exists( 'http_build_url' ) ) {
	require 'src/helpers.php'; 
}

registerAutoloader(); 

if (!defined('WPINC')) { 
	die; 
}

if (is_admin()) {
	require_once(dirname(__FILE__) . '/src/i4aAuth/Views/Internet4Associations.php');
	require_once(dirname(__FILE__) . '/src/i4aAuth/Views/Internet4AssociationsHelp.php');
	require_once(dirname(__FILE__) . '/src/i4aAuth/Views/Internet4AssociationsImport.php');
	require_once(dirname(__FILE__) . '/src/i4aAuth/Views/Render.php');

	new \i4aAuth\Views\Render();
}

new \i4aAuth\Authenticate(); 
new \i4aAuth\AutoLogin();						// new for bidirectional sso
new \i4aAuth\RestrictPassword(); 
new \i4aAuth\RemoveAdminBar();



// clear_i4a_cookies_on_logout: unset the cookies and force them to expire so they are not continually auto-logged back into the WP site after WP logout request
// ******************************************************************************************************************************************************
function clear_i4a_cookies_on_logout($i4a_cookieRootDomain) {
	$i4a_cookieRootDomain = i4a_getCookieRootDomain();
	setcookie( 'amsID', '', time() - ( 15 * 60 ), '/',  $i4a_cookieRootDomain, false);
	setcookie( 'ssoToken', '', time() - ( 15 * 60 ), '/', $i4a_cookieRootDomain, false );
}

add_action('wp_logout', 'clear_i4a_cookies_on_logout');


if (is_admin()) {
// import custom i4a roles from i4a to WordPress
// ******************************************************************************************************************************************************
function i4aCustomSetRoleValues($thisI4aRole) {
	$thisRoleValue = strtolower($thisI4aRole);
	$thisRoleValue = preg_replace('/[^A-Za-z0-9]/', '', $thisRoleValue); // Removes special chars and spaces
	$thisRoleValue = preg_replace('/i4a/', 'i4a_', $thisRoleValue, 1);	// Set prefix of "i4a_" to all i4a custom roles
	return $thisRoleValue;
}

function i4a_import_roles_action()
{
	
	/* test manually removing roles to see if they are added again and what it looks like when there are only a couple roles added
	remove_role('i4a_formula');
	remove_role('i4a_fixedorg');
	return;
	*/
	
	// import i4a roles with no capabilities; wp admin must use role management plugin to assign capabilities to i4a roles as desired
	$i4aWpOption = get_option('internet4associations');
	if (!is_array($i4aWpOption) || empty($i4aWpOption['single_sign_on']['wsdl'])) {
		throw new \Exception('Something went wrong, credentials not set.');
	}
	
	$i4aApiObj = array(
			'debug' => false,
			'ttl' => 12,
			'timeout' => 9,
			'wsdl' => $i4aWpOption['single_sign_on']['wsdl'],
			'username' => $i4aWpOption['single_sign_on']['username'],
			'password' => $i4aWpOption['single_sign_on']['password'],
			'stream_context' => stream_context_create(array(
					'ssl' => array(
							'verify_peer' => false,
							'verify_peer_name' => false,
							'allow_self_signed' => true
					)
			))
	);
	
	$i4aApiServiceProvider = new \Internet4Associations\Providers\ServiceProvider($i4aApiObj);
	$i4aMemberContactTypes = $i4aApiServiceProvider->simple->getMemberAndContactTypes();
	
	if (! empty( $i4aMemberContactTypes->roles )) {
		$i4aUserRolesObj = $i4aMemberContactTypes->roles->role;
		
		// get existing i4a WP roles so we don't try to add ones that already exist
		$roles = wp_roles()->get_names();
		$i4aWpRoles = [];
		$i4aSearchPrefix = 'i4a_';
		foreach($roles as $key=>$value){
			if (strpos($key, $i4aSearchPrefix) !== false) {
				array_push($i4aWpRoles, $key);
			}
		}
		
		// add new i4a roles to WordPress
		$i4aRoles = [];
		$newRolesList = '';
		foreach($i4aUserRolesObj as $key=>$value){
			$thisI4aRoleDisplayName = (string) $value;
			$thisI4aRole = i4aCustomSetRoleValues($thisI4aRoleDisplayName);
			if( ! in_array($thisI4aRole, $i4aWpRoles) ) {
				array_push($i4aRoles, $thisI4aRole);
				add_role($thisI4aRole, $thisI4aRoleDisplayName, array());	 // Example Code: add_role('i4a_membertypename', 'i4a: Member Type Name', array());
				$newRolesList .= $thisI4aRoleDisplayName . '<br>';	// save the newly added roles to the list
			}
		}
		
		$newRoleCount = count($i4aRoles);
		
		// set message to display results of import
		if($newRoleCount > 0) {
			echo '<div id="i4aImportResultMessage" class="updated"><p>'
					. '<b>Import Complete!</b><br><br>The following i4a Member Types and Contact Types have been imported:</b><br><br><i>' . $newRolesList .'</i></p></div>';
		} else {
			echo '<div id="i4aImportResultMessage" class="updated"><p><b>Import Complete!</b><br><br>There were no new i4a Member Types or Contact Types found to import.</p></div>';
		}
		
	} else {
		
		echo '<div id="i4aImportResultMessage" class="updated"><p>'
				.'<b>Import Complete!</b><br><br>There were no active Member Types or Contact Types found to import.' . '</p></div>';
	}
}

}

