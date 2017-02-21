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

$this->resourceNames = array(
    "larvae" => clienttranslate('Larvae'),
    "food" => clienttranslate('Food'),
    "dirt" => clienttranslate('Dirt'),
    "stone" => clienttranslate('Stone'),
    "vp" => clienttranslate('Victory Points'),
);

$this->playerTileTypes = array(
    array("type" => 1, "points" => 0, "count" =>4), //tunnels
    array("type" => 2, "points" => 0, "count" =>6), //2-space
    array("type" => 3, "points" => 2, "count" =>2), //3-space triangle
    array("type" => 4, "points" => 2, "count" =>2), //3-space line
    array("type" => 5, "points" => 4, "count" =>2), //4-space even
    array("type" => 6, "points" => 4, "count" =>2), //4-space irreg
    array("type" => 7, "points" => 6, "count" =>2), //5-space
    array("type" => 8, "points" => 8, "count" =>1), //6-space
);

$this->sharedTileTypes = array(
    array("type" => 9, "points" => 2, "count" =>6), //aphid/scavenging
    array("type" => 10, "points" => 4, "count" =>8) //subcolony
);

$this->hexInfo = array(array());
$this->hexInfo[9][0] = "mushroom";
$this->hexInfo[10][0] = "grass";

$this->startPositions2p = array(
    array("x" => 2, "y" => 11),
    array("x" => 8, "y" => 11)
    );

$this->startPositions3p = array(
    array("x" => 5, "y" => 12),
    array("x" => 12, "y" => 4),
    array("x" => 4, "y" => 5),
    );

$this->startPositions4p = array(
    array("x" => 2, "y" => 11),
    array("x" => 8, "y" => 11),
    array("x" => 6, "y" => 3),
    array("x" => 12, "y" => 3)
    );

$this->boardSpaces =array(array());

$this->boardSpaces[1][7] = "GRASS";
$this->boardSpaces[2][7] = "DIRT";
$this->boardSpaces[3][7] = "MUSHROOM";
$this->boardSpaces[4][7] = "GRASS";
$this->boardSpaces[5][7] = "MUSHROOM";
$this->boardSpaces[6][7] = "GRASS";
$this->boardSpaces[7][7] = "MUSHROOM";
$this->boardSpaces[8][7] = "GRASS";
$this->boardSpaces[9][7] = "MUSHROOM";
$this->boardSpaces[10][7] = "GRASS";
$this->boardSpaces[11][7] = "MUSHROOM";
$this->boardSpaces[12][7] = "DIRT";
$this->boardSpaces[13][7] = "GRASS";

$this->boardSpaces[1][8] = "DIRT";
$this->boardSpaces[2][8] = "GRASS";
$this->boardSpaces[3][8] = "GRASS";
$this->boardSpaces[4][8] = "WATER";
$this->boardSpaces[5][8] = "GRASS";
$this->boardSpaces[6][8] = "GRASS";
$this->boardSpaces[7][8] = "GRASS";
$this->boardSpaces[8][8] = "GRASS";
$this->boardSpaces[9][8] = "WATER";
$this->boardSpaces[10][8] = "GRASS";
$this->boardSpaces[11][8] = "GRASS";
$this->boardSpaces[12][8] = "DIRT";

$this->boardSpaces[0][9] = "MUSHROOM";
$this->boardSpaces[1][9] = "GRASS";
$this->boardSpaces[2][9] = "MUSHROOM";
$this->boardSpaces[3][9] = "STONE";
$this->boardSpaces[4][9] = "WATER";
$this->boardSpaces[5][9] = "MUSHROOM";
$this->boardSpaces[6][9] = "GRASS";
$this->boardSpaces[7][9] = "MUSHROOM";
$this->boardSpaces[8][9] = "WATER";
$this->boardSpaces[9][9] = "STONE";
$this->boardSpaces[10][9] = "MUSHROOM";
$this->boardSpaces[11][9] = "GRASS";
$this->boardSpaces[12][9] = "MUSHROOM";

