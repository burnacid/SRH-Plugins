<?php
// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

//HOOKS
$plugins->add_hook('parse_message', 'steamidMyCode_parse_message');

function steamidMyCode_info()
{
    global $mybb, $plugins_cache;
    $info = array(
        "name" => "SteamID My Code",
        "description" => "SteamID MyCode to link profiles",
        "website" => "http://lenders-it.nl",
        "author" => "Burnacid",
        "authorsite" => "http://lenders-it.nl",
        "version" => "1.0",
        "guid" => "",
        "compatibility" => "18*");

    return $info;
}

function steamidMyCode_parse_message($message)
{
    require_once MYBB_ROOT . "inc/functions_steamid.php";
    global $firephp;
    
    $find = "#STEAM_0:(1|0):(\d+)#";    
    preg_match_all($find, $message, $matches);
    
    $steamids = $matches[0];
    for($i=0;$i<count($steamids);$i++){
        $steamid = $steamids[$i];
        $steam64 = steamIDtoSteam64($steamids[$i]);
        
        $firephp->log($steamid ."|". $steam64);
        
        $message = preg_replace("#".$steamid."#","<a href='http://www.steamcommunity.com/profiles/".$steam64."' target='_blank'>".$steamid."</a>",$message);
    }
    
    return $message;
}

?>