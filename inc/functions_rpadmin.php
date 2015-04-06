<?php

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