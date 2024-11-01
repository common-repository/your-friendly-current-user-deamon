=== Plugin Name ===
Contributors: F J Kaiser
Donate link: http://example.com/
Tags: current_user, current, user, users, usermeta
Requires at least: 2.8
Tested up to: 2.9.2
Stable tag: trunk

Shows the users- and usermeta-table-data of the currently logged in user in admin>tools>show user data.

== Description ==
Gives you an overview about any Data of the currently logged in user including all code to get or display it. 
Includes different code-approaches of user_role and the (depracated) user_level inside the code. 
So just take a look at the code and inline-comments.

== Installation ==
This section describes how to install the plugin and get it working.

1. Upload the folder `current_user_deamon` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Tools" > "Show User Data" to see the information about yourself and your users.

== Frequently Asked Questions ==

= 1) What information is displayed by the plugin? =

* Global data about all of your users and roles: 
-- A list of all roles including the number of users assigned to it.
-- Total # of users.
-- Default user role.
* Data from the currently logged in user:
-- Role, Name, Login-Name, ID, etc.; all basic stuff like nicename,... 
-- User Settings
-- Roles and Capabilities
* A short how-to for adding capabilities & roles
* A comparison between the data that comes from global current_user vs. userdata

= 2) Can the plugin *do* something? =

Not really. It's just here as a reference for
1. core developers
2. plugin developers
3. theme developers
4. people who want to customize or add/change something at a theme or plugin.
Use it to get an overview of what user specific data can be handled and the way(s) to do this. 

== Screenshots ==

Please look at the **FAQ > Q1**. 

== Changelog ==

= 0.4 = 
* Compare-table for current_user vs. userdata

= 0.3.1 = 
* Bug-fixed with all_caps-value
* contributers, testers, commenters added
* new explanations at the bottom of the data-table added

= 0.3 =
* added styling to the table
* better code-column
* added global data about all users (count, etc.)
* offers now a small example on how to extend roles with capabilities

= 0.2 =
* extended with all capabilities
* better & more logical sorting

= 0.1 =
* Table that catches different data of the currently logged in user

== Upgrade Notice ==

= 0.4+ =
Maybe there will come somthing that makes a note here worth, so i keep it, but never mind this time.

== Arbitrary section ==

Nothing here at the moment.