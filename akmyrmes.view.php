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
 * akmyrmes.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in akmyrmes_akmyrmes.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_akmyrmes_akmyrmes extends game_view
  {
    function getGameName() {
        return "akmyrmes";
    }    
  	
    function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/


        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "akmyrmes_akmyrmes", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */
        
        $this->page->begin_block( "akmyrmes_akmyrmes", "hex" );
        $this->page->begin_block( "akmyrmes_akmyrmes", "hexUnusable" );
        for( $x=0; $x<15; $x++ )
        {
            for ($y=0; $y<15; $y++)
            {
                if ($x + $y > 21 || $x + $y < 7)
                    continue;
                if ($y == 0)
                {
                    if ($x == 7 || $x == 8 || $x == 13 || $x == 14)
                    continue;
                }
                if ($y == 1)
                {
                    if ($x == 6 || $x == 14)
                    continue;
                }
                if ($y == 6)
                {
                    if ($x == 1 || $x == 14)
                    continue;
                }
                if ($y == 7)
                {
                    if ($x == 0 || $x == 14)
                    continue;
                }
                if ($y == 8)
                {
                    if ($x == 0 || $x > 12)
                    continue;
                }
                if ($y == 13)
                {
                    if ($x == 0 || $x == 8)
                    continue;
                }
                if ($y == 14)
                {
                    if ($x == 0 || $x == 1 || $x == 6 || $x == 7)
                    continue;
                }
                $cartX = $x + floor(($y-1) / 2);
                $offset = (1 - ($y % 2))*23;
                
                //mask
                $unusable = false;
                
                if ($players_nbr == 2)
                {
                    if ($y <=6)
                    {
                        $unusable = true;
                    }                    
                }
                
                if ($players_nbr == 3)
                {
                    if ($y <=1)
                    {
                        $unusable = true;
                    }                    
                    if ($x <=1)
                    {
                        $unusable = true;
                    }     
                    if ($x + $y >=20)
                    {
                        $unusable = true;
                    }                    
                }
                
                if ($unusable)
                {
                    $this->page->insert_block( "hexUnusable", array(
                        'X' => $x,
                        'Y' => $y,
                        'XPOS' => $cartX*46.4 + (-154) + $offset,
                        'YPOS' => $y*40.4 + 249                        
                    ));                
                }
                else
                {
                    $this->page->insert_block( "hex", array(
                        'X' => $x,
                        'Y' => $y,
                        'XPOS' => $cartX*46.4 + (-154) + $offset,
                        'YPOS' => $y*40.4 + 249                        
                    ));                
                }
            }
        }
        
        global $g_user;
        $current_player_id = $g_user->get_id();
        $started = false;
        
        $this->page->begin_block( "akmyrmes_akmyrmes", "playerBoard" );        
        foreach( $players as $player )
        {
            if ($player['player_id'] == $current_player_id || $started)
            {
                $color = self::getUniqueValueFromDB("select player_color_name from player where player_id='".$player["player_id"]."'" );
                
                $this->page->insert_block( "playerBoard", array( 
                    "PLAYER_NAME" => $player['player_name'],
                    "PLAYER_ID" => $player['player_id'],
                    "PLAYER_COLOR" => $color
                ) );           
                $started = true;
            }
        }
        
        foreach( $players as $player )
        {
            if ($player['player_id'] != $current_player_id && $started)
            {
                $color = self::getUniqueValueFromDB("select player_color_name from player where player_id='".$player["player_id"]."'" );
                
                $this->page->insert_block( "playerBoard", array( 
                    "PLAYER_NAME" => $player['player_name'],
                    "PLAYER_ID" => $player['player_id'],
                    "PLAYER_COLOR" => $color
                ) );           
            }
            else {
                $started = false;
            }
        }
        /*********** Do not change anything below this line  ************/
    }
}
  

