<?php

function secondsToTime($seconds)
{   
    if($seconds == 0){
        return "Permanent";
    }else{
        $return ="";
        
        $year = floor($seconds / 31536000);
        $seconds %= 31536000;
        
        $month = floor($seconds / 2592000);
        $seconds %= 2592000;
        
        $week = floor($seconds / 604800);
        $seconds %= 604800;
        
        $day = floor($seconds / 86400);
        $seconds %= 86400;
        
        $hours = floor($seconds / 3600);
        $seconds %= 3600;
        
        $minute = floor($seconds / 60);
        $seconds %= 60;
        
        if($year != 0){
            $return .= $year. "Y ";
        }
        
        if($month != 0){
            $return .= $month. "M ";
        }
        
        if($week != 0){
            $return .= $week. "W ";
        }
        
        if($day != 0){
            $return .= $day. "D ";
        }
        
        if($hours != 0){
            $return .= $hours. "H ";
        }
        
        if($minute != 0){
            $return .= $minute. "Min ";
        }
        
        return $return;
    }
}

function is_steamid($steamid)
{
    return preg_match("/STEAM_0:[0-1]:[0-9]+/", $steamid);
}

function is_rpadmin($uid)
{
    global $mybb;

    $groups = explode(",", $mybb->settings['rpadmin_admin']);
    return in_array($mybb->user['usergroup'], $groups);
}

function get_warning($wid)
{
    global $db;

    $query = $db->query("SELECT * FROM rp_warnings WHERE warningid = '" . $db->
        escape_string($wid) . "'");
    return $db->fetch_array($query);
}

function get_ban($bid)
{
    global $db;

    $query = $db->query("SELECT * FROM rp_bans WHERE banid = '" . $db->
        escape_string($bid) . "'");
    return $db->fetch_array($query);
}

function warnings_match($warnings, $steamid)
{
    global $db;

    foreach ($warnings as $warn) {
        $warn_info = get_warning($warn);
        if ($warn_info['steamid'] != $steamid) {
            return false;
        }
    }
    return true;
}

function edit_ban($bid, $update)
{
    global $db;

    end($update);
    $last_key = key($update);
    $update_string = "";
    foreach ($update as $key => $value) {
        $update_string .= $key . " = '" . $db->escape_string($value) . "'";
        if ($key != $last_key) {
            $update_string .= ",";
        }
    }

    $query = $db->query("UPDATE rp_bans SET " . $update_string . " WHERE banid = '" .
        $db->escape_string($bid) . "'");
}

function edit_warning($wid, $update)
{
    global $db;

    end($update);
    $last_key = key($update);
    $update_string = "";
    foreach ($update as $key => $value) {
        $update_string .= $key . " = '" . $db->escape_string($value) . "'";
        if ($key != $last_key) {
            $update_string .= ",";
        } else {
            $update_string .= ",edittime='" . time() . "' ";
        }
    }

    $query = $db->query("UPDATE rp_warnings SET " . $update_string .
        " WHERE warningid = '" . $db->escape_string($wid) . "'");
}

function delete_warning($wid)
{
    global $db;

    return $db->query("DELETE FROM rp_warnings WHERE warningid = '" . $db->
        escape_string($wid) . "'");
}

?>