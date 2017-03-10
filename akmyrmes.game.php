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
  * akmyrmes.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

require 'test.inc.php';
require 'akpathfinding.php';

class akMyrmes extends Table
{
	function akMyrmes( )
	{
        	
 
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();self::initGameStateLabels( array( 
            "state" => 1, //do not ever write to this!
            "current_year"=> 10,
            "current_season"=> 11,
            "spring_event"=> 12,
            "summer_event"=> 13,
            "fall_event"=> 14,
            "first_player"=>15,
            "active_worker_x"=>16,
            "active_worker_y"=>17,
            "active_worker_flag"=>18,
            "active_worker_moves"=>19
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );
        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "akmyrmes";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        //Myrmes - red, yellow, blue, black
        $default_colors = array( "ff0000", "ffff00", "0000ff", "000000" );
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $nurseCountInitial = 3;
        $workerCountInitial = 2;
        $larvaeCountInitial = 1;
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_nurses, player_workers, player_larvae) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            //each player starts with 3 nurses, 2 workers, 1 larvae
            //also soldiers 0, colony level 0, food 0, dirt 0, stone 0
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."', '".$nurseCountInitial."', '".$workerCountInitial."', '".$larvaeCountInitial."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, array(  "ff0000", "ffff00", "0000ff", "000000" ) );
        self::reloadPlayersBasicInfos();
        $playerData = self::getCollectionFromDb("select player_id, player_color from player");
        
        //tiles
        //first, the ones each player has
        foreach( $playerData as $player_id => $player )
        {
            $colorName = "";
            if ($player['player_color'] == "ff0000")
            {
                $colorName = "red";
            }
            if ($player['player_color'] == "ffff00")
            {
                $colorName = "yellow";
            }
            if ($player['player_color'] == "0000ff")
            {
                $colorName = "blue";
            }
            if ($player['player_color'] == "000000")
            {
                $colorName = "black";
            }
            
            foreach( $this->playerTileTypes as $type_id => $tile )
            {
                for ($i =0; $i< $tile["count"]; $i++)
                {
                    $sql = "INSERT INTO tiles (player_id, type_id, location, color)";
                    $sql = $sql . " VALUES ('".$player_id."', '".$tile["type"]."', 'storage', '".$colorName."')";
                    self::DbQuery( $sql );
                }
            }
        }
        
        //then the shared ones
        foreach( $this->sharedTileTypes as $type_id => $tile )
        {
            for ($i =0; $i< $tile["count"]; $i++)
            {
                $sql = "INSERT INTO tiles (player_id, type_id, location, color)";
                $sql = $sql . " VALUES (0, ".$tile["type"].", 'storage', '')";
                self::DbQuery( $sql );
            }
        }
        
        //each player gets a tunnel at a random available starting location
        //$startPositions = array();
        
        if (count($players) == 2)
        {
            $startPositions = $this->startPositions2p;
        }
        if (count($players) == 3)
        {
            $startPositions = $this->startPositions3p;
        }
        if (count($players) == 4)
        {
            $startPositions = $this->startPositions4p;
        }
        
        shuffle($startPositions);
            
        foreach($players as $player_id => $player )
        {
            $startPosition = array_shift($startPositions);
                
            $sql = "select tile_id from tiles where type_id = 1 and player_id = '".$player_id."' limit 1";
            $tunnel = self::getObjectFromDb( $sql );

            $tile_id = $tunnel["tile_id"];
                
            $sql = "update tiles set x1 = ".$startPosition["x"].", y1 = ".$startPosition["y"].", location = 'board' where tile_id = ".$tile_id.";";
            self::DbQuery( $sql );                
        }            
                
        /************ Start the game initialization *****/
        //---------------
        //--YEAR/SEASON--
        //---------------
        $this->setGameStateValue('current_year', 1);
        $this->setGameStateValue('current_season', 1);       
        $this->setGameStateValue('active_worker_flag', 0);
        
        //----------
        //--EVENTS--
        //----------
        $this->rollSeasons();
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();
        $active_player_id = self::getActivePlayerId();
        $this->setGameStateValue('first_player', $active_player_id);
        
        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array( 'players' => array() );
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_color color, player_colony_level colony, player_score score, player_nurses nurses, player_larvae larvae, player_soldiers soldiers, player_workers workers, player_food food, player_stone stone, player_dirt dirt FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
 
        //Gather all information about current game situation (visible by player $current_player_id).
        $result['current_year'] = $this->getGameStateValue("current_year");
        $result['current_season'] = $this->getGameStateValue("current_season");
        
        //the hex info needs to be in a form more suitable for js
        $hexInfo = array();
        
        $sql = "SELECT tile_id, color, x1, y1 from tiles where location='board' and type_id='1'";
        $result['tunnels'] = self::getObjectListFromDB( $sql );
        
        $sql = "SELECT tile_id, type_id, color, rotation, x1, y1 from tiles where location='board' and type_id > '1'";
        $result['pheromones'] = self::getObjectListFromDB( $sql );
        
        if ($this->getGameStateValue("active_worker_flag")== 1)
        {
            $result['activeWorker'] = array(
                "x"=>$this->getGameStateValue("active_worker_x"),
                "y"=>$this->getGameStateValue("active_worker_y"),
                );
        }
            
            
        //TODO - return which birthing choices this player has made (if appropriate?)
                
        //$hexInfo['key'] = 'value';
        
        //$result['hex_info'] = $this->hexInfo;
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */
    
    private function incPlayerVariable($varName, $playerId, $increment)
    {
        $value = $this->getPlayerVariable($varName, $playerId);
        $value += $increment;
        $this->setPlayerVariable($varName, $playerId, $value);
    }
    
    private function getPlayerVariable($varName, $playerId)
    {
        $sql = "SELECT 0, ".$varName." FROM player where player_id = '".$playerId."'";
        $player = self::getCollectionFromDb( $sql );
        return $player[0][$varName];
    }

    private function setPlayerVariable($varName, $playerId, $newValue)
    {
        $sql = "update player set ".$varName." = '".$newValue."' where player_id = '".$playerId."'";
        self::DbQuery( $sql );
    }
    
    private function playerSubtractScore($playerId, $loss)
    {
        $sql = "update player set player_score = player_score - ".$loss." where player_id = '".$playerId."'";
        self::DbQuery( $sql );
    }
    
    private function rollSeasons(){
        $this->setGameStateValue('spring_event', rand(1, 6));
        $this->setGameStateValue('summer_event', rand(1, 6));
        $this->setGameStateValue('fall_event', rand(1, 6));        
    }
    
    private function getCurrentEvent(){
        $currentEvent = 0;
        $season = $this->getGameStateValue('current_season');
        
        if ($season == 1)
        {
            $currentEvent = $this->getGameStateValue('spring_event');
        }
        else if ($season == 2)
        {
            $currentEvent = $this->getGameStateValue('summer_event');
        }
        else if ($season == 3)
        {
            $currentEvent = $this->getGameStateValue('fall_event');
        }
        else
        {
            throw new feException("Invalid season " . $season);            
        }
        return $currentEvent;
    }
    
    private function getAvailableNurses($playerID)
    {
        $availableNurses = $this->getPlayerVariable('player_atelier_1_allocated', $playerID);
        $availableNurses += $this->getPlayerVariable('player_atelier_2_allocated', $playerID);
        $availableNurses += $this->getPlayerVariable('player_atelier_3_allocated', $playerID);
        $availableNurses += $this->getPlayerVariable('player_atelier_4_allocated', $playerID);        
        
        return $availableNurses;
    }
    
    private function getAvailableWorkers($playerID)
    {
        $availableWorkers = $this->getPlayerVariable('player_workers', $playerID);
        $passedWorkers = $this->getPlayerVariable('player_workers_passed', $playerID);
        
        $availableWorkers -= $passedWorkers;
        
        $colonyUsed = $this->getPlayerVariable('player_colony_used', $playerID);//1,2,4,8
        
        if ($colonyUsed >= 8)
        {
            $colonyUsed -=8;
            $availableWorkers--;
        }
        if ($colonyUsed >= 4)
        {
            $colonyUsed -=4;
            $availableWorkers--;
        }
        if ($colonyUsed >= 2)
        {
            $colonyUsed -=2;
            $availableWorkers--;
        }
        if ($colonyUsed >= 1)
        {
            $colonyUsed -=1;
            $availableWorkers--;
        }
        return $availableWorkers;
    }
    
    private function setFirstPlayerActive(){
        $firstPlayer = $this->getGameStateValue('first_player');
        
        $this->activeNextPlayer();
        $activePlayerID = self::getActivePlayerId();
        
               
        while ($firstPlayer != $activePlayerID)
        {
            $this->activeNextPlayer();
            $activePlayerID = self::getActivePlayerId();
        }
    }
    
    private function getPlayerAllocations($playerID) {
        
        $sql = "SELECT * FROM player where player_id = '".$playerID."'";
        $player = self::getObjectFromDB( $sql );
        
        $private = array();
        
        $private['allocated'] = array();
        $private['can_allocate'] = array();
        $private['can_deallocate'] = array();
        
        $currentEvent = $this->getCurrentEvent();
        $selectedEvent = $player['player_event_selected'];
        $larvae = $player['player_larvae'];
        array_push($private['allocated'], 'event_'.$selectedEvent);
        for ($i =0; $i<=7; $i++)
        {
            if ($i == $selectedEvent)
            {
                continue;
            }
            if (($currentEvent + $larvae) >= $i && ($currentEvent - $larvae) <= $i)
            {
                array_push($private['can_allocate'], 'event_'.$i);
            }
        }
                
                    
        $totalNurses = $player['player_nurses'];
        $availableNurses = $totalNurses;
        $larvaeAllocated = $player['player_larvae_slots_allocated'];
        $soldiersAllocated = $player['player_soldier_slots_allocated'];
        $workersAllocated = $player['player_worker_slots_allocated'];
        $atelier1Allocated = $player['player_atelier_1_allocated'];
        $atelier2Allocated = $player['player_atelier_2_allocated'];
        $atelier3Allocated = $player['player_atelier_3_allocated'];
        $atelier4Allocated = $player['player_atelier_4_allocated'];
            
        $availableNurses -= $larvaeAllocated;                    
        $availableNurses -= $soldiersAllocated;                    
        $availableNurses -= $workersAllocated;                    
        $availableNurses -= $atelier1Allocated;                    
        $availableNurses -= $atelier2Allocated;                    
        $availableNurses -= $atelier3Allocated;                    
        $availableNurses -= $atelier4Allocated;                    
        
        $private['available_nurses'] = $availableNurses;                    
        
        if ($larvaeAllocated == 0 && $availableNurses >= 1)
        {
            array_push($private['can_allocate'], 'larvae_1');                
        }
        if ($larvaeAllocated == 1)
        {
            array_push($private['allocated'], 'larvae_1');                
            array_push($private['can_deallocate'], 'larvae_1');
            if ($availableNurses >= 1)
            {
                array_push($private['can_allocate'], 'larvae_2');
            }
        }
        if ($larvaeAllocated == 2)
        {
            array_push($private['allocated'], 'larvae_1');
            array_push($private['allocated'], 'larvae_2');                                
            array_push($private['can_deallocate'], 'larvae_2');
            if ($availableNurses >= 1)
            {
                array_push($private['can_allocate'], 'larvae_3');
            }
        }
        if ($larvaeAllocated == 3)
        {
            array_push($private['allocated'], 'larvae_1');                
            array_push($private['allocated'], 'larvae_2');                
            array_push($private['allocated'], 'larvae_3');                
            array_push($private['can_deallocate'], 'larvae_3');
        }
        
        if($soldiersAllocated == 0 && $availableNurses >= 2)
        {
            array_push($private['can_allocate'], 'soldier_1');
        }
        if($soldiersAllocated == 2)
        {
            array_push($private['allocated'], 'soldier_1');                
            array_push($private['can_deallocate'], 'soldier_1');
            if ($availableNurses >= 1)
            {
                array_push($private['can_allocate'], 'soldier_2');
            }
        }
        if($soldiersAllocated == 3)
        {
            array_push($private['allocated'], 'soldier_1');                
            array_push($private['allocated'], 'soldier_2');                
            array_push($private['can_deallocate'], 'soldier_2');
        }
        
        if($workersAllocated == 0 && $availableNurses >= 2)
        {
            array_push($private['can_allocate'], 'worker_1');
        }
        if($workersAllocated == 2)
        {
            array_push($private['allocated'], 'worker_1');                
            array_push($private['can_deallocate'], 'worker_1');
            if ($availableNurses >= 2)
            {
                array_push($private['can_allocate'], 'worker_2');
            }
        }
        if($workersAllocated == 4)
        {
            array_push($private['allocated'], 'worker_1');                
            array_push($private['allocated'], 'worker_2');                
            array_push($private['can_deallocate'], 'worker_2');
        }
        
        if ($atelier1Allocated == 0 && $availableNurses >= 1)
        {
            array_push($private['can_allocate'], 'atelier_1');
        }
        if ($atelier1Allocated == 1)
        {
            array_push($private['allocated'], 'atelier_1');
            array_push($private['can_deallocate'], 'atelier_1');
        }
        
        if ($atelier2Allocated == 0 && $availableNurses >= 1)
        {
            array_push($private['can_allocate'], 'atelier_2');
        }
        if ($atelier2Allocated == 1)
        {
            array_push($private['allocated'], 'atelier_2');
            array_push($private['can_deallocate'], 'atelier_2');
        }
        
        if ($atelier3Allocated == 0 && $availableNurses >= 1)
        {
            array_push($private['can_allocate'], 'atelier_3');
        }
        if ($atelier3Allocated == 1)
        {
            array_push($private['allocated'], 'atelier_3');
            array_push($private['can_deallocate'], 'atelier_3');
        }
        
        if ($atelier4Allocated == 0 && $availableNurses >= 1)
        {
            array_push($private['can_allocate'], 'atelier_4');
        }
        if ($atelier4Allocated == 1)
        {
            array_push($private['allocated'], 'atelier_4');
            array_push($private['can_deallocate'], 'atelier_4');
        }
        
        return $private;
    }
    



//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in akmyrmes.action.php)
    */
    
    function saveTileToBoard($splitHexes, $tileType, $rotation, $isFlipped)
    {
        //first, get the ID of the tile we are adding to the board
        $sql = "select tile_id from tiles where type_id='".$tileType."' and location='storage' and player_id='".self::getActivePlayerId()."' or player_id='0' limit 1";
        $tile = self::getObjectFromDb( $sql );
        $tileID = $tile["tile_id"];
                
        $x1 = $y1 = $x2 = $y2 = $x3 = $y3 = $x4 = $y4 = $x5 = $y5 = 0;
        $xy1 = explode("_", $splitHexes[0]);
        $x1 = $xy1[0];
        $y1 = $xy1[1];
                    
        if (count($splitHexes) > 1)
        {
            $xy2 = explode("_", $splitHexes[1]);
            $x2 = $xy2[0];
            $y2 = $xy2[1];
        }
                    
        if (count($splitHexes) > 2)
        {
            $xy3 = explode("_", $splitHexes[2]);
            $x3 = $xy3[0];
            $y3 = $xy3[1];
        }
                    
        if (count($splitHexes) > 3)
        {
            $xy4 = explode("_", $splitHexes[3]);
            $x4 = $xy4[0];
            $y4 = $xy4[1];
        }
                    
        if (count($splitHexes) > 4)
        {
            $xy5 = explode("_", $splitHexes[4]);
            $x5 = $xy5[0];
            $y5 = $xy5[1];
        }
        
        //5, 0, 1 work as is. 2,3,4 will not since they will use a different origin
        //in this case, subtract 3.
        $newRot = $rotation;
        if ($rotation = 2 || $rotation == 3 || $rotation == 4)
        {
            $newRot = $newRot - 3;
        }
                            
        $sql = "update tiles set location='board', rotation='".$newRot."', player_id = '".self::getActivePlayerId()."', x1='".$x1."', y1='".$y1."' , x2='".$x2."', y2='".$y2."' , x3='".$x3."', y3='".$y3."' , x4='".$x4."', y4='".$y4."' , x5='".$x5."', y5='".$y5."' where tile_id = '".$tileID."'";
        self::DbQuery( $sql );
    }
    
    function onStartPlaceTile()
    {
        //user clicked the button to place a tile
        $this->gamestate->nextstate("chooseTile");
    }
    
    function onCancelTile()
    {
        //user clicked the button to place a tile
        $this->gamestate->nextstate("cancel");
    }
    
    //X_YxX_YxX_Y etc - can't use comma to separate as invalid arg type
    function onConfirmTile($hexes)
    {
        //var_dump($hexes);
        //die('ok');
        
        //remap hexes into vector array based on starting point.
        //get all valid tile placements and check each type against it
        //checking number of tiles first!
        $x = $this->getGameStateValue("active_worker_x");
        $y = $this->getGameStateValue("active_worker_y");
        
        $splitHexes = explode("x", $hexes);
        $vectors = array();
        
        //strings can be more easily sorted, so we can compare vectors
        foreach($splitHexes as $splitHex)
        {
            $parts = explode("_", $splitHex);
            $vectors[] = ($parts[0] - $x)."_".($parts[1] - $y);
            
            //also check for conflicts while we're here            
            $sql = "select type_id from tiles where location='board' and ".
                                " (x1='".$parts[0]."' and y1='".$parts[1]."') OR ".
                                " (x2='".$parts[0]."' and y2='".$parts[1]."') OR ".
                                " (x3='".$parts[0]."' and y3='".$parts[1]."') OR ".
                                " (x4='".$parts[0]."' and y4='".$parts[1]."') OR ".
                                " (x5='".$parts[0]."' and y5='".$parts[1]."')";
            
            $clashes = self::getCollectionFromDb($sql);
            if (count($clashes) > 0)
            {
                $errMsg = clienttranslate('Spaces are not empty');
                self::notifyPlayer( self::getCurrentPlayerId(), "tilePlacementInvalid", "", 
                    array("errMsg"=>$errMsg)
                );
                return;
            }
        }
        
        sort($vectors);
        
               
        //VALIDATION - check if the player has the required level for this size
        $colonyLevel = $this->getPlayerVariable("player_colony_level", self::getActivePlayerId());

                
        //get the actual valid vectors, and somehow match them.
        //this is an array of arrays. Each array is a list of hexes that might match
        //those supplied. Find out!
        $tilePlacements = $this->getTilePlacements(false);
        $errMsg = "";
                
        //strings can be more easily sorted, so we can compare vectors
        foreach($tilePlacements as $tilePlacement)
        {
            $targetVectors = array();
            foreach($tilePlacement->Tiles as $hex)
            {
                $targetVectors[] = $hex["x"]."_".$hex["y"];
            }
            sort($targetVectors);
            //var_dump("checking");
            //var_dump($targetVectors);
            
            if (count($vectors) == count($targetVectors))
            {
                if (count(array_diff($vectors, $targetVectors)) == 0 && count(array_diff($targetVectors, $vectors)) == 0 )
                {
                    //var_dump("MATCH!!");
                    //var_dump("type: ".$tilePlacement->Type);
                    //var_dump("rotation: ".$tilePlacement->Rotation);
                    //var_dump("isFlipped: ".$tilePlacement->IsFlipped);
                    //var_dump("vectors: ");
                    //var_dump($vectors);
                    
                    $requiredLevel = $this->playerTileTypes[$tilePlacement->Type]["levelRequired"];
                    
                    //validate colony level
                    if ($requiredLevel > $colonyLevel)
                    {
                        $errMsg = clienttranslate('Colony level too low');
                        continue;
                    }
                    
                    //validate any tiles remaining, message if ran out of matches
                    $sql = "select distinct type_id from tiles where type_id='".$tilePlacement->Type."' and location = 'storage' and player_id = '".self::getActivePlayerId()."'";
                    $pheromoneTypes = self::getCollectionFromDb($sql);
                    if (count($pheromoneTypes) == 0)
                    {
                        $errMsg = clienttranslate('No matching tiles remaining');
                        continue;
                    }
                                        
                    //so, we got a match. We need to know what type of tile this was
                    //and figure out how we will draw it on the board                   
                    
                    //todo - save to db and process to next state
                    //var_dump("type: ".$tilePlacement->Type);
                    //var_dump("rotation: ".$tilePlacement->Rotation);
                    //var_dump("isFlipped: ".$tilePlacement->IsFlipped);
                    $this->saveTileToBoard($splitHexes, $tilePlacement->Type, $tilePlacement->Rotation, $tilePlacement->IsFlipped);                   
                    $this->gamestate->nextstate("tilePlaced");
                    return;
                }
                else
                {
                    //var_dump("no match");
                }
            }   
            else
            {
                //wrong size, ignore
            }
        }
        
        if ($errMsg == "")
        {
            $errMsg = clienttranslate( 'Tile placement invalid');
        }
        
        //user clicked the button to place a tile
        //if their chosen tile configuration was valid, build the tile (or cofirm type)
        //otherwise return an error message
        //
        //$this->gamestate->nextstate("confirmTile");
        self::notifyPlayer( self::getCurrentPlayerId(), "tilePlacementInvalid", "", 
                array("errMsg"=>$errMsg)
        );
    }
    
    function onHexClicked($x, $y)
    {
        //check valid
        $hex = "hex_".$x."_".$y;
        
        $currentState = $this->getGameStateValue("state");
        
        if ($currentState != "10" && $currentState != "12")
        {
            throw new feException("Unexpected hex click state ".$currentState);
        }
        
        //place worker state. Must have chosen a tunnel
        if ($currentState == "10")
        {
            $args = $this->argPlaceWorker();
            if (!in_array($hex, $args["tunnels"]))
            {
                throw new feException("Invalid tunnel choice ".$hex);
            }
            
            //okay! mark this hex has having an active worker of this colour. (how?)
            $this->setGameStateValue("active_worker_flag", 1);
            $this->setGameStateValue("active_worker_x", $x);
            $this->setGameStateValue("active_worker_y", $y);
            $this->setGameStateValue('active_worker_moves', 3);//todo handle extra move event
            
            //TODO - place a worker on the board (move it from player's board if possible)
            //TODO - make sure that reloading immediately after this state adds the worker
            //to the board
            self::notifyAllPlayers( "workerPlaced", clienttranslate( '${player_name} places a worker on a colony entrance' ), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'x' => $x,
                'y' => $y,
                ));
            
            $this->gamestate->nextstate("workerPlaced");
            return;
        }
        
        if ($currentState == "12")
        {
            $args = $this->argMoveWorker();
            if (!in_array($hex, $args["validMoves"]))
            {
                throw new feException("Invalid hex choice ".$hex);
            }
            
            //okay! mark this hex has having an active worker of this colour. (how?)
            $this->setGameStateValue("active_worker_flag", 1);
            $this->setGameStateValue("active_worker_x", $x);
            $this->setGameStateValue("active_worker_y", $y);
            
            //TODO - place a worker on the board (move it from player's board if possible)
            //TODO - make sure that reloading immediately after this state adds the worker
            //to the board
            self::notifyAllPlayers( "workerMoved", clienttranslate( '${player_name} moves a worker' ), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'x' => $x,
                'y' => $y,
                ));
            
            $this->gamestate->nextstate("workerMoved");
            return;
        }
        
