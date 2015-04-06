<?php

function steamIDtoSteam64($steamId)
{
    $gameType = 0;
    $authServer = 0;
    $steamId = str_replace('STEAM_', '', $steamId);
    $parts = explode(':', $steamId);
    $gameType = $parts[0];
    $authServer = $parts[1];
    $clientId = $parts[2];
    $res = bcadd((bcadd('76561197960265728', $authServer)), (bcmul($clientId, '2')));
    $cid = str_replace('.0000000000', '', $res);
    
    return $cid;
}


?>