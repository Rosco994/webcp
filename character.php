<?php

$pagetitle = 'Character';

$NEEDPUB = array('EIF' => true, 'ECF' => true, 'ESF' => true);
require 'common.php';

if (!$logged) {
    $tpl->message = 'You must be logged in to view this page.';
    $tpl->Execute(null);
    exit;
}

if (empty($_GET['name'])) {
    $tpl->message = 'No character name specified.';
    $tpl->Execute(null);
    exit;
}

if ($GM) {
    $character = webcp_db_fetchall("SELECT * FROM characters WHERE name = ? LIMIT 1", strtolower($_GET['name']));
} else {
    $character = webcp_db_fetchall("SELECT * FROM characters WHERE name = ? AND account = ? LIMIT 1", strtolower($_GET['name']), $sess->username);
}

if (empty($character)) {
    $tpl->message = 'Character does not exist' . ($GM ? '.' : ' or is not yours.');
    $tpl->Execute(null);
    exit;
}

$character = $character[0];

// Calculate rank for the character (using reborn count, level, and exp for better ranking)
$rank = webcp_db_fetchall("
    SELECT RANK() OVER (ORDER BY (level + (reborn * 250) + (exp / 2104743708)) DESC) as rank
    FROM characters
    WHERE name = ?", strtolower($_GET['name'])
);

$character['rank'] = !empty($rank) ? $rank[0]['rank'] : 'Unranked';

// Adjust level based on reborns and max level of 250
$max_level = 250;
$level_with_reborns = $character['level'] + ($character['reborn'] * $max_level);
$character['level'] = $level_with_reborns;  // Update level with reborn calculation

// Adjust experience: Max exp is 2,104,743,708 for max level
$max_exp = 2104743708;
$exp_with_reborns = $character['exp'] + ($character['reborn'] * $max_exp);
$character['exp'] = number_format($exp_with_reborns);  // Update exp with reborn calculation

// Character fields (pre-existing)
$character['name'] = ucfirst($character['name']);
$character['gender'] = $character['gender'] ? 'Male' : 'Female';
$character['title'] = empty($character['title']) ? '-' : ucfirst($character['title']);
$character['home'] = empty($character['home']) ? '-' : ucfirst($character['home']);
$character['usage_str'] = floor($character['usage'] / 60) . ' hour(s)';
$character['karma_str'] = karma_str($character['karma']);
$character['inventory'] = unserialize_inventory($character['inventory']);
$character['bank'] = unserialize_inventory($character['bank']);
$character['paperdoll'] = unserialize_paperdoll($character['paperdoll']);
$character['spells'] = unserialize_spells($character['spells']);
if (!empty($character['guild'])) {
    $guildinfo = webcp_db_fetchall("SELECT * FROM guilds WHERE tag = ?", $character['guild']);
    if (!empty($guildinfo[0])) {
        $character['guild_name'] = ucfirst($guildinfo[0]['name']);
        $character['guild_rank_str'] = guildrank_str(unserialize_guildranks($guildinfo[0]['ranks']), $character['guild_rank']);
    }
}
$character['class_str'] = class_str($character['class']);
$character['haircolor_str'] = haircolor_str($character['haircolor']);
$character['race_str'] = race_str($character['race']);
$character['partner'] = empty($character['partner']) ? '-' : ucfirst($character['partner']);
$character['admin_str'] = adminrank_str($character['admin']);

$pagetitle .= ': ' . htmlentities($character['name']);
$tpl->pagetitle = $pagetitle;

$tpl->character = $character;

$tpl->Execute('character');
