<?php
namespace i4aAuth\Views;
use WP\Views\Page;
use WP\Views\Input;
class Render extends Page {

	public static $default = 'Internet4Associations';

	public static $plugin = array('i4a SSO'
			=> array(
				'uri' => 'Internet4Associations',
				'menu_logo' => '/images/favicon.png',
				'pages' => array(
						'settings' => 'Internet4Associations', 
						'help' => 'Internet4Associations_help', 
						'import roles' => 'Internet4Associations_import'
					)
			)
	);

	public static function getHeaderLogo() {
		printf('<img alt="i4a" src="../wp-content/plugins/internet4associations-single-sign-on/assets/images/i4a-header-logo-new.png" width="260" height="77"/>', NULL);
	}

	public static function getHeader() {
		/* placeholder */
	}

	public static function getFooter() {
		printf('<small style="float: right;">&copy; <a href="%s" target="_blank">Internet4Associations</a>, %s. All rights reserved. v1.44.</small>',
				esc_url('http://www.i4a.com'),
				date('Y')
		);
	}

}