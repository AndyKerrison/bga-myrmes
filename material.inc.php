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

$this->defaultMovePoints = 3; //set back to 3 for release
$this->initialColonyLevel = 0; //set back to 0 for release
$this->initialWorkerCount = 2; //set back to 2 for release
$this->initialNurseCount = 3; //set back to 3 for release
$this->initialLarvaeCount = 1; //set back to 1 for release
$this->initialFoodCount = 0; //set back to 0 for release
$this->initialDirtCount = 0; //set back to 0 for release

$this->maxNurseCount = 8;
$this->maxWorkerCount = 8; //set to 8 before release
$this->maxSpecialTileCount = 4;

$this->resourceNames = array(
    "larvae" => clienttranslate('Larvae'),
    "food" => clienttranslate('Food'),
    "dirt" => clienttranslate('Dirt'),
    "stone" => clienttranslate('Stone'),
    "vp" => clienttranslate('Victory Points'),
    "ladybug" => clienttranslate('Ladybug'),
    "termite" => clienttranslate('Termite'),
    "spider" => clienttranslate('Spider'),
);

$this->seasonNames = array(
    1 => clienttranslate('Spring'),
    2 => clienttranslate('Summer'),
    3 => clienttranslate('Fall'),
    4 => clienttranslate('Winter')
);

$this->events = array(
    0 => array("name" => clienttranslate('Level +1')),
    1 => array("name" => clienttranslate('VP +1')),
    2 => array("name" => clienttranslate('Larvae +2')),
    3 => array("name" => clienttranslate('Harvest +3')),
    4 => array("name" => clienttranslate('Move +3')),
    5 => array("name" => clienttranslate('Soldier +1')),
    6 => array("name" => clienttranslate('Hex +1')),
    7 => array("name" => clienttranslate('Worker +1')),
);

$this->eventLevel = 0;
$this->eventVP = 1;
$this->eventLarvae = 2; //working
$this->eventHarvest = 3;
$this->eventMove = 4;
$this->eventSoldier = 5; //working
$this->eventHex = 6;
$this->eventWorker = 7; //working

//2 = X.X [0 0, +1 0]
//
//3 = X.X [0 0, +1 0, 0 +1]
//     X 
//     
//4 = X.X.X
//
//     X
//5 = X.X
//     X
//     
//6 = X.X.X
//     X
//     
//     X.X
//7 = X.X.X
//     
//8 = X.X.X
//     X.X
//      X
$this->playerTileTypes = array(
    "1"=>array("type" => 1, "levelRequired"=>0, "points" => 0, "count" =>4, "size"=>1), //tunnels
    "2"=>array("type" => 2, "levelRequired"=>0, "points" => 0, "count" =>6, "size"=>2), //2-space
    "3"=>array("type" => 3, "levelRequired"=>1, "points" => 2, "count" =>2, "size"=>3), //3-space triangle
    "4"=>array("type" => 4, "levelRequired"=>1, "points" => 2, "count" =>2, "size"=>3), //3-space line
    "5"=>array("type" => 5, "levelRequired"=>2, "points" => 4, "count" =>2, "size"=>4), //4-space even
    "6"=>array("type" => 6, "levelRequired"=>2, "points" => 4, "count" =>2, "size"=>4), //4-space irreg
    "7"=>array("type" => 7, "levelRequired"=>3, "points" => 6, "count" =>2, "size"=>5), //5-space
    "8"=>array("type" => 8, "levelRequired"=>4, "points" => 8, "count" =>1, "size"=>6), //6-space
);

$this->sharedTileTypes = array(
    "9"=>array("type" => 9, "levelRequired"=>1, "points" => 2, "count" =>8, "size"=>3), //aphid/scavenging (8)
    "10"=>array("type" => 10, "levelRequired"=>3, "points" => 4, "count" =>8, "size"=>4) //subcolony (8)
);

$this->bugTileTypes = array(
    "11"=>array("type" => 11, "points" => 0, "count" =>6, "isBug"=> true, "resourceName"=>"ladybug"), //ladybug
    "12"=>array("type" => 12, "points" => 2, "count" =>6, "isBug"=> true, "resourceName"=>"termite"), //termite
    "13"=>array("type" => 13, "points" => 4, "count" =>6, "isBug"=> true, "resourceName"=>"spider")//spider
    );

$this->hexInfo = array(array());
$this->hexInfo[9][0] = "mushroom";
$this->hexInfo[10][0] = "grass";

