<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'rpadmin.php');

require_once "./global.php";

if ($mybb->settings['rpadmin_enabled']) {

    add_breadcrumb('Roleplay Admin', "rpadmin.php");

    $rpadmin_page = "<div class='rpadmin'>";

    if (isset($mybb->input["steamid"])) {
        if (is_steamid(strtoupper($mybb->input["steamid"]))) {
            add_breadcrumb(strtoupper($mybb->input["steamid"]) . ' Warnings/Bans',
                "rpadmin.php?steamid=" . strtoupper($mybb->input["steamid"]));
            $steamid = strtoupper($mybb->input["steamid"]);
        }
    } else {
        if (is_steamid(strtoupper($mybb->user['steamid']))) {
            add_breadcrumb('My Warnings/Bans', "rpadmin.php?steamid=" . strtoupper($mybb->
                user['steamid']));
            $steamid = strtoupper($mybb->user['steamid']);
        }
    }

    if ($steamid) {

        if ($mybb->request_method == "post") {
            if ($mybb->input['wadmin'] == "Delete") {
                if (count($mybb->input['warnings']) > 0) {
                    foreach ($mybb->input['warnings'] as $warn) {
                        delete_warning($warn);
                    }
                    redirect("rpadmin.php?steamid=" . $steamid, "Warnings have been deleted");
                } else {
                    redirect("rpadmin.php?steamid=" . $steamid, "No warnings selected to delete!");
                }
            } elseif ($mybb->input['wadmin'] == "Merge") {
                if (isset($mybb->input['reason'])) {
                    $warnings = explode(",", $mybb->input['warnings']);
                    if (warnings_match($warnings, $mybb->input['steamid'])) {
                        $warning_to_edit = array_pop($warnings);
                        $update_array = array(
                            "reason" => $mybb->input['reason'] . " (MERGED)",
                            "arpname" => $mybb->user['username'],
                            "asid" => $mybb->user['steamid']);
                        edit_warning($warning_to_edit, $update_array);

                        //delete the rest
                        foreach ($warnings as $warn) {
                            delete_warning($warn);
                        }

                        redirect("rpadmin.php?steamid=" . $steamid, "Warnings have been merged");

                    } else {
                        redirect("rpadmin.php?steamid=" . $steamid, "Warning IDs didn't match SteamID!");
                    }
                } else {
                    if (count($mybb->input['warnings']) > 1) {
                        $warnings = implode(",", $mybb->input['warnings']);
                        $string = implode(", #", $mybb->input['warnings']);
                        $rpadmin_page .= "You are going to merge warnings: #" . $string;
                        $rpadmin_page .= "<form action='' method='post'>
                            Merged warning reason: <input type='text' name='reason' size='50' />
                            <input type='hidden' name='steamid' value='" . $mybb->
                            input['steamid'] . "' />
                            <input type='hidden' name='warnings' value='" . $warnings .
                            "' />
                            <br />
                            <br /><input type='submit' name='wadmin' value='Merge' />
                        </form>";
                    } else {
                        redirect("rpadmin.php?steamid=" . $steamid,
                            "In order to merge warnings you have to select atleast 2!");
                    }
                }
            } elseif ($mybb->input['badmin'] == "Revoke" && isset($mybb->input['ban'])) {
                $baninfo = get_ban($mybb->input['ban']);

                $update = array("removed" => 1);

                edit_ban($baninfo['banid'], $update);

                redirect("rpadmin.php?steamid=" . $steamid, "Ban has been revoked!");
            } elseif ($mybb->input['badmin'] == "Undo Revoke" && isset($mybb->input['ban'])) {
                $baninfo = get_ban($mybb->input['ban']);

                $update = array("removed" => 0);

                edit_ban($baninfo['banid'], $update);

                redirect("rpadmin.php?steamid=" . $steamid, "Ban is active again!");
            } elseif ($mybb->input['badmin'] == "Edit" && isset($mybb->input['ban'])) {
                $baninfo = get_ban($mybb->input['ban']);

                if ($mybb->input['submit']) {
                    if ($baninfo['steamid'] == $steamid) {
                        if (!empty($mybb->input['length']) && !empty($mybb->input['reason'])) {
                            $update = array("length" => $mybb->input['length'], "reason" => $mybb->input['reason']);

                            edit_ban($baninfo['banid'], $update);
                            redirect("rpadmin.php?steamid=" . $steamid, "Ban edited!");
                        } else {
                            redirect("rpadmin.php?steamid=" . $steamid,
                                "You didn't set a correct value for length or reason!");
                        }
                    } else {
                        redirect("rpadmin.php?steamid=" . $steamid, "You are not suposed to be here!");
                    }
                } else {
                    $ban = $baninfo;

                    $rpadmin_page .= "<form action='rpadmin.php?steamid=" . $steamid .
                        "' method='post'>";

                    $ban['date'] = date("d M Y - H:i:s", $ban['time']);

                    $ban['length'] = "<input name='length' style='background:#25272A;' size='100' type='text' value='" .
                        $ban['length'] . "'";
                    $ban['reason'] = "<input name='reason' style='background:#25272A;' size='100' type='text' value='" .
                        $ban['reason'] . "'";

                    eval("\$ban = \"" . $templates->get("rpadmin_ban") . "\";");
                    $rpadmin_page .= $ban;
                    $rpadmin_page .= "<input type='hidden' name='ban' value='" . $baninfo['banid'] .
                        "' /><input type='hidden' name='submit' value='1' /><input type='submit' name='badmin' value='Edit' /></form>";
                }

            } else {
                redirect("rpadmin.php", "You are not suposed to be here!");
            }
        } else {

            // WARNINGS
            $select = "";
            $query_warnings = $db->query("SELECT * FROM rp_warnings WHERE steamid = '" . $db->
                escape_string($steamid) . "'");

            $rpadmin_page .= "<div class='warnings'>";

            if ($mybb->user['steamid'] == $steamid || is_rpadmin($mybb->user['uid'])) {
                $rpadmin_page .= "<form name='warnings' action='rpadmin.php?steamid=" . $steamid .
                    "' method='post'>";
            }

            while ($warn = $db->fetch_array($query_warnings)) {
                if ($mybb->user['steamid'] == $steamid || is_rpadmin($mybb->user['uid'])) {
                    if ($mybb->user['steamid'] == $steamid && ($warn['appealed'] > time() - ($mybb->
                        settings['rpadmin_appealinterval'] * 86400))) {

                        if (is_rpadmin($mybb->user['uid'])) {
                            $select = "<input name='warnings[]' type='checkbox' value='" . $warn['warningid'] .
                                "' /> ";
                        }

                        $warn['appealdate'] = "<span class='small'>Appealed on " . date("d M Y - H:i:s",
                            $warn['appealed']) . "</span>";
                    } elseif ($warn['appealed'] != 0) {
                        $warn['appealdate'] = "<span class='small'>Appealed on " . date("d M Y - H:i:s",
                            $warn['appealed']) . "</span>";
                        $select = "<input name='warnings[]' type='checkbox' value='" . $warn['warningid'] .
                            "' /> ";
                    } else {
                        $warn['appealdate'] = "";
                        $select = "<input name='warnings[]' type='checkbox' value='" . $warn['warningid'] .
                            "' /> ";
                    }
                }

                if ($warn['edittime'] == 0) {
                    $warn['edited'] = "";
                } else {
                    $warn['edited'] = "Modify date: " . date("d M Y - H:i:s", $warn['edittime']);
                }
                $warn['date'] = date("d M Y - H:i:s", $warn['time']);

                eval("\$warn = \"" . $templates->get("rpadmin_warning") . "\";");

                $rpadmin_page .= $warn;
            }


            if ($db->num_rows($query_warnings) == 0) {
                $rpadmin_page .= $steamid . " has no warnings!";
            } else {
                if ($mybb->user['steamid'] == $steamid) {
                    $rpadmin_page .= "<input type='submit' name='appeal' value='Appeal Warnings' onclick='document.warnings.action = \"rpappeal.php\"' /> ";
                }

                if (is_rpadmin($mybb->user['uid'])) {
                    $rpadmin_page .= "<input type='submit' name='wadmin' value='Merge' /> ";
                    //$rpadmin_page .= "<input type='submit' name='wadmin' value='Edit' /> ";
                    $rpadmin_page .= "<input type='submit' name='wadmin' value='Delete' onclick=\"return confirm('Are you sure you want to delete selected warnings?')\" /> ";
                }
            }

            if ($mybb->user['steamid'] == $steamid || is_rpadmin($mybb->user['uid'])) {
                $rpadmin_page .= "</form>";
            }

            $rpadmin_page .= "</div>";


            //BANS
            $query_bans = $db->query("SELECT * FROM rp_bans WHERE steamid = '" . $db->
                escape_string($steamid) . "'");

            $rpadmin_page .= "<div class='bans'>";
            while ($ban = $db->fetch_array($query_bans)) {
                $select = "";
                $ban['date'] = date("d M Y - H:i:s", $ban['time']);

                if ($ban['appealed'] != 0) {
                    $ban['appealdate'] = "<span class='small'>Appealed on " . date("d M Y - H:i:s",
                        $ban['appealed']) . "</span>";
                } else {
                    $ban['appealdate'] = "";
                }

                if ($ban['removed']) {
                    $ban['status'] = "<strong>Status: </strong> <span style='color: #00FFFF;'>REVOKED</span>";
                } elseif ($ban['length'] == 0) {
                    $ban['status'] = "<span style='color: red;'>PERMANENT</span>";
                } elseif (($ban['time'] + $ban['length']) < time()) {
                    $ban['status'] = "<strong>Status: </strong> <span style='color: green;'>EXPIRED</span>";
                } else {
                    $ban['expires'] = ($ban['length'] == 0) ? "Permanent" : date("d M Y - H:i:s", $ban['time'] +
                        $ban['length']);
                    $ban['status'] = "<strong>Expires: </strong> <span style='color: red;'>" . $ban['expires'] .
                        "</span>";
                }

                if (($mybb->user['steamid'] == $steamid && ($ban['length'] == 0 || $ban['length'] +
                    $ban['time'] > time())) || is_rpadmin($mybb->user['uid'])) {
                    $select .= "<tr><td colspan='2'>";
                }
                if ($mybb->user['steamid'] == $steamid && ($ban['length'] == 0 || $ban['length'] +
                    $ban['time'] > time()) && $ban['appealed'] < time() - ($mybb->settings['rpadmin_appealinterval'] *
                    86400)) {
                    $select .= "<form style='display: inline-block;' action='rpappeal.php' method='post'><input type='hidden' name='ban' value='" .
                        $ban['banid'] . "' /><input type='submit' name='appeal' value='Appeal Ban' /></form> ";
                }
                if (is_rpadmin($mybb->user['uid'])) {
                    if ($ban['removed'] == 0) {
                        $select = $select . "<form style='display: inline-block;' action='rpadmin.php?steamid=" .
                            $steamid . "' method='post'><input type='hidden' name='ban' value='" . $ban['banid'] .
                            "' /><input type='submit' name='badmin' value='Edit' /> <input type='submit' name='badmin' value='Revoke' onclick=\"return confirm('Are you sure you want to revoke this ban?')\" /></form>";
                    } else {
                        $select = $select . "<form style='display: inline-block;' action='rpadmin.php?steamid=" .
                            $steamid . "' method='post'><input type='hidden' name='ban' value='" . $ban['banid'] .
                            "' /><input type='submit' name='badmin' value='Edit' /> <input type='submit' name='badmin' value='Undo Revoke' onclick=\"return confirm('Are you sure you want to undo this revoked ban?')\" /></form>";
                    }
                }

                eval("\$ban = \"" . $templates->get("rpadmin_ban") . "\";");
                $rpadmin_page .= $ban;
            }
            if ($db->num_rows($query_bans) == 0) {
                $rpadmin_page .= $steamid . " has no bans!";
            }

            $rpadmin_page .= "</div>";
            $rpadmin_page .= "<div style='clear:both;'></div>";


        }
    } else {
        if (isset($mybb->input["steamid"])) {
            redirect("rpadmin.php", "The steamID entered is not valid");
        } else {
            $rpadmin_page = "Your steamID is not set or invalid";
        }
    }

    $rpadmin_page .= "</div>";

    eval("\$page = \"" . $templates->get("rpadmin") . "\";");
    output_page($page);

} else {
    echo "Plugin is not installed";
}

?>