        throw new feException("Unhandled hex click state ".$currentState);        
    }
    
    function onActivateColony($slot){
        //check valid
        //mark as used
        //give appropriate bonus (notify other players)
        //move to next worker
        
        //check valid
        $validMoves = $this->argPlaceWorker();
        $colonyStr = "colony_".$slot;
        
        if (!in_array($colonyStr, $validMoves["availableColony"]))
        {
            throw new feException("Invalid selection ".$colonyStr);
        }
        
        //mark used
        $colonyUsed = $this->getPlayerVariable("player_colony_used", self::getActivePlayerId());
        $colonyUsed += 2*($slot)+1;
        $this->setPlayerVariable("player_colony_used", self::getActivePlayerId(), $colonyUsed);
        
        //give bonus + notify
        switch($slot)
        {
            case "0":
                $rescount = 1;
                $resname = $this->resourceNames["larvae"];
                $this->incPlayerVariable("player_larvae", self::getActivePlayerId(), 1);
                break;
            case "1":
                $rescount = 1;
                $resname = $this->resourceNames["food"];
                $this->incPlayerVariable("player_food", self::getActivePlayerId(), 1);
                break;
            case "2a":
                $rescount = 1;
                $resname = $this->resourceNames["stone"];
                $this->incPlayerVariable("player_stone", self::getActivePlayerId(), 1);
                break;
            case "2b":
                $rescount = 1;
                $resname = $this->resourceNames["dirt"];
                $this->incPlayerVariable("player_dirt", self::getActivePlayerId(), 1);
                break;
            case "3":
                $rescount = 2;
                $resname = $this->resourceNames["vp"];
                //todo - validate food and different notification maybe?
                $this->incPlayerVariable("player_food", self::getActivePlayerId(), -1);
                $this->incPlayerVariable("player_score", self::getActivePlayerId(), 2);
                break;
            default:
                throw new feException("Invalid colony slot ". $slot);
        }
                
        self::notifyAllPlayers( "colonyActivated", clienttranslate( '${player_name} activates colony level ${slot} and gains ${num} ${resname}' ), array(
            'player_id' => self::getActivePlayerId(),
            'player_name' => self::getActivePlayerName(),
            'slot' => $slot,
            'num' => $rescount,
            'resname' => $resname
        ) );
        
        //next worker
        $this->gamestate->nextState("workerFinished");
    }
    
    function allocateNurse($type, $slot){
        
        $currentPlayerID = self::getCurrentPlayerId();
                
        $allocations = $this->getPlayerAllocations($currentPlayerID);
        $identifier = $type . "_" . $slot;
        
        if (in_array($identifier, $allocations['can_allocate']))
        {
            if ($type == "larvae") {
                $this->setPlayerVariable("player_larvae_slots_allocated", $currentPlayerID, $slot);
            }
            else if ($type == "soldier" && $slot == 1) {
                $this->setPlayerVariable("player_soldier_slots_allocated", $currentPlayerID, 2);
            }
            else if ($type == "soldier" && $slot == 2) {
                $this->setPlayerVariable("player_soldier_slots_allocated", $currentPlayerID, 3);
            }
            else if ($type == "worker" && $slot == 1) {
                $this->setPlayerVariable("player_worker_slots_allocated", $currentPlayerID, 2);
            }
            else if ($type == "worker" && $slot == 2) {
                $this->setPlayerVariable("player_soldier_slots_allocated", $currentPlayerID, 4);
            }
            else if ($type == "atelier" && $slot == 1) {
                $this->setPlayerVariable("player_atelier_1_allocated", $currentPlayerID, 1);
            }
            else if ($type == "atelier" && $slot == 2) {
                $this->setPlayerVariable("player_atelier_2_allocated", $currentPlayerID, 1);
            }
            else if ($type == "atelier" && $slot == 3) {
                $this->setPlayerVariable("player_atelier_3_allocated", $currentPlayerID, 1);
            }
            else if ($type == "atelier" && $slot == 4) {
                $this->setPlayerVariable("player_atelier_4_allocated", $currentPlayerID, 1);
            }
            else if ($type == "event") {
                $this->setPlayerVariable("player_event_selected", $currentPlayerID, $slot);
            }
            else
            {
                throw new feExcepton("Unrecognised identifier: " + $identifier);
            }
        }
        else if (in_array($identifier, $allocations['can_deallocate']))
        {
            if ($type == "larvae") {
                $this->setPlayerVariable("player_larvae_slots_allocated", $currentPlayerID, ($slot-1));
            }
            else if ($type == "soldier" && $slot == 1) {
                $this->setPlayerVariable("player_soldier_slots_allocated", $currentPlayerID, 0);
            }
            else if ($type == "soldier" && $slot == 2) {
                $this->setPlayerVariable("player_soldier_slots_allocated", $currentPlayerID, 2);
            }
            else if ($type == "worker" && $slot == 1) {
                $this->setPlayerVariable("player_worker_slots_allocated", $currentPlayerID, 0);
            }
            else if ($type == "worker" && $slot == 2) {
                $this->setPlayerVariable("player_soldier_slots_allocated", $currentPlayerID, 2);
            }
            else if ($type == "atelier" && $slot == 1) {
                $this->setPlayerVariable("player_atelier_1_allocated", $currentPlayerID, 0);
            }
            else if ($type == "atelier" && $slot == 2) {
                $this->setPlayerVariable("player_atelier_2_allocated", $currentPlayerID, 0);
            }
            else if ($type == "atelier" && $slot == 3) {
                $this->setPlayerVariable("player_atelier_3_allocated", $currentPlayerID, 0);
            }
            else if ($type == "atelier" && $slot == 4) {
                $this->setPlayerVariable("player_atelier_4_allocated", $currentPlayerID, 0);
            }
            else
            {
                throw new feExcepton("Unrecognised identifier: " + $identifier);
            }
        }
        else {
            throw new feExcepton("Unmatched identifier: " + $identifier);
        }
        
        
        $allocations = $this->getPlayerAllocations($currentPlayerID);
                          
        self::notifyPlayer( self::getCurrentPlayerId(), "allocationConfirmed", "", 
            $allocations
        );        
    }
    
    function harvestChosen(){
        $currentPlayerID = self::getCurrentPlayerId();
        $this->gamestate->setPlayerNonMultiactive( $currentPlayerID, "");
    }
    
    function storageChosen(){
        $currentPlayerID = self::getCurrentPlayerId();
        $this->gamestate->setPlayerNonMultiactive( $currentPlayerID, "");
    }
    
    function allocateNurseFinished(){
        
        //todo - notify all players who has finished, might encourage them to speed up
        //also, when this notification gets to the active player, deactivate their stuff
        
        $currentPlayerID = self::getCurrentPlayerId();
                      
        self::notifyPlayer( self::getCurrentPlayerId(), "yourNursesAllocated", "", 
            array()
        );
        
        // Notify all players about the card played
        self::notifyAllPlayers( "nursesAllocated", clienttranslate( '${player_name} has chosen an event and finished allocating nurses' ), array(
            'player_name' => self::getCurrentPlayerName()
        ) );
        
        $this->gamestate->setPlayerNonMultiactive( $currentPlayerID, "");        
    }
    
    function pass(){
        
        $test = akUtils::getPlayerVariable('player_nurses', self::getCurrentPlayerId());
        //var_dump($test);
        die('ok');        
        
        
        $this->gamestate->nextstate("pass");             
    }

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} played ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */
    
    function argPlaceTile()
    {
        //player chose to place a tile. Picks hexes and confirms
        // Show list of valid tiles as buttons?
        //or enable click on tiles from near player board or something?
        
        //make everything valid within 3 hexes clickable
        $x = $this->getGameStateValue("active_worker_x");
        $y = $this->getGameStateValue("active_worker_y");
                      
        $searchNodeData = new MyrmesHexGrid($this->boardSpaces);
    
        $moves = akPathfinding::FindAllDestinations($x, $y, $searchNodeData, 2);
        
        $result = array();
        $result["hexes"] = array();
        foreach($moves as $move)
        {
            $result["hexes"][] = "hex_".$move->X."_".$move->Y;
        }
        return $result;
    }
    
    function argMoveWorker(){
        //var_dump("argMoveWorker");
        //will depend on the current worker's location, blocked tiles, etc.
        //This one will be complicated.
        //If on an empty hex, can move to an adjacent hex
        //If on a special tile, can move to any hex adjacent to any space on the pheromone
        //TODO - check on boiteajeux about moving on specil tiles and opposition special tiles.
        //Can't move through water
        //Can't move off board
        //Can't move onto enemy tunnels
        //Can't move onto enemy tiles UNLESS player has a soldier
        //Can't move onto bugs UNLESS enough soldiers are present
        
        $x = $this->getGameStateValue("active_worker_x");
        $y = $this->getGameStateValue("active_worker_y");
                      
        $searchNodeData = new MyrmesHexGrid($this->boardSpaces);
               
        $result = array();
        $result["moves"]= $this->getGameStateValue('active_worker_moves');
        $result["validMoves"] = array();
        
        if ((int)$result["moves"] > 0)
        {
            $moves = akPathfinding::FindAllDestinations($x, $y, $searchNodeData, 2);
        
            foreach($moves as $move)
            {
                $result["validMoves"][] = "hex_".$move->X."_".$move->Y;
            }
        }
        
        $tilePlacements = $this->getTilePlacements(true);
        
        $result["canPlaceTile"] = count($tilePlacements) > 0;
        
        return $result;
    
    }
    
    function isValidTilePlacement($hexVectors, $xOrigin, $yOrigin)
    {
        //todo - also check for existing tiles, bugs, etc
        
        foreach ($hexVectors as $tile)
        {
            $tileX = $tile["x"] + $xOrigin;
            $tileY = $tile["y"] + $yOrigin;
            
            //var_dump("CHECKING ".$tileX.",".$tileY);
            
            if (!isset($this->boardSpaces[$tileX][$tileY]))
            {
                return false;
            }
            
            if ($this->boardSpaces[$tileX][$tileY] == "WATER")
            {
                return false;
            }
        }
        
        return true;
    }
    
    //sometimes we only want to know if there is a placement, not what they all are
    //so in this case, return early tosave processing time
    function getTilePlacements($returnFirstMatch)
    {
        $tilePlacements = array();
        
        //Where can the current player put tiles?
        //this is probably the single most complex calculation of the game.
        
        //each piece type has 6 orientations (12 flipped) and 2-6 possible source spots
        //if any of these is valid and we have the right piece, add to list
        
        //- tiles the player has in storage are valid
        //- TODO get generic tiles that are shared also
        //- TODO do not return tiles this player isn't a high enough level to use
        
        $x = $this->getGameStateValue("active_worker_x");
        $y = $this->getGameStateValue("active_worker_y");
        
        //if we only want the first match,we are checking for tiles this player can place
        //if we aren't, check all tiles.
        if ($returnFirstMatch)
        {
            $sql = "select distinct type_id from tiles where location = 'storage' and player_id = '".self::getActivePlayerId()."'";
        }
        else
        {
            //all player tile types
            $sql = "select distinct type_id from tiles where type_id < 9";
        }
        
        $pheromoneTypes = self::getCollectionFromDb($sql);
                        
        //2 = X.X [0 0, +1 0]
        //3 = X.X [0 0, +1 0, 0 +1]
        //     X 
        //4 = X.X.X
        //
        //     X
        //5 = X.X
        //     X
        //6 = X.X.X
        //     X
        //7 = X.X.X
        //     X.X
        //8 = X.X.X
        //     X.X
        //      X
        //var_dump($pheromoneTypes);
                        
        foreach($pheromoneTypes as $pheromoneType)
        {
            $type_id = $pheromoneType["type_id"];           
                       
            if ($type_id == "1")
            {
                continue; //don't want tunnels
            }
            
            $hexes = array();
            $flippedHexes = array();
                        
            if ($type_id == "2")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0));                
            }
            
            if ($type_id == "3")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>0, "y"=>1));
            }
            
            if ($type_id == "4")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0));
            }
            
            if ($type_id == "5")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>0, "y"=>1), array("x"=>1, "y"=>-1));
            }
            
            //types 6 and 7 require mirror flipped versions
            if ($type_id == "6")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>0, "y"=>1));
                $flippedHexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>1, "y"=>-1));
            }
            
            if ($type_id == "7")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>0, "y"=>1), array("x"=>1, "y"=>1));
                $flippedHexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>1, "y"=>-1), array("x"=>2, "y"=>-1));
            }
            
            if ($type_id == "8")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>0, "y"=>1), array("x"=>1, "y"=>1), array("x"=>0, "y"=>2));
            }
            
            //check twice, once for regular orientation and a second time for mirror flip.
            for ($m=0;$m<=1; $m++)
            {
                if($m ==1)
                {
                    $hexes = $flippedHexes;
                }
                
                if (count($hexes) > 0)
                {
                    foreach($hexes as $hex)
                    {
                        //var_dump("using starting point:");
                        //var_dump($hex);

                        $newHexes = AxialHexGrid::SetOrigin($hexes, $hex);

                        $rotatedHexes = $newHexes;

                        //var_dump("hex array is now:");
                        //var_dump($newHexes);                     

                        //hex has 6 rotations
                        for($i=0;$i<=5;$i++)
                        {
                            //var_dump("rotation ".$i);                       

                            if ($i > 0) //don't rotate first time
                            {
                                $rotatedHexes = AxialHexGrid::RotateHexes($rotatedHexes, 1);
                            }
                            //var_dump("rotated axial hex is");
                            //var_dump($rotatedHexes);                       

                            //check the returned array hexes for placement validity
                            $isValid = $this->isValidTilePlacement($rotatedHexes, $x, $y);

                            if ($isValid)
                            {
                                $match = new TileData($rotatedHexes, $i, $pheromoneType["type_id"], $m==1);
                                $tilePlacements[] = $match;
                                
                                if ($returnFirstMatch)
                                {
                                    return $tilePlacements;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $tilePlacements;        
    }
    
    function argPlaceWorker() {
        //valid spots are UNOCCUPIED colony levels, and starting tunnels belonging to this player
        $player_id = self::getActivePlayerId();
        $colonyUsed = $this->getPlayerVariable('player_colony_used', $player_id);
        $colonyLevel = $this->getPlayerVariable('player_colony_level', $player_id);
        
        $result['availableColony'] = array();
        if ($colonyUsed % 2 < 1)
        {
            $result['availableColony'][] = 'colony_0';
        }
        if ($colonyUsed % 4 < 2 && $colonyLevel >= 1 )
        {
            $result['availableColony'][] = 'colony_1';
        }
        if ($colonyUsed % 8 < 4 && $colonyLevel >= 2 )
        {
            $result['availableColony'][] = 'colony_2a';
            $result['availableColony'][] = 'colony_2b';
        }
        if ($colonyUsed  < 8 && $colonyLevel >= 3)
        {
            $result['availableColony'][] = 'colony_3';
        }
        
        //now get the tunnels
        $sql = "select tile_id, x1, y1 from tiles where type_id = 1 and location='board' and player_id = '".$player_id."'";
        $tunnels = self::getObjectListFromDb( $sql );
        
        $result["tunnels"] = array();
        foreach($tunnels as $tunnel)
        {
            //todo - maybe some hex ids?
            $result["tunnels"][] = "hex_".$tunnel["x1"]."_".$tunnel["y1"];
        }
        
        return $result;
    }
    
    function argBirths(){
        
        //get the event
        $current_event = 0;
        if ($this->getGameStateValue("current_season") == 1)
            $current_event = $this->getGameStateValue("spring_event");
        else if ($this->getGameStateValue("current_season") == 2)
            $current_event = $this->getGameStateValue("summer_event");
        else if ($this->getGameStateValue("current_season") == 3)
            $current_event = $this->getGameStateValue("fall_event");        
        
        $privateInfos = array();
        
        //calculate which slots the player can allocate, and which slots they can deallocate
        //return only this list
        
        $sql = "SELECT * FROM player ";
        $players = self::getCollectionFromDb( $sql );
        foreach($players as $player)
        {
            $private = $this->getPlayerAllocations($player['player_id']);
                        
            $privateInfos[$player['player_id']] = $private;
        }
                
        return array("event" => $current_event, "_private" => $privateInfos);
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    function stWorkerFinished()
    {
        $this->activeNextPlayer();
        $this->gamestate->nextState("");
    }
    
    function stWorkerUsed(){
        //TODO
        //remove a worker from the active player. Make sure this updates in all UIs and current
        //game state.
        
        $this->gamestate->nextState("");
    }
    
    function stWorkerPass(){
        $passedWorkers = $this->getPlayerVariable('player_workers_passed', self::getActivePlayerId());
        $passedWorkers++;
        $this->setPlayerVariable('player_workers_passed', self::getActivePlayerId(), $passedWorkers);
        
        self::notifyAllPlayers( "workerPass", clienttranslate( '${player_name} passes' ), array(
                'player_name' => $this->getPlayerVariable('player_name', self::getActivePlayerId())
        ) ); 
        
        $this->gamestate->nextstate('');
    }
    
    function stAtelierPass(){
        
        self::notifyAllPlayers( "nursePass", clienttranslate( '${player_name} passes further atelier actions' ), array(
                'player_name' => $this->getPlayerVariable('player_name', self::getActivePlayerId())
        ) ); 
        
        //TODO - move stuff on interface?
        $this->setPlayerVariable('player_atelier_1_allocated', self::getActivePlayerId(), 0);
        $this->setPlayerVariable('player_atelier_2_allocated', self::getActivePlayerId(), 0);
        $this->setPlayerVariable('player_atelier_3_allocated', self::getActivePlayerId(), 0);
        $this->setPlayerVariable('player_atelier_4_allocated', self::getActivePlayerId(), 0);
        
        
        //unlike worker phase, we don't switch to next player after each place/pass
        //because if you have more nurses, you do them all one after another
        //although this doesn't matter when you pass anyway.
                
        $this->gamestate->nextstate('');
    }
    
    function stEvent(){
        //entering the event state, set all players to the current event
        $currentEvent = $this->getCurrentEvent();      
        
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player)
        {
            $this->setPlayerVariable("player_event_selected", $player['player_id'], $currentEvent);
        }
        
        $currentYear = $this->getGameStateValue('current_year');
        $currentSeason = $this->getGameStateValue('current_season');
        
        self::notifyAllPlayers( "season", clienttranslate( 'SEASON ${season} of year ${year}. The event is ${eventname}' ), array(
            'year' => $currentYear,
            'season'=>$currentSeason,
            'eventname'=>"EVENTNAME"
        ) ); 
                
        $this->gamestate->nextstate('');
    }
    
    function stProcessBirths(){
        //give everybody new ants!
        $sql = "SELECT * FROM player ";
        $players = self::getCollectionFromDb( $sql );
        
        $baseEvent = $this->getCurrentEvent();
        
        foreach($players as $player)
        {
            //reduce larvae for event
            $chosenEvent = $this->getPlayerVariable('player_event_selected', $player['player_id']);
            
            $larvae = $player['player_larvae'];
            $spent = abs($baseEvent - $chosenEvent);
            $larvae -= $spent;
            $this->setPlayerVariable('player_larvae', $player['player_id'], $larvae);

            
            //birth larvae
            $larvaeAllocated = $player['player_larvae_slots_allocated'];
            $larvaeGain = 0;
            if ($larvaeAllocated == 1)
            {
                $larvaeGain = 1;
            }
            if ($larvaeAllocated == 2)
            {
                $larvaeGain = 3;
            }
            if ($larvaeAllocated == 3)
            {
                $larvaeGain = 5;
            }
            if ($larvaeGain > 0 && $chosenEvent == 2) //TODO - use constant
            {
                $larvaeGain += 2;
            }
            
            $larvae += $larvaeGain;
            $this->setPlayerVariable('player_larvae', $player['player_id'], $larvae);
                        
            
            //birth soldiers
            $soldiers = $player['player_soldiers'];
            $soldiersAllocated = $player['player_soldier_slots_allocated'];
            $soldierGain = 0;
            if ($soldiersAllocated == 2)
            {
                $soldierGain = 1;
            }
            if ($soldiersAllocated == 3)
            {
                $soldierGain = 2;
            }
            if ($soldierGain > 0 && $chosenEvent == 5) //TODO - use constant
            {
                $soldierGain += 1;
            }
            
            $soldiers += $soldierGain;
            $this->setPlayerVariable('player_soldiers', $player['player_id'], $soldiers);
            
            
            //birth workers
            $workers = $player['player_workers'];
            $workersAllocated = $player['player_worker_slots_allocated'];
            $workerGain = 0;
            if ($workersAllocated == 2)
            {
                $workerGain = 1;
            }
            if ($workersAllocated == 4)
            {
                $workerGain = 2;
            }
            if ($workerGain > 0 && $chosenEvent == 7) //TODO - use constant
            {
                $workerGain += 1;
            }
            
            $workers += $workerGain;
            $this->setPlayerVariable('player_workers', $player['player_id'], $workers);
                       
            
            //sent to atelier        
            $atelier1 = $player['player_atelier_1_allocated'];
            $atelier2 = $player['player_atelier_2_allocated'];
            $atelier3 = $player['player_atelier_3_allocated'];
            $atelier4 = $player['player_atelier_4_allocated'];
            $atelierCount = $atelier1 + $atelier2 + $atelier3 + $atelier4; 
            
            //notifications
            self::notifyAllPlayers( "eventChosen", clienttranslate( '${player_name} spends ${spent} larvae to trigger event ${eventName}' ), array(
                'player_name' => $player['player_name'],
                'spent' => $spent,
                'eventName' => "SOME EVENT HERE",
                'larvaeCount' => $larvae
            ) );
            
            self::notifyAllPlayers( "birthLarvae", clienttranslate( '${player_name} births ${larvaeGain} larvae, ${soldierGain} soldiers, and ${workerGain} workers' ), array(
                'player_name' => $player['player_name'],
                'larvaeGain' => $larvaeGain,            
                'larvaeCount' => $larvae,
                'soldierGain' => $soldierGain,            
                'soldierCount' => $soldiers,
                'workerGain' => $workerGain,            
                'workerCount' => $workers
            ) );
            
            self::notifyAllPlayers( "atelierAllocation", clienttranslate( '${player_name} sends ${atelierCount} nurses to the atelier' ), array(
                'player_name' => $player['player_name'],
                'atelierCount' => $atelierCount
            ) ); 
        }
        
        $this->gamestate->nextstate('');
    }
    
    function stBirths(){
        //entering the births state, set all players active
        $this->gamestate->setAllPlayersMultiactive();
    }
    
    function stHarvest(){
        //entering the births state, set all players active
        $this->gamestate->setAllPlayersMultiactive();
    }
    
    function stStorage(){
        //entering the births state, set all players active
        $this->gamestate->setAllPlayersMultiactive();
    }
    
    function stNextAvailableNurse(){
        //does this player have an available nurse?
        //if yes, take a turn. Otherwise cycle through other players and check
        //if no players have available workers, we are finished.
        
        $initialPlayerID = self::getActivePlayerId();
        $availableNurses = $this->getAvailableNurses($initialPlayerID);
       
        while($availableNurses == 0)
        {
            self::notifyAllPlayers( "noNurse", clienttranslate( '${player_name} has no nurses to place in the atelier' ), array(
                'player_name' => self::getActivePlayerName()
            ) );
                    
            $this->activeNextPlayer();
            $activePlayerID = self::getActivePlayerId();
            $availableNurses = $this->getAvailableNurses($activePlayerID);
            
            //if we are back to the initial player and still no nurses, we are done
            if ($activePlayerID == $initialPlayerID && $availableNurses == 0)
            {
                self::notifyAllPlayers( "noNurse", clienttranslate( 'All nurses have been placed' ), array(
                ) );
                
                $this->gamestate->nextstate('allNursesPlaced');
                return;
            }
        }
                        
        $this->gamestate->nextstate('hasNurse');        
    }
    
    function stNextAvailableWorker(){
        
        //does this player have an available worker?
        //if yes, take a turn. Otherwise cycle through other players and check
        //if no players have available workers, we are finished.
        
        $initialPlayerID = self::getActivePlayerId();
        $availableWorkers = $this->getAvailableWorkers($initialPlayerID);
       
        while($availableWorkers == 0)
        {
            self::notifyAllPlayers( "noWorker", clienttranslate( '${player_name} has no workers left to place' ), array(
                'player_name' => self::getActivePlayerName()
            ) );
                    
            $this->activeNextPlayer();
            $activePlayerID = self::getActivePlayerId();
            $availableWorkers = $this->getAvailableWorkers($activePlayerID);
            
            //if we are back to the initial player and still no workers, we are done
            if ($activePlayerID == $initialPlayerID && $availableWorkers == 0)
            {
                self::notifyAllPlayers( "noWorker", clienttranslate( 'All workers have been placed' ), array(
                ) );
                
                $this->gamestate->nextstate('allWorkersPlaced');
                return;
            }
        }
                        
        $this->gamestate->nextstate('hasWorker');
    }
    
    function stSetFirstPlayerActive() {
        $this->setFirstPlayerActive();
        
        $this->gamestate->nextstate("");
    }
    
    function stWinter(){
        //in winter, each player must feed
        //Food required = year + 3 - soldiers (to a min of zero)
        //-3vp per missing food
        
        $currentYear = $this->getGameStateValue('current_year');
        
        self::notifyAllPlayers( "winter", clienttranslate( 'Winter of year ${year}. Each player requires ${food} food for their colony' ), array(
            'year' => $currentYear,
            'food'=>$currentYear + 3,
        ) );                       
        
        $sql = "SELECT * FROM player ";
        $players = self::getCollectionFromDb( $sql );
        foreach($players as $player)
        {
            $soldiers = (int)$player['player_soldiers'];
            $foodRequired = 3 + $currentYear - $soldiers;
            $foodAvailable = (int)$player['player_food'];
            if ($foodRequired < 0)
            {
                $foodRequired = 0;
            }
            
            if ($foodRequired <= $foodAvailable)
            {
                $foodAvailable -= $foodRequired;
                $this->setPlayerVariable('player_food', $player['player_id'], $foodAvailable);
                
                self::notifyAllPlayers( "colonyFed", clienttranslate( '${player_name} spent ${spent} food (${soldiers} soldiers)' ), array(
                    'player_name' => self::getActivePlayerName(),
                    'spent' => $foodRequired,    
                    'soldiers'=>$soldiers
                ) );       
            } 
            else
            {
                $this->setPlayerVariable('player_food', $player['player_id'], 0);
                $vploss = ($foodRequired - $foodAvailable)*3;
                
                $this->playerSubtractScore($player['player_id'], $vploss);
                
                self::notifyAllPlayers( "colonyFed", clienttranslate( '${player_name} only has ${spent} food (${soldiers} soldiers) and loses ${vploss} VP' ), array(
                    'player_name' => self::getActivePlayerName(),
                    'spent' => $foodAvailable,    
                    'soldiers' => $soldiers,
                    'vploss' => $vploss                    
                ) );                       
            }
        }
        
        $this->gamestate->nextstate('');
    }
    
    function stCleanup()
    {
        //do all end of turn cleanup stuff, and then either end the game or
        //advance the season.
        
        //return workers from colony
        //return nurses to board
        //move start player left
        //advance season
        
        //return workers from colony
        $sql = "update player set player_colony_used = 0";
        self::DbQuery( $sql );
        
        //return nurses to board
        $sql = "update player set player_larvae_slots_allocated = 0,"
                . " player_worker_slots_allocated = 0,"
                . " player_soldier_slots_allocated = 0,"
                . " player_atelier_1_allocated = 0,"
                . " player_atelier_2_allocated = 0,"
                . " player_atelier_3_allocated = 0,"
                . " player_atelier_4_allocated = 0,"
                . " player_workers_passed = 0";
        self::DbQuery( $sql );
        
        //move start player left
        $this->setFirstPlayerActive();
        $this->activeNextPlayer();
        $active_player_id = self::getActivePlayerId();
        $this->setGameStateValue('first_player', $active_player_id);
        
        //advance season
        $current_year = $this->getGameStateValue('current_year');
        $current_season = $this->getGameStateValue('current_season');       
                
        if($current_season == 4 && $current_year == 3)
        {
            $this->gamestate->nextstate("endGame");
            return;
        }
        
        if ($current_season == 4 && $current_year < 3)
        {
            //throw new feException("yarr");
            $current_year++;
            $current_season = 1;
            $this->rollSeasons();
        }
        else
        {
            //throw new feException("zogg");
            $current_season++;
        }
        
        //var_dump($current_season);
        //die('ok');
        
        $this->setGameStateValue('current_year', $current_year);
        $this->setGameStateValue('current_season', $current_season);       
        
        if ($current_season == 4)
        {
            $this->gamestate->nextstate("winter");        
        }
        else
        {
            $this->gamestate->nextstate("startNextSeason");        
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] == "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] == "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $sql = "
                UPDATE  player
                SET     player_is_multiactive = 0
                WHERE   player_id = $active_player
            ";
            self::DbQuery( $sql );

            $this->gamestate->updateMultiactiveOrNextState( '' );
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            $sql = "ALTER TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            $sql = "CREATE TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
