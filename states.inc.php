<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * akMyrmes implementation : © Andrew Kerrison <adesignforlife@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * akMyrmes game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 2 )
    ),
    
    // Note: ID=2 => your first state

    2 => array(
        "name" => "event",
        "action"=> "", //"stEvent",
        "type" => "game",
        "updateGameProgression" => true,
        "transitions" => array( "" => 4)
    ),
    
    4 => array(
        "name" => "prepBirths",
        "type" => "game",
        "action"=> "stBirths",
        "updateGameProgression" => true,
        "transitions" => array( ""  => 5)
    ),
    
    5 => array(
        "name" => "births",
        "type" => "multipleactiveplayer",        
        "args" => "argBirths",
        "updateGameProgression" => true,
        "description" => clienttranslate('Waiting for other players to choose their event and births'),
        "descriptionmyturn" => clienttranslate('You must choose your event and births'),
        "possibleactions" => array("allocateNurse", "finished"),
        "transitions" => array( ""  => 7)
    ),
        
    7 => array(
        "name" => "processBirths",
        "updateGameProgression" => true,
        "description" => clienttranslate('Processing Births...'),
        "type" => "game",
        "action" => "stProcessBirths",
        "transitions" => array( "" => 8)
    ),
    
    8 => array(
        "name" => "initWorkerPhase",
        "type" => "game",
        "action" => "stSetFirstPlayerActive",
        "updateGameProgression" => true,
        "transitions" => array( "" => 9)
    ),
    
    9 => array(
        "name" => "checkAvailableWorker",
        "type" => "game",
        "action" => "stNextAvailableWorker",
        "updateGameProgression" => true,
        "transitions" => array( "hasWorker" => 10, "allWorkersPlaced"=> 28)
    ),
    
    10 => array(
        "name" => "placeWorker",
        "type" => "activeplayer",
        "args" => "argPlaceWorker",
        "updateGameProgression" => true,
        "description" => clienttranslate('${actplayer} must place a worker'),
        "descriptionmyturn" => clienttranslate('You must place a worker on the board, in your colony, or pass'),
        "possibleactions" => array("pass", "selectHex", "activateColony"),
        "transitions" => array("workerPlaced"=> 12, "workerFinished" => 20, "pass" => 11, "zombiePass" => 11)
    ),
    
    11 => array(
        "name" => "workerPass",
        "type" => "game",
        "action" => "stWorkerPass",
        "updateGameProgression" => true,
        "transitions" => array( "" => 20)
    ),
    
    12 => array(
        "name" => "moveWorker",
        "type" => "activeplayer",
        "args" => "argMoveWorker",
        "updateGameProgression" => true,
        "description" => clienttranslate('${actplayer} is moving a worker'),
        "descriptionmyturn" => clienttranslate('You may move the worker. ${moves} moves remaining'),
        "possibleactions" => array("discardWorker", "selectHex", "placeTile", "clearPheromone"),
        "transitions" => array("workerMoved" => 12, "workerUsed" => 15, "chooseTile" => 13, "zombiePass"=> 15)        
    ),
    
    13 => array(
        "name" => "placeTile",
        "type" => "activeplayer",
        "args" => "argPlaceTile",
        "updateGameProgression" => true,
        "description" => clienttranslate('${actplayer} is placing a tile'),
        "descriptionmyturn" => clienttranslate('Select the tiles to cover, and confirm'),
        "possibleactions" => array("cancel", "selectHex", "confirmTile"),
        "transitions" => array("multiTileChoice" => 14, "tilePlaced" => 15, "cancel" => 12, "zombiePass"=>12)        
    ),
    
    14 => array(
        "name" => "tileChoice",
        "type" => "activeplayer",
        "args" => "argMultiTile",
        "updateGameProgression" => true,
        "description" => clienttranslate('${actplayer} is placing a tile'),
        "descriptionmyturn" => clienttranslate('Select the tile to place'),
        "possibleactions" => array("cancel", "selectTile"),
        "transitions" => array("tilePlaced" => 15, "cancel" => 12, "zombiePass"=>12)        
    ),
    
    15 => array(
        "name" => "workerUsed",
        "type" => "game",
        "action" => "stWorkerUsed",
        "updateGameProgression" => true,
        "transitions" => array( "" => 20)
    ),
    
    20 => array(
        "name" => "workerFinished",
        "type" => "game",
        "action" => "stWorkerFinished",
        "updateGameProgression" => true,
        "transitions" => array( "" => 9)
    ),
    
    28 => array(
        "name" => "prepHarvest",
        "type" => "game",
        "action"=> "stHarvest",
        "updateGameProgression" => true,
        "transitions" => array( ""  => 30)
    ),
    
    30 => array(
        "name" => "harvest",
        "type" => "multipleactiveplayer",
        "args" => "argHarvest",
        "updateGameProgression" => true,
        "description" => clienttranslate('Waiting for other players to choose their harvests'),
        "descriptionmyturn" => clienttranslate('You must choose your harvest. Select one cube from each tile, and up to three extra if you chose the harvest event'),
        "possibleactions" => array("selectHex", "chooseHarvest"),
        "transitions" => array( ""  => 31)
    ),
      
    //here or in state 30? depends if other player results should be hidden
    31 => array(
        "name" => "harvestEvaluate",
        "type" => "game",
        "action" => "stProcessHarvest",
        "updateGameProgression" => true,
        "transitions" => array( "" => 32)
    ),
    
    32 => array(
        "name" => "initAtelierPhase",
        "type" => "game",
        "action" => "stSetFirstPlayerActive",
        "updateGameProgression" => true,
        "transitions" => array( "" => 33)
    ),
    
    33 => array(
        "name" => "checkAvailableNurse",
        "type" => "game",
        "action" => "stNextAvailableNurse",
        "updateGameProgression" => true,
        "transitions" => array( "hasNurse" => 35, "allNursesPlaced"=> 40)
    ),
        
    35 => array(
        "name" => "atelier",
        "type" => "activeplayer",
        "args" => "argAtelier",
        "updateGameProgression" => true,
        "description" => clienttranslate('${actplayer} must choose an action in the atelier'),
        "descriptionmyturn" => clienttranslate('You must choose an action in the atelier'),
        "possibleactions" => array( "pass", "selectHex", "convertLarvae" ),
        "transitions" => array("zombiePass" => 36, "pass" => 36, "nurse" => 33, "level" => 33, "tunnel"=>37, "larvae" => 35)
    ),
    
    36 => array(
        "name" => "atelierPass",
        "type" => "game",
        "action" => "stAtelierPass",
        "updateGameProgression" => true,
        "transitions" => array( "" => 33)
    ),
    
    37 => array(
        "name" => "placeTunnel",
        "type" => "activeplayer",
        "args" => "argPlaceTunnel",
        "updateGameProgression" => true,
        "description" => clienttranslate('${actplayer} is placing a tunnel'),
        "descriptionmyturn" => clienttranslate('Select the location for the new tunnel'),
        "possibleactions" => array("cancel", "selectHex"),
        "transitions" => array("cancel" => 35, "tilePlaced"=>33, "zombiePass"=>35)        
    ),
    
    40 => array(
        "name" => "prepStorage",
        "type" => "game",
        "action"=> "stStorage",
        "updateGameProgression" => true,
        "transitions" => array( ""  => 42)
    ),    
    
    42 => array(
        "name" => "storage",
        "type" => "multipleactiveplayer",      
        "action"=> "stStorage2",
        "args"=> "argStorage",
        "updateGameProgression" => true,
        "description" => clienttranslate('Waiting for other players to discard excess resources'),
        "descriptionmyturn" => clienttranslate('You must discard excess resources'),
        "possibleactions" => array( "discard" ),
        "transitions" => array( ""  => 45)        
    ),
    
    45 => array(
        "name" => "cleanup",
        "type" => "game",
        "action" => "stCleanup",
        "updateGameProgression" => true,
        "transitions" => array("winter" => 50, "startNextSeason" => 2, "endGame" => 99)
    ),
    
    50 => array(
        "name" => "prepWinter",
        "type" => "game",
        "action" => "stPreWinter",
        "updateGameProgression" => true,
        "transitions" => array("" => 51)
    ),
    
    51 => array(
        "name" => "winter",
        "type" => "multipleactiveplayer",
        "description" => clienttranslate('Waiting for other players to feed their colony'),
        "descriptionmyturn" => clienttranslate('You may convert larvae into food to meet feeding requirements'),
        "args"=> "argWinter",
        "action" => "stWinterFoodCheck",
        "possibleactions" => array( "pass", "convertLarvae"),
        "updateGameProgression" => true,
        "transitions" => array("" => 52)
    ),
    
    52 => array(
        "name" => "processWinter",
        "type" => "game",
        "action" => "stWinter",
        "updateGameProgression" => true,
        "transitions" => array("" => 45)
    ),
      
    // Final state.
    // Please do not modify.
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



