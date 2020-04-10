<?php

class MyrTile
{
	private $tile;
        
	public $Size;
        
	//public $Type;
	//public $IsFlipped;
        //public $OriginHex

	function __construct($tile, $playerTileTypes, $sharedTileTypes)
	{
		$this->tile = $tile;
                $type_id = $tile['type_id'];
		//$this->Rotation = $rotation;
		//$this->Type = $type;
		//$this->IsFlipped = $isFlipped;
                //$this->OriginHex = $origin;
                if ($type_id < 9)
                {
                    $type = $playerTileTypes[$type_id];
                }
                else
                {
                    $type = $sharedTileTypes[$type_id];
                }
                $this->Size = $type["size"];  
	}
        
        public function getHarvestHexes()
        {
            $harvestHexes = array();
            
            $selectedHarvestHexes = $this->tile['selected_harvest_hexes'];
            
            for ($i=5; $i>= 0; $i--)
            {
                $value = pow(2, $i); //2^5 = 32, 2^0 = 1,etc
            
                if ($selectedHarvestHexes >= $value)
                {            
                    $selectedHarvestHexes -= $value;
                    $harvestHexes[] = array($i+1, $this->tile['res'.($i+1)] );
                }                
            }    
            
            return $harvestHexes;
        }
        
        public function getHexID($num)
        {
            return $this->tile['x'.$num]."_".$this->tile['y'.$num];
        }
        
        public function hasResourceOnHex($i)
        {
            $res = $this->tile['res'.$i];
            return $res != "";
        }
        
        public function hasResources()
        {
            for ($i=1;$i<=6;$i++)
            {
                $res = $this->tile['res'.$i];
                if ($res != "")
                    return true;
            }
            return false;
        }
        
        public function getTileCategory()
        {
            $type_id = $this->tile['type_id'];
            if ($type_id == 1)
            {
                return "tunnel";
            }
            else if ($type_id <= 8)
            {
                return "pheromone";
            }
            else if ($type_id <= 10)
            {
                return "special";
            }
            throw new feException("NOT RECOGNISED type ".$type_id);            
        }
        
        public function isSubColony()
        {
            return $this->tile['type_id'] == 10;
        }
        
        public function isScavengingTile()
        {
            return $this->tile['type_id'] == 9 && $this->tile['subtype_id'] == "9b";
        }
}

class TileData
{
	public $Tiles;
	public $Rotation;
	public $Type;
	public $IsFlipped;
        public $OriginHex;

	function __construct($tiles, $rotation, $type, $isFlipped, $origin)
	{
		$this->Tiles = $tiles;
		$this->Rotation = $rotation;
		$this->Type = $type;
		$this->IsFlipped = $isFlipped;
                $this->OriginHex = $origin;
	}
}

//This could really be more generic?
interface ISearchNodeData
{
    public function GetNeighbours($x1, $y1); //returns list of (Cost/X/Y)
}

class SearchNode
{
	public $Cost;
	public $Processed;
	public $X;
	public $Y;

	function __construct($x, $y, $cost, $processed)
	{
		$this->X = $x;
		$this->Y = $y;
		$this->Cost = $cost;
		$this->Processed = $processed;
	}
}

class AxialHexGrid
{
    public static function GetNeighbours($x, $y)
    {
        $moves = array(
            array("x"=> $x+1, "y"=>(int)$y),
            array("x"=> (int)$x, "y"=>$y+1),
            array("x"=> $x-1, "y"=>$y+1),
            array("x"=> $x-1, "y"=>(int)$y),
            array("x"=> (int)$x, "y"=>$y-1),
            array("x"=> $x+1, "y"=>$y-1)
        );
        return $moves;
    }
    
    public static function SetOrigin($hexes, $origin)
    {
        foreach($hexes as $key => $hex)
        {
            $hexes[$key]["x"] -= (int)$origin["x"];
            $hexes[$key]["y"] -= (int)$origin["y"];
        }
        
        return $hexes;        
    }
    
