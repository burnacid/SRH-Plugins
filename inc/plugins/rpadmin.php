<?php
// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if (!defined("PLUGINLIBRARY")) {
    define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

//HOOKS
$plugins->add_hook("global_start", "rpadmin_global_start");
$plugins->add_hook("forumdisplay_get_threads",
    "rpadmin_forumdisplay_get_threads");

function rpadmin_info()
{
    global $mybb, $plugins_cache;
    $info = array(
        "name" => "Roleplay Admin",
        "description" => "This plugin is specialy made for SRH to manage Bans and Warnings",
        "website" => "http://lenders-it.nl",
        "author" => "Burnacid",
        "authorsite" => "http://lenders-it.nl",
        "version" => "1.0",
        "guid" => "",
        "compatibility" => "18*");

    return $info;
}
function rpadmin_is_installed()
{
    global $settings;
    // This plugin creates settings on install. Check if setting exists.
    // Another example would be $db->table_exists() for database tables.
    if (isset($settings['rpadmin_enabled'])) {
        return true;
    }
}
function rpadmin_install()
{
    if (!file_exists(PLUGINLIBRARY)) {
        flash_message("The selected plugin could not be installed because <a href=\"https://github.com/frostschutz/MyBB-PluginLibrary/blob/master/inc/plugins/pluginlibrary.php\">PluginLibrary</a> is missing.",
            "error");
        admin_redirect("index.php?module=config-plugins");
    }

    global $PL;
    $PL or require_once PLUGINLIBRARY;

    if ($PL->version < 12) {
        flash_message("The selected plugin could not be installed because <a href=\"https://github.com/frostschutz/MyBB-PluginLibrary/blob/master/inc/plugins/pluginlibrary.php\">PluginLibrary</a> is too old.",
            "error");
        admin_redirect("index.php?module=config-plugins");
    }
}
function rpadmin_uninstall()
{
    global $PL;
    $PL or require_once PLUGINLIBRARY;

    $PL->settings_delete("rpadmin", true);
    $PL->templates_delete("rpadmin", true);
    $PL->stylesheet_delete('rpadmin', true);
}
function rpadmin_activate()
{
    global $PL, $mybb;
    $PL or require_once PLUGINLIBRARY;

    $PL->settings("rpadmin", // group name and settings prefix
        "Roleplay Admin", "Setting group for the Roleplay Admin plugin.", array(
        "enabled" => array(
            "title" => "Roleplay Admin Enabled?",
            "description" => "The default is yes.",
            "value" => 1,
            ),
        "admin" => array(
            "title" => "Admin Groups",
            "description" => "Group IDs (comma seperated) from groups that are allowed to add, remove, revoke, edit etc. either bans or warnings",
            "optionscode" => "text",
            "value" => "4"),
        "appealinterval" => array(
            "title" => "Appeal Interval",
            "description" => "Time in days a user has to wait before re-appealing warnings",
            "optionscode" => "text",
            "value" => "30"),
        "appealfid" => array(
            "title" => "Appeal Forum",
            "description" => "The forum ID of the forum where appeals should be posted",
            "optionscode" => "text",
            "value" => "")) // , true /* optional,  prints a language file */
        );

    //TEMPLATES

    $t_rpadmin = '
        <html>
        <head>
        <title>Roleplay Admin</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
			<div class="rp_search">
			<form action="rpadmin.php" method="get">
				SteamID: <input type="text" name="steamid" />
				<input type="submit" value="Search" />
			</form>
			</div>
        {$rpadmin_page}
        {$footer}
        </body>
        </html>
        ';

    $t_rpadmin_warning = '
        <div class="warning">
        <table>
            <tr>
                <th colspan=\'2\'>{$select}Warning #{$warn[\'warningid\']} {$warn[\'appealdate\']}</th>
            </tr>
            <tr>
                <td class="small" style="width: 50%;">Issue date: {$warn[\'date\']}</td>
                <td class="small" style="width: 50%;">{$warn[\'edited\']}</td>
            </tr>
            <tr>
                <td>
                <strong>Player:</strong>
                <br />{$warn[\'name\']}<br />({$warn[\'steamid\']})
                </td>
                <td>
                <strong>Issuer:</strong>
                <br />{$warn[\'arpname\']}<br />({$warn[\'asid\']})
                </td>
            </tr>
            <tr>
                <td colspan=\'2\'><strong>Reason:</strong> {$warn[\'reason\']}</td>
            </tr>
        </table>
        </div>
        ';

    $t_rpadmin_ban = '
        <div class="ban">
        <table>
            <tr>
                <th colspan=\'2\'>Ban #{$ban[\'banid\']} {$ban[\'appealdate\']}</th>
            </tr>
            <tr>
                <td class="small" colspan=\'2\'>Issue date: {$ban[\'date\']}</td>
            </tr>
            <tr>
                <td>
                <strong>Player:</strong>
                <br />{$ban[\'name\']}<br />({$ban[\'steamid\']})
                </td>
                <td>
                <strong>Issuer:</strong>
                <br />{$ban[\'aname\']}<br />({$ban[\'adminid\']})
                </td>
            </tr>
            <tr>
                <td><strong>Length:</strong> {$ban[\'length\']}</td>
                <td>{$ban[\'status\']}</td>
            </tr>
            <tr>
                <td><b>Reason:</b> {$ban[\'reason\']}</td>
            </tr>
            {$select}
        </table>
        </div>
        ';


    $PL->templates("rpadmin", // template prefix, must not contain _
        "Roleplay Admin", // you can also use "<lang:your_language_variable>" here
        array(
        "" => $t_rpadmin,
        "warning" => $t_rpadmin_warning,
        "ban" => $t_rpadmin_ban,
        ));

    $PL->stylesheet('rpadmin_css', '
            div.rp_search{
                text-align: right;
            }
            
            .rpadmin input[type="text"],.rp_search input[type="text"]{
                background: #2f3236;
                color: #fff;
                border: 1px solid #2f3236;
                padding: 3px;
                outline: 0;
                font-size: 13px;
                font-family: Tahoma, Verdana, Arial, Sans-Serif;    
            }
            
            .rp_admin input[type="text"]{
                background: #2f3236;
                color: #fff;
                border: 1px solid #2f3236;
                padding: 3px;
                outline: 0;
                font-size: 13px;
                font-family: Tahoma, Verdana, Arial, Sans-Serif;    
            }
            
            .small{
                font-size: 10px;    
            }
            
            .warnings{
                width: 45%;
                padding: 10px;
                float: left;
            }
            
            .warning{
                background:#2f3236;
                padding: 3px;
                margin: 0 0 15px 0;
            }
            
            .warning table{
                width: 100%;    
            }
            
            .warning table th{
                font-size: 18px;
                background:#383B41; 
            }
            
            .bans{
                width: 45%;
                padding: 10px;
                float:right;
            }
            
            .ban{
                background:#2f3236;
                padding: 3px;
                margin: 0 0 15px 0;
            }
            
            .ban table{
                width: 100%;    
            }
            
            .ban table th{
                font-size: 18px;
                background:#383B41; 
            }
            
            textarea.reason{
                width: 100%;
                height: 100px;    
                margin: 0 0 20px 0;
            }
            
            a.button.appeal_button span {
                background-position: 0 -460px;
            }
        ');
}
function rpadmin_deactivate()
{
    global $PL;
    $PL or require_once PLUGINLIBRARY;

    $PL->cache_delete("rpadmin", true);
}

function rpadmin_global_start()
{
    require_once MYBB_ROOT . "inc/functions_rpadmin.php";
}

function rpadmin_forumdisplay_get_threads()
{
    global $newthread, $mybb, $fid;

    if ($mybb->settings['rpadmin_appealfid'] == $fid) {
        $newthread = '<a href="rpadmin.php" class="button appeal_button"><span>Post Appeal</span></a>';
    }
}

?>