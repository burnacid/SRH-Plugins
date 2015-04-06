<?php

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'rpappeal.php');

require_once "./global.php";

$rpadmin_page .= "";

if ($mybb->settings['rpadmin_enabled']) {

    add_breadcrumb('Roleplay Admin', "rpadmin.php");

    if ($mybb->request_method == "post") {
        if ($mybb->input['appeal'] == "Appeal Warnings") {
            if (count($mybb->input['warnings']) > 0) {
                if (!is_array($mybb->input['warnings'])) {
                    $mybb->input['warnings'] = explode(",", $mybb->input['warnings']);
                }
                $warning = get_warning($mybb->input['warnings'][0]);
                if (warnings_match($mybb->input['warnings'], $mybb->user['steamid'])) {
                    add_breadcrumb('Appealing Warnings', "rpadmin.php");

                    if (isset($mybb->input['submit'])) {
                        $post_msg = "";
                        foreach ($mybb->input['warnings'] as $warning) {
                            $warn = get_warning($warning);
                            if (empty($mybb->input['reason_' . $warn['warningid']])) {
                                redirect("rpadmin.php", "You didn't enter in all reasons for your appeal");
                            }

                            $post_msg .= "[b]Warning #" . $warn['warningid'] . "[/b]
[b]RP name:[/b] " . $warn['name'] . " (" . $warn['steamid'] . ")
[b]Admin/mod:[/b] " . $warn['arpname'] . " (" . $warn['asid'] . ")
[b]Date of warning:[/b] " . date("d M Y - H:i:s", $warn['time']) . "
[b]Warning reason:[/b] " . $warn['reason'] . "
[b]What happened leading up to this warning and why should I get un-warned:[/b]\n\r
" . $mybb->input['reason_' . $warn['warningid']] . "\n\r\n\r";

                        }

                        require_once MYBB_ROOT . "inc/datahandlers/post.php";
                        $posthandler = new PostDataHandler("insert");
                        $posthandler->action = "thread";

                        $warnings_string = implode(", #", $mybb->input['warnings']);
                        // Set the thread data
                        $new_thread = array(
                            "fid" => $mybb->settings['rpadmin_appealfid'],
                            "subject" => 'WARNING APPEAL: ' . $mybb->user['username'] .
                                ' appealing warning #' . $warnings_string,
                            "icon" => -1,
                            "uid" => $mybb->user['uid'],
                            "username" => $mybb->user['username'],
                            "message" => $post_msg,
                            "ipaddress" => '127.0.0.1',
                            "posthash" => '',
                            "savedraft" => 0,
                            );

                        // Set up the thread options
                        $new_thread['options'] = array(
                            "signature" => 'no',
                            "emailnotify" => 'no',
                            "disablesmilies" => 'no');

                        $posthandler->set_data($new_thread);

                        // Now let the post handler do all the hard work.
                        if ($posthandler->validate_thread()) {

                            foreach ($mybb->input['warnings'] as $warning) {
                                $update = array("appealed" => time());
                                edit_warning($warning, $update);
                            }

                            $thread_info = $posthandler->insert_thread();
                            redirect("showthread.php?tid=" . $thread_info['tid'] . "&pid=" . $thread_info['pid'] .
                                "#pid" . $thread_info['pid'] . "", "Appeal created!");
                        } else {
                            redirect("rpadmin.php",
                                "Creating the Appeal didn't go well. Try again later or contact Burnacid");
                        }

                    } else {
                        $rpadmin_page .= "<form action='' method='post'>";

                        foreach ($mybb->input['warnings'] as $warning) {
                            $warn = get_warning($warning);

                            if ($warn['edittime'] == 0) {
                                $warn['edited'] = "";
                            } else {
                                $warn['edited'] = "Modify date: " . date("d M Y - H:i:s", $warn['edittime']);
                            }
                            $warn['date'] = date("d M Y - H:i:s", $warn['time']);

                            eval("\$warntable = \"" . $templates->get("rpadmin_warning") . "\";");
                            $rpadmin_page .= $warntable;

                            $rpadmin_page .= "What happened leading up to your warning and why should we un-warn you: <br /><textarea class='reason' name='reason_" .
                                $warn['warningid'] . "'></textarea>";
                        }
                        $warnings = implode(",", $mybb->input['warnings']);
                        $rpadmin_page .= "<input type='hidden' name='submit' value='1' /><input type='hidden' name='warnings' value='" .
                            $warnings . "' /><input name='appeal' value='Appeal Warnings' type='submit' /></form>";
                    }

                } else {
                    redirect("rpadmin.php", "You can only appeal your own warnings!");
                }
            } else {
                redirect("rpadmin.php", "You did not select any warnings to appeal!");
            }
        } elseif ($mybb->input['appeal'] == "Appeal Ban" && isset($mybb->input['ban'])) {
            $ban = get_ban($mybb->input['ban']);
            if ($ban['steamid'] == $mybb->user['steamid']) {
                add_breadcrumb('Appealing Ban #' . $ban['banid'], "rpadmin.php");

                if (isset($mybb->input['submit'])) {
                    if (!empty($mybb->input['reason_ban']) && !empty($mybb->input['reason_unban'])) {
                        $ban_until = ($ban['length'] == 0) ? "Permanent" : date("d M Y - H:i:s", $ban['time'] +
                            $ban['length']);
                        $post_msg .= "[b]Ban #" . $ban['banid'] . "[/b]
[b]RP name:[/b] " . $ban['name'] . " (" . $ban['steamid'] . ")
[b]Admin/mod:[/b] " . $ban['aname'] . " (" . $ban['adminid'] . ")
[b]Date of Ban:[/b] " . date("d M Y - H:i:s", $ban['time']) . "
[b]Banned until:[/b] " . $ban_until . "
[b]Ban reason:[/b] " . $ban['reason'] . "
[b]What happened leading up to this ban:[/b]\n\r " . $mybb->input['reason_ban'] .
                            "\n\r
[b]Why should I get un-ban:[/b]\n\r" . $mybb->input['reason_unban'] . "\n\r";

                        require_once MYBB_ROOT . "inc/datahandlers/post.php";
                        $posthandler = new PostDataHandler("insert");
                        $posthandler->action = "thread";

                        // Set the thread data
                        $new_thread = array(
                            "fid" => $mybb->settings['rpadmin_appealfid'],
                            "subject" => 'BAN APPEAL: ' . $mybb->user['username'] . ' appealing ban #' . $ban['banid'],
                            "icon" => -1,
                            "uid" => $mybb->user['uid'],
                            "username" => $mybb->user['username'],
                            "message" => $post_msg,
                            "ipaddress" => '127.0.0.1',
                            "posthash" => '',
                            "savedraft" => 0,
                            );

                        // Set up the thread options
                        $new_thread['options'] = array(
                            "signature" => 'no',
                            "emailnotify" => 'no',
                            "disablesmilies" => 'no');

                        $posthandler->set_data($new_thread);

                        // Now let the post handler do all the hard work.
                        if ($posthandler->validate_thread()) {

                            $update = array("appealed" => time());
                            edit_ban($ban['banid'], $update);

                            $thread_info = $posthandler->insert_thread();
                            redirect("showthread.php?tid=" . $thread_info['tid'] . "&pid=" . $thread_info['pid'] .
                                "#pid" . $thread_info['pid'] . "", "Appeal created!");
                        } else {
                            redirect("rpadmin.php",
                                "Creating the Appeal didn't go well. Try again later or contact Burnacid");
                        }
                    } else {
                        redirect("rpadmin.php",
                            "You didn't correctly fill in your ban appeal. Try again!");
                    }
                } else {
                    $ban['date'] = date("d M Y - H:i:s", $ban['time']);

                    if ($ban['removed']) {
                        $ban['status'] = "<strong>Status: </strong> <span style='color: blue;'>REMOVED</span>";
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

                    eval("\$bantable = \"" . $templates->get("rpadmin_ban") . "\";");
                    $rpadmin_page .= $bantable;

                    $rpadmin_page .= "<form action='' method='post'>";
                    $rpadmin_page .= "What happened leading up to this ban: <br /><textarea class='reason' name='reason_ban'></textarea>";
                    $rpadmin_page .= "Why should we un-ban you: <br /><textarea class='reason' name='reason_unban'></textarea>";
                    $rpadmin_page .= "<input type='hidden' name='submit' value='1' /><input type='hidden' name='ban' value='" .
                        $ban['banid'] . "' /><input name='appeal' value='Appeal Ban' type='submit' /></form>";
                }
            } else {
                redirect("rpadmin.php", "You can't appeal a ban that isn't yours!");
            }
        } else {
            redirect("rpadmin.php", "You are not supposed to be here!");
        }
    } else {
        redirect("rpadmin.php", "You are not supposed to be here!");
    }

    eval("\$page = \"" . $templates->get("rpadmin") . "\";");
    output_page($page);

} else {
    echo "Plugin is not installed";
}

?>