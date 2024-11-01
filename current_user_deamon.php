<?php
/*
Plugin Name: Your friendly "Current User Data" Deamon
Plugin URI: http://not-going-to-come-anytime.com
Description: Gives you an overview about any Data of the currently logged in user including all code to get or display it. Includes different code-approaches of user_role and the (depracated) user_level inside the code. So just take a look at the code and inline-comments. Makes a new menu at "Tools" > "Show User Data".
Version: 0.4
Author: F. J. Kaiser
Author URI: http://unserkaiser.com
License: GPL2
=========================================================================
Copyright (c) 2009-present Franz Josef Kaiser. All rights reserved.
http://unserkaiser.com

Note: Please, don't expect any support for this release.
This is a plugin for developers to lern more about the data, that a current user deliveres.
Not everything in this code is done with "best practice", but it works.
If you got updates, feel free to send them to der.kaiser(at)gmx.at. You'll get mentioned below.

Released under the GPL license
http://www.opensource.org/licenses/gpl-license.php

This is an add-on for WordPress
http://wordpress.org/
=========================================================================
This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY;
without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
=========================================================================
 Thanks to all...
 Contributers, Testers & Commenters:
 * William Hunter Satterwhite
 * Scott Kingsley Clark
=========================================================================
Changelog:
    0.4 -   Compare-table for current_user vs. userdata

    0.3.1 - Bug-fixed with all_caps-value
            contributers, testers, commenters added
            new explanations at the bottom of the data-table added

    0.3 -   added styling to the table
            better code-column
            added global data about all users (count, etc.)
            offers now a small example on how to extend roles with capabilities

    0.2 -   extended with all capabilities
            better & more logical sorting

    0.1 -   Table that catches different data of the currently logged in user
=========================================================================
*/

// Define & construct plugin-path
// should better be that way: http://wpengineer.com/wordpress-plugin-path/
// but got no time to do it. Feel free to add and send me your code for an update: to der.kaiser(at)gmx.at
    define('WP_PATH', get_bloginfo('wpurl')) ;
    define('PLUGIN_PATH', WP_PATH . '/wp-content/plugins/current_user_deamon') ;

// LANGUAGES
// in case someone really cares about that
// if you got lang-files and implementation send me the updated code to: der.kaiser(at)gmx.at
    load_theme_textdomain('lang');

    $locale = get_locale();
    $locale_file = PLUGIN_PATH . "/lang/$locale.php";
    if ( is_readable($locale_file) )
        require_once($locale_file);

    function add_userdeamon_page() {
        add_management_page(__('Show all current user data ', 'lang'), __('Show User Data', 'lang'), 0, 'user_deamon_page', user_deamon);
    }
    add_action('admin_menu','add_userdeamon_page');