    //as below, but rotate an array of hexes
    public static function RotateHexes($hexes, $rotations)
    {
        foreach ($hexes as $key=>$hex)
        {
            $hexes[$key] = AxialHexGrid::RotateHex($hex, $rotations);
        }                       
        
        return $hexes;
    }
    
    //$vector = array("x"=>x, "y"=>y)
    //$rotations = 0-5 (clockwise iterations)
    public static function RotateHex($vector, $rotations)
    {   
        $x = $vector["x"];
        $y = $vector["y"];
        
        $cubic = array("x"=>$x, "y" => -$x-$y, "z" => $y);
        
        //perform cubic rotation
        //rotate 1 space clockwise
        //[ x,  y,  z] -> [-z, -x, -y]
        while ($rotations > 0)
        {
            $x = $cubic["x"];
            $y = $cubic["y"];
            $z = $cubic["z"];
            
            $cubic["x"] = -$z;
            $cubic["y"] = -$x;
            $cubic["z"] = -$y;
            
            $rotations--;
        }
        
        $axial = array("x"=> $cubic["x"], "y" => $cubic["z"]);
        return $axial;                        
    }
}


class akPathfinding
{
    public static function FindAllDestinations($x1, $y1, ISearchNodeData $searchNodeData, $maxCost)
    {
	//create a list of hexes, each flagged as processed or not
	//just starting hex to begin with

	//for each unprocessed hex
		//get all valid neighbours ISearchNodeData.GetNeighbours(x, y) not already on the list and their cost
		//add them to the list as unprocessed, with appropriate cost
	//repeat until no unprocessed hexes remain

	$searchNodeList = array();

	$searchNodeList[$x1."_".$y1] = new SearchNode($x1, $y1, 0, false);
		
	return akPathfinding::DoAStar($searchNodeList, $searchNodeData, $maxCost);
    }

    private static function DoAStar($searchNodeList, ISearchNodeData $searchNodeData, $maxCost)
    {
	//get the first unprocessed node in the list
	$searchNode = null;

	foreach($searchNodeList as $node)
	{
            if (!$node->Processed)
            {
                $searchNode = $node;
		break;
            }
	}

	if ($searchNode == null) //all processed
        {
            return $searchNodeList;
        }

	$searchNode->Processed = true;

	//use the custom link data to get neighbours of the search node, and their costs
        $neighbours = $searchNodeData->GetNeighbours($searchNode->X, $searchNode->Y); //returns array of x/y/cost arrays.

	foreach($neighbours as $newnode)
	{
            $newX = $newnode["x"];
            $newY = $newnode["y"];
            $newCost = $searchNode->Cost + $newnode["cost"];
            
            $key = $newX."_".$newY;
            
            //if this node already exists with an EQUAL or BETTER cost, then there's no point adding it
            if (array_key_exists($key, $searchNodeList))
            {
                if ($searchNodeList[$key]->Cost <= $newCost)
                {             
                    continue;
                }
            }
            
            //new node, so add it to the list (or replace existing) with the increased journey cost
            if ($newCost <= $maxCost)
            {            
                $searchNodeList[$key] = new SearchNode($newX, $newY, $newCost, false);
            }            
	}

	return akPathfinding::DoAStar($searchNodeList, $searchNodeData, $maxCost);
    }
}


//valid moves and costings will be specific to your grid
class MyrmesHexGrid implements ISearchNodeData
{
    private $boardState;
    private $tileData;
    private $soldierCount;
    private $currentPlayerId;
    
    //TODO - build pheromone list & whatever else may be necessary
    function __construct($boardState, $tileData, $playerCount, $soldierCount, $currentPlayerId)
    {
        $this->boardState = $boardState;
        $this->tileData = $tileData;
        $this->soldierCount = $soldierCount;
        $this->currentPlayerId = $currentPlayerId;
        
        //remove from boardstate according to player count
        foreach($this->boardState as $nodeX=>$nodeData)
        {
            foreach($nodeData as $nodeY=>$value)
            {
                if (!$this->IsPartOfBoard($nodeX, $nodeY,  $playerCount))
                {
                    unset($this->boardState[$nodeX][$nodeY]);
                }                    
            }            
        }                
    }
    
