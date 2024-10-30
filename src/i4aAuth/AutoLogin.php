<?php
namespace i4aAuth; 
class AutoLogin { 
	private $ssoToken; 
	
	public function __construct() { 
		add_filter('init', array($this, 'i4a_autologin'), 10, 3); 
	} 
	

	public function i4a_autologin() { 			
	
		global $wpdb;
		
		// don't run autoLogin if in WP admin OR if they are already logged in to WP 
		if( is_admin() || is_user_logged_in() ) {
			return false;
		}
		
		
		try { 
			
			if( isset($_COOKIE['amsID']) && $_COOKIE['amsID'] <> '' && $_COOKIE['amsID'] <> 0) {
				
				$amsID = $_COOKIE['amsID'];
				
				// get the user by their amsID
				$i4a_WpUserMetaQuery = $wpdb->get_row(sprintf('select * from %s where meta_value like "%s" limit 1', $wpdb->usermeta, '%\\"cst_id\\";i:' . (int) esc_sql($amsID) . ';%'));
				if($i4a_WpUserMetaQuery){
					$i4a_wpID = $i4a_WpUserMetaQuery->user_id;
				} else {
					$i4a_wpID = 0;
				}
				$i4aWordpressUserObj = get_user_by('id', $i4a_wpID);
				
			
				// authenticate against the API with the user's amsID and return the i4aUser object
				$i4aUser = $this->authenticateByAmsID($amsID);
				
				if($i4aUser->cst_id == 0){
					// do nothing; we can't find a valid user for WP login by their amsID	
					return false;
				}
				
				// continue on if we do have a valid i4a user ...
				
				// get the user's wordpress details from the api return object
				$i4aUserWordpressDetails = array(
					'user_email' => strtolower($i4aUser->EmailAddress), 
					'user_login' => strtolower($i4aUser->EmailAddress), 
					'first_name' => ucwords($i4aUser->ind_first_name), 
					'last_name' => ucwords($i4aUser->ind_last_name), 
					'display_name' => ucwords($i4aUser->ind_first_name) . ' ' . ucwords($i4aUser->ind_last_name)
				); 
				
				$userNickname = $i4aUser->nickname;
				
				if (!is_object($i4aWordpressUserObj)) { 
					// add new WP user
					$i4aWordpressInsertedUser = wp_insert_user($i4aUserWordpressDetails); 
					if ($i4aWordpressInsertedUser instanceof \WP_Error) { 
						if (array_key_exists('existing_user_email', $i4aWordpressInsertedUser->errors)) { 
							$i4aWordpressInsertedUser = get_user_by('email', $i4aUserWordpressDetails['user_email']); 
						} 
					} 
					$i4aWordpressUserObj = new \WP_User($i4aWordpressInsertedUser);  
				} else { 
					// update exiswting WP user
					wp_update_user($i4aUserWordpressDetails + array('ID' => $i4aWordpressUserObj->ID)); 
				} 
				
				// now that we have a user in Wordpress, run custom update of roles with member and contact types if enabled in the plugin
				$i4aWpOption = get_option('internet4associations');
				if ( isset($i4aWpOption['single_sign_on']['enable_membercontacttypes']) ) {
					$this->i4aCustomUpdateRoles($i4aUser, $i4aWordpressUserObj);
				}
				
				// if the user is not a "current" member, then remove their "subscriber" role and add the role of "i4a: Non-Member" to their user record in WP
				// Note: we will leave any other contact types that they have on their record so that they have access to contact-type data
				$i4aUserMembershipStatus = strtolower($i4aUser->member_status);
				if($i4aUserMembershipStatus != 'current') {
					$i4aWordpressUserObj->remove_role('subscriber');
					$i4aWordpressUserObj->add_role('i4a_nonmember');
				} else {
					// make sure user has "Subscriber" role assigned in case they lost it when membership expired or if they were originally created as non-members
					$i4aWordpressUserObj->add_role('subscriber');
				}
				
				// finally, log the user in to Wordpress 
				$this->setSession($i4aUser, $i4aWordpressUserObj, $userNickname);
				
			}
			
		} catch (\Exception $i4aAutoLoginError) { 
			$i4aWordpressUserObj = new \WP_Error('denied', __('Automatic Login failed. Something wrong with your code.<br>')); 
		} 
		return true; 
	} 
	
	
	