function user_deamon() {
// in case you want to get it into a separate file, uncomment the following line:
// require_once(PLUGIN_PATH . 'current_user_showdata.php');

    global $current_user;
    global $userdata;

    global $wpdb;
    global $wp_roles;

    /*
    * Only in use when using this code as template
    if ( is_user_logged_in() ) {
    */

    /* Working, but completely unneccassary:
    $user_id = $current_user->ID;
    $user = get_userdata($user_id);
    *
    * uncomment Var-Dump to get the array-data:
    * echo "<p>CUR_USER: " . var_dump($current_user) . "</p>";
    * echo "<p>USER: " . var_dump($user) . "</p>"; */

    // user_meta arrays:
    $metaboxhidden_dashboard = $current_user->metaboxhidden_dashboard;
    $capabilities = $current_user->{ $wpdb->prefix . 'capabilities' };
    $closedpostboxes_page = $current_user->closedpostboxes_page;
    $metaboxhidden_page = $current_user->metaboxhidden_page;
    $autosave_draft_ids = $current_user->{ $wpdb->prefix . 'autosave_draft_ids' };
    $closedpostboxes_post = $current_user->closedpostboxes_post;
    $metaboxhidden_post = $current_user->metaboxhidden_post;
    $metaboxorder_dashboard = $current_user->{ $wpdb->prefix . 'metaboxorder_dashboard' };


    echo "<div class='wrap'>";
    /*
    echo "<div class='span-24 last' id='current-user-data'>";
        // Another approach of getting the user data
        // as used in wp_set_current_user($id, $name)-function
        // /wp-include/pluggable.php > line 51
        // $user = new WP_User($ID);
        // $current_user_test = get_current_user();
        // echo "<p>wp_capabilities: " . print_r(array_keys($current_user_test->capabilities)) . "</p>";

        // How WP makes it to get user data:
        // A) $current_user = wp_get_current_user();
        // B) the wp_get_current_user-function get's it all from: get_currentuserinfo-function
    */
    echo "<div id='icon-edit-pages' class='icon32'>";
        echo "<br />";
    echo "</div>";

    echo "<h2>Data from currently logged in User</h2>";

/*
* Here is general/global user-stuff: Roles, # of users, etc.
*/
    echo "<h3><b>Global Data about all of your users and roles.</b></h3>";
    echo "<p><i>These are the <b>current roles</b> in your system (<b>#</b> of users with this role): </i><br />";
        foreach( $wp_roles->role_names as $role => $name ) {
        $this_role = "'[[:<:]]".$role."[[:>:]]'";
        $role_query = "
            SELECT *
            FROM $wpdb->users
            WHERE ID = ANY (
                SELECT user_id
                FROM $wpdb->usermeta
                WHERE meta_value
                RLIKE $this_role
            )
            ORDER BY user_nicename ASC
            LIMIT 10000
        ";
        $users_of_this_role = $wpdb->get_results($role_query);
        $user_count = count($users_of_this_role);
            if($user_count > 0) {
                echo "&nbsp;&rarr; <i>" . $name . ": <b>" . $user_count . "</b></i><br />";
            } else {
                echo "&nbsp;&rarr; <i>" . $name . ": no users assigned to this role</i><br />";
            }
        }
    echo "</p>";
    $wpdb->flush();

    $user_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users;"));
    echo "<p><i>At the moment there are <b>" . $user_count . "</b> registered users in your database.</i></p>";
    $wpdb->flush();

    echo "<p>";
        echo "The <b>default role</b> is: <i>" . get_option('default_role') . "</i>";
    echo "</p>";

    echo "<hr />";

    echo "<p>";
        echo "<h3>Data from the currently logged in user:</h3>";
        echo "<p><i>To get most out of this information you should be logged in as Administrator.</i></p>";
    echo "</p>";


    echo "<table class='widefat' rules='rows'>";

    $cur_user_roles = $current_user->roles;
        foreach($cur_user_roles as $cur_user_roles_value) {
            $role_to_compare = $cur_user_roles_value;
        }
    echo "<caption>Data of the currently logged in user with the ";
        echo "<i>Role: ";
            if($role_to_compare == 'administrator') {
                echo "Administrator ";
            } elseif($role_to_compare == 'editor') {
                echo "Editor ";
            } elseif($role_to_compare == 'author') {
                echo "Author ";
            } elseif($role_to_compare == 'contributor') {
                echo "Contributer ";
            } elseif($role_to_compare == 'subscriber') {
                echo "Subscriber ";
            } else {
                echo "<i>Att.: user got no actual role in the system</i> ";
            }
        echo "</i>and the <i>Name: ";
        if(!empty($current_user->user_firstname) && !empty($current_user->user_lastname)) {
            echo $current_user->user_firstname;
            echo " ";
            echo $current_user->user_lastname;
            echo " ";
        }
        echo "</i> - <i>Login Name(ID): ";
        echo $current_user->user_login;
        echo "(";
        echo $current_user->ID;
        echo ")</i>";
    echo "</caption>";

    echo "<thead>";
        echo "<tr>";
            echo "<th class='manage-column' scope='col'>What is?</th>";
            echo "<th class='manage-column' scope='col'>code</th>";
            echo "<th class='manage-column' scope='col'>Output</th>";
        echo "</tr>";
    echo "</thead>";

    echo "<tfoot>";
        echo "<tr>";
            echo "<th class='manage-column' scope='col'>What is?</th>";
            echo "<th class='manage-column' scope='col'>code</th>";
            echo "<th class='manage-column' scope='col'>Output</th>";
        echo "</tr>";
    echo "</tfoot>";

    echo "<tbody>";
        echo "<tr>";
            echo "<td colspan='3'>";
                echo "Here we got the profile: ";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Description: </td>" . "<td>current_user->description</td>" . "<td>";
                if(!empty($current_user->description)) { echo $current_user->description; } else { echo "<i>The User doesn't have to say anything about himself.</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>AIM: </td>" . "<td>current_user->aim</td>" . "<td>";
                if(!empty($current_user->aim)) { echo $current_user->aim; } else { echo "<i>No AIM User.</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Jabber/Google Talk: </td>" . "<td>current_user->jabber</td>" . "<td>";
                if(!empty($current_user->jabber)) { echo $current_user->jabber; } else { echo "<i>No Jabber / Google-Talk User.</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Yahoo IM: </td>" . "<td>current_user->yim</td>" . "<td>";
                if(!empty($current_user->yim)) { echo $current_user->yim; } else { echo "<i>No Yahoo IM User.</i>"; }
            echo "</td>";
        echo "</tr>";
// HERE GOES THE DATA
        echo "<tr>";
            echo "<td colspan='3'>";
                echo "Here we got the basic user data: ";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>ID: </td>" . "<td>current_user->ID</td>" . "<td>";
                if(!empty($current_user->ID)) { echo $current_user->ID; } else { echo "<i>EMPTY (No ID)</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>id <i>depr.</i>: </td>" . "<td>current_user->id</td>" . "<td>";
                if(!empty($current_user->id)) { echo $current_user->id; } else { echo "<i>EMPTY (No id)</i>"; }
            echo "</td>"; // depracated
        echo "</tr>";
        echo "<tr>";
            echo "<td>Login: </td>" . "<td>current_user->user_login</td>" . "<td>";
                if(!empty($current_user->user_login)) { echo $current_user->user_login; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>First Name: </td>" . "<td>current_user->user_firstname</td>" . "<td>";
                if(!empty($current_user->user_firstname)) { echo $current_user->user_firstname; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Last Name: </td>" . "<td>current_user->user_lastname</td>" . "<td>";
                if(!empty($current_user->user_lastname)) { echo $current_user->user_lastname; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Display Name: </td>" . "<td>current_user->display_name</td>" . "<td>";
                if(!empty($current_user->display_name)) { echo $current_user->display_name; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Nice Name: </td>" . "<td>current_user->user_nicename</td>" . "<td>";
                if(!empty($current_user->user_nicename)) { echo $current_user->user_nicename; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Nick Name: </td>" . "<td>current_user->nickname</td>" . "<td>";
                if(!empty($current_user->nickname)) { echo $current_user->nickname; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Register Date: </td>" . "<td>current_user->user_registered</td>" . "<td>";
                if(!empty($current_user->user_registered)) { echo $current_user->user_registered; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        /*
        echo "<tr>";
            echo "<td>Pass(md5)): </td>" . "<td>current_user->user_pass</td>" . "<td>";
                if(!empty($current_user->user_pass)) { echo $current_user->user_pass; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        */
        echo "<tr>";
            echo "<td>Activation Key: </td>" . "<td>current_user->user_activation_key</td>" . "<td>";
                if(!empty($current_user->user_activation_key)) { echo $current_user->user_activation_key; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Status <i>not used</i>: </td>" . "<td>current_user->user_status</td>" . "<td>";
                if(!empty($current_user->user_status)) { echo $current_user->user_status; } else { echo "<i><a href='http://wordpress.org/support/topic/65971?replies=2' target='_blank'>This a link. Click it for further info</a></i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Rich Editing allowed: </td>" . "<td>current_user->rich_editing</td>" . "<td>";
                if(!empty($current_user->rich_editing)) {
                    if($current_user->rich_editing == 1) { echo "Allowed"; } else { echo "Forbidden"; }
                } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Comment Shortcut: </td>" . "<td>current_user->comment_shortcuts</td>" . "<td>";
                if(!empty($current_user->comment_shortcuts)) {
                    if($current_user->comment_shortcuts == 1) { echo "Allowed"; } else { echo "Forbidden"; };
                } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Admin Color Scheme: </td>" . "<td>current_user->admin_color</td>" . "<td>";
                if(!empty($current_user->admin_color)) { echo $current_user->admin_color; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>User Settings: </td>" . "<td>current_user->$wpdb->prefix" . "usersettings</td>" . "<td>";
                $user_settings = $current_user->{$wpdb->prefix . 'usersettings'};
                if(!empty($user_settings)) {
                if(is_array($user_settings)) { echo "<i>(Saved as an array)</i> "; } else { echo "<i>(This is a string)</i> <br />"; }
                    $user_settings_string = explode("&", $user_settings);
                    foreach($user_settings_string as $key => $val) {
                        echo $val . "; ";
                    }
                    // echo $splitted_user_settings;
                    // echo $user_settings;
                } else { echo "<i>EMPTY (There are no User-Settings at the moment.)</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Time of User Settings: </td>" . "<td>current_user->$wpdb->prefix" . "usersettingstime</td>" . "<td>";
                $user_settingstime = $current_user->{$wpdb->prefix . 'usersettingstime'};
                if(!empty($user_settingstime)) { echo $user_settingstime; } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
// It seems that there is a default DB-field, so this can't be empty.
        echo "<tr>";
            echo "<td>Closed Boxes @Pages: </td>" . "<td>closedpostboxes_page</td>" . "<td>";
                if(!empty($closedpostboxes_page)) {
                    foreach($closedpostboxes_page as $closedpostboxes_page_value) {
                        echo $closedpostboxes_page_value . "; ";
                    }
                } else { echo "<i>Currently there are no closed boxes @Pages.</i>"; }
            echo "</td>";
        echo "</tr>";
// It seems that there is a default DB-field, so this can't be empty.
        echo "<tr>";
            echo "<td>Meta Boxen hidden @Pages: </td>" . "<td>metaboxhidden_page</td>" . "<td>";
                if(!empty($metaboxhidden_page)) {
                    foreach($metaboxhidden_page as $metaboxhidden_page_value) {
                        echo $metaboxhidden_page_value . "; ";
                    }
                } else { echo "<i>Currently there are no hidden boxes @Pages.</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Autosave of Drafts(IDs): </td>" . "<td>autosave_draft_ids</td>" . "<td>";
                if(!empty($autosave_draft_ids)) {
                    echo "<i>IDs:</i> ";
                    foreach($autosave_draft_ids as $autosave_draft_ids_value) {
                        echo $autosave_draft_ids_value . "; ";
                    }
                } else { echo "<i>Currently there are no Autosaved drafts.</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Closed Boxes @Posts: </td>" . "<td>closedpostboxes_post</td>" . "<td>";
                if(!empty($closedpostboxes_post)) {
                    foreach($closedpostboxes_post as $closedpostboxes_post_value) {
                        echo $closedpostboxes_post_value . "; ";
                    }
                } else { echo "<i>Currently there are no closed boxes @Posts.</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Meta Boxen hidden @Post(?): </td>" . "<td>metaboxhidden_post</td>" . "<td>";
                if(!empty($metaboxhidden_post)) {
                    foreach($metaboxhidden_post as $metaboxhidden_post_value) {
                        echo $metaboxhidden_post_value . "; ";
                    }
                } else { echo "<i>Currently there are no hidden boxes @Posts.</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Metabox-Order @Dashboard: </td>" . "<td>metaboxorder_dashboard</td>" . "<td>";
                if(!empty($metaboxorder_dashboard)) {
                    if(is_array($metaboxorder_dashboard)) {
                        echo "<i>(This is an array. Listed as key/where and val/what)</i> <br />";
                    }
                    list($month, $day, $year) = split('[/.-]', $metaboxorder_dashboard);
                        foreach($metaboxorder_dashboard as $key => $val) {
                            $str = $val;
                            $single_values = explode(",", $val);
                            echo "<i>Where: </i> ";
                            echo $key;
                            echo " <i>What:</i> ";
                            foreach($single_values as $key => $val) { echo $val . "; "; }
                            echo "<br /> ";
                        }
                } else {
                    echo "<i>Currently there is no custom-/user-defined order of boxes @Dashboard.</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Screen Layout @Dashboard: </td>" . "<td>current_user->screen_layout_dashboard</td>" . "<td>";
                if(!empty($current_user->screen_layout_dashboard)) {
                    echo "<i>Columns on the Dashboard-Layout:</i> " . $current_user->screen_layout_dashboard;
                } else {
                    echo "<i>Currently there is no custom-/user-defined screen layout @Dashboard.</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Hidden Elements @Dashboard: </td>" . "<td>metaboxhidden_dashboard</td>" . "<td>";
                if(!empty($metaboxhidden_dashboard)) {
                    foreach($metaboxhidden_dashboard as $metaboxhidden_dashboard_value) {
                        echo $metaboxhidden_dashboard_value . "; ";
                    }
                } else { echo "<i>Currently there are no hidden Elements.</i>"; }
            echo "</td>";
        echo "</tr>";

        echo "<tr>";
           echo "<td colspan='3'>";
// NOTE: Here we should dynamically count Roles and Capabilities.
           echo "<i><b>Here we start with checking for all that Role &amp; Capability stuff.</b>" . "<br />" . "Currently there are <b>5</b> (Core-)Roles and <b>40</b> (Core-)Capabilities.</i>";
           echo "</td>";
        echo "</tr>";

        echo "<tr>";
            echo "<td>User-Level <i>depr.</i>: </td>" . "<td>current_user->$wpdb->prefix" . "user_level</td>" . "<td>";
                $user_level_nr = $current_user->{$wpdb->prefix . 'user_level'};
                if(!empty($user_level_nr)) { echo $user_level_nr; } else { echo "<i>EMPTY</i>"; }
            echo "</td>"; // depracated
        echo "</tr>";
        echo "<tr>";
            echo "<td>user_level <i>depr.</i>: </td>" . "<td>current_user->user_level</td>" . "<td>";
                if(!empty($current_user->user_level)) { echo $current_user->user_level; } else { echo "<i>EMPTY</i>"; }
            echo "</td>"; // depracated
        echo "</tr>";
        // USER LEVEL
        echo "<tr>";
            echo "<td>User Level: <i>depr.</i></td>" . "<td>current_user_can('level_X')</td>" . "<td>";
                if (current_user_can('level_10')) {
                    echo "0-10";
                } elseif (current_user_can('level_9')) {
                    echo "0-9";
                } elseif (current_user_can('level_8')) {
                    echo "0-8";
                } elseif (current_user_can('level_7')) {
                    echo "0-7";
                } elseif (current_user_can('level_6')) {
                    echo "0-6";
                } elseif (current_user_can('level_5')) {
                    echo "0-5";
                } elseif (current_user_can('level_4')) {
                    echo "0-4";
                } elseif (current_user_can('level_3')) {
                    echo "0-3";
                } elseif (current_user_can('level_2')) {
                    echo "0-2";
                } elseif (current_user_can('level_1')) {
                    echo "0-1";
                } elseif (current_user_can('level_0')) {
                    echo "0";
                } else {
                    echo "<i><u>No User Level assigned!</u></i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Roles: </td>" . "<td>current_user->roles</td>" . "<td>";
                $cur_user_roles = $current_user->roles;
                if(!empty($cur_user_roles)) {
                    foreach($cur_user_roles as $cur_user_roles_value) {
                        echo $cur_user_roles_value . "<br />";
                    }
                } else { echo "<i>EMPTY</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Capabilities: </td>" . "<td>capabilities</td>" . "<td>";
                if(!empty($capabilities)) {
                    foreach($capabilities as $capabilities_value) {
                        if($capabilities_value == 1) { echo "<i>The User has Capabilities (true/1).</i>"; }
                    }
                } else { echo "<i>EMPTY (User has no assigned capabilities)</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Capabilities: </td>" . "<td>current_user->caps</td>" . "<td>";
                $cur_user_caps = $current_user->caps;
                if(!empty($cur_user_caps)) {
                    foreach($cur_user_caps as $cur_user_caps_value) {
                        if($cur_user_caps_value == 1) { echo "<i>The User has Capabilities (true/1).</i>"; }
                    }
                } else { echo "<i>EMPTY (User has no assigned capabilities)</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Capabilities Key: </td>" . "<td>current_user->cap_key</td>" . "<td>";
                if(!empty($current_user->cap_key)) { echo $current_user->cap_key; } else { echo "<i>No capabilities key.</i>"; }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>All Capabilities: </td>" . "<td>current_user->allcaps</td>" . "<td>";
                $cur_user_allcaps = $current_user->allcaps;
                if(!empty($cur_user_allcaps)) {
                    ksort($cur_user_allcaps);
                    foreach($cur_user_allcaps as $key => $val) {
                        if($val == 1) {
                            echo $key . " = Yes; ";
                        } elseif($val == 0) {
                            echo $key . " = No; ";
                        }
                    }
                } else { echo "<i>EMPTY (There are no assigned capabilities)</i>"; }
            echo "</td>";
        echo "</tr>";

/*
* HERE WE START WITH ALL CAPABILITIES OUR USER HAS
*/

        echo "<tr>";
           echo "<td colspan='3'>";
           echo "<i>The following capabilities can be checked with current_user_can('cap_name'). See the column 'code' for the cap_name.</i>";
           echo "</td>";
        echo "</tr>";

        echo "<tr>";
            echo "<td>Role: </td>" . "<td>rolename</td>" . "<td>";
                if (current_user_can('administrator')) {
                    echo "Administrator";
                } elseif (current_user_can('editor')) {
                    echo "Editor";
                } elseif (current_user_can('author')) {
                    echo "Author";
                } elseif (current_user_can('contributor')) {
                    echo "Contributor";
                } elseif (current_user_can('subscriber')) {
                    echo "Subscriber";
                } else {
                    echo "<i><u>No Role assigned!</u></i>";
                }
            echo "</td>";
        echo "</tr>";
        // DASHBOARD

        echo "<tr>";
            echo "<td>Dashboard: </td>" . "<td>edit_dashboard</td>" . "<td>";
                if (current_user_can('edit_dashboard')) {
                    echo "Edit dashboard";
                } else {
                    echo "<i><b>Not allowed to</b> edit_dashboard</i>";
                }
            echo "</td>";
        echo "</tr>";
        // USERS
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>create_users</td>" . "<td>";
                if (current_user_can('create_users')) {
                    echo "Can create users";
                } else {
                    echo "<i><b>Not allowed to</b> create users</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_users</td>" . "<td>";
                if (current_user_can('edit_users')) {
                    echo "Can edit users";
                } else {
                    echo "<i><b>Not allowed to</b> edit users</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_users</td>" . "<td>";
                if (current_user_can('delete_users')) {
                    echo "Can delete users";
                } else {
                    echo "<i><b>Not allowed to</b> delete users</i>";
                }
            echo "</td>";
        echo "</tr>";
        // THEMES
        echo "<tr>";
            echo "<td>Themes: </td>" . "<td>install_themes</td>" . "<td>";
                if (current_user_can('install_themes')) {
                    echo "Can install themes";
                } else {
                    echo "<i><b>Not allowed to</b> install themes</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_themes</td>" . "<td>";
                if (current_user_can('edit_themes')) {
                    echo "Can edit themes";
                } else {
                    echo "<i><b>Not allowed to</b> edit themes</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>switch_themes</td>" . "<td>";
                if (current_user_can('switch_themes')) {
                    echo "Can switch themes";
                } else {
                    echo "<i><b>Not allowed to</b> switch themes</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>update_themes</td>" . "<td>";
                if (current_user_can('update_themes')) {
                    echo "Can update themes";
                } else {
                    echo "<i><b>Not allowed to</b> update themes</i>";
                }
            echo "</td>";
        echo "</tr>";
        // PLUGINS
        echo "<tr>";
            echo "<td>Plugins: </td>" . "<td>install_plugins</td>" . "<td>";
                if (current_user_can('install_plugins')) {
                    echo "Can install plugins";
                } else {
                    echo "<i><b>Not allowed to</b> install plugins</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>activate_plugins</td>" . "<td>";
                if (current_user_can('activate_plugins')) {
                    echo "Can activate plugins";
                } else {
                    echo "<i><b>Not allowed to</b> activate plugins</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_plugins</td>" . "<td>";
                if (current_user_can('edit_plugins')) {
                    echo "Can edit plugins";
                } else {
                    echo "<i><b>Not allowed to</b> edit plugins</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>update_plugins</td>" . "<td>";
                if (current_user_can('update_plugins')) {
                    echo "Can update plugins";
                } else {
                    echo "<i><b>Not allowed to</b> update plugins</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_plugins</td>" . "<td>";
                if (current_user_can('delete_plugins')) {
                    echo "Can delete plugins";
                } else {
                    echo "<i><b>Not allowed to</b> delete plugins</i>";
                }
            echo "</td>";
        echo "</tr>";
        // READ POSTS AND PAGES
        echo "<tr>";
            echo "<td>Posts &amp; Pages: </td>" . "<td>read</td>" . "<td>";
                if (current_user_can('read')) {
                    echo "Can read";
                } else {
                    echo "<i><b>Not allowed to</b> read</i>";
                }
            echo "</td>";
        echo "</tr>";
        // IMPORT
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>import</td>" . "<td>";
                if (current_user_can('import')) {
                    echo "Can import";
                } else {
                    echo "<i><b>Not allowed to</b> import</i>";
                }
            echo "</td>";
        echo "</tr>";
        // POSTS
        echo "<tr>";
            echo "<td>Pages: </td>" . "<td>read_private_posts</td>" . "<td>";
                if (current_user_can('read_private_posts')) {
                    echo "Can read private posts";
                } else {
                    echo "<i><b>Not allowed to</b> read private posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>publish_posts</td>" . "<td>";
                if (current_user_can('publish_posts')) {
                    echo "Can publish posts";
                } else {
                    echo "<i><b>Not allowed to</b> publish posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_posts</td>" . "<td>";
                if (current_user_can('edit_posts')) {
                    echo "Can edit posts";
                } else {
                    echo "<i><b>Not allowed to</b> edit posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_others_posts</td>" . "<td>";
                if (current_user_can('edit_others_posts')) {
                    echo "Can edit others posts";
                } else {
                    echo "<i><b>Not allowed to</b> edit others posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_published_posts</td>" . "<td>";
                if (current_user_can('edit_published_posts')) {
                    echo "Can edit published posts";
                } else {
                    echo "<i><b>Not allowed to</b> edit published posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_private_posts</td>" . "<td>";
                if (current_user_can('edit_private_posts')) {
                    echo "Can edit private posts";
                } else {
                    echo "<i><b>Not allowed to</b> edit private posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>Posts: </td>" . "<td>delete_posts</td>" . "<td>";
                if (current_user_can('delete_posts')) {
                    echo "Can delete posts";
                } else {
                    echo "<i><b>Not allowed to</b> delete posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_published_posts</td>" . "<td>";
                if (current_user_can('delete_published_posts')) {
                    echo "Can delete published posts";
                } else {
                    echo "<i><b>Not allowed to</b> delete published posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_others_posts</td>" . "<td>";
                if (current_user_can('delete_others_posts')) {
                    echo "Can delete others posts";
                } else {
                    echo "<i><b>Not allowed to</b> delete others posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_private_posts</td>" . "<td>";
                if (current_user_can('delete_private_posts')) {
                    echo "Can delete private posts";
                } else {
                    echo "<i><b>Not allowed to</b> delete private posts</i>";
                }
            echo "</td>";
        echo "</tr>";
        // PAGES
        echo "<tr>";
            echo "<td>Pages: </td>" . "<td>read_private_pages</td>" . "<td>";
                if (current_user_can('read_private_pages')) {
                    echo "Can read private pages";
                } else {
                    echo "<i><b>Not allowed to</b> read private pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>publish_pages</td>" . "<td>";
                if (current_user_can('publish_pages')) {
                    echo "Can publish pages";
                } else {
                    echo "<i><b>Not allowed to</b> publish pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_pages</td>" . "<td>";
                if (current_user_can('edit_pages')) {
                    echo "Can edit pages";
                } else {
                    echo "<i><b>Not allowed to</b> edit pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_others_pages</td>" . "<td>";
                if (current_user_can('edit_others_pages')) {
                    echo "Can edit others pages";
                } else {
                    echo "<i><b>Not allowed to</b> edit others pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_published_pages</td>" . "<td>";
                if (current_user_can('edit_published_pages')) {
                    echo "Can edit published pages";
                } else {
                    echo "<i><b>Not allowed to</b> edit published pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_private_pages</td>" . "<td>";
                if (current_user_can('edit_private_pages')) {
                    echo "Can edit private pages";
                } else {
                    echo "<i><b>Not allowed to</b> edit private pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_pages</td>" . "<td>";
                if (current_user_can('delete_pages')) {
                    echo "Can delete pages";
                } else {
                    echo "<i><b>Not allowed to</b> delete pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_published_pages</td>" . "<td>";
                if (current_user_can('delete_published_pages')) {
                    echo "Can delete published pages";
                } else {
                    echo "<i><b>Not allowed to</b> delete published pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_others_pages</td>" . "<td>";
                if (current_user_can('delete_others_pages')) {
                    echo "Can delete others pages";
                } else {
                    echo "<i><b>Not allowed to</b> delete others pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>delete_private_pages</td>" . "<td>";
                if (current_user_can('delete_private_pages')) {
                    echo "Can delete private pages";
                } else {
                    echo "<i><b>Not allowed to</b> private pages</i>";
                }
            echo "</td>";
        echo "</tr>";
        // ADDITIONAL POSTS AND PAGES STUFF
        echo "<tr>";
            echo "<td>Add. Stuff to Posts &amp; Pages: </td>" . "<td>manage_categories</td>" . "<td>";
                if (current_user_can('manage_categories')) {
                    echo "Can manage categories";
                } else {
                    echo "<i><b>Not allowed to</b> manage categories</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>moderate_comments</td>" . "<td>";
                if (current_user_can('moderate_comments')) {
                    echo "Can moderate comments";
                } else {
                    echo "<i><b>Not allowed to</b> moderate comments</i>";
                }
            echo "</td>";
        echo "</tr>";
        // FILES
        echo "<tr>";
            echo "<td>Files: </td>" . "<td>upload_files</td>" . "<td>";
                if (current_user_can('upload_files')) {
                    echo "Can upload files";
                } else {
                    echo "<i><b>Not allowed to</b> upload files</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>edit_files</td>" . "<td>";
                if (current_user_can('edit_files')) {
                    echo "Can edit files";
                } else {
                    echo "<i><b>Not allowed to</b> edit files</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>unfiltered_upload</td>" . "<td>";
                if (current_user_can('unfiltered_upload')) {
                    echo "Can do unfiltered upload";
                } else {
                    echo "<i><b>Not allowed to do</b> unfiltered upload</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>unfiltered_html</td>" . "<td>";
                if (current_user_can('unfiltered_html')) {
                    echo "Can generate unfiltered html";
                } else {
                    echo "<i><b>Not allowed to</b> generate unfiltered html</i>";
                }
            echo "</td>";
        echo "</tr>";
        // LINKS
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>manage_links</td>" . "<td>";
                if (current_user_can('manage_links')) {
                    echo "Can manage links";
                } else {
                    echo "<i><b>Not allowed to</b> manage links</i>";
                }
            echo "</td>";
        echo "</tr>";
        // OPTIONS
        echo "<tr>";
            echo "<td>&nbsp;&rarr; </td>" . "<td>manage_options</td>" . "<td>";
                if (current_user_can('manage_options')) {
                    echo "Can manage options";
                } else {
                    echo "<i><b>Not allowed to</b> manage options</i>";
                }
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td colspan='3'>";
                echo "That's all folks!";
                // echo "You can extend Roles by adding this to your functions.php: <pre>$role = get_role('editor'); if ($role !== NULL) { $role->add_cap('edit_users'); }</pre> ";
            echo "</td>";
        echo "</tr>";

    echo "</tbody>";

    echo "</table>";

    echo "<p>";
        echo "<i>";
        echo "<b>Everything on this page is made with WP 2.9.2.</b> <br />";
        echo "Example on how to add capabilities to a role: ";
        echo "Add the Capabilites 'Edit User', 'Create User' and 'Delete User' to the role 'Editor'.";
        echo "<br />";
        echo "Note: The code should be added to the functions.php of your current theme. <br />";
        echo "[S] is in the examples below the dollar-sign for a var.";
        echo "</i>";
    echo "</p>";
    echo "<p>";
        echo "<code>";
        echo "&nbsp;" . "[S]role = get_role('editor');" . "<br />";
        echo "&nbsp;&nbsp;" . "if ([S]role !== NULL) {" . "<br />";
        echo "&nbsp;&nbsp;&nbsp;" . "[S]role->add_cap('edit_users');" . "<br />";
        echo "&nbsp;&nbsp;&nbsp;" . "[S]role->add_cap('create_users');" . "<br />";
        echo "&nbsp;&nbsp;&nbsp;" . "[S]role->add_cap('delete_users');" . "<br />";
        echo "&nbsp;" . "}";
        echo "</code>";
    echo "</p>";

    echo "<p>";
        echo "<i>";
        echo "Example on how to add and remove a new role: ";
        echo "<br />";
        echo "Note: The code should be added to the functions.php of your current theme.";
        echo "</i>";
    echo "</p>";
    echo "<p>";
        echo "<code>";
        echo "&nbsp;" . "[S]role = 'newrole';" . "<br />";
        echo "&nbsp;" . "[S]display_name = 'NewRole';" . "<br />";
        echo "&nbsp;" . "[S]capabilities = array();" . "<br />";
        echo "&nbsp;&nbsp;" . "add_role( [S]role, [S]display_name, [S]capabilities )" . "<br />";
        echo "&nbsp;&nbsp;" . "remove_role( [S]role );";
        echo "</code>";
    echo "</p>";

    echo "<p>";
        echo "<i>";
        echo "Adding Capabilities for Pods-Plugin (coming with Pods 1.8.5): ";
        echo "</i>";
    echo "</p>";
    echo "<p>";
        echo "<code>";
        echo "&nbsp;&nbsp;" . "add_filter('pods_access','my_overriding_function');";
        echo "</code>";
    echo "</p>";

    echo "<p>";
        echo "<b>Additional info-area: </b>";
        echo "<ol>";
            echo "<li>There is no need to use 'get_currentuserinfo' (as it's written in the codex). ";
                echo "You just need global current_user or userdata.</li>";
            echo "<li>You can always use the following globals without the globals [S]current_user or [S]userdata: <br />";
                echo "[S]user_login, [S]user_level, [S]user_ID, [S]user_email, [S]user_url, [S]user_pass_md5, [S]display_name.</li>";
            echo "<li>You *should* always use current_user in favor of userdata. <br />";
            echo "You can see a comparisson in the table below. </li>";
        echo "</ol>";
    echo "</p>";


/* HERE WE START THE TABLE WITH THE GLOBALS-COMPARISON */

    echo "<table class='widefat' rules='rows'>";

    echo "<caption><b>Compare:</b> [S]current_user  ";
        echo "<i>VS ";
        echo "</i>[S]userdata <i>(<b>X</b> = avaible; <b>-</b> = not avaible)</i>";
    echo "</caption>";

    echo "<thead>";
        echo "<tr>";
            echo "<th class='manage-column' scope='col'>Data</th>";
            echo "<th class='manage-column' scope='col'>current_user</th>";
            echo "<th class='manage-column' scope='col'>userdata</th>";
        echo "</tr>";
    echo "</thead>";

    echo "<tfoot>";
        echo "<tr>";
            echo "<th class='manage-column' scope='col'>Data</th>";
            echo "<th class='manage-column' scope='col'>current_user</th>";
            echo "<th class='manage-column' scope='col'>userdata</th>";
        echo "</tr>";
    echo "</tfoot>";

    echo "<tbody>";
        echo "<tr>";
            echo "<td>";
                echo "<b>ID</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_login</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_pass</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_nicename</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_email</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_url</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_registered</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_activation_key</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_status</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>display_name</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>metaboxhidden_dashboard</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>closedpostboxes_dashboard</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>nickname</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>rich_editing</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>comment_shortcuts</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>admin_color</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_capabilities</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_user_level</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_usersettings</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_usersettingstime</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>closedpostboxes_page</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>metaboxhidden_page</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_autosave_draft_ids</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>first_name</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>last_name</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>closedpostboxes_post</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>metaboxhidden_post</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_metaboxorder_dashboard</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>screen_layout_dashboard</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_level</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_firstname</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_lastname</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
        echo "</tr>";

// HERE IS THE BEGINNIG OF THE END. LISTEN TO THE DOORS AND ENJOY THE END OF GLOBAL USERDATA.
//    echo $current_user->description . " - ";
//    echo $userdata->description . " - ";
//    echo $current_user->jabber . " - ";
//    echo $userdata->jabber . " - ";
//    echo $current_user->aim . " - ";
//    echo $userdata->aim . " - ";
//    echo $current_user->yim . " - ";
//    echo $userdata->yim . " - ";

        echo "<tr>";
            echo "<td colspan='3'>";
                echo "<i>This is where the [S]current_user can deliver more than [S]userdata:</i>";
            echo "</td>";
            echo "</tr>";
        echo "<tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>id</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>caps</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>cap_key</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>roles</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>allcaps</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>first_name</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>last_name</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>filter</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td colspan='3'>";
                echo "<i>doubled entries in the global [S]current_user</i>";
            echo "</td>";
            echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>ID</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_login</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_pass</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_nicename</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_email</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_url</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_registered</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_activation_key</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_status</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>display_name</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>metaboxhidden_dashboard</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>closedpostboxes_dashboard</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>nickname</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>rich_editing</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>comment_shortcuts</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>admin_color</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_capabilities</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_usersettings</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_usersettingstime</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>closedpostboxes_page</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>metaboxhidden_page</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_autosave_draft_ids</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>closedpostboxes_post</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>metaboxhidden_post</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>DB-PREFIX_metaboxorder_dashboard</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>screen_layout_dashboard</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_level</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_firstname</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>user_lastname</b>";
            echo "</td>";
            echo "<td>";
                echo "X";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";

    echo "</tbody>";

    echo "</table>";

/* FUNCTIONS: Table of all user-related functions. */
/*
    echo "<hr />";

    echo "<h3>Functions that relate to user(s).</h3>";

    echo "<table class='widefat' rules='rows'>";

    echo "<caption><b>Table of user related functions you can use</b> ";
        echo "<i> ";
        echo "</i> <i> <b></b>";
    echo "</caption>";

    echo "<thead>";
        echo "<tr>";
            echo "<th class='manage-column' scope='col'>function name</th>";
            echo "<th class='manage-column' scope='col'>what</th>";
            echo "<th class='manage-column' scope='col'>output</th>";
            echo "<th class='manage-column' scope='col'>covered by var</th>";
            echo "<th class='manage-column' scope='col'>uses</th>";
        echo "</tr>";
    echo "</thead>";

    echo "<tfoot>";
        echo "<tr>";
            echo "<th class='manage-column' scope='col'>function</th>";
            echo "<th class='manage-column' scope='col'>what</th>";
            echo "<th class='manage-column' scope='col'>output</th>";
            echo "<th class='manage-column' scope='col'>covered by var</th>";
            echo "<th class='manage-column' scope='col'>uses</th>";
        echo "</tr>";
    echo "</tfoot>";

    echo "<tbody>";

        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_by (field, value)</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user interface setting value based on setting name.";
                $get_user_by = get_user_by($ID, $current_user);
                echo $get_user_by;
            echo "</td>";
            echo "<td>";
                echo "Ex.: by ID";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "field: id, slug, email, login";
                echo "value: user ID ([S]current_user->ID), slug, email address, login_name";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_by_email (email)</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user interface setting value based on setting name.";
            echo "</td>";
            echo "<td>";
                $get_user_by_email = get_user_by_email($user_email);
                echo $get_user_by_email;
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_count</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user interface setting value based on setting name.";
            echo "</td>";
            echo "<td>";
                // global $wpdb; $get_user_count = get_user_count(); echo $get_user_count;
                echo "undefined function!";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_details</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user interface setting value based on setting name.";
            echo "</td>";
            echo "<td>";
                // $get_user_details = get_user_details(); echo $get_user_details;
                echo "undefined function!";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_id_from_string</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user interface setting value based on setting name.";
            echo "</td>";
            echo "<td>";
                // $get_user_id_from_string = get_user_id_from_string(); echo $get_user_id_from_string;
                echo "undefined function!";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_meta</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user interface setting value based on setting name.";
            echo "</td>";
            echo "<td>";
                // $get_user_meta = get_user_meta(); echo $get_user_meta;
                echo "undefined function!";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_metavalues</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user interface setting value based on setting name.";
            echo "</td>";
            echo "<td>";
                // $ids = new WP_User($userdata->ID);
                // $get_user_metavalues = get_user_metavalues($ids);
                // echo $get_user_metavalues;
                echo "undefined function!";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_option*</b>";
            echo "</td>";
            echo "<td>";
                echo "<i>depracated:</i>";
            echo "</td>";
            echo "<td>";
                echo "use get_option() instead.";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_setting</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user interface setting value based on setting name.";
            echo "</td>";
            echo "<td>";
                $name = "mfold"; // Name of the setting
                $value = "f"; // Value of the setting
                $get_user_setting = get_user_setting($name, $value);
                echo $get_user_setting;
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "[S]name, [S]value";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_user_to_edit</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user data and filter it.";
            echo "</td>";
            echo "<td>";
                // $user_id = $current_user->ID;
                // $user_id = new WP_User( $user_id );
                // $get_user_to_edit = get_user_to_edit($user_id);
                // echo $get_user_to_edit;
                echo "undefined function!";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "[S]user_id";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_userdata</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve user info by user ID.";
            echo "</td>";
            echo "<td>";
                $user_id = $current_user->ID;
                $get_userdata = get_userdata($user_id);
                foreach($get_userdata as $key => $val) {
                    echo "" . $key . " = ";
                    if(is_array($val)) {
                        echo "Look at userdata and current_user table above. ";
                    } else {
                        echo $val . "; ";
                    }
                }
            echo "</td>";
            echo "<td>";
                echo "[S]current_user";
            echo "</td>";
            echo "<td>";
                echo "[S]user_id";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_userdatabylogin</b>";
            echo "</td>";
            echo "<td>";
                echo "Simple Pie.";
            echo "</td>";
            echo "<td>";
                // $get_userdatabylogin = get_userdatabylogin();
                // echo $get_userdatabylogin;
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_usermeta</b>";
            echo "</td>";
            echo "<td>";
                echo "<i>depracated:</i> ";
            echo "</td>";
            echo "<td>";
                echo "nothing instead.";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_usernumposts</b>";
            echo "</td>";
            echo "<td>";
                echo "<i>depracated:</i>";
            echo "</td>";
            echo "<td>";
                echo "use count_user_posts instead.";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "<tr>";
            echo "<td>";
                echo "<b>get_users_drafts</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve post-drafts with title and ID.";
            echo "</td>";
            echo "<td>";
                $user_id = $current_user->ID;
                $get_users_drafts = get_users_drafts($user_id);
                $i = 1;
                foreach($get_users_drafts as $users_drafts) {
                    echo "<b>" . $i . ":</b> ";
                    echo "<i>ID:</i> " . $users_drafts->ID . " - ";
                    echo "<i>post_title:</i> " . $users_drafts->post_title . "<hr />";
                    $i++;
                }
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "[S]user_id";
            echo "</td>";
        echo "</tr>";
            echo "<td>";
                echo "<b>get_users_of_blog</b>";
            echo "</td>";
            echo "<td>";
                echo "Retrieve all users in your system.";
            echo "</td>";
            echo "<td>";
                $users_of_blog = get_users_of_blog();
                $i = 1;
                foreach($users_of_blog as $user_of_blog) {
                    echo "<b>" . $i . ":</b> ";
                    echo "<i>user_ID:</i> " . $user_of_blog->user_ID . " - ";
                    echo "<i>ID:</i> " . $user_of_blog->ID . " - ";
                    echo "<i>user_login:</i> " . $user_of_blog->user_login . " - ";
                    echo "<i>display_name:</i> " . $user_of_blog->display_name . " - ";
                    echo "<i>user_email:</i> " . $user_of_blog->user_email . " - ";
                    echo "<i>meta_value:</i> " . $user_of_blog->meta_value . "<hr />";
                    $i++;
                }
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
            echo "<td>";
                echo "-";
            echo "</td>";
        echo "</tr>";

    echo "</tbody>";

    echo "</table>";
*/

    echo "</div>";
    }
?>