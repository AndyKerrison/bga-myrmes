<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * aktestgame implementation : © Andrew Kerrison <adesignforlife@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * aktestgame.action.php
 *
 * aktestgame main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/aktestgame/aktestgame/myAction.html", ...)
 *
 */
  
  
class action_aktestgame extends APP_GameAction
{ 
    // Constructor: please do not modify
    public function __default()
    {
        if( self::isArg( 'notifwindow') )
        {
            $this->view = "common_notifwindow";
            $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
        }
        else
        {
            $this->view = "aktestgame_aktestgame";
            self::trace( "Complete reinitialization of board game" );
        }
    } 
  
    // TODO: defines your action entry points there
    public function allocateNurse()
    {
        self::setAjaxMode(); 
                
        $type = self::getArg( "type", AT_alphanum, true );
        $slot = self::getArg( "slot", AT_posint, true );
        $this->game->allocateNurse($type, $slot);

        self::ajaxResponse();
    }
    
    public function clearPheromone()
    {
        self::setAjaxMode();     
        $this->game->onClearPheromone();
        self::ajaxResponse();
    }
    
    public function hexClicked()
    {
        self::setAjaxMode(); 
                
        $x = self::getArg( "x", AT_alphanum, true );
        $y = self::getArg( "y", AT_alphanum, true );
        $this->game->onHexClicked($x, $y);

        self::ajaxResponse();
    }
    
    public function atelierClicked()
    {
        self::setAjaxMode(); 
                
        $action = self::getArg( "atelier", AT_alphanum, true );
        $this->game->onAtelierClicked($action);

        self::ajaxResponse();
    }
    
    public function activateColony()
    {
        self::setAjaxMode(); 
                
        $slot = self::getArg( "slot", AT_alphanum, true );
        $this->game->onActivateColony($slot);

        self::ajaxResponse();
    }
    
    public function allocateNurseFinished()
    {
        self::setAjaxMode(); 
                
        $this->game->allocateNurseFinished();

        self::ajaxResponse();
    }
    
    public function pass()
    {
        self::setAjaxMode(); 
                
        $this->game->pass();

        self::ajaxResponse();
    }
    
    public function multiPass()
    {
        self::setAjaxMode(); 
                
        $this->game->onMultiPass();

        self::ajaxResponse();
    }
    
    public function startPlaceTile()
    {
        self::setAjaxMode(); 
                
        $this->game->onStartPlaceTile();

        self::ajaxResponse();
    }
    
    public function cancelTile()
    {
        self::setAjaxMode(); 
                
        $this->game->onCancelTile();

        self::ajaxResponse();
    }
    
    public function multiTileChoice()
    {
        self::setAjaxMode(); 
                
        $type_id = self::getArg( "type_id", AT_alphanum, true );
        $this->game->onMultiTileChosen($type_id);

        self::ajaxResponse();
    }
    
    public function confirmTile()
    {
        self::setAjaxMode(); 
                
        $hexes = self::getArg( "hexes", AT_alphanum, true );
        $this->game->onConfirmTile($hexes);

        self::ajaxResponse();
    }
    
    public function harvestChosen()
    {
        self::setAjaxMode(); 

        $hexes = self::getArg( "hexes", AT_alphanum, true );
        $this->game->onHarvestChosen($hexes);

        self::ajaxResponse();
    }
    
    public function storageDiscardFood()
    {
        self::setAjaxMode(); 
                
        $this->game->onStorageDiscard("FOOD");

        self::ajaxResponse();
    }
    
    public function storageDiscardDirt()
    {
        self::setAjaxMode(); 
                
        $this->game->onStorageDiscard("DIRT");

        self::ajaxResponse();
    }
    
    public function storageDiscardStone()
    {
        self::setAjaxMode(); 
                
        $this->game->onStorageDiscard("STONE");

        self::ajaxResponse();
    }
    
    public function onDiscardWorker()
    {
        self::setAjaxMode(); 
                
        $this->game->onDiscardWorker();

        self::ajaxResponse();
    }
    
    public function onConvertLarvae()
    {
        self::setAjaxMode(); 
                
        $this->game->onConvertLarvae();

        self::ajaxResponse();
    }


    /*
    
    Example:
  	
    public function myAction()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $arg1 = self::getArg( "myArgument1", AT_posint, true );
        $arg2 = self::getArg( "myArgument2", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->myAction( $arg1, $arg2 );

        self::ajaxResponse( );
    }
    
    */

  }
  