	protected function setSession($i4aUser, $i4aWordpressUserObj, $userNickname) { 
		
		$thisSsoToken = strval($this->ssoToken);
		
		$i4aUserData = array('cst_id' => (int) $i4aUser->cst_id, 'cst_key' => (string) $i4aUser->cst_key, 'sso_token' => $thisSsoToken);

		update_user_meta($i4aWordpressUserObj->ID, 'internet4associations', $i4aUserData); 
		update_user_meta($i4aWordpressUserObj->ID, 'nickname',  (string) $userNickname);
		
		if (!session_id()) { 
			session_start(); 
		} 
		$_SESSION += array('internet4associations' => $i4aUserData);
	
		
		$i4aServerInfo = function ($i4aSiteURL) { 
			if (!preg_match('/^http/', $i4aSiteURL)) { 
				$i4aSiteURL = 'http://' . $i4aSiteURL; 
			} 
			if ($i4aSiteURL[strlen($i4aSiteURL) - 1] != '/') { 
				$i4aSiteURL .= '/'; 
			} 
			$i4aParsedURL = parse_url($i4aSiteURL); 
			$i4aParsedURLHostname = isset($i4aParsedURL['host']) ? $i4aParsedURL['host'] : ''; 
			if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\\-]{1,63}\\.[a-z\\.]{2,6})$/i', $i4aParsedURLHostname, $i4aMatchString)) { 
				$i4aReturnURLHostName = preg_replace('/^www\\./', '', $i4aMatchString['domain']); 
				return $i4aReturnURLHostName; 
			} 
			return false; 
		}; 
		
		// set ssoToken cookie to expire within 1 day - required to automatically log the user into the i4a-hosted website without another login request
		@setcookie('ssoToken', $i4aUserData['sso_token'], time() + 86400, '/', $i4aServerInfo($_SERVER['HTTP_HOST']), false);
		
		// log the user in to Wordpress
		wp_clear_auth_cookie();
		wp_set_current_user ( $i4aWordpressUserObj->ID );
		wp_set_auth_cookie  ( $i4aWordpressUserObj->ID );
		
	} 
	
	
	protected function authenticateByAmsID($amsID) {
		$i4aWpOption = get_option('internet4associations');

		if (!is_array($i4aWpOption) || empty($i4aWpOption['single_sign_on']['wsdl'])) { 
			throw new \Exception('Something went wrong, credentials not set.'); 
		} 
		
		$amsIDUsernameLookupVal = 'amsid_' . $amsID;
		
		$i4aApiObj = array(
				'debug' => false, 
				'ttl' => 12, 
				'timeout' => $i4aWpOption['connection']['timeout'], 
				'wsdl' => $i4aWpOption['single_sign_on']['wsdl'],
				'username' => $i4aWpOption['single_sign_on']['username'], 
				'password' => $i4aWpOption['single_sign_on']['password'], 
				'credentials' => array('username' => $amsIDUsernameLookupVal, 'password' => 'amsIDLookup')
		); 
		
		$i4aUser = new \Internet4Associations\Providers\ServiceProvider($i4aApiObj);
		
		$this->ssoToken = $i4aUser->simple->getSsoToken(); 
		$myReturnVar = $i4aUser->simple->getCustomerByKey();
			
		return $myReturnVar;	
			
	} 
	
	
	private function getWordpressUserData($i4aWPUsername, $i4aWordpressUserReturnVar) { 
		global $wpdb; 
		if ($i4aWPUsername <= 0) { 
			return false; 
		} 
		$i4aWpUserObj = $wpdb->get_row(sprintf('select * from %s where meta_value like "%s" limit 1', $wpdb->usermeta, '%\\"cst_id\\";i:' . (int) esc_sql($i4aWPUsername) . ';%')); 
		if (!is_object($i4aWpUserObj)) { 
			return false; 
		} 
		return $i4aWordpressUserReturnVar != $i4aWpUserObj->user_id ? $i4aWpUserObj->user_id : false; 
	} 
	
	
	protected function i4aCustomUpdateRoles( $i4aUser, $i4aWordpressUserObj ) {
	
		if ( ! empty( $i4aUser->roles->role ) ) {
			
			$i4aWordpressUserID = $i4aWordpressUserObj->ID;
			
			// get list of i4a roles in that are in the WordPress database (we are looking only for role types that start with a prefix of "i4a_")
			// remove all i4a roles upon login so that they can be reinserted with the current values
			$roles = wp_roles()->get_names();
			$i4aSearchPrefix = 'i4a_';
			foreach($roles as $key=>$value){
				if (strpos($key, $i4aSearchPrefix) !== false) {
					$i4aWordpressUserObj->remove_role($key);
				}
			}
			
			// get the updated list of i4a user roles from i4a database and add them to WP for this user
			$i4aUserRolesObj = $i4aUser->roles->role;
			foreach($i4aUserRolesObj as $key=>$value){
				$thisI4aUserRoleDisplayName = (string) $value;
				$thisI4aUserRole = $this->i4aCustomSetRoleValues($thisI4aUserRoleDisplayName);
				$i4aWordpressUserObj->add_role($thisI4aUserRole);
			}
			
		}
	}
	
	protected function i4aCustomSetRoleValues($thisI4aUserRole) {
		$thisRoleValue = strtolower($thisI4aUserRole);
		$thisRoleValue = preg_replace('/[^A-Za-z0-9]/', '', $thisRoleValue); // Removes special chars and spaces
		$thisRoleValue = preg_replace('/i4a/', 'i4a_', $thisRoleValue, 1);	// Set prefix of "i4a_" to all i4a custom roles
		return $thisRoleValue;
	}

}