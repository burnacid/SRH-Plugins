<?php

if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if (!defined("PLUGINLIBRARY")) {
    define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

//HOOKS
$plugins->add_hook("global_intermediate", "darkRPServer_serverinfo");

function darkRPServer_info()
{
    global $mybb, $plugins_cache;
    $info = array(
        "name" => "DarkRP Server Info",
        "description" => "This plugin displays server info",
        "website" => "http://lenders-it.nl",
        "author" => "Burnacid",
        "authorsite" => "http://lenders-it.nl",
        "version" => "1.0",
        "guid" => "",
        "compatibility" => "18*");

    return $info;
}

function darkRPServer_activate()
{
    if (!file_exists(PLUGINLIBRARY)) {
        flash_message("The selected plugin could not be installed because <a href=\"https://github.com/frostschutz/MyBB-PluginLibrary/blob/master/inc/plugins/pluginlibrary.php\">PluginLibrary</a> is missing.",
            "error");
        admin_redirect("index.php?module=config-plugins");
    }

    global $PL;
    $PL or require_once PLUGINLIBRARY;
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    if ($PL->version < 12) {
        flash_message("The selected plugin could not be installed because <a href=\"https://github.com/frostschutz/MyBB-PluginLibrary/blob/master/inc/plugins/pluginlibrary.php\">PluginLibrary</a> is too old.",
            "error");
        admin_redirect("index.php?module=config-plugins");
    }


    $PL->settings("darkRPServer", // group name and settings prefix
        "Dark RP Server Info", "Setting group for the Online Players plugin.", array("name" =>
            array(
            "title" => "Server Name",
            "description" => "The name displayed for this server",
            "optionscode" => "text",
            "value" => "DarkRP",
            ),"server" => array(
            "title" => "Garrys Mod Server",
            "description" => "The IP:Port of the server for admins to quick connect",
            "optionscode" => "text",
            "value" => "69.162.71.162:27015",
            ), "max" => array(
            "title" => "Max players",
            "description" => "The max players for the server",
            "optionscode" => "text",
            "value" => "64",
            )));

    $t_darkRPServer_serverinfo = '<a href="steam://connect/{$mybb->settings[\'darkRPServer_server\']}" class="DRPserverinfo">{$mybb->settings[\'darkRPServer_name\']} {$onlineplayers}/{$mybb->settings[\'darkRPServer_max\']}</a>';

    $PL->templates("darkRPServer", // template prefix, must not contain _
        "DarkRP Server Info", // you can also use "<lang:your_language_variable>" here
        array("serverinfo" => $t_darkRPServer_serverinfo, ));

    $PL->stylesheet('darkRPServer_css', '
        .DRPserverinfo{
            float: right;
            display: inline-block;
            background: url(images/garrysmod_logo24.png)no-repeat center left;
            padding: 5px 0 5px 30px;
            margin: -5px 0 0 0;
        }
    ');

    find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('{$lang->welcome_register}</a></span>') .
        '#', "{\$lang->welcome_register}</a></span>{\$darkrpinfo}");
    find_replace_templatesets('header_welcomeblock_member', '#' . preg_quote('{$lang->welcome_logout}</a></span>') .
        '#', "{\$lang->welcome_logout}</a></span>{\$darkrpinfo}");
}

function darkRPServer_deactivate()
{
    global $PL;
    $PL or require_once PLUGINLIBRARY;
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    $PL->settings_delete("darkRPServer", true);
    $PL->templates_delete("darkRPServer", true);
    $PL->stylesheet_delete('darkRPServer', true);

    find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('{$darkrpinfo}') .
        '#', "");
    find_replace_templatesets('header_welcomeblock_member', '#' . preg_quote('{$darkrpinfo}') .
        '#', "");
}

function darkRPServer_serverinfo()
{
    global $mybb, $db, $templates, $darkrpinfo;

    $query = $db->query("SELECT * FROM rp_players WHERE online != 0");
    $onlineplayers = $db->num_rows($query);

    eval('$darkrpinfo = "' . $templates->get('darkRPServer_serverinfo') . '";');
}

?>