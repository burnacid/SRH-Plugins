<?php
// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if (!defined("PLUGINLIBRARY")) {
    define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

//HOOKS
$plugins->add_hook('forumdisplay_thread_end', 'styleclosedthreads_thread');

function styleclosedthreads_info()
{
    global $mybb, $plugins_cache;
    $info = array(
        "name" => "Style Closed Threads",
        "description" => "This plugin adds a class to closed threads so it's possible to style them",
        "website" => "http://lenders-it.nl",
        "author" => "Burnacid",
        "authorsite" => "http://lenders-it.nl",
        "version" => "1.0",
        "guid" => "",
        "compatibility" => "18*");

    return $info;
}

function styleclosedthreads_activate()
{
    global $PL, $mybb;
    $PL or require_once PLUGINLIBRARY;
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    if (!file_exists(PLUGINLIBRARY)) {
        flash_message("The selected plugin could not be installed because <a href=\"https://github.com/frostschutz/MyBB-PluginLibrary/blob/master/inc/plugins/pluginlibrary.php\">PluginLibrary</a> is missing.",
            "error");
        admin_redirect("index.php?module=config-plugins");
    }

    if ($PL->version < 12) {
        flash_message("The selected plugin could not be installed because <a href=\"https://github.com/frostschutz/MyBB-PluginLibrary/blob/master/inc/plugins/pluginlibrary.php\">PluginLibrary</a> is too old.",
            "error");
        admin_redirect("index.php?module=config-plugins");
    }

    find_replace_templatesets('forumdisplay_thread', '#' . preg_quote('<tr class="inline_row">') .
        '#', "<tr class=\"inline_row{\$threadclass}\">");

    $PL->stylesheet('styleclosedthreads_css', '
        tr.closed span.subject_old,tr.closed span.subject_new{
            text-decoration: line-through;   
        }
    ');
}

function styleclosedthreads_deactivate()
{
    global $PL;
    $PL or require_once PLUGINLIBRARY;
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    find_replace_templatesets('forumdisplay_thread', '#' . preg_quote('{$threadclass}') .
        '#', "");
    $PL->stylesheet_delete('styleclosedthreads_css', true);
}

function styleclosedthreads_thread()
{
    global $mybb, $threadclass, $thread;

    if ($thread['closed'] == 1 && $thread['sticky'] == 0) {
        $threadclass = " closed";
    } else {
        $threadclass = " open";
    }
}
?>