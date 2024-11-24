<?php

$pagetitle = 'Top Players';

require 'common.php';

// Set the limit for the top players to show
$tpl->limit = $topplayers;

// Query to select the top players, sorted by reborns first, then by level (in case of a tie in reborns)
$characters = webcp_db_fetchall("SELECT name, title, level, exp, gender, reborn FROM characters WHERE admin = 0 ORDER BY reborn DESC, level DESC LIMIT ?", $topplayers);

foreach ($characters as &$character)
{
    // Ensure numeric values are correctly handled
    $character['name'] = ucfirst($character['name']);
    $character['gender'] = $character['gender'] ? 'Male' : 'Female';
    $character['title'] = empty($character['title']) ? '-' : ucfirst($character['title']);
    
    // Convert experience to integer for proper formatting and calculation
    $character['exp'] = intval($character['exp']);
    
    // Ensure 'reborn' is treated as an integer
    $character['reborn'] = intval($character['reborn']);
    
    // Max experience required to reach level 250
    $max_exp = 2104743708;
    
    // Adjust the player's level based on the number of reborns
    $character['level'] = intval($character['level']) + ($character['reborn'] * 250); // Add 250 for each reborn
    
    // Calculate the total experience based on reborns
    $total_exp = $character['exp'] + ($character['reborn'] * $max_exp); // Add the experience for each reborn
    
    // Format the adjusted experience (this will show the total experience across reborns)
    $character['exp'] = number_format($total_exp);
}

unset($character);

// Pass the modified characters to the template
$tpl->characters = $characters;

$tpl->Execute('players');
