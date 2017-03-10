<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * akMyrmes implementation : © Amdrew Kerrison <adesignforlife@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * akMyrmes game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/

class akUtils
{
    public static function test()
    {
        return "included";
    }    
}

$this->resourceNames2 = array(
    "larvae" => clienttranslate('Larvae'),
    "food" => clienttranslate('Food'),
    "dirt" => clienttranslate('Dirt'),
    "stone" => clienttranslate('Stone'),
    "vp" => clienttranslate('Victory Points'),
);

$this->playerTileTypes2 = array(
    array("type" => 1, "points" => 0, "count" =>4), //tunnels
    array("type" => 2, "points" => 0, "count" =>6), //2-space
    array("type" => 3, "points" => 2, "count" =>2), //3-space triangle
    array("type" => 4, "points" => 2, "count" =>2), //3-space line
    array("type" => 5, "points" => 4, "count" =>2), //4-space even
    array("type" => 6, "points" => 4, "count" =>2), //4-space irreg
    array("type" => 7, "points" => 6, "count" =>2), //5-space
    array("type" => 8, "points" => 8, "count" =>1), //6-space
);