$this->boardSpaces[0][10] = "DIRT";
$this->boardSpaces[1][10] = "WATER";
$this->boardSpaces[2][10] = "MUSHROOM";
$this->boardSpaces[3][10] = "DIRT";
$this->boardSpaces[4][10] = "GRASS";
$this->boardSpaces[5][10] = "WATER";
$this->boardSpaces[6][10] = "WATER";
$this->boardSpaces[7][10] = "GRASS";
$this->boardSpaces[8][10] = "DIRT";
$this->boardSpaces[9][10] = "MUSHROOM";
$this->boardSpaces[10][10] = "WATER";
$this->boardSpaces[11][10] = "DIRT";

$this->boardSpaces[0][11] = "STONE";
$this->boardSpaces[1][11] = "MUSHROOM";
$this->boardSpaces[2][11] = "GRASS";
$this->boardSpaces[3][11] = "STONE";
$this->boardSpaces[4][11] = "GRASS";
$this->boardSpaces[5][11] = "WATER";
$this->boardSpaces[6][11] = "GRASS";
$this->boardSpaces[7][11] = "STONE";
$this->boardSpaces[8][11] = "GRASS";
$this->boardSpaces[9][11] = "MUSHROOM";
$this->boardSpaces[10][11] = "STONE";

$this->boardSpaces[0][12] = "GRASS";
$this->boardSpaces[1][12] = "DIRT";
$this->boardSpaces[2][12] = "DIRT";
$this->boardSpaces[3][12] = "GRASS";
$this->boardSpaces[4][12] = "MUSHROOM";
$this->boardSpaces[5][12] = "MUSHROOM";
$this->boardSpaces[6][12] = "GRASS";
$this->boardSpaces[7][12] = "DIRT";
$this->boardSpaces[8][12] = "DIRT";
$this->boardSpaces[9][12] = "GRASS";

$this->boardSpaces[1][13] = "GRASS";
$this->boardSpaces[2][13] = "DIRT";
$this->boardSpaces[3][13] = "MUSHROOM";
$this->boardSpaces[4][13] = "STONE";
$this->boardSpaces[5][13] = "MUSHROOM";
$this->boardSpaces[6][13] = "DIRT";
$this->boardSpaces[7][13] = "GRASS";

$this->boardSpaces[2][14] = "STONE";
$this->boardSpaces[3][14] = "GRASS";
$this->boardSpaces[4][14] = "GRASS";
$this->boardSpaces[5][14] = "STONE";

