<?php

class TileData
{
	public $Tiles;
	public $Rotation;
	public $Type;
	public $IsFlipped;

	function __construct($tiles, $rotation, $type, $isFlipped)
	{
		$this->Tiles = $tiles;
		$this->Rotation = $rotation;
		$this->Type = $type;
		$this->IsFlipped = $isFlipped;
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
    
    //TODO - build pheromone list & whatever else may be necessary
    function __construct($boardState)
    {
        $this->boardState = $boardState;
    }
    
    public function GetNeighbours($x, $y) //returns list of (Cost/X/Y)
    {
        //use basic hex grid function to get neighbour coords
        $neighbours = AxialHexGrid::GetNeighbours($x, $y);
        
        $results = array();
        
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
            
            //TODO - handle enemy tiles, tunnels, bugs etc
            
            //TODO - increase efficiency by only building pheromone data when it is encountered by the search function
            //if target node and current node are in the same pheromone, add with cost of 0
            //if ($pheromoneData[$x][$y] == $pheromoneData[$nodeX][$nodeY])
            //{
            //	$results[] = array("x"=>node["x"], "y"=>node["y"], "cost"=>0);
            //	continue;
            //}
	
            //otherwise, add with cost of 1
            $results[] = array("x"=>$node["x"], "y"=>$node["y"], "cost"=>1);
        }
        
    return $results;
    }
}

