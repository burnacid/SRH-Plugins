<?php
function steamlogin_info()
{
	return array(
		"name"			=> "Steam OpenID Authentication",
		"description"	=> "Requires users to sign in through Steam on registration",
		"website"		=> "http://srh.im",
		"author"		=> "Drakehawke",
		"authorsite"	=> "http://www.srh.im",
		"version"		=> "1.0",
		"guid"			=> "",
		"compatibility" => ""
	);
}

function steamlogin_activate()
{
}

function steamlogin_deactivate()
{
}

$plugins->add_hook("member_do_register_end", "steamlogin_redirect");
function steamlogin_redirect()
{
	global $mybb;
	$return_url = '/misc.php?action=steam_return';
	require_once MYBB_ROOT.'inc/openid.php';
	$openid = new LightOpenID();
	$openid->returnUrl = $mybb->settings['bburl'].$return_url;
	$openid->__set('realm', $mybb->settings['bburl'].$return_url);
	$openid->identity = 'http://steamcommunity.com/openid';
	redirect($openid->authUrl(), 'You are being redirected to Steam to authenticate your account for use on our website.', 'Login via Steam');
}

$plugins->add_hook("misc_start", "steamlogin_receive_misc");
function steamlogin_receive_misc()
{
	global $mybb, $db;
	if($mybb->input['action'] == 'steam_return')
	{
		require_once MYBB_ROOT.'inc/openid.php';
		$openid = new LightOpenID();
		$openid->validate();
		$uid = $mybb->user["uid"];
		if( $uid == 0 )
		{
			error( "User not logged in!" );
		}
		else
		{
			$id = $openid->identity;
			$ptn = "/^http:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/";
			preg_match($ptn, $id, $matches);
			$steamid64 = $matches[1];
			$authserver = bcsub( $steamid64, '76561197960265728' ) & 1;
			$authid = (bcsub( $steamid64, '76561197960265728' ) - $authserver ) / 2;
			$steamid = "STEAM_0:$authserver:$authid";
			$db->update_query('users', array( 'steamid' => $steamid ), 'uid = '.$uid );
			redirect($mybb->settings['bburl'], "Steam authentication successful.", "Success");
		}
	}
}

$plugins->add_hook("misc_start", "steamlogin_redirect_misc");
function steamlogin_redirect_misc()
{
	global $mybb;
	if($mybb->input['action'] == 'steam_login')
	{
		steamlogin_redirect();
	}
}

$plugins->add_hook("datahandler_post_validate_post", "steamlogin_block_posting");
function steamlogin_block_posting($datahandler)
{
	global $mybb;
	if( !$mybb->user["steamid"] or $mybb->user["steamid"] == "" )
	{
		error( "Posting is not allowed until you validate your SteamID." );
		$datahandler->set_error("no_steamid");
	}
}

$plugins->add_hook("global_intermediate", "steamlogin_display_error");
function steamlogin_display_error()
{
	global $mybb, $templates;

	if( $mybb->user["uid"] != 0 and ( !$mybb->user["steamid"] or $mybb->user["steamid"] == "" ) )
	{
		eval('$nosteam = "'.$templates->get('srh_nosteamerror').'";');
	}
	else
	{
		$nosteam = "";
	}

	$GLOBALS['nosteam'] = $nosteam;
}

?>