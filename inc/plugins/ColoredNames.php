<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 *
 */

// Make sure we can't access this file directly from the browser.
if(!defined('IN_MYBB'))
{
	die('This file cannot be accessed directly.');
}
	
// cache templates - this is important when it comes to performance
// THIS_SCRIPT is defined by some of the MyBB scripts, including index.php
if(defined('THIS_SCRIPT'))
{
    global $templatelist;
}

if(defined('IN_ADMINCP'))
{
	// Add our colorednames_settings() function to the setting management module to load language strings.
	//$plugins->add_hook('admin_config_settings_manage', 'colorednames_settings');
	// We could hook at 'admin_config_settings_begin' only for simplicity sake.
}
else
{
	// Add our colorednames_index() function to the index_start hook so when that hook is run our function is executed
	$plugins->add_hook('forumdisplay_thread_end', 'colorednames_thread');
    $plugins->add_hook('build_forumbits_forum', 'colorednames_forumlist');
    $plugins->add_hook('forumdisplay_announcement', 'colorednames_announcement');
    $plugins->add_hook('search_results_post','colorednames_search');
}

function colorednames_info()
{


	/**
	 * Array of information about the plugin.
	 * name: The name of the plugin
	 * description: Description of what the plugin does
	 * website: The website the plugin is maintained at (Optional)
	 * author: The name of the author of the plugin
	 * authorsite: The URL to the website of the author (Optional)
	 * version: The version number of the plugin
	 * compatibility: A CSV list of MyBB versions supported. Ex, '121,123', '12*'. Wildcards supported.
	 * codename: An unique code name to be used by updated from the official MyBB Mods community.
	 */
	return array(
		'name'			=> 'Colored Names',
		'description'	=> 'Shows group colors on usernames',
		'website'		=> 'http://lenders-it.nl',
		'author'		=> 'Burnacid',
		'authorsite'	=> 'http://lenders-it.nl',
		'version'		=> '1.0',
		'compatibility'	=> '18*',
		'codename'		=> 'colorednames'
	);
}

/*
 * _activate():
 *    Called whenever a plugin is activated via the Admin CP. This should essentially make a plugin
 *    'visible' by adding templates/template changes, language changes etc.
*/
function colorednames_activate()
{
	global $db, $lang;

	// no activation script
}

/*
 * _deactivate():
 *    Called whenever a plugin is deactivated. This should essentially 'hide' the plugin from view
 *    by removing templates/template changes etc. It should not, however, remove any information
 *    such as tables, fields etc - that should be handled by an _uninstall routine. When a plugin is
 *    uninstalled, this routine will also be called before _uninstall() if the plugin is active.
*/
function colorednames_deactivate()
{
    
}

function colorednames_thread()
{
    global $lastposterlink,$thread;
    
    $lastposter = get_user($thread['lastposteruid']);
    $lastposterurl = get_profile_link($thread['lastposteruid']);
    $lastpostername = format_name($lastposter['username'],$lastposter['usergroup']);
    $lastposterlink = "<a href='".$lastposterurl."'>".$lastpostername."</a>";
    
    $poster = get_user($thread['uid']);
    $posterurl = get_profile_link($thread['uid']);
    $postername = format_name($poster['username'],$poster['usergroup']);
    $thread['profilelink'] = "<a href='".$posterurl."'>".$postername."</a>";
}

function colorednames_forumlist($forum)
{
    global $lastpost_profilelink;
    
    $poster = get_user($forum['lastposteruid']);
    $forum['lastposter'] = format_name($poster['username'],$poster['usergroup']);

    return $forum;
}

function colorednames_announcement()
{
    global $announcement;
    
    $poster = get_user($announcement['uid']);
    $posterurl = get_profile_link($announcement['uid']);
    $postername = format_name($poster['username'],$poster['usergroup']);
    $announcement['profilelink'] = "<a href='".$posterurl."'>".$postername."</a>";     
}

function colorednames_search()
{
    global $post;
    
    $poster = get_user($post['uid']);
    $posterurl = get_profile_link($post['uid']);
    $postername = format_name($poster['username'],$poster['usergroup']);
    $post['profilelink'] = "<a href='".$posterurl."'>".$postername."</a>";  
}

?>