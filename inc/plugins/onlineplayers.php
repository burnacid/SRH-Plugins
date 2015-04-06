<?php

if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if (!defined("PLUGINLIBRARY")) {
    define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

//HOOKS
$plugins->add_hook("global_intermediate", "onlineplayers_global");

function onlineplayers_info()
{
    global $mybb, $plugins_cache;
    $info = array(
        "name" => "Online Players",
        "description" => "This plugin displays online players and admins",
        "website" => "http://lenders-it.nl",
        "author" => "Burnacid",
        "authorsite" => "http://lenders-it.nl",
        "version" => "1.0",
        "guid" => "",
        "compatibility" => "18*");

    return $info;
}

function onlineplayers_activate()
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


    $PL->settings("onlineplayers", // group name and settings prefix
        "Online Players", "Setting group for the Online Players plugin.", array(
        "errorlvl" => array(
            "title" => "Error level",
            "description" => "Number of admins online to display an error (lower or equal to)",
            "optionscode" => "text",
            "value" => 1,
            ),
        "warninglvl" => array(
            "title" => "Warning level",
            "description" => "Number of admins online to display an warning (lower or equal to)",
            "optionscode" => "text",
            "value" => 3,
            ),
        "server" => array(
            "title" => "Garrys Mod Server",
            "description" => "The IP:Port of the server for admins to quick connect",
            "optionscode" => "text",
            "value" => "69.162.71.162:27015",
            ),
        "groupids" => array(
            "title" => "Visible to groups",
            "description" => "Groep ID's to how the error is visible",
            "optionscode" => "text",
            "value" => "4",
            )));

    $t_onlineplayers_adminmsg =
        '<div class="{$warnclass}"><a href="steam://connect/{$mybb->settings[\'onlineplayers_server\']}">There are currently {$adminsonline} admins online</a></div>';

    $PL->templates("onlineplayers", // template prefix, must not contain _
        "Online Players", // you can also use "<lang:your_language_variable>" here
        array("adminmsg" => $t_onlineplayers_adminmsg, ));

    $PL->stylesheet('onlineplayers_css', '
    .note_error {
        background: #FFCDCD;
        border: 1px solid #A5161A;
        color: #FF0000;
        text-align: center;
        padding: 5px 20px;
        margin-bottom: 15px;
        font-size: 11px;
        word-wrap: break-word;
    }
    
    .note_error a{color:#A5161A;}
    
    .note_warn {
        background: #FDFFB7;
        border: 1px solid #FFB800;
        color: #FFA500;
        text-align: center;
        padding: 5px 20px;
        margin-bottom: 15px;
        font-size: 11px;
        word-wrap: break-word;
    }
    
    .note_warn a{color:#A5161A;}
    ');

    find_replace_templatesets('header', '#' . preg_quote('{$bbclosedwarning}') . '#',
        "{\$bbclosedwarning}{\$onlineadmins}");
}

function onlineplayers_deactivate()
{
    global $PL;
    $PL or require_once PLUGINLIBRARY;
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    $PL->settings_delete("onlineplayers", true);
    $PL->templates_delete("onlineplayers", true);
    $PL->stylesheet_delete('onlineplayers', true);

    find_replace_templatesets('header', '#' . preg_quote('{$onlineadmins}') . '#',
        "");
}

function onlineplayers_global()
{
    global $onlineadmins, $db, $templates, $mybb;

    $groups = explode(",", $mybb->settings['onlineplayers_groupids']);
    
    if (in_array($mybb->user['usergroup'], $groups)) {
        $query = $db->query("SELECT * FROM rp_players WHERE online = 1 AND rank != 'user'");
        $admins = $db->num_rows($query);

        if ($admins <= $mybb->settings['onlineplayers_warninglvl']) {
            if ($admins <= $mybb->settings['onlineplayers_errorlvl']) {
                $warnclass = "note_error";
            } else {
                $warnclass = "note_warn";
            }
            $adminsonline = $admins;

            eval('$onlineadmins = "' . $templates->get('onlineplayers_adminmsg') . '";');
        }
    }
}


?>