$this->bugLocations = array(
    array("x" => 7, "y" => 1),
    array("x" => 13, "y" => 1),
    array("x" => 7, "y" => 4),
    array("x" => 10, "y" => 4),
    array("x" => 8, "y" => 5),
    array("x" => 6, "y" => 6),
    array("x" => 9, "y" => 6),
    array("x" => 1, "y" => 7),
    array("x" => 4, "y" => 7),
    array("x" => 10, "y" => 7),
    array("x" => 13, "y" => 7),
    array("x" => 5, "y" => 8),
    array("x" => 8, "y" => 8),
    array("x" => 6, "y" => 9),    
    array("x" => 4, "y" => 10),
    array("x" => 7, "y" => 10),
    array("x" => 1, "y" => 13),
    array("x" => 7, "y" => 13),
    );

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

$this->boardSpaces[9][0] = "MUSHROOM";
$this->boardSpaces[10][0] = "GRASS";
$this->boardSpaces[11][0] = "GRASS";
$this->boardSpaces[12][0] = "MUSHROOM";

$this->boardSpaces[7][1] = "GRASS";
$this->boardSpaces[8][1] = "DIRT";
$this->boardSpaces[9][1] = "GRASS";
$this->boardSpaces[10][1] = "STONE";
$this->boardSpaces[11][1] = "GRASS";
$this->boardSpaces[12][1] = "DIRT";
$this->boardSpaces[13][1] = "GRASS";

$this->boardSpaces[5][2] = "GRASS";
$this->boardSpaces[6][2] = "DIRT";
$this->boardSpaces[7][2] = "DIRT";
$this->boardSpaces[8][2] = "GRASS";
$this->boardSpaces[9][2] = "MUSHROOM";
$this->boardSpaces[10][2] = "MUSHROOM";
$this->boardSpaces[11][2] = "GRASS";
$this->boardSpaces[12][2] = "DIRT";
$this->boardSpaces[13][2] = "DIRT";
$this->boardSpaces[14][2] = "GRASS";

$this->boardSpaces[4][3] = "STONE";
$this->boardSpaces[5][3] = "MUSHROOM";
$this->boardSpaces[6][3] = "GRASS";
$this->boardSpaces[7][3] = "STONE";
$this->boardSpaces[8][3] = "GRASS";
$this->boardSpaces[9][3] = "WATER";
$this->boardSpaces[10][3] = "GRASS";
$this->boardSpaces[11][3] = "STONE";
$this->boardSpaces[12][3] = "GRASS";
$this->boardSpaces[13][3] = "MUSHROOM";
$this->boardSpaces[14][3] = "STONE";

$this->boardSpaces[3][4] = "GRASS";
$this->boardSpaces[4][4] = "WATER";
$this->boardSpaces[5][4] = "MUSHROOM";
$this->boardSpaces[6][4] = "DIRT";
$this->boardSpaces[7][4] = "GRASS";
$this->boardSpaces[8][4] = "WATER";
$this->boardSpaces[9][4] = "WATER";
$this->boardSpaces[10][4] = "GRASS";
$this->boardSpaces[11][4] = "DIRT";
$this->boardSpaces[12][4] = "MUSHROOM";
$this->boardSpaces[13][4] = "WATER";
$this->boardSpaces[14][4] = "GRASS";

$this->boardSpaces[2][5] = "DIRT";
$this->boardSpaces[3][5] = "DIRT";
$this->boardSpaces[4][5] = "MUSHROOM";
$this->boardSpaces[5][5] = "STONE";
$this->boardSpaces[6][5] = "WATER";
$this->boardSpaces[7][5] = "MUSHROOM";
$this->boardSpaces[8][5] = "GRASS";
$this->boardSpaces[9][5] = "MUSHROOM";
$this->boardSpaces[10][5] = "WATER";
$this->boardSpaces[11][5] = "STONE";
$this->boardSpaces[12][5] = "MUSHROOM";
$this->boardSpaces[13][5] = "DIRT";
$this->boardSpaces[14][5] = "DIRT";

$this->boardSpaces[2][6] = "STONE";
$this->boardSpaces[3][6] = "GRASS";
$this->boardSpaces[4][6] = "GRASS";
$this->boardSpaces[5][6] = "WATER";
$this->boardSpaces[6][6] = "GRASS";
$this->boardSpaces[7][6] = "GRASS";
$this->boardSpaces[8][6] = "GRASS";
$this->boardSpaces[9][6] = "GRASS";
$this->boardSpaces[10][6] = "WATER";
$this->boardSpaces[11][6] = "GRASS";
$this->boardSpaces[12][6] = "GRASS";
$this->boardSpaces[13][6] = "STONE";

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
        




