<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * AKTestgame implementation : © <Your name here> <Your email address here>
  * 
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  * 
  * aktestgame.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );
require 'modules/myrpathfinding.php';

class AKTestgame extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
		    "current_year"=> 10,
            "current_season"=> 11,
            "spring_event"=> 12,
            "summer_event"=> 13,
            "fall_event"=> 14,
            "first_player"=>15,
            "active_worker_x"=>16,
            "active_worker_y"=>17,
            "active_worker_flag"=>18,
            "active_worker_moves"=>19,
            "player_count"=>20,
            "progress_indicator"=>21
        ) );        
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "aktestgame";
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
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        //$default_colors = $gameinfos['player_colors'];
		$default_colors = array( "ff0000", "ffff00", "0000ff", "000000" );
		$default_colors2 = array( "ff0000", "ffff00", "0000ff", "000000" );
         
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_nurses, player_workers, player_larvae, player_colony_level, player_food, player_dirt) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            //each player starts with 3 nurses, 2 workers, 1 larvae
            //also soldiers 0, colony level 0, food 0, dirt 0, stone 0
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."', '".$this->initialNurseCount."', '".$this->initialWorkerCount."', '".$this->initialLarvaeCount."', '".$this->initialColonyLevel."', '".$this->initialFoodCount."', '".$this->initialDirtCount."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, array(  "ff0000", "ffff00", "0000ff", "000000" ) );
        self::reloadPlayersBasicInfos();
        
        $sql = "update player set player_color_name = 'red' where player_color = '".$default_colors2[0]."'";
        self::DbQuery($sql);
        $sql = "update player set player_color_name = 'yellow' where player_color = '".$default_colors2[1]."'";
        self::DbQuery($sql);
        $sql = "update player set player_color_name = 'blue' where player_color = '".$default_colors2[2]."'";
        self::DbQuery($sql);
        $sql = "update player set player_color_name = 'black' where player_color = '".$default_colors2[3]."'";
        self::DbQuery($sql);
               
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
        
        //also the bugs (randomised)
        //there are 18 bugs available, 6 of each type
        //there are 18 possible spots for bugs in a 4p game, 11 in 2p, 12 in 3p
        //place them all, then hide or remove?
        $bugs = array();
        foreach( $this->bugTileTypes as $type_id => $tile )
        {
            for ($i =0; $i< $tile["count"]; $i++)
            {
                $bugs[] = $type_id;                
            }
        }
        shuffle($bugs);
        
        foreach($this->bugLocations as $bugLocation)
        {
            if (MyrmesHexGrid::IsPartOfBoard($bugLocation['x'], $bugLocation['y'], count($players)))
            {
                //get a random bug and add it to the board
                $bug = array_pop($bugs); 
                
                $sql = "INSERT INTO tiles (player_id, type_id, x1, y1, location, color)";
                $sql = $sql . " VALUES (0, ".$bug.", '".$bugLocation['x']."','".$bugLocation['y']."', 'board', '')";
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
        $this->setGameStateValue('player_count', count($players));
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
        $sql = "SELECT player_id id, player_color color, player_color_name color_name, player_colony_level colony, player_score score, player_nurses nurses, player_larvae larvae, player_soldiers soldiers, player_workers workers, player_food food, player_stone stone, player_dirt dirt FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
		
		//do NOT return allocations in births phase
		$currentState = $this->gamestate->state();
		$currentStateName = $currentState["name"];
		if ($currentStateName != "births")
		{
			foreach($result['players'] as $id=>$player)
			{
				$result['players'][$id]['allocations'] = $this->getPlayerAllocations($player['id']);			
				$result['players'][$id]['colony_used'] = $this->getPlayerColonyUsed($player['id']);
			}		
		}
		
        $result['maxWorkerCount'] = $this->maxWorkerCount;
        $result['maxNurseCount'] = $this->maxNurseCount;
 
        //Gather all information about current game situation (visible by player $current_player_id).
        $result['current_year'] = $this->getGameStateValue("current_year");
        $result['current_season'] = $this->getGameStateValue("current_season");
		$result['spring_event'] = $this->getGameStateValue("spring_event");
		$result['summer_event'] = $this->getGameStateValue("summer_event");
		$result['fall_event'] = $this->getGameStateValue("fall_event");
		                   
        //the hex info needs to be in a form more suitable for js
        $hexInfo = array();
        
        $sql = "SELECT tile_id, color, x1, y1 from tiles where location='board' and type_id='1'";
        $result['tunnels'] = self::getObjectListFromDB( $sql );
        
        $sql = "SELECT tile_id, player_id, subtype_id, type_id, color, flipped, rotation, x1, y1, res1, x2, y2, res2, x3, y3, res3, x4, y4, res4, x5, y5, res5, x6, y6, res6 from tiles where location='board' and type_id > '1'";
        $result['pheromones'] = self::getObjectListFromDB( $sql );
        
        if ($this->getGameStateValue("active_worker_flag")== 1)
        {
            $result['activeWorker'] = array(
                "x"=>$this->getGameStateValue("active_worker_x"),
                "y"=>$this->getGameStateValue("active_worker_y"),
                "color"=>$this->getPlayerVariable("player_color_name", self::getActivePlayerId()),
                );
        }       
        
        //return all the spare tile counts
        $result['tileCounts'] = $this->getTileCounts();
        $result['specialTileCounts'] = $this->getSpecialTileCounts();
                            
        //TODO - return which birthing choices this player has made (if appropriate?)
                
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
        // 33% per completed year, plus 11% per completed season
        $currentYear = $this->getGameStateValue("current_year");
        $currentSeason = $this->getGameStateValue("current_season");
        $progressIndicator = $this->getGameStateValue("progress_indicator");
        $completion = 33*($currentYear-1);
        if ($currentSeason <=3)
        {
            $completion += 11*($currentSeason-1);
        }
        
        if ($progressIndicator >= 4) //storage phase is 10/11
        {
            $completion += 10;
        }
        else if ($progressIndicator >= 3) //atelier phase is 8/11
        {
            $completion += 8;
        }
        else if ($progressIndicator >= 2) //harvest phase is 7/11
        {
            $completion += 7;
        }
        else if ($progressIndicator >= 1) //worker phase is 2/11
        {
            $completion += 2;
        }   
        
        return $completion;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    private function getTileCounts()
    {
        $sql = "select 0 as count, color, player_id, type_id from tiles group by player_id, type_id, color
union
SELECT count(tile_id) as count, color, player_id, type_id from tiles where location='storage' group by player_id, type_id, color";
        return self::getObjectListFromDB( $sql );
    }
    
    private function getSpecialTileCounts()
    {
        $sql = "select (".$this->maxSpecialTileCount."-0) as count, color, player_id from tiles group by player_id, type_id, color
union
SELECT (".$this->maxSpecialTileCount."-count(tile_id)) as count, color, player_id from tiles where location='board' and (type_id=9 or type_id=10) group by player_id, color";
        return self::getObjectListFromDB( $sql ); 
    }
    
    private function IsVPBonusActive($playerId)
    {
        $event = $this->getPlayerVariable("player_event_selected", $playerId);
        return $event == $this->eventVP;
    }
    
    private function colonyStorageExceeded($playerID)
    {
        $sql = "SELECT * FROM player where player_id='".$playerID."'";
        $player = self::getObjectFromDb( $sql );
        
        $colonyLevel = $player["player_colony_level"];
        $food = $player["player_food"];
        $dirt = $player["player_dirt"];
        $stone = $player["player_stone"];
        $resources = $food + $dirt + $stone;
            
        if ($resources <=4)
        {
            return false;            
        }
        else if ($resources <=6 && $colonyLevel >=2)
        {
            return false;            
        }
        return true;        
    }
    
    private function getTileByXY($x, $y)
    {
        $sql = "select player_id, type_id, tile_id, res1, res2, res3, res4, res5, res6 from tiles where ".
                " (x1='".$x."' and y1='".$y."') OR ".
                " (x2='".$x."' and y2='".$y."') OR ".
                " (x3='".$x."' and y3='".$y."') OR ".
                " (x4='".$x."' and y4='".$y."') OR ".
                " (x5='".$x."' and y5='".$y."') OR ".
                " (x6='".$x."' and y6='".$y."')";
        $match = self::getCollectionFromDb($sql);
        $match = reset($match);
        return $match;
    }
    
    private function incPlayerVariable($varName, $playerId, $increment)
    {
        $value = $this->getPlayerVariable($varName, $playerId);
        $value += $increment;
        $this->setPlayerVariable($varName, $playerId, $value);
    }
    
    private function getPlayerVariable($varName, $playerId)
    {
        $sql = "SELECT 0,".$varName." FROM player where player_id = '".$playerId."'";
        $player = self::getCollectionFromDb( $sql );
        return $player[0][$varName];
    }

    private function setPlayerVariable($varName, $playerId, $newValue)
    {
        $sql = "update player set ".$varName." = '".$newValue."' where player_id = '".$playerId."'";
        self::DbQuery( $sql );
        
        if ($varName == "player_score")
        {
            self::notifyAllPlayers( "setScore", '', array(
                        'player_id' => $playerId,
                        'score' => $newValue
                    ));
        }    
        
        if ($varName == "player_workers" || $varName == "player_soldiers")
        {
            $soldierCount = $this->getPlayerVariable("player_soldiers", $playerId);
            $workerCount = $this->getPlayerVariable("player_workers", $playerId);
            self::notifyAllPlayers( "setWorkers", '', array(
                        'player_id' => $playerId,
                        'workerCount' => $newValue,
                        'workersRemaining' => $this->maxWorkerCount - (int)$workerCount - (int)$soldierCount
                    ));
        }    
        if ($varName == "player_nurses")
        {
            $nurseCount = $this->getPlayerVariable("player_nurses", $playerId);
            //todo - count objective ones
            self::notifyAllPlayers( "setNurses", '', array(
                        'player_id' => $playerId,
                        'nurseCount' => $newValue,
                        'nursesRemaining' => $this->maxNurseCount - (int)$nurseCount
                    ));
        }  
    }
    
    private function removeAtelierAction($playerId)
    {
        for ($i=1;$i<=4; $i++)
        {
            $atelier = $this->getPlayerVariable("player_atelier_".$i."_allocated", $playerId);
            if ($atelier > 0)
            {
                $this->setPlayerVariable("player_atelier_".$i."_allocated", $playerId, 0);
                return;
            }
        }
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
    
    private function cleanupTempTiles()
    {
        //if this was a player tile type (2-8), reassign it. Otherwise, it's neutral
        $sql = "select tile_id, type_id from tiles where location = 'board_temp'";
        $tiles = self::GetCollectionFromDb($sql);
        
        foreach($tiles as $tile)
        {
            $owner = "0";
            $color = "";
            if ($tile["type_id"] < 9)
            {
                $owner = self::getActivePlayerId();
                $color = $this->getPlayerVariable('player_color_name', self::getActivePlayerId());
            }
            $tileID = $tile["tile_id"];
            $sql = "update tiles set color='".$color."', player_id='".$owner."', x1=0,x2=0,x3=0,x4=0,x5=0,x6=0,y1=0,y2=0,y3=0,y4=0,y5=0,y6=0, location = 'storage' where tile_id='".$tileID."'";
            self::DbQuery($sql);
        }   
    }
    
    private function processPlacedTile($tileID)
    {
        $sql = "SELECT tile_id, player_id, subtype_id, type_id, color, flipped, rotation, x1, y1, res1, x2, y2, res2, x3, y3, res3, x4, y4, res4, x5, y5, res5, x6, y6, res6 from tiles where tile_id = '".$tileID."'";
                    
        $playerId = self::getActivePlayerId();
        $placedTile = self::getObjectListFromDB( $sql );
        $tileType = $placedTile[0]["type_id"];
        $subType = $placedTile[0]["subtype_id"];
                    
        $points = 0;
        if ($tileType <9 )
        {
            $points = $this->playerTileTypes[$tileType]["points"];
        }   
        else {
            $points = $this->sharedTileTypes[$tileType]["points"];
        }
        
        if ($points > 0 && $this->isVPBonusActive($playerId))
        {
            $points++;
        }
        
        if ($tileType == 9 && $subType == "9a")
        {
            $this->incPlayerVariable("player_stone", $playerId, -1);
        }
        if ($tileType == 9 && $subType == "9b")
        {
            $this->incPlayerVariable("player_food", $playerId, -1);
        }
        if ($tileType == 1)
        {
            $this->incPlayerVariable("player_dirt", $playerId, 1);
        }
        
        $this->incPlayerVariable("player_score", $playerId, $points);
               
        self::notifyAllPlayers( "tilePlaced", clienttranslate( '${player_name} places a tile (${points} points)' ), array(
            'player_id' => $playerId,
            'player_name' => self::getActivePlayerName(),
            'tile' => $placedTile[0],
            'points'=> $points,
            'dirtCount'=> $this->getPlayerVariable('player_dirt', $playerId),
            'foodCount'=> $this->getPlayerVariable('player_food', $playerId),
            'stoneCount'=> $this->getPlayerVariable('player_stone', $playerId),
            'tileCounts'=>$this->getTileCounts(),
            'specialTileCounts'=>$this->getSpecialTileCounts()
        ));                   
    }
    
    private function getAvailableNurses($playerID)
    {
        $availableNurses = $this->getPlayerVariable('player_atelier_1_allocated', $playerID);
        $availableNurses += $this->getPlayerVariable('player_atelier_2_allocated', $playerID);
        $availableNurses += $this->getPlayerVariable('player_atelier_3_allocated', $playerID);
        $availableNurses += $this->getPlayerVariable('player_atelier_4_allocated', $playerID);        
        
        return $availableNurses;
    }
	
	private function getPlayerColonyUsed($playerId)
	{
		$colonyUsed = $this->getPlayerVariable('player_colony_used', $playerId);//1,2,4,8		
        
		$result = array();
        if ($colonyUsed % 2 > 0)
        {
            $result[] = 'colony_0';
        }
		if ($colonyUsed % 4 >= 2)
		{
            $result[] = 'colony_1';
        }
        if ($colonyUsed % 8 >= 4)
        {
            $result[] = 'colony_2';            
        }
        if ($colonyUsed  >=8)
        {
            $result[] = 'colony_3';
        }
		
		return $result;        
	}
    
    private function getAvailableWorkers($playerID)
    {
		//total available workers = numer of workers, minus those passed, and those used in the colony. 
        $availableWorkers = $this->getPlayerVariable('player_workers', $playerID);
		
        $passedWorkers = $this->getPlayerVariable('player_workers_passed', $playerID);        
        $availableWorkers -= $passedWorkers;
        
        $colonyUsed = $this->getPlayerColonyUsed($playerID);        
		$availableWorkers-= count($colonyUsed);
		        
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
    
    private function getPlayerHarvestAllocations($playerID) {
        
        $private = array();
        $private['allocated'] = array();
        $private['can_allocate'] = array();
        $private['can_deallocate'] = array();
		
		$sql = "SELECT * FROM tiles where player_id = '".$playerID."' and location='board'";
        $playerTiles = self::getCollectionFromDB( $sql );
        
		//tiles have a 'harvest_selected' int column
        //binary - 1,2,4,8,16,32 for the 6 resource spots
        //therefore >0 means something selected
        //normally only one per tile, but we may have the 3 extra cubes in harvest selected
                               
        $selectedEvent = $this->getPlayerVariable('player_event_selected', $playerID);
        $extraAllocations = 0;
        if ($selectedEvent == $this->eventHarvest)
        {
            $extraAllocations = 3;
        }
              
        foreach($playerTiles as $tile)
        {
            $tileID = $tile['tile_id'];
			$myrmesTile = new MyrTile($tile, $this->playerTileTypes, $this->sharedTileTypes);
			           
            $hexesWithResources = array();
            $harvestHexes = $myrmesTile->getHarvestHexes();
            $category = $myrmesTile->getTileCategory();
                        
            if ($category == "pheromone" || $category == "special")
            {
                if (count($harvestHexes) == 0) //none selected, add all of them
                {
                    for($i=0; $i< $myrmesTile->Size; $i++)
                    {
                        if ($myrmesTile->hasResourceOnHex($i+1))
                        {
                            array_push($private['can_allocate'], $myrmesTile->getHexID($i+1));
                        }
                    }                     
                }
            }
        }        
        
        return $private;
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
                
		$colorName = $player['player_color_name'];
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
        
		$private['color'] = $colorName;
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
    

    private function getBoardResource($x, $y)
    {
        if ($x == 0 && $y == 0)
            return "";
        
        $board1 = $this->boardSpaces[$x][$y];
        if ($board1 == "GRASS")
        {
            return "FOOD";
        }
        if ($board1 == "DIRT")
        {
            return "DIRT";
        }
        if ($board1 == "STONE")
        {
            return "STONE";
        }
        return "";
    }
    
    private function saveTileToBoard($tiles, $tileType, $rotation, $isFlipped, $originHex, $isTemp)
    {                      
        //first, get the ID of the tile we are adding to the board
        $sql = "select tile_id, type_id from tiles where type_id='".$tileType."' and location='storage' and (player_id='".self::getActivePlayerId()."' or player_id='0') limit 1";
        $tile = self::getObjectFromDb( $sql );
        $tileID = $tile["tile_id"];
        $typeID = $tile["type_id"];
        
        $x = self::getGameStateValue("active_worker_x");        
        $y = self::getGameStateValue("active_worker_y");        
        
        $x1 = $y1 = $x2 = $y2 = $x3 = $y3 = $x4 = $y4 = $x5 = $y5 = $x6 = $y6 = 0;
        
        if (count($tiles) > 0)
        {
            $tile = $tiles[0];
            $x1 = $tile["x"] + $x;
            $y1 = $tile["y"] + $y;
        }
        
        if (count($tiles) > 1)
        {
            $tile = $tiles[1];
            $x2 = $tile["x"] + $x;
            $y2 = $tile["y"] + $y;
        }
        
        if (count($tiles) > 2)
        {
            $tile = $tiles[2];
            $x3 = $tile["x"] + $x;
            $y3 = $tile["y"] + $y;
        }
        
        if (count($tiles) > 3)
        {
            $tile = $tiles[3];
            $x4 = $tile["x"] + $x;
            $y4 = $tile["y"] + $y;
        }
        
        if (count($tiles) > 4)
        {
            $tile = $tiles[4];
            $x5 = $tile["x"] + $x;
            $y5 = $tile["y"] + $y;
        }
        
        if (count($tiles) > 5)
        {
            $tile = $tiles[5];
            $x6 = $tile["x"] + $x;
            $y6 = $tile["y"] + $y;
        }
        
        if ($typeID > 1 && $typeID < 9)
        {
            $res1 = $this->getBoardResource($x1, $y1);
            $res2 = $this->getBoardResource($x2, $y2);  
            $res3 = $this->getBoardResource($x3, $y3);
            $res4 = $this->getBoardResource($x4, $y4);
            $res5 = $this->getBoardResource($x5, $y5);
            $res6 = $this->getBoardResource($x6, $y6); 
        }
        else
        {
            $res1 = $res2 = $res3 = $res4 = $res5 = $res6 = "";            
        }
        
        $playerColor = $this->getPlayerVariable('player_color_name', self::getActivePlayerId());
        $location = "board";
        if ($isTemp)
        {
            $location = "board_temp";
        }
        
        $sql = "update tiles set color='".$playerColor."', location='".$location."', flipped='".$isFlipped."', rotation='".$rotation."', player_id = '".self::getActivePlayerId()."', x1='".$x1."', y1='".$y1."' , x2='".$x2."', y2='".$y2."' , x3='".$x3."', y3='".$y3."' , x4='".$x4."', y4='".$y4."' , x5='".$x5."', y5='".$y5."', x6='".$x6."', y6='".$y6."', res1='".$res1."', res2='".$res2."', res3='".$res3."', res4='".$res4."', res5='".$res5."', res6='".$res6."' where tile_id = '".$tileID."'";
        self::DbQuery( $sql );
        return $tileID;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in aktestgame.action.php)
    */
	
    function onStartPlaceTile()
    {
        //user clicked the button to place a tile
        self::checkAction( 'placeTile' );
        $this->gamestate->nextstate("chooseTile");
    }
    
    function onCancelTile()
    {
        self::checkAction( 'cancel' );
        
        $this->cleanupTempTiles();           
        
        $this->gamestate->nextstate("cancel");
    }
    
    function onDiscardWorker()
    {        
        self::checkAction( 'discardWorker' );
        self::notifyAllPlayers( "discardWorker", clienttranslate( '${player_name} discards a worker' ), array(
                        'player_name' => self::getActivePlayerName()                
                    ));
        $this->gamestate->nextstate("workerUsed");
    }
    
    function onConvertLarvae()
    {        
        self::checkAction( 'convertLarvae' );
        $playerId = self::getCurrentPlayerid();
        $food = $this->getPlayerVariable("player_food", $playerId);
        $larvae = $this->getPlayerVariable("player_larvae", $playerId);
        
        if ($larvae < 3)
        {
            throw new feException("Invalid conversion, not enough larvae");
        }
        
        $food++;
        $larvae-=3;
        
        $this->setPlayerVariable("player_food", $playerId, $food);
        $this->setPlayerVariable("player_larvae", $playerId, $larvae);
        
        self::notifyAllPlayers( "larvaeConverted", clienttranslate( '${player_name} converts 3 Larvae into 1 Food' ), array(
            'player_name' => self::getCurrentPlayerName(),
            'player_id' => $playerId,
            'foodCount' => $food,
            'larvaeCount' => $larvae
        ));
        
        //if we are in winter, re-run the food check.
        $currentState = $this->gamestate->state();
        if ($currentState == 51)
        {
            $this->stWinterFoodCheck();
            return;
        }
        
        $this->gamestate->nextstate("larvae");
    }
    
    //todo - check action on ALL actions and JS side too
    function onClearPheromone(){
    
        self::checkAction( 'clearPheromone' );
        
        $validMoves = $this->argMoveWorker();
        
        if (!$validMoves["canClearPheromone"])
        {
            throw new feException("Invalid clear pheromone action ");
        }
               
        //give active player X vp IF it is an opponent's tile
        $pointsValue = 0;
        
        $x = $this->getGameStateValue("active_worker_x");
        $y = $this->getGameStateValue("active_worker_y");
        $match = $this->getTileByXY($x,$y);

        if ($match["player_id"] != self::getActivePlayerId())
        {
            $type_id = $match["type_id"];
            $pointsValue = $this->playerTileTypes[$type_id]["points"];
            if ($pointsValue > 0 && $this->isVPBonusActive(self::getActivePlayerId()))
            {
                $pointsValue++;
            }
            $this->incPlayerVariable('player_score', self::getActivePlayerId(), $pointsValue);
        }
        
        //remove tile from board
        $sql = "update tiles set location='discard' where tile_id='".$match["tile_id"]."'";
        
        self::DbQuery($sql);
        
        //remove 1 dirt from active player
        $this->incPlayerVariable('player_dirt', self::getActivePlayerId(), -1);
        
        //must notify players
        if ($pointsValue > 0)
        {
            self::notifyAllPlayers( "pheromoneCleared", clienttranslate( '${player_name} pays 1 dirt to clear a pheromone tile, and scores ${points} points)' ), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'tile_id' => $match["tile_id"],
                'points'=> $pointsValue,
                'dirtCount'=> $this->getPlayerVariable('player_dirt', self::getActivePlayerId())
            ));
        }
        else
        {
            self::notifyAllPlayers( "pheromoneCleared", clienttranslate( '${player_name} pays 1 dirt to clear a pheromone tile' ), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'tile_id' => $match["tile_id"],
                'dirtCount'=> $this->getPlayerVariable('player_dirt', self::getActivePlayerId())
            ));         
        }
        
        $this->gamestate->nextstate("workerUsed");
    }
    
    function onMultiTileChosen($type_id)
    {
        self::checkAction( 'selectTile' );
                
        //save this tile to board. Move all others back to storage.
        //update scores etc. Use common function for this
        $sql = "select tile_id from tiles where location = 'board_temp' and type_id='".$type_id."'";
        $tile_id = self::getUniqueValueFromDb($sql);
        
        $sql = "update tiles set location='board' where tile_id='".$tile_id."'";
        self::DbQuery($sql);
        
        //handle special case for 9a/9b
        if ($type_id == "9a"|| $type_id == "9b")
        {
            $sql = "update tiles set subtype_id='".$type_id."' where tile_id='".$tile_id."'";
            self::DbQuery($sql);            
        }
        
        $this->processPlacedTile($tile_id);
        $this->cleanupTempTiles();  
        
        $this->gamestate->nextstate("tilePlaced");       
    }
    
    //X_YxX_YxX_Y etc - can't use comma to separate as invalid arg type
    function onConfirmTile($hexes)
    {
        self::checkAction( 'confirmTile' );
        
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
        //if they have the level+1 or hex+1 event, these are accounted for here
        $chosenEvent = $this->getPlayerVariable('player_event_selected', self::getActivePlayerId());            
        if ($chosenEvent == $this->eventLevel || $chosenEvent == $this->eventHex)
        {
            $colonyLevel++;
        }
                
        //get the actual valid vectors, and somehow match them.
        //this is an array of arrays. Each array is a list of hexes that might match
        //those supplied. Find out!		
        $tilePlacements = $this->getTilePlacements(false);
		                        
        $errMsg = "";
        $matches = array();
        $matchedTypes = array();
                
        //strings can be more easily sorted, so we can compare vectors
        foreach($tilePlacements as $tilePlacement)
        {
            //no point adding more than one orientation of the same tile as a match
            if (in_array($tilePlacement->Type, $matchedTypes))
            {
                continue;
            }
            
            $targetVectors = array();
            foreach($tilePlacement->Tiles as $hex)
            {
                $targetVectors[] = $hex["x"]."_".$hex["y"];
            }
            sort($targetVectors);
            
            if (count($vectors) == count($targetVectors))
            {
                if (count(array_diff($vectors, $targetVectors)) == 0 && count(array_diff($targetVectors, $vectors)) == 0 )
                {                   
                    if ($tilePlacement->Type <= 8)
                    {
                        $requiredLevel = $this->playerTileTypes[$tilePlacement->Type]["levelRequired"];
                    }
                    else
                    {
                        $requiredLevel = $this->sharedTileTypes[$tilePlacement->Type]["levelRequired"];                        
                    }
                    
                    
                    //validate colony level and/or resource requirements
                    if ($requiredLevel > $colonyLevel)
                    {
                        $errMsg = clienttranslate('Colony level too low');                        
                        continue;
                    }
                    
                    //if it's 9,require EITHER food or stone
                    if ($tilePlacement->Type == 9)
                    {
                        $foodCount = $this->getPlayerVariable('player_food', self::getActivePlayerId());
                        $stoneCount = $this->getPlayerVariable('player_stone', self::getActivePlayerId());
                        if ($foodCount + $stoneCount == 0)
                        {
                            $errMsg = clienttranslate('Insufficient resources to build an Aphid farm or Scavenging tile');                            
                            continue;
                        }
                    }
                    
                    //validate any tiles remaining, message if ran out of matches
                    $sql = "select distinct type_id from tiles where type_id='".$tilePlacement->Type."' and location = 'storage' and (player_id = '".self::getActivePlayerId()."' OR player_id='')";
                    $pheromoneTypes = self::getCollectionFromDb($sql);
                    if (count($pheromoneTypes) == 0)
                    {
                        $errMsg = clienttranslate('No matching tiles remaining');                        
                        continue;
                    }
                    
                    $matchedTypes[] = $tilePlacement->Type;
                    $matches[] = $tilePlacement;
                    continue;
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
        
        //var_dump($matchedTypes);
        //var_dump($matches);		
        //die('ok');
                
        //if we only got one match, process it. Otherwise the user needs to pick one
        //and we need to not forget the spaces chosen or tiles available!
        
        //IMPORTANT! type 9 is a double sided tile, so offer two choices
        //if this is present too (with BOTH resources)
        $foodCount = $this->getPlayerVariable('player_food', self::getActivePlayerId());
        $stoneCount = $this->getPlayerVariable('player_stone', self::getActivePlayerId());
        
        if (count($matches) >1 || ((count($matches) == 1) && $matches[0]->Type == 9 && $foodCount > 0 && $stoneCount > 0))
        {
            //save all matches to 'temporary' board state so we don't lose them
            foreach($matches as $tilePlacement)
            {				
                $this->saveTileToBoard($tilePlacement->Tiles, $tilePlacement->Type, $tilePlacement->Rotation, $tilePlacement->IsFlipped, $tilePlacement->OriginHex, true);
            }
            $this->gamestate->nextstate("multiTileChoice");
            return;
        }
        else if (count($matches) == 1)
        {
            //so, we got a match. We need to know what type of tile this was
            //and figure out how we will draw it on the board                   
                    
            //save to db and process to next state			
            foreach($matches as $tilePlacement)
            {
                $tileID = $this->saveTileToBoard($tilePlacement->Tiles, $tilePlacement->Type, $tilePlacement->Rotation, $tilePlacement->IsFlipped, $tilePlacement->OriginHex, false);
            }
			$this->processPlacedTile($tileID);            
			                    
            $this->gamestate->nextstate("tilePlaced");
            return;            
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
    
    function onAtelierClicked($action)
    {
        self::checkAction( 'selectHex' );
        
        $playerId = self::getActivePlayerId();
        
        //TODO check valid
        $validMoves = $this->argAtelier();
        
        if (!in_array($action, $validMoves["atelierActions"]))
        {
            throw new feException("Invalid atelier action ".$action);
        }
        
        //handle actions
        if ($action == "nurse")
        {
            $this->incPlayerVariable("player_food", $playerId, -2);
            $this->incPlayerVariable("player_larvae", $playerId, -2);
            $this->incPlayerVariable("player_nurses", $playerId, 1);
            
            self::notifyAllPlayers( "atelierNurseGained", clienttranslate( '${player_name} gains a nurse' ), array(
                'player_id' => $playerId,
                'player_name' => self::getActivePlayerName(),
                'foodCount' => $this->getPlayerVariable('player_food', $playerId),
                'larvaeCount' => $this->getPlayerVariable('player_larvae', $playerId),
                'nurseCount' => $this->getPlayerVariable('player_nurses', $playerId),
            ));
            
            //mark this atelier action as used
            $this->incPlayerVariable('player_atelier_used', $this->getActivePlayerId(), 1);
            
            //remove one atelier alloction
            $this->removeAtelierAction($this->getActivePlayerId());
            
            //manage next state            
            $this->gamestate->nextstate("nurse");    
            return;
        }
        else if($action == "level")
        {
            $colonyLevel = $this->getPlayerVariable("player_colony_level", $playerId);
            if ($colonyLevel == 0)
            {
                $this->incPlayerVariable("player_dirt", $playerId, -2);
            }
            if ($colonyLevel == 1)
            {
                $this->incPlayerVariable("player_dirt", $playerId, -2);
                $this->incPlayerVariable("player_stone", $playerId, -1);
            }
            if ($colonyLevel == 2)
            {
                $this->incPlayerVariable("player_stone", $playerId, -3);
            }
            $this->incPlayerVariable("player_colony_level", $playerId, 1);
            
            self::notifyAllPlayers( "atelierLevelGained", clienttranslate( '${player_name} gains a colony level' ), array(
                'player_id' => $playerId,
                'player_name' => self::getActivePlayerName(),
                'dirtCount' => $this->getPlayerVariable('player_dirt', $playerId),
                'stoneCount' => $this->getPlayerVariable('player_stone', $playerId),
                'colonyLevel' => $this->getPlayerVariable('player_colony_level', $playerId),
            ));
            
            //mark this atelier action as used
            $this->incPlayerVariable('player_atelier_used', $this->getActivePlayerId(), 2);
            
            //remove one atelier alloction
            $this->removeAtelierAction($this->getActivePlayerId());
            
            //manage next state            
            $this->gamestate->nextstate("level");    
            return;
        }
        else if ($action == "tunnel")
        {
            //TODO
            
            //$this->incPlayerVariable('player_atelier_used', $this->getActivePlayerId(), 4);
            //$this->removeAtelierAction($this->getActivePlayerId());
            $this->gamestate->nextstate("tunnel");
            return;
        }
        
        throw new feException("atelier action ".$action." not implemented");
    }
    
    function onHexClicked($x, $y)
    {
        self::checkAction( 'selectHex' );
        
        //check valid
        $hex = $x."_".$y;
        
        $currentState = $this->gamestate->state();
		$currentState = $currentState["name"];
		        
        if ($currentState != "placeWorker" && $currentState != "moveWorker" && $currentState != "placeTunnel")
        {
            throw new feException("Unexpected hex click state ".$currentState);
        }
        
        //place tunnel state. Validate & add the new tunnel
        if ($currentState == "placeTunnel")
        {
            $args = $this->argPlaceTunnel();
            if (!in_array($hex, $args["validTunnelSpaces"]))
            {
                throw new feException("Invalid tunnel choice ".$hex);
            }
            
            //place a tunnel!
            //Move it from player store to board
            //notify player
            $tiles = array();
            $tile = array("x"=>$x, "y"=>$y);
            $tiles[] = $tile;
            $tileID = $this->saveTileToBoard($tiles, 1, 0, 0, "", false);
            $this->processPlacedTile($tileID);           
            
            //mark this atelier action as used
            $this->incPlayerVariable('player_atelier_used', $this->getActivePlayerId(), 4);
            
            //remove one atelier alloction
            $this->removeAtelierAction($this->getActivePlayerId());
            
            $this->gamestate->nextstate("tilePlaced");
            return;
        }
        
        //place worker state. Must have chosen a tunnel
        if ($currentState == "placeWorker")
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
            
            $movePoints = $this->defaultMovePoints;
            $chosenEvent = $this->getPlayerVariable('player_event_selected', self::getActivePlayerId());            
            if ($chosenEvent == $this->eventMove)
            {
                $movePoints +=3;
            }
            
            $this->setGameStateValue('active_worker_moves', $movePoints);
            
            
            // place a worker on the board (move it from player's board if possible)
            self::notifyAllPlayers( "workerPlaced", clienttranslate( '${player_name} places a worker on a colony entrance' ), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'x' => $x,
                'y' => $y,
                'color'=>$this->getPlayerVariable('player_color_name', self::getActivePlayerId())
                ));
            
            $this->gamestate->nextstate("workerPlaced");
            return;
        }
        
        //move worker
        if ($currentState == "moveWorker")
        {
            $args = $this->argMoveWorker();
            if (!in_array($hex, $args["validMoves"]))
            {
                throw new feException("Invalid hex choice ".$hex);
            }
            
            $oldX = $this->getGameStateValue("active_worker_x");
            $oldY = $this->getGameStateValue("active_worker_y");
            $oldTile = $this->getTileByXY($oldX, $oldY);
            $newTile = $this->getTileByXY($x, $y);
            if ($oldTile != false && $newTile != false && $oldTile["tile_id"] == $newTile["tile_id"])
            {
                //same tile, don't decrease move
            }
            else
            {
                $moves = $this->getGameStateValue('active_worker_moves');
                $moves--;
                $this->setGameStateValue('active_worker_moves', $moves);
            }
            
            //okay! mark this hex has having an active worker of this colour. (how?)
            $this->setGameStateValue("active_worker_flag", 1);
            $this->setGameStateValue("active_worker_x", $x);
            $this->setGameStateValue("active_worker_y", $y);                   
            
            //place a worker on the board (move it from player's board if possible)
            self::notifyAllPlayers( "workerMoved", clienttranslate( '${player_name} moves a worker' ), array(
                'player_id' => self::getActivePlayerId(),
                'player_name' => self::getActivePlayerName(),
                'x' => $x,
                'y' => $y,
                ));
            
            //if this hex blongs to an opponent (and ISN'T the same tile as before), lose a soldier
            if ($newTile != false && 
                    $oldTile["tile_id"] != $newTile["tile_id"] && 
                    $newTile['player_id'] > 0 && 
                    $newTile['player_id'] != self::getActivePlayerId())
            {
                //throw new feException("Handle tresspassing here");
                $soldierCount = $this->getPlayerVariable("player_soldiers", self::getActivePlayerId());
                if ($soldierCount == 0)
                {
                    throw new feException("Invalid move, no soldiers");
                }
                $this->incPlayerVariable("player_soldiers", self::getActivePlayerId(), -1);
                $soldierCount = $this->getPlayerVariable("player_soldiers", self::getActivePlayerId());
                self::notifyAllPlayers( "soldierLost", clienttranslate( '${player_name} entered an opposing hex and loses a soldier' ), array(
                    'player_id' => self::getActivePlayerId(),
                    'player_name' => self::getActivePlayerName(),                    
                    'soldiers' => $soldierCount
                ));
            }
            
            //if this hex contains a bug, eat it
            if ($newTile['type_id'] > 10)
            {
                $soldierCost = 1;
                if ($newTile['type_id'] == 11)
                {
                    $foodGain = 2;
                    $scoreGain = 0;
                }
                if ($newTile['type_id'] == 12)
                {
                    $foodGain = 1;
                    $scoreGain = 2;
                }
                if ($newTile['type_id'] == 13)
                {
                    $soldierCost = 2;
                    $foodGain = 1;
                    $scoreGain = 4;
                }
                
                $soldierCount = $this->getPlayerVariable("player_soldiers", self::getActivePlayerId());
                if ($soldierCount < $soldierCost)
                {
                    throw new feException("Invalid move, not enough soldiers");
                }
                
                $this->incPlayerVariable("player_soldiers", self::getActivePlayerId(), -$soldierCount);
                $soldierCount = $this->getPlayerVariable("player_soldiers", self::getActivePlayerId());
                
                //TODO - move the bug tile to the player board, give food etc              
                $bugResName = $this->bugTileTypes[$newTile['type_id']]["resourceName"];
                
                self::notifyAllPlayers( "soldierLost", clienttranslate( '${player_name} captured a ${bugname} and lost ${soldierCost} soldier(s)' ), array(
                    'player_id' => self::getActivePlayerId(),
                    'player_name' => self::getActivePlayerName(),                    
                    'soldiers' => $soldierCount,
                    'soldierCost' => $soldierCost,
                    'bugname' => $this->resourceNames[$bugResName]
                ));
                
                if ($scoreGain > 0 && $this->isVPBonusActive(self::getActivePlayerId()))
                {
                    $scoreGain++;
                }
                $this->incPlayerVariable("player_food", self::getActivePlayerId(), $foodGain);
                $this->incPlayerVariable("player_score", self::getActivePlayerId(), $scoreGain);
                
                if ($scoreGain == 0)
                {
                    self::notifyAllPlayers( "bugEaten", clienttranslate( '${player_name} gains ${foodGain} food' ), array(
                        'player_id' => self::getActivePlayerId(),
                        'player_name' => self::getActivePlayerName(),                    
                        'foodGain' => $foodGain,                    
                        'foodCount' => $this->getPlayerVariable('player_food', self::getActivePlayerId()),
                        'bugTileID' => $newTile["tile_id"]
                    ));
                }
                else {
                    self::notifyAllPlayers( "bugEaten", clienttranslate( '${player_name} gains ${foodGain} food and ${scoreGain} points' ), array(
                        'player_id' => self::getActivePlayerId(),
                        'player_name' => self::getActivePlayerName(),                    
                        'foodGain' => $foodGain,                    
                        'scoreGain' => $scoreGain,                    
                        'foodCount' => $this->getPlayerVariable('player_food', self::getActivePlayerId()),
                        'bugTileID' => $newTile["tile_id"]
                    ));
                }               
            }
            
            
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
        
        self::checkAction( 'activateColony' );
        
        //check valid
        $validMoves = $this->argPlaceWorker();
        $colonyStr = "colony_".$slot;
        
        if (!in_array($colonyStr, $validMoves["availableColony"]))
        {
            throw new feException("Invalid selection ".$colonyStr);
        }
        
        //mark used
        $colonyUsed = $this->getPlayerVariable("player_colony_used", self::getActivePlayerId());
        $colonyUsed += pow(2, $slot);
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
                
                $points = 2;
                if ($points > 0 && $this->isVPBonusActive(self::getActivePlayerId()))
                {
                    $points++;
                }
                
                $this->incPlayerVariable("player_score", self::getActivePlayerId(), $points);
                break;
            default:
                throw new feException("Invalid colony slot ". $slot);
        }
                
        self::notifyAllPlayers( "colonyActivated", clienttranslate( '${player_name} activates colony level ${slot} and gains ${num} ${resname}' ), array(
            'player_id' => self::getActivePlayerId(),
            'player_name' => self::getActivePlayerName(),
            'slot' => $slot,
            'num' => $rescount,
            'resname' => $resname,
            'larvaeCount' => $this->getPlayerVariable("player_larvae", self::getActivePlayerId()),
            'foodCount' => $this->getPlayerVariable("player_food", self::getActivePlayerId()),
            'stoneCount' => $this->getPlayerVariable("player_stone", self::getActivePlayerId()),
            'dirtCount' => $this->getPlayerVariable("player_dirt", self::getActivePlayerId())
        ) );
        
        //next worker
        $this->gamestate->nextState("workerFinished");
    }
    
    function allocateNurse($type, $slot){
        
        self::checkAction( 'allocateNurse' );
        
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
    
    function onHarvestChosen($hexes){
        
        self::checkAction( 'chooseHarvest' );
        
        $currentPlayerID = self::getCurrentPlayerId();
        
        //validate!
        //at least one resource selected from every tile
        //no more than one resource selected from any tile
        //if harvest event chosen, allow 3 extra
        
        $sql = "SELECT * FROM tiles where player_id = '".$currentPlayerID."' and location='board'";
        $playerTiles = self::getCollectionFromDB( $sql );
        
        $selectedEvent = $this->getPlayerVariable('player_event_selected', $currentPlayerID);
        $extraAllocations = 0;
        if ($selectedEvent == $this->eventHarvest)
        {
            $extraAllocations = 3;
        }
        
        $hexIDs = explode("x", $hexes);
            
        //check each tile. If it has no resources chosen, throw an error
        //if it has many, throw an error if the number extra chosen exceeds extraAllocations
        foreach($playerTiles as $tile)
        {
            $tileID = $tile['tile_id'];
            $myrmesTile = new MyrTile($tile, $this->playerTileTypes, $this->sharedTileTypes);
            
            if ($myrmesTile->getTileCategory() == "tunnel")
            {
                continue;
            }
            
            $resSelectedCount = 0;
            
            for ($i=$myrmesTile->Size;$i>0;$i--)
            {
                
                if (in_array($myrmesTile->getHexID($i), $hexIDs))
                {
                    $resSelectedCount++;
                }
            }  
            
            if ($myrmesTile->hasResources() && $resSelectedCount == 0)
            {
                self::notifyPlayer( self::getCurrentPlayerId(), "tilePlacementInvalid", "", 
                    array('errMsg'=>'You must select a cube from all tiles ')           
                );  
                return;                
            }
            else if ($resSelectedCount > 1)
            {
                if ($myrmesTile->isScavengingTile())
                {
                    self::notifyPlayer( self::getCurrentPlayerId(), "tilePlacementInvalid", "", 
                        array('errMsg'=>'Harvest +3 cannot be used on scavenging tile')           
                    );  
                    return;
                }
                    
                $extraAllocations -= ($resSelectedCount-1);
                if ($extraAllocations < 0)
                {
                    if ($selectedEvent == $this->eventHarvest)
                    {
                        self::notifyPlayer( self::getCurrentPlayerId(), "tilePlacementInvalid", "", 
                            array('errMsg'=>'Only 3 extra cubes can be harvested')           
                        );  
                        return;
                    }
                    else
                    {
                        self::notifyPlayer( self::getCurrentPlayerId(), "tilePlacementInvalid", "", 
                            array('errMsg'=>'Only one resource can be collected from each tile ')           
                        );  
                        return;
                    }
                }                
            }
        }
        
        //passed! save to DB but do NOT update screen until all players have chosen
        //maybe leave the tiles highlighted in some way instead of clearing?
        //this would also require working from game load state though...
        
        foreach($playerTiles as $tile)
        {
            $tileID = $tile['tile_id'];
            $myrmesTile = new MyrTile($tile, $this->playerTileTypes, $this->sharedTileTypes);
            
            if ($myrmesTile->getTileCategory() == "tunnel")
            {
                continue;
            }       
            
            $harvestSelected = 0;
            
            for ($i=$myrmesTile->Size;$i>0;$i--)
            {
                if (in_array($myrmesTile->getHexID($i), $hexIDs))
                {
                    $harvestSelected += pow(2, ($i-1));             
                }
            }
            
            $sql = "update tiles set selected_harvest_hexes = '".$harvestSelected."' where tile_id = '".$tileID."'";
            self::DbQuery( $sql );            
        }
              
        $this->gamestate->setPlayerNonMultiactive( $currentPlayerID, "");
    }
       
    function onStorageDiscard($resType){
        
        self::checkAction( 'discard' );
        
        $currentPlayerID = self::getCurrentPlayerId();
                
        $res_name = "null";
        
        if ($resType == "FOOD")
        {
            $res_name = $this->resourceNames["food"];
            $this->incPlayerVariable("player_food", $currentPlayerID, -1);
        }
        if ($resType == "DIRT")
        {
            $res_name = $this->resourceNames["dirt"];
            $this->incPlayerVariable("player_dirt", $currentPlayerID, -1);
        }
        if ($resType == "STONE")
        {
            $res_name = $this->resourceNames["stone"];
            $this->incPlayerVariable("player_stone", $currentPlayerID, -1);
        }
        
        //todo - notify everyone
        self::notifyAllPlayers( "discard", clienttranslate( '${player_name} discards 1 ${res_name}' ), array(
            'player_name' => self::getCurrentPlayerName(),
            'player_id' => $currentPlayerID,
            'foodCount'=> $this->getPlayerVariable('player_food', $currentPlayerID),
            'dirtCount'=> $this->getPlayerVariable('player_dirt', $currentPlayerID),
            'stoneCount'=> $this->getPlayerVariable('player_stone', $currentPlayerID),
            'res_name'=>$res_name
        ) );
        
        //do they need to discard more?
        if (!$this->colonyStorageExceeded($currentPlayerID))
        {
            $this->gamestate->setPlayerNonMultiactive( $currentPlayerID, "");
        }
    }
    
    function allocateNurseFinished(){
        
        //notify all players who has finished, might encourage them to speed up
        //also, when this notification gets to the active player, deactivate their stuff
        
        self::checkAction( 'finished' );
        
        $currentPlayerID = self::getCurrentPlayerId();
                      
        self::notifyPlayer( self::getCurrentPlayerId(), "yourNursesAllocated", "", 
            array()
        );
        
        self::notifyAllPlayers( "nursesAllocated", clienttranslate( '${player_name} has chosen an event and finished allocating nurses' ), array(
            'player_name' => self::getCurrentPlayerName()
        ) );
        
        $this->gamestate->setPlayerNonMultiactive( $currentPlayerID, "");        
    }
    
    function onMultiPass()
    {
        self::checkAction( 'pass' );
        $this->gamestate->setPlayerNonMultiactive( self::getCurrentPlayerId(), "");
    }
    
    function pass(){ 
        self::checkAction( 'pass' );
        $this->gamestate->nextstate("pass");             
    }

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments //argSomething()
////////////

    function argPlaceTunnel()
    {
        $playerId = $this->getActivePlayerId();
        
        //return all spaces empty & adjacent to a player owned tile
        $result = array();
        $result["validTunnelSpaces"] = array();
        
        //$sql = "select tile_id,x1,y1,x2,y2,x3,y3,x4,y4,x5,y5,x6,y6 from tiles where player_id='".self::getActivePlayerId()."'";
        //$tiles = self::getCollectionFromDb($sql);
        
        $sql = "select tile_id, type_id, player_id, location, x1, y1, x2, y2, x3, y3, x4, y4, x5, y5, x6, y6 from tiles where player_id='".$playerId."'";
        $playerTiles = self::getCollectionFromDb($sql);
        
        $sql = "select tile_id, type_id, player_id, location, x1, y1, x2, y2, x3, y3, x4, y4, x5, y5, x6, y6 from tiles";
        $allTiles = self::getCollectionFromDb($sql);
                      
                
        $hexGrid = new MyrmesHexGrid($this->boardSpaces, $allTiles, $this->getGameStateValue("player_count"), 0, $playerId);
                
        foreach($playerTiles as $tile)
        {
            for ($i=1; $i<=6; $i++)
            {
                $x = $tile["x".$i];
                $y = $tile["y".$i];

                if ($x != 0 && $y != 0)
                {
                    //get the adjacent tiles  
                    $neighbours = $hexGrid->GetNeighbours($x, $y);
                    
                    foreach($neighbours as $neighbour)
                    {
                        //need to reject the ones with any tileID
                        if ($neighbour["nodeTileID"] == 0)
                        {                            
                            $result["validTunnelSpaces"][] = $neighbour["x"]."_".$neighbour["y"];
                        }
                    }                    
                }
            }            
        }
        
        return $result;
    }
    
    function argAtelier()
    {
        //return to active player all valid atelier actions not yet performed
        
        $result = array();
        $result["convertLarvae"] = false;
        $result["atelierActions"] = array();
        $playerID = self::getActivePlayerId();
                
        
        //todo - can't use same one twice
        
        //level up (requirements vary by current level)
        $currentLevel = $this->getPlayerVariable('player_colony_level', $playerID);
        $atelierUsed = $this->getPlayerVariable('player_atelier_used', $this->getActivePlayerId());
        $dirt = $this->getPlayerVariable('player_dirt', $playerID);
        $stone = $this->getPlayerVariable('player_stone', $playerID);
        
        //todo - objective completion
        if ($atelierUsed >=8)
        {
            $atelierUsed -= 8;
        }
        else
        {
     
        }
        
        if ($atelierUsed >=4)
        {
            $atelierUsed -=4;
        }
        else
        {
            //new tunnel (if they aren't all placed and 1+ available adjacent hex)
            $sql = "select * from tiles where type_id=1 and player_id = '".$playerID."' and location != 'board'";
            $availableTunnels = $this->getCollectionFromDb($sql);
        
            if (count($availableTunnels) > 0)
            {
                $result["atelierActions"][] = "tunnel";
            }
        }
        
        if ($atelierUsed >= 2)
        {
            $atelierUsed -= 2;
        }
        else
        {
            if ($currentLevel == 0 && $dirt >= 2)
            {
                $result["atelierActions"][] = "level";
            }
            else if ($currentLevel == 1 && $dirt >= 2 && $stone >= 1)
            {
                $result["atelierActions"][] = "level";
            }       
            else if ($currentLevel == 1 && $dirt >= 2 && $stone >= 1)
            {
                $result["atelierActions"][] = "level";
            }             
        }
        
        if ($atelierUsed >= 1)
        {
            $atelierUsed -=1;
        }
        else
        {
            //create new nurse (2f + 2l)
            $food = $this->getPlayerVariable('player_food', $playerID);
            $larvae = $this->getPlayerVariable('player_larvae', $playerID);
            
            $nursesUsed = $this->getPlayerVariable('player_nurses', $playerID);
            //TODO - count objective nurse uses also
            
            if ($food >= 2 && $larvae >= 2 && $nursesUsed < $this->maxNurseCount)
            {
                $result["atelierActions"][] = "nurse";
            }             
            
            if ($food < 2 && $larvae >= 3)
            {
                $result["convertLarvae"] = true;
            }
        }
               
        return $result;
    }
    
    function argMultiTile()
    {
        //send the ids of tiles in board_temp to choose from
        $result = array();
        
        $sql = "select type_id from tiles where location = 'board_temp'";
        $tiles = self::getObjectListFromDb($sql, true);
        
        //split type 9 into 9a and 9b
        if (in_array(9, $tiles))
        {
            if(($key = array_search(9, $tiles)) !== false) {
                unset($tiles[$key]);
            }
            
            $foodCount = $this->getPlayerVariable('player_food', self::getActivePlayerId());
            $stoneCount = $this->getPlayerVariable('player_stone', self::getActivePlayerId());
            
            if ($foodCount > 0)
            {
                array_push($tiles,"9b");   
            }
            if ($stoneCount > 0)
            {
                array_push($tiles,"9a");   
            }                     
        }
        
        $result["tileTypes"] = array_values($tiles);
        $result["color"] = $this->getPlayerVariable('player_color_name', self::getActivePlayerId());
        return $result;
    }
    
    function argPlaceTile()
    {
        //player chose to place a tile. Picks hexes and confirms
        // Show list of valid tiles as buttons?
        //or enable click on tiles from near player board or something?
        
        //make everything valid within 3 hexes clickable
        $x = $this->getGameStateValue("active_worker_x");
        $y = $this->getGameStateValue("active_worker_y");
        
        $sql = "select tile_id, type_id, player_id, location, x1, y1, x2, y2, x3, y3, x4, y4, x5, y5, x6, y6 from tiles ";
        $tiles = self::getCollectionFromDb($sql);
                      
        $playerId = $this->getActivePlayerId();
        $soldierCount = $this->getPlayerVariable("player_soldiers", $playerId);
        
        $searchNodeData = new MyrmesHexGrid($this->boardSpaces, $tiles, $this->getGameStateValue("player_count"), $soldierCount, $playerId);
    
        $moves = akPathfinding::FindAllDestinations($x, $y, $searchNodeData, 2);
        
        $result = array();
        $result["hexes"] = array();
        foreach($moves as $move)
        {
            //don't include current space
            if ($x != $move->X || $y != $move->Y)
            {
                //don't include existing tiles
                $found = false;
                foreach($tiles as $tile)
                {
                    if (!$found && $tile["location"] == "board" &&
                            (
                            ($tile["x1"] == $move->X && $tile["y1"] == $move->Y) ||
                            ($tile["x2"] == $move->X && $tile["y2"] == $move->Y) ||
                            ($tile["x3"] == $move->X && $tile["y3"] == $move->Y) ||
                            ($tile["x4"] == $move->X && $tile["y4"] == $move->Y) ||
                            ($tile["x5"] == $move->X && $tile["y5"] == $move->Y) ||
                            ($tile["x6"] == $move->X && $tile["y6"] == $move->Y)
                            )
                       )
                    {
                        $found = true;
                    }
                }
                if (!$found)
                {
                    $result["hexes"][] = $move->X."_".$move->Y;
                }
            }
        }
        return $result;
    }
    
    function argMoveWorker(){
        //var_dump("argMoveWorker");
        //will depend on the current worker's location, blocked tiles, etc.
        //This one will be complicated.
        //If on an empty hex, can move to an adjacent hex
        //If on a special tile, can move to any hex adjacent to any space on the pheromone
        //TODO - check on boiteajeux about moving on special tiles and opposition special tiles.
        //Can't move through water
        //Can't move off board
        //Can't move onto enemy tunnels
        //Can't move onto enemy tiles UNLESS player has a soldier
        //Can't move onto bugs UNLESS enough soldiers are present
        
        $x = $this->getGameStateValue("active_worker_x");
        $y = $this->getGameStateValue("active_worker_y");
        
        $sql = "select tile_id, type_id, player_id, x1, y1, x2, y2, x3, y3, x4, y4, x5, y5, x6, y6 from tiles ";
        $tiles = self::getCollectionFromDb($sql);
        
        $playerId = $this->getActivePlayerId();
        $soldierCount = $this->getPlayerVariable("player_soldiers", $playerId);
                      
        $searchNodeData = new MyrmesHexGrid($this->boardSpaces, $tiles, $this->getGameStateValue("player_count"), $soldierCount, $playerId);
               
        $result = array();
        $result["moves"]= $this->getGameStateValue('active_worker_moves');
        $result["canClearPheromone"] = false;
        $result["validMoves"] = array();
        
        if ((int)$result["moves"] > 0)
        {
            $moves = akPathfinding::FindAllDestinations($x, $y, $searchNodeData, 1);
        
            foreach($moves as $move)
            {
                $result["validMoves"][] = $move->X."_".$move->Y;
            }
        }
        
        //if the current tile is not empty, no tile can be placed.
        $match = $this->getTileByXY($x,$y);        
        if ($match != false)
        {
            $result["canPlaceTile"] = 0;
            
            //can we clear a pheromone?
            if ($match["type_id"] > 1 && $match["type_id"] < 9 && $this->getPlayerVariable('player_dirt', $playerId) >=1)
            {
                if ($match["res1"] == "" && $match["res2"] == "" && $match["res3"] == "" && $match["res4"] == "" && $match["res5"] == "" && $match["res6"] == "")
                {
                    $result["canClearPheromone"] = true;
                }                
            }            
        }
        else
        {
            //TODO - I don't think this is considering colony level or supplies
            $tilePlacements = $this->getTilePlacements(true);
            $result["canPlaceTile"] = count($tilePlacements) > 0;            
        }       
        
        return $result;    
    }
    
    //TODO - move to MyrmesHexGrid class
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
                
        $x = $this->getGameStateValue("active_worker_x");
        $y = $this->getGameStateValue("active_worker_y");
        
        //if we only want the first match,we are checking for tiles this player can place
        //if we aren't, check all tiles.
        if ($returnFirstMatch)
        {
            $sql = "select distinct type_id from tiles where type_id > 1 and location = 'storage' and (player_id='' OR player_id = '".self::getActivePlayerId()."')";
        }
        else
        {
            //all placable tile types
            $sql = "select distinct type_id from tiles where type_id < 11 and type_id > 1";
        }
        
        $tileTypes = self::getCollectionFromDb($sql);
                        
                        
        foreach($tileTypes as $tileType)
        {
            $type_id = $tileType["type_id"];  
                                              
            $hexes = array();
            $flippedHexes = array();
                        
            if ($type_id == "2")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0));                
            }
            
            if ($type_id == "3" || $type_id == "9")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>0, "y"=>1));
            }
            
            if ($type_id == "4")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0));
            }
            
            if ($type_id == "5" || $type_id == "10")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>0, "y"=>1), array("x"=>1, "y"=>1));
            }
            
            //types 6 and 7 require mirror flipped versions
            if ($type_id == "6")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>2, "y"=>-1));
                $flippedHexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>1, "y"=>1));
            }
            
            if ($type_id == "7")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>1, "y"=>-1), array("x"=>2, "y"=>-1));
                //$flippedHexes = array(array("x"=> 0, "y"=>0), array("x"=>1, "y"=>0), array("x"=>2, "y"=>0), array("x"=>0, "y"=>1), array("x"=>1, "y"=>1));
            }
            
            if ($type_id == "8")
            {
                $hexes = array(array("x"=> 0, "y"=>0), array("x"=>-1, "y"=>1), array("x"=>0, "y"=>1), array("x"=>-2, "y"=>2), array("x"=>-1, "y"=>2), array("x"=>0, "y"=>2));
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
                                $match = new TileData($rotatedHexes, $i, $tileType["type_id"], $m, $hex);
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
        $colonyUsed = $this->getPlayerColonyUsed($player_id);
        $colonyLevel = $this->getPlayerVariable('player_colony_level', $player_id);
        
        $chosenEvent = $this->getPlayerVariable('player_event_selected', self::getActivePlayerId());            
        if ($chosenEvent == $this->eventLevel)
        {
            $colonyLevel++;
        }			
        
        $result['availableColony'] = array();
        if (!in_array('colony_0', $colonyUsed))
        {
            $result['availableColony'][] = 'colony_0';
        }
        if (!in_array('colony_1', $colonyUsed) && $colonyLevel >= 1)
        {
            $result['availableColony'][] = 'colony_1';
        }
        if (!in_array('colony_2', $colonyUsed) && $colonyLevel >= 2)
        {
            $result['availableColony'][] = 'colony_2a';
            $result['availableColony'][] = 'colony_2b';
        }
        if (!in_array('colony_3', $colonyUsed) && $colonyLevel >=3)
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
            $result["tunnels"][] = $tunnel["x1"]."_".$tunnel["y1"];
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
    
    function argHarvest(){        
        $privateInfos = array();
        
        //calculate which hexes the player can allocate, and which ones they can deallocate
        //return only this list
        
        $sql = "SELECT * FROM player ";
        $players = self::getCollectionFromDb( $sql );
        foreach($players as $player)
        {
            $private = $this->getPlayerHarvestAllocations($player['player_id']);
                        
            $privateInfos[$player['player_id']] = $private;
        }
                		
        return array("_private" => $privateInfos);
    }
    
    function argWinter(){
        
        $privateInfos = array();
        
        //calculate which hexes the player can allocate, and which ones they can deallocate
        //return only this list
        
        $sql = "SELECT * FROM player ";
        $players = self::getCollectionFromDb( $sql );
        foreach($players as $player)
        {
            $actions = array();
            $actions[] = "pass";
            $larvae = $this->getPlayerVariable("player_larvae", $player['player_id']);
            if ($larvae >= 3)
            {
                $actions[] = "convertLarvae";
            }
                        
            $privateInfos[$player['player_id']] = $actions;
        }
                
        return array("_private" => $privateInfos);
    }
    
    function argStorage(){
        
        $privateInfos = array();
        
        $sql = "SELECT * FROM player ";
        $players = self::getCollectionFromDb( $sql );
        foreach($players as $player)
        {
            $private = array();
            $private["foodCount"] = $player['player_food'];
            $private["dirtCount"] = $player['player_dirt'];
            $private["stoneCount"] = $player['player_stone'];
                        
            $privateInfos[$player['player_id']] = $private;
        }
                
        return array("_private" => $privateInfos);
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions //stState()
////////////

function stWorkerFinished()
    {
        $this->activeNextPlayer();
        $this->gamestate->nextState("");
    }
    
    function stWorkerUsed(){
        //remove a worker from the active player. Make sure this updates in all UIs and current
        //game state.
        $this->setGameStateValue("active_worker_x", 0);
        $this->setGameStateValue("active_worker_y", 0);
        $this->setGameStateValue("active_worker_flag", 0);
        $this->incPlayerVariable('player_workers', self::getActivePlayerId(), -1);
        self::notifyAllPlayers( "pause", "", array() ); 
        self::notifyAllPlayers( "activeWorkerRemoved", "", array(
                'player_id' => self::getActivePlayerId(),
                'workers' => $this->getPlayerVariable('player_workers', self::getActivePlayerId())
        ) ); 
        
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
    
    function stProcessHarvest() {
        //execute harvest!
        //give all players the resources they chose, make sure this gets animated
        //resource limit not applied till after atelier
        
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player)
        {
            $playerID = $player['player_id'];
            
            $sql = "SELECT * FROM tiles where player_id = '".$playerID."' and location='board'";
            $playerTiles = self::getCollectionFromDB( $sql );
            
            //allocate all chosen resources
            foreach($playerTiles as $tile)
            {
                $tileID = $tile['tile_id'];
                
                $myrmesTile = new MyrTile($tile, $this->playerTileTypes, $this->sharedTileTypes);
                
                //if this is a colony tile, you get 2VP
                if ($myrmesTile->isSubColony())
                {
                    $points = 2;
                    if ($points > 0 && $this->isVPBonusActive($playerID))
                    {
                        $points++;
                    }
        
                    $this->incPlayerVariable('player_score', $playerID, $points);
                    self::notifyAllPlayers( "harvest", clienttranslate( '${player_name} harvests 2 ${resource_name}' ), array(
                        'player_name' => $player['player_name'],
                        'player_id' => $player['player_id'],
                        'resource_name' => $this->resourceNames["vp"],
                        'hex_id' => $tileID,
                        'foodCount' => $this->getPlayerVariable('player_food', $playerID),
                        'dirtCount' => $this->getPlayerVariable('player_dirt', $playerID),
                        'stoneCount' => $this->getPlayerVariable('player_stone', $playerID),
                    ) );                     
                }
                
                //identify resource. Delete from resn in tiles table.
                //animate/move resx_y from board to player + delete. Increment resource count
                //todo - updates in colony
                
                $harvestHexes = $myrmesTile->getHarvestHexes();
                
                foreach($harvestHexes as $harvestHex)
                {
                    $index = $harvestHex[0];
                    $resource = $harvestHex[1];
                            
                    $sql = "update tiles set res".$index." = '' where tile_id = '".$tileID."'";
                    self::DbQuery($sql);
                                
                    if ($resource == "FOOD")
                    {
                        $this->incPlayerVariable('player_food', $playerID, 1);
                    }
                    if ($resource == "DIRT")
                    {
                        $this->incPlayerVariable('player_dirt', $playerID, 1);
                    }
                    if ($resource == "STONE")
                    {
                        $this->incPlayerVariable('player_stone', $playerID, 1);
                    }
                    
                    $resNameLower = strtolower($resource);
             
                    self::notifyAllPlayers( "harvest", clienttranslate( '${player_name} harvests 1 ${resource_name}' ), array(
                        'player_name' => $player['player_name'],
                        'player_id' => $player['player_id'],
                        'resource_name' => $this->resourceNames[$resNameLower],
                        'hex_id' => $index."_".$tileID,
                        'foodCount' => $this->getPlayerVariable('player_food', $playerID),
                        'dirtCount' => $this->getPlayerVariable('player_dirt', $playerID),
                        'stoneCount' => $this->getPlayerVariable('player_stone', $playerID),
                    ) );                     
                }
            }           
        }
        
        //now we remove any unpicked dirt/stone from scavenging tiles
        $sql = "select tile_id,x1,y1,x2,y2,x3,y3 from tiles where subtype_id='9b' and location='board'";
        $scavengingTiles = self::getCollectionFromDb($sql);
        foreach($scavengingTiles as $scavengingTile)
        {
            //var_dump($scavengingTile);
            $sql = "update tiles set res1='',res2='' where tile_id='".$scavengingTile["tile_id"]."'";           
            self::DbQuery($sql);
            
            $sql = "select tile_id,x1,y1,x2,y2,x3,y3,x4,y4,x5,y5,x6,y6,res1,res2,res3,res4,res5,res6 from tiles where tile_id='".$scavengingTile["tile_id"]."'";           
            $tile = self::GetObjectFromDb($sql);
            
            //remove them from the board
            self::notifyAllPlayers( "resourcesDestroyed", '', array(
                'tile' => $tile,                
            ));           
        }   
        
        $this->setGameStateValue("progress_indicator", 3);
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
        
        self::notifyAllPlayers( "season", clienttranslate( '${season} of year ${year}. The event is ${eventname}' ), array(
            'year' => $currentYear,
            'season'=> $this->seasonNames[$currentSeason],
            'eventname'=> $this->events[$currentEvent]["name"]
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
            if ($larvaeGain > 0 && $chosenEvent == $this->eventLarvae)
            {
                $larvaeGain += 2;
            }
            
            $larvae += $larvaeGain;
            $this->setPlayerVariable('player_larvae', $player['player_id'], $larvae);
                        
            
            //birth soldiers
            $soldiers = $player['player_soldiers'];
            $workers = $player['player_workers'];
            
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
            if ($soldierGain > 0 && $chosenEvent == $this->eventSoldier)
            {
                $soldierGain += 1;
            }
            
            $soldierWorkerExcess = 0;
            while (($soldiers + $soldierGain + $workers) > $this->maxWorkerCount)
            {
                $soldierWorkerExcess++;
                $soldierGain--;
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
            if ($workerGain > 0 && $chosenEvent == $this->eventWorker)
            {
                $workerGain += 1;
            }
            
            
            while (($soldiers + $workerGain + $workers) > $this->maxWorkerCount)
            {
                $soldierWorkerExcess++;
                $workerGain--;
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
                'eventName' => $this->events[$chosenEvent]["name"],
                'larvaeCount' => $larvae
            ) );
            
            self::notifyAllPlayers( "birthing", clienttranslate( '${player_name} births ${larvaeGain} larvae, ${soldierGain} soldiers, and ${workerGain} workers' ), array(
                'player_id' => $player['player_id'],
                'player_name' => $player['player_name'],
                'larvaeGain' => $larvaeGain,            
                'larvaeCount' => $larvae,
                'soldierGain' => $soldierGain,            
                'soldierCount' => $soldiers,
                'workerGain' => $workerGain,            
                'workerCount' => $workers
            ) );
            
            if ($soldierWorkerExcess > 0)
            {
                self::notifyAllPlayers( "excessBirthing", clienttranslate( '${player_name} fails to birth ${soldierWorkerExcess} soldiers/workers due to running out of tokens' ), array(
                    'player_id' => $player['player_id'],
                    'player_name' => $player['player_name'],            
                    'soldierWorkerExcess' => $soldierWorkerExcess
                ) );
            }
            self::notifyAllPlayers( "atelierAllocation", clienttranslate( '${player_name} sends ${atelierCount} nurses to the atelier' ), array(
                'player_name' => $player['player_name'],
                'atelierCount' => $atelierCount
            ) ); 
        }
        
        $this->setGameStateValue("progress_indicator", 1);
        $this->gamestate->nextstate('');
    }
    
    function stBirths(){
        //entering the births state, set all players active
        $this->gamestate->setAllPlayersMultiactive();
        $this->gamestate->nextstate('');
    }
    
    function stHarvest(){       
		
        //any food/dirt tiles should be populated, so the player can choose 
        $sql = "select tile_id,x1,y1,x2,y2,x3,y3,subtype_id from tiles where type_id='9' and location='board'";
        $scavengingTiles = self::getCollectionFromDb($sql);
        foreach($scavengingTiles as $scavengingTile)
        {
            //var_dump($scavengingTile);
            if ($scavengingTile["subtype_id"] == "9b")
            {
                $sql = "update tiles set res1='DIRT',res2='STONE' where tile_id='".$scavengingTile["tile_id"]."'";
            }
            else
            {
                $sql = "update tiles set res1='FOOD' where tile_id='".$scavengingTile["tile_id"]."'";
            }
            self::DbQuery($sql);
            
            $sql = "select tile_id,x1,y1,x2,y2,x3,y3,x4,y4,x5,y5,x6,y6,res1,res2,res3,res4,res5,res6 from tiles where tile_id='".$scavengingTile["tile_id"]."'";           
            $tile = self::GetObjectFromDb($sql);
            
            //place them on the board
            self::notifyAllPlayers( "resourcesPlaced", '', array(
                'tile' => $tile,                
            ));   
        }           
        
        $this->gamestate->setAllPlayersMultiactive();
        $this->setGameStateValue("progress_indicator", 2);
		$this->gamestate->nextstate('');
    }
    
    function stStorage(){
        $this->gamestate->setAllPlayersMultiactive();
        $this->setGameStateValue("progress_indicator", 4);
        $this->gamestate->nextstate('');
    }
    
    function stStorage2(){
        //anybody who is within their storage limit gets set to inactive
        $sql = "SELECT * FROM player ";
        $players = self::getCollectionFromDb( $sql );
        foreach($players as $player)
        {
            if (!$this->colonyStorageExceeded($player['player_id']))
            {
                $this->gamestate->setPlayerNonMultiactive( $player['player_id'], "");
            }
        }        
    }
    
    function stPreWinter(){
        $this->gamestate->setAllPlayersMultiactive();
        $this->setGameStateValue("progress_indicator", 5);
        $this->gamestate->nextstate('');
    }
    
    function stWinterFoodCheck(){
        //anybody who has enough food for winter OR less than 3 larvae gets set to inactive
        
        $sql = "SELECT * FROM player ";
        $players = self::getCollectionFromDb( $sql );
        
        $currentYear = $this->getGameStateValue('current_year');
        
        foreach($players as $player)
        {
            $larvae = (int)$player['player_larvae'];
            $soldiers = (int)$player['player_soldiers'];
            $foodRequired = 3 + $currentYear - $soldiers;
            $foodAvailable = (int)$player['player_food'];
            
            if ($foodRequired <= $foodAvailable || $larvae < 3)
            {
                $this->gamestate->setPlayerNonMultiactive( $player['player_id'], "");
            }
        }        
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
                
                $this->incPlayerVariable("player_score", $player['player_id'], -$vploss);
                
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
        
        $sql = "update player set player_atelier_used = 0";
        self::DbQuery( $sql );
        
        $sql = "update tiles set selected_harvest_hexes = 0";
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
        $this->setGameStateValue("progress_indicator", 0);
        
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
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
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
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
