=== i4a Single Sign-On  ===
Tags: sso, single sign-on, membership, AMS, association management, membership management
Requires at least: 3.0.1
Tested up to: 6.6.2 
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.6

Allows a bidirectional single sign-on between a WordPress website and an i4a-hosted website for current members who can sign in to either the WordPress site or i4a-hosted site using their i4a credentials.


== Description ==

The i4a Single Sign On (SSO) plugin for WordPress enables a SSO between our nationally recognized membership management software and a WordPress site hosted anywhere in the world. 

The plugin allows members, and non-members if you so choose, to log in to your WordPress site using their i4a credentials, eliminating the need for multiple usernames and passwords. And, if your WordPress site shares the same root domain as your i4a membership management website, your users will enjoy a seamless experience as they will be automatically logged in to the membership management site after logging in to the WordPress site and vice versa. 

In addition to providing a single sign-on, the plugin also allows you to restrict content in your WordPress site to one or more member types and one or more contact types. These types, saved as user roles in WordPress, can be easily imported into your WordPress site from your i4a membership management software. 

== Installation ==

The i4a SSO plugin connects to the i4a SSO web service API to authenticate users. This allows for single sign-on capabilities, where individuals can sign in to WordPress using their i4a credentials (username and password) instead of having to have a separate login for the WordPress website.  

For this plugin to work, you need to have licensed a current i4a software product and have set up your WordPress site on the same root domain as your i4a-hosted website.

Instructions:

1. You can either install the plugin inside the WordPress Dashboard or install it manually. To install the plugin  inside the WordPress Dashboard, select the "Add New" link from the "Plugins" menu on the left. Type "i4a sso" in the "Keyword" field to search for the plugin, then click on the "Install Now" button to install it. For manual installation, unzip the ‘i4a_sso_v2.zip’ in to the ‘/wp-content/plugins/’ directory.

2. After the plugin has been installed, activate the plugin through the "Plugins" menu in WordPress.

3. Once the plugin has been activated, go into the WordPress dashboard and configure the plugin by going into the admin page and clicking the sidebar menu item "i4a SSO".  

	The following settings must be configured:

  * i4a Single Sign On WSDL URL: this is the URL for the i4a SSO web service (usually of the form https://www.yourdomain.com/i4a/utilities/api/wordpress.cfc?WSDL) 
  * i4a SSO Web Service Username: the username for the i4a SSO web service 
  * i4a SSO Web Service Password: The password for the i4a SSO web service
   
  The i4a SSO Web Service Username and Password can be obtained from within your i4a site’s admin interface under Admin > API settings.  
  
  Note: If you do not know the password and your site uses the API for other purposes, do not change the password or the other connections to the API will be broken.
  
  The following settings are optional:
   
  * Member/Contact Type Sync: This setting must be enabled in order for you to import i4a Member and Contact Types into WordPress as custom user roles and for user’s member type and contact types to be synced upon login. Once you have enabled this option and saved your settings, you can then use the "Import Roles" utility in the plugin to import all active member and contact types from your i4a site into WordPress. Once the member and contact types have been imported, users will be automatically assigned any member or contact types that are on their i4a record upon next login.
 
  * Enable Non-Member Logins: This setting allows non-members and expired members to log into your WordPress site with a custom role of "i4a: Non-member". Non-members will not have the default WordPress role of "Subscriber" assigned to them. If you have not enabled Member and Contact Type sync, members are created with a role of "Subscriber" in WordPress and you can give access to members-only content with just the "Subscriber" role. Therefore, non-members will not have the "Subscriber" role assigned to them to ensure that they cannot inadvertently access members-only content in the WordPress site.
   
   

== Frequently Asked Questions ==

= How does this plugin authenticate users? =

The i4a SSO plugin uses the i4a SSO web service API to authenticate individuals with a user account in the i4a database. You can configure the plugin to authenticate and allow only active, non-expired members to log into your WordPress site. Or you can configure it to authenticate and allow active members, expired members and non-members to log in to your WordPress site. The SSO plugin will not authenticate staff or admin accounts.

= Does the plugin create new users in WordPress? =

Yes, a user will automatically be created in WordPress upon first login if they don’t exist already. The plugin does not save the user’s password in WordPress. The user will always need to sign in with their i4a credentials. Users need to navigate to the i4a-hosted website to change their passwords.

= How can I create hyperlinks to my i4a-hosted website so that the WordPress user doesn't have to login again? =

As long as your WordPress site is on the same root domain as your i4a-hosted website you don’t need to do anything special to create a hyperlink to your i4a-hosted website. When a user logs into your WordPress site, the i4a SSO plugin saves a single sign-on token for the user to a cookie in their web browser. Once the user visits any page on your i4a-hosted website, the i4a-hosted website will automatically detect that single sign-on token and log the user in to the i4a-hosted website without the user needing to manually log in again. 

= Will the user be automatically logged in to the WordPress website if they have already logged in to the i4a-hosted website? =

Yes, as long as your WordPress site is on the same root domain as your i4a-hosted website. If the user has already logged in to the i4a-hosted website and they are allowed to log in based on your plugin’s member/non-member configuration settings, they will be automatically logged into the WordPress site upon their first page visit to the WordPress site. The WordPress site looks for a cookie on the shared "root domain" of the website and if that cookie is found, will use the cookie to look up the user’s information in the i4a-hosted site database and log them in to the WordPress site automatically.

= Will the user's Member Type and Contact Type(s) be automatically updated if I have enabled Member and Contact Types in the WordPress plugin? =

Yes, each time a user logs into the WordPress site their member type and contact types will be updated to match their settings in the i4a-hosted website for all member and contact types that you have previously imported into WordPress. This means if a user was once a member and is now expired the user will no longer have the member type assigned to them as a role in WordPress. The opposite is also true. If a user was once a non-member but has since joined your association, their member type is assigned to them as a role in WordPress automatically upon their next login to the WordPress site, instantly giving them access to any member-restricted content in your WordPress site.

= If my Member and Contact Types change in my i4a-hosted website will they be automatically updated in WordPress? =

No, if you change or add any member or contact types in your i4a-hosted website you’ll need to run the "Import Roles" again from the SSO plugin page. If you delete any member or contact types in your i4a-hosted website you’ll need to manually remove those roles from WordPress using another third-party role management plugin.  

= If I allow Non-Member logins, but don’t enable Member and Contact Types, will non-members be able to access members-only content?  = 

No, non-members are only assigned a custom role of "i4a: Non-member" in WordPress and will not have the default WordPress role of "Subscriber" assigned to them. If you have not enabled Member and Contact Type sync, members are assigned a role of "Subscriber" in WordPress and you can give access to members-only content with just the "Subscriber" role. Therefore, since non-members will not have the "Subscriber" role assigned to them they will not be able to access members-only content in the WordPress site. 
 

== Screenshots ==

1. Plugin Settings. Enter your i4a API credentials here (received from i4a) and configure your plugin options.