/*$this->boardSpaces = array(
    array("x" => 9, "y" => 0, "type" => "MUSHROOM"),
    array("x" => 10, "y" => 0, "type" => "GRASS"),
    array("x" => 11, "y" => 0, "type" => "GRASS"),
    array("x" => 12, "y" => 0, "type" => "MUSHROOM"),
    
    array("x" => 7, "y" => 1, "type" => "GRASS"),
    array("x" => 8, "y" => 1, "type" => "DIRT"),
    array("x" => 9, "y" => 1, "type" => "GRASS"),
    array("x" => 10, "y" => 1, "type" => "STONE"),
    array("x" => 11, "y" => 1, "type" => "GRASS"),
    array("x" => 12, "y" => 1, "type" => "DIRT"),
    array("x" => 13, "y" => 1, "type" => "GRASS"),
    
    array("x" => 5, "y" => 2, "type" => "GRASS"),
    array("x" => 6, "y" => 2, "type" => "DIRT"),
    array("x" => 7, "y" => 2, "type" => "DIRT"),
    array("x" => 8, "y" => 2, "type" => "GRASS"),
    array("x" => 9, "y" => 2, "type" => "MUSHROOM"),
    array("x" => 10, "y" => 2, "type" => "MUSHROOM"),
    array("x" => 11, "y" => 2, "type" => "GRASS"),
    array("x" => 12, "y" => 2, "type" => "DIRT"),
    array("x" => 13, "y" => 2, "type" => "DIRT"),
    array("x" => 14, "y" => 2, "type" => "GRASS"),
    
    array("x" => 4, "y" => 3, "type" => "STONE"),
    array("x" => 5, "y" => 3, "type" => "MUSHROOM"),
    array("x" => 6, "y" => 3, "type" => "GRASS"),
    array("x" => 7, "y" => 3, "type" => "STONE"),
    array("x" => 8, "y" => 3, "type" => "GRASS"),
    array("x" => 9, "y" => 3, "type" => "WATER"),
    array("x" => 10, "y" => 3, "type" => "GRASS"),
    array("x" => 11, "y" => 3, "type" => "STONE"),
    array("x" => 12, "y" => 3, "type" => "GRASS"),
    array("x" => 13, "y" => 3, "type" => "MUSHROOM"),
    array("x" => 14, "y" => 3, "type" => "STONE"),

    array("x" => 3, "y" => 4, "type" => "GRASS"),
    array("x" => 4, "y" => 4, "type" => "WATER"),
    array("x" => 5, "y" => 4, "type" => "MUSHROOM"),
    array("x" => 6, "y" => 4, "type" => "DIRT"),
    array("x" => 7, "y" => 4, "type" => "GRASS"),
    array("x" => 8, "y" => 4, "type" => "WATER"),
    array("x" => 9, "y" => 4, "type" => "WATER"),
    array("x" => 10, "y" => 4, "type" => "GRASS"),
    array("x" => 11, "y" => 4, "type" => "DIRT"),
    array("x" => 12, "y" => 4, "type" => "MUSHROOM"),
    array("x" => 13, "y" => 4, "type" => "WATER"),
    array("x" => 14, "y" => 4, "type" => "GRASS"),    
    
    array("x" => 2, "y" => 5, "type" => "DIRT"),
    array("x" => 3, "y" => 5, "type" => "DIRT"),
    array("x" => 4, "y" => 5, "type" => "MUSHROOM"),
    array("x" => 5, "y" => 5, "type" => "STONE"),
    array("x" => 6, "y" => 5, "type" => "WATER"),
    array("x" => 7, "y" => 5, "type" => "MUSHROOM"),
    array("x" => 8, "y" => 5, "type" => "GRASS"),
    array("x" => 9, "y" => 5, "type" => "MUSHROOM"),
    array("x" => 10, "y" => 5, "type" => "WATER"),
    array("x" => 11, "y" => 5, "type" => "STONE"),
    array("x" => 12, "y" => 5, "type" => "MUSHROOM"),
    array("x" => 13, "y" => 5, "type" => "DIRT"),
    array("x" => 14, "y" => 5, "type" => "DIRT"),  
    
    array("x" => 2, "y" => 6, "type" => "STONE"),
    array("x" => 3, "y" => 6, "type" => "GRASS"),
    array("x" => 4, "y" => 6, "type" => "GRASS"),
    array("x" => 5, "y" => 6, "type" => "WATER"),
    array("x" => 6, "y" => 6, "type" => "GRASS"),
    array("x" => 7, "y" => 6, "type" => "GRASS"),
    array("x" => 8, "y" => 6, "type" => "GRASS"),
    array("x" => 9, "y" => 6, "type" => "GRASS"),
    array("x" => 10, "y" => 6, "type" => "WATER"),
    array("x" => 11, "y" => 6, "type" => "GRASS"),
    array("x" => 12, "y" => 6, "type" => "GRASS"),
    array("x" => 13, "y" => 6, "type" => "STONE"),

    array("x" => 1, "y" => 7, "type" => "STONE"),
    array("x" => 2, "y" => 7, "type" => "STONE"),
    array("x" => 3, "y" => 7, "type" => "GRASS"),
    array("x" => 4, "y" => 7, "type" => "GRASS"),
    array("x" => 5, "y" => 7, "type" => "WATER"),
    array("x" => 6, "y" => 7, "type" => "GRASS"),
    array("x" => 7, "y" => 7, "type" => "GRASS"),
    array("x" => 8, "y" => 7, "type" => "GRASS"),
    array("x" => 9, "y" => 7, "type" => "GRASS"),
    array("x" => 10, "y" => 7, "type" => "WATER"),
    array("x" => 11, "y" => 7, "type" => "GRASS"),
    array("x" => 12, "y" => 7, "type" => "GRASS"),
    array("x" => 13, "y" => 7, "type" => "STONE"),    
    
);*/
        