    public static function GetSoldierCost($tile, $playerId)
    {
        $tileType = $tile['type_id'];
        
        if ($tileType == 13)
        {
            return 2;
        }
        if ($tileType > 10)
        {
            return 1;
        }
        
        $tileOwner = $tile['player_id'];
        
        if ($tileOwner != $playerId && $tileOwner > 0)
        {
            return 1;
        }
        
        return 0;
    }
    
    public static function IsPartOfBoard($x, $y, $playerCount)
    {
        if ($playerCount == 2 && $y <= 6)
        {
            return false;
        }
        
        if ($playerCount == 3)
        {
            if ($x <= 1)
            {
                return false;
            }
            else if ($y <= 1)
            {
                return false;
            }
            else if ($x + $y >= 20)
            {
                return false;
            }
        }
        
        return true;        
    }
    
    public function GetNeighbours($x, $y) //returns list of (Cost/X/Y)
    {
        //use basic hex grid function to get neighbour coords
        $neighbours = AxialHexGrid::GetNeighbours($x, $y);
        
        $results = array();
        
        //TODO - increase efficiency by only building pheromone data when it is encountered by the search function
        //if target node and current node are in the same pheromone, add with cost of 0
        $currentTile = 0;
        foreach ($this->tileData as $tileID=>$tile)
        {
            if (
                    ($tile["x1"] == $x && $tile["y1"] == $y) ||
                    ($tile["x2"] == $x && $tile["y2"] == $y) ||
                    ($tile["x3"] == $x && $tile["y3"] == $y) ||
                    ($tile["x4"] == $x && $tile["y4"] == $y) ||
                    ($tile["x5"] == $x && $tile["y5"] == $y) ||
                    ($tile["x6"] == $x && $tile["y6"] == $y)
                )
            {
                $currentTile = $tileID;
                break;
            }
        }
        
        foreach($neighbours as $node)
        {
            (int)$nodeX = $node["x"];
            (int)$nodeY = $node["y"];
                       
            //if target node isn't in the board, or is water, it does not exist, so we don't return it
            if (!isset($this->boardState[$nodeX][$nodeY]))
            {            
                continue;
            }
            
            if ($this->boardState[$nodeX][$nodeY] == "WATER")
            {             
                continue;
            }            
                       
            //is this node on a tile?
            $nodeTileID = 0;
            foreach ($this->tileData as $tileID=>$tile)
            {
                if (
                    ($tile["x1"] == $node["x"] && $tile["y1"] == $node["y"]) ||
                    ($tile["x2"] == $node["x"] && $tile["y2"] == $node["y"]) ||
                    ($tile["x3"] == $node["x"] && $tile["y3"] == $node["y"]) ||
                    ($tile["x4"] == $node["x"] && $tile["y4"] == $node["y"]) ||
                    ($tile["x5"] == $node["x"] && $tile["y5"] == $node["y"]) ||
                    ($tile["x6"] == $node["x"] && $tile["y6"] == $node["y"])
                    )
                {
                    $nodeTileID = $tileID;
                    $nodeTile = $this->tileData[$nodeTileID];
                    //var_dump($node["x"].",".$node["y"]." is ".$nodeTile["type_id"]);
                    break;
                }
            }
                        
            //moving on the same tile is free
            if ($currentTile > 0 && $currentTile == $nodeTileID)
            {                
                $results[] = array("x"=>$node["x"], "y"=>$node["y"], "cost"=>0, "nodeTileID"=>$nodeTileID);
                continue;
            }
            
            //if this is a bug, you cannot enter unless you have enough soldiers to kill it
            //
            //handle enemy tiles, tunnels, bugs etc
            if ($nodeTileID > 0)
            {
                $soldierCost = MyrmesHexGrid::GetSoldierCost($nodeTile, $this->currentPlayerId);
                if ($soldierCost > $this->soldierCount)
                    continue;                
            }
            	
            //otherwise, add with cost of 1
            $results[] = array("x"=>$node["x"], "y"=>$node["y"], "cost"=>1, "nodeTileID"=>$nodeTileID);
        }
        
    return $results;
    }
}

