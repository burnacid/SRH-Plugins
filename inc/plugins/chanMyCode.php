<?php
// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

//HOOKS
$plugins->add_hook('parse_message', 'chanMyCode_parse_message');

function chanMyCode_info()
{
    global $mybb, $plugins_cache;
    $info = array(
        "name" => "4chan My Code (green text)",
        "description" => "This plugin adds the > greentext MyCode",
        "website" => "http://lenders-it.nl",
        "author" => "Burnacid",
        "authorsite" => "http://lenders-it.nl",
        "version" => "1.0",
        "guid" => "",
        "compatibility" => "18*");

    return $info;
}

function chanMyCode_parse_message($message)
{
    $find = "#(^|\n)&gt;(.*)($|\n)#m";
    $replace = "$1<span style=\"color:#32CD32;\">&gt;$2</span>$3";
    
    $message = preg_replace($find, $replace, $message);
    
    return $message;
}

?>