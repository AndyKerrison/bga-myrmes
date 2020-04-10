/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * aktestgame implementation : © Andrew Kerrison <adesignforlife@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * aktestgame.js
 *
 * aktestgame user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "dojo/dom-class"
],
function (dojo, declare, domClass) {
    return declare("bgagame.aktestgame", ebg.core.gamegui, {
        constructor: function(){
            console.log('aktestgame constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;
            this.strActiveTileRequired = _("Current tile must be selected");
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            console.log(gamedatas);
            this.maxWorkerCount = gamedatas.maxWorkerCount;
            this.maxNurseCount = gamedatas.maxNurseCount;
			
			//set up event board
			var event_board_div = $('eventBoard');
			if (gamedatas.current_season <=3) //show fall event
			{
				dojo.place(this.format_block('jstpl_dice', {
                    id: "fall",
                    number: gamedatas.fall_event
                }), event_board_div);
				this.placeOnObjectPos("event_fall", "eventBoard", 40, 0);
			}
			if (gamedatas.current_season <=2) //show summer event
			{
				dojo.place(this.format_block('jstpl_dice', {
                    id: "summer",
                    number: gamedatas.summer_event
                }), event_board_div);
				this.placeOnObjectPos("event_summer", "eventBoard", -30, 0);
			}
			if (gamedatas.current_season <=1) //show spring event
			{
				dojo.place(this.format_block('jstpl_dice', {
                    id: "spring",
                    number: gamedatas.spring_event
                }), event_board_div);
				this.placeOnObjectPos("event_spring", "eventBoard", -100, 0);				
			}			
			//show year
			dojo.place(this.format_block('jstpl_year', {                    
                }), event_board_div);
			this.placeOnObjectPos("yearMarker", "eventBoard", 95, -15+(gamedatas.current_year-1)*15);
            
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                
                var player_board_div = $('player_board_' + player_id);
                dojo.place(this.format_block('jstpl_player_iconsA', player), player_board_div);
                dojo.place(this.format_block('jstpl_player_iconsB', player), player_board_div);
                
                this.setPlayerResourceCount(player_id, "nurse", player['nurses']);
                this.setPlayerResourceCount(player_id, "larvae", player['larvae']);
                this.setPlayerResourceCount(player_id, "soldier", player['soldiers']);
                this.setPlayerResourceCount(player_id, "worker", player['workers']);
                this.setPlayerResourceCount(player_id, "food", player['food']);
                this.setPlayerResourceCount(player_id, "stone", player['stone']);
                this.setPlayerResourceCount(player_id, "dirt", player['dirt']);
                this.setPlayerResourceCount(player_id, "colony", player['colony']);				
				
				//if allocations are visible in this stage, show them
				this.clearAllocation(player['color_name']);
				var allocation = player['allocations'];	
				if (allocation != null)
				{
					var available_nurses = allocation['available_nurses'];								
					var allocated = allocation['allocated'];
					var can_allocate = allocation['can_allocate'];
					var can_deallocate = allocation['can_deallocate'];
                    
					this.setAllocation(allocation['color'], available_nurses, allocated, can_allocate, can_deallocate, false);
				}			
                                
                //this.addTooltip("strawicon_"+player_id, this.strStrawTooltip, '');
                //this.addTooltip("woodicon_"+player_id, this.strWoodTooltip, '');
                //this.addTooltip("stoneicon_"+player_id, this.strStoneTooltip, ''); 
                
                var elementID = "stock_"+player_id+"_worker";
                if (dojo.byId(elementID) != null)
                {
                    $(elementID).innerHTML = this.maxWorkerCount - player['soldiers'] - player['workers'];                    
                }
                
                elementID = "stock_"+player_id+"_nurse";
                if (dojo.byId(elementID) != null)
                {
                    $(elementID).innerHTML = this.maxNurseCount - player['nurses'];                    
                }
            }
            
            //tile counts
            console.log("loading tile counts")
            this.setTileCounts(gamedatas.tileCounts);
            this.setSpecialTileCounts(gamedatas.specialTileCounts);
            
            //set up hexes here
            dojo.query('.js-nurseSlot').connect('onclick', this, 'onNurseAllocation');  
			dojo.query('.js-eventSlot').connect('onclick', this, 'onNurseAllocation');  
            dojo.query('.js-colony').connect('onclick', this, 'onColonyActivated');  
            dojo.query('.hex').connect('onclick', this, 'onHexClicked');
            
            var tunnels = gamedatas['tunnels'];
            for(var i=0; i<tunnels.length; i++)
            {
                var hexID = "hex_"+tunnels[i].x1+"_"+tunnels[i].y1;
                
                dojo.place(
                this.format_block('jstpl_tunnel', {
                    id: "tile_"+tunnels[i]['tile_id'],
                    color: tunnels[i]['color']
                }), hexID);
                this.placeOnObject("tile_"+tunnels[i]['tile_id'], hexID);
            }
            
            var pheromones = gamedatas['pheromones'];
            for(var i=0; i<pheromones.length; i++)
            {
                this.placeTile(pheromones[i], false, 0);
            }
            
            if (gamedatas['activeWorker'] != null)
            {
                this.placeActiveWorker(gamedatas['activeWorker'].x, gamedatas['activeWorker'].y, gamedatas['activeWorker'].color, 0, false);
            }
             
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            this.stateName = stateName;
            console.log( 'Entering state: '+stateName );
            console.log(args);
            
            //clear active state
            //dojo.query('.active').removeClass('active');
            var elems = document.querySelectorAll(".active");
            [].forEach.call(elems, function(el) {
                el.classList.remove("active");
            });
            
            elems = document.querySelectorAll(".selected");           
            for (var i = 0, len = elems.length; i < len; i++) {
                elems[i].classList.remove("selected");
            }
            
            dojo.query('.currentEvent').removeClass('currentEvent');

			var isPlayerActive = this.isCurrentPlayerActive();
			
			//event when not active, in certain states we will want to show stuff to this player only
			switch(stateName)
			{
				case 'births':
                    console.log("switch births");
                    var allocated = args.args._private.allocated;
                    var can_allocate = args.args._private.can_allocate;
                    var can_deallocate = args.args._private.can_deallocate;
                    
                    this.setAllocation(args.args._private.color, args.args._private.available_nurses, allocated, can_allocate, can_deallocate, isPlayerActive);
                                        
                    //dojo.query('.larvae').style('display', 'block');   
                    //dojo.style('larvae_1','display', 'block');
                    //dojo.style('larvae_2','display', 'block');
                    //dojo.style('larvae_3','display', 'block');
                    
                    //TODO - unset this when state ends
                    var eventID = args.args.event;
                    dojo.addClass(args.args._private.color+'_event_'+eventID, 'currentEvent');                                      
                                                      
                    break;
			}
                
				
            if (!isPlayerActive) {
                console.log("not active");
                return;
            }
            
            //dojo.query('.larvae').style('display', 'none');   
            
            switch( stateName )
            {           
                //during a multiactivestate
                //onUpdateActionButtons gets called with it's original args every time someone
                //becomes inactive, which screws upthe button visibility
                //entering state doesnt get called again, so this works
                case 'storage' :
                    this.foodCount = args.args._private.foodCount;
                    this.dirtCount = args.args._private.dirtCount;
                    this.stoneCount = args.args._private.stoneCount;
                                   
                    break;
                                            
                case 'placeWorker':
                    
                    var availableColonySpots = args.args.availableColony;
                    var tunnels = args.args.tunnels;
                    
                    for (var item in availableColonySpots)
                    {						
                        dojo.addClass(args.active_player + "_" + availableColonySpots[item], 'active');
                    }
                    
                    for (var item in tunnels)
                    {
                        //var x = tunnels[item].x1;
                        //var y = tunnels[item].y1;
                        //dojo.addClass("hex_"+x+"_"+y, 'active');
                        //alert("placeWorker enabled on " + tunnels[item]);
                        //dojo.addClass(tunnels[item], 'active');
                        //dojo.attr(tunnels[item], 'class', 'active');
                        document.getElementById("hexPoly_"+tunnels[item]).classList.add('active');
                    }
                    
                    break;
                case 'moveWorker':
                                       
                    var validHexMoves = args.args.validMoves;
                    for (var item in validHexMoves)
                    {
                        //var x = validHexMoves[item].X;
                        //var y = validHexMoves[item].Y;
                        //alert("hexPoly_"+validHexMoves[item]);
                        //var elem = dojo.byId("hex_"+x+"_"+y);
                        //f(elem != null){
                            //dojo.addClass("hex_"+x+"_"+y, 'active');
                        //}
                        //dojo.addClass("hexPoly_"+validHexMoves[item], 'active');
                        document.getElementById("hexPoly_"+validHexMoves[item]).classList.add('active');
                    }
                    
                    break;
                    
                case 'placeTile':
                                       
                    var hexes = args.args.hexes;
                    for (var item in hexes)
                    {
                        //dojo.addClass("hexPoly_"+hexes[item], 'active');                        
                        document.getElementById("hexPoly_"+hexes[item]).classList.add('active');
                    }
                    
                    //select the current tile
                    document.getElementById("hexPoly_"+this.activeWorkerX+"_"+this.activeWorkerY).classList.add('selected');
                    
                    break;
                    
                case 'placeTunnel':
                                   
                    var hexes = args.args.validTunnelSpaces;
                    for (var item in hexes)
                    {                        
                        //dojo.addClass("hexPoly_"+hexes[item], 'active');                        
                        document.getElementById("hexPoly_"+hexes[item]).classList.add('active');
                    }                   
                    
                    break;

                case 'harvest':
                                  
                    console.log(args.args._private.can_allocate);
                    var hexes = args.args._private.can_allocate;
                    for (var item in hexes)
                    {
                        document.getElementById("hexPoly_"+hexes[item]).classList.add('active');
                    }                   
                    
                    break;    
                
                case 'atelier':
                                  
                    console.log(args.args.atelierActions);
                    var actions = args.args.atelierActions;
                    for (var item in actions)
                    {
                        document.getElementById("atelierPoly_"+actions[item]).classList.add('active');
                    }                   
                    
                    break; 
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
            console.log(args);        
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                    case 'births' :
                        this.addActionButton( 'button_1_id', _('Finished'), 'onNurseAllocationFinished' ); 
                        break;
                        
                    case 'placeWorker' :
                    case 'atelier' :
                        if (args["convertLarvae"])
                        {
                            this.addActionButton( 'button_convertLarvae', _('Convert 3 Larvae to 1 Food'), 'onConvertLarvae' );   
                        }
                        this.addActionButton( 'button_pass', _('Pass'), 'onPass' ); 
                        break;
                    
                    case 'harvest' :
                        this.addActionButton( 'button_harvest', _('Done'), 'onHarvestChosen' ); 
                        break;
                        
                    case 'moveWorker':
                        if (args["canPlaceTile"])
                        {
                           this.addActionButton('button_tile', _('Place Tile'), 'onPlaceTileButton');
                        }
                        if (args["canClearPheromone"])
                        {
                           this.addActionButton('button_clear', _('Clear Pheromone'), 'onClearPheromone');
                        }
                        this.addActionButton('button_discard', _('Discard Worker'), 'onDiscardWorkerButton');
                        break;
                    case 'placeTile':
                        this.addActionButton('button_confirm', _('Confirm'), 'onConfirmTileButton');
                        this.addActionButton('button_cancel', _('Cancel'), 'onCancelButton');
                        break;
                    case 'placeTunnel':
                        this.addActionButton('button_cancel', _('Cancel'), 'onCancelButton');
                        break;
                    case 'tileChoice':
                        for(var i=0; i<args.tileTypes.length; i++)
                        {
                            var type_id = args.tileTypes[i];
                            console.log("adding type "+ type_id);
                            var tokenDiv = this.format_block('jstpl_pheromone', {
                                id: "0",
                                color: args.color,
                                type: type_id,
                                class: "button"
                            });
                            this.addImageActionButton('button_type_'+type_id, tokenDiv, 'onMultiTileChoice');                            
                        }
                        this.addActionButton('button_cancel', _('Cancel'), 'onCancelButton');
            
                        break;
                                            
                    //during a multiactivestate
                    //onupdateActionButtons gets called with it's original args every time someone
                    //becomes inactive, which screws upthe button visibility
                    //entering state doesnt get called again, so this works
                    case 'winter' :

                        if (this.larvaeCount > 0)
                        {
                            this.addActionButton( 'button_convertLarvae', _('Convert 3 Larvae to 1 Food'), 'onConvertLarvae' );                          
                        }    
                        
                        this.addActionButton('button_pass', _('Pass'), 'onMultiPass');
                           
                        break;                    
                    case 'storage' :

                        if (this.foodCount > 0)
                        {
                            var foodDiv = this.format_block('jstpl_resource', {
                                id: "0",
                                tokenType: "FOOD",
                                class: "btnRes"
                            });
                            this.addImageActionButton('button_discardFood', foodDiv, 'onStorageDiscardFood'); 
                        }                    
                        
                        if (this.dirtCount > 0)
                        {
                            var dirtDiv = this.format_block('jstpl_resource', {
                                id: "0",
                                tokenType: "DIRT",
                                class: "btnRes"
                            });
                            this.addImageActionButton('button_discardDirt', dirtDiv, 'onStorageDiscardDirt'); 
                        }   
                        
                        if (this.stoneCount > 0)
                        {
                            var stoneDiv = this.format_block('jstpl_resource', {
                                id: "0",
                                tokenType: "STONE",
                                class: "btnRes"
                            });
                            this.addImageActionButton('button_discardStone', stoneDiv, 'onStorageDiscardStone'); 
                        }                        
                        
                        break;
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
        
        addImageActionButton : function(id, div, handler) {
            // this will actually make a transparent button
            this.addActionButton(id, div, handler, '', false, 'gray');
            // remove boarder, for images it better without
            dojo.style(id, "border", "none");
            // but add shadow style (box-shadow, see css)
            dojo.addClass(id, "shadow");
            // you can also add addition styles, such as background
            // dojo.style(id, "background-color", "white");
        },
        
        hasClass:function(nodeID, className)
        {
            var element = document.getElementById(nodeID);
            return element.classList.contains(className);
        },
        
        setTileCounts: function(tileCounts)
        {
            for( var tileCount in tileCounts )
            {
                var player_id = tileCounts[tileCount].player_id;
                var type = tileCounts[tileCount].type_id;
                var count = tileCounts[tileCount].count;
                var elementID = "stock_"+player_id+"_"+type;
                
                //update storage counts
                if (dojo.byId(elementID) != null)
                {
                    $(elementID).innerHTML = count;
                }        
            }
        },
        
        //for the 'cube' marker in theplayer reserve, not the general available store
        setSpecialTileCounts: function(tileCounts)
        {
            for( var tileCount in tileCounts )
            {
                var player_id = tileCounts[tileCount].player_id;
                var count = tileCounts[tileCount].count;
                var elementID = "stock_"+player_id+"_special";
                
                //update storage counts
                if (dojo.byId(elementID) != null)
                {
                    $(elementID).innerHTML = count;
                }        
            }
        },
        
        placeTile:function(tile, isFromPlayerBoard, player_id)
        {
            console.log(tile); 
            var tileID = tile["tile_id"];
            var subTypeID = tile["subtype_id"];
            var color = tile["color"];
            var typeID = tile["type_id"];
            var x = tile["x1"];
            var y = tile["y1"];
            var tileRotation = tile["rotation"];
            var playerID = tile["player_id"];
            var scaleX = 1;
            
            if (tile["flipped"] === "1")
            {
                scaleX = -1;
            }
                       
            var placePoint = "hex_"+x+"_"+y;
            if (isFromPlayerBoard)
            {
                placePoint = "overall_player_board_" + playerID;
            }
            
            var ydiff = 20;
            var xdiff = 0;
            
            if (typeID == 3 || typeID == 9)
            {
                xdiff = 25;
            }
            if (typeID == 4)
            {
                ydiff = 40;
            }
            if (typeID == 5 || typeID == 10)
            {
                ydiff = 30;
                xdiff = 20;
            }
            
            if (typeID == 6)
            {
                ydiff = 45;
                xdiff = 25;
            }
            
            if (typeID == 7)
            {
                ydiff = 45;
                xdiff = 20;
            }
            
            if (typeID == 8)
            {
                ydiff = 40;
                xdiff = 40;
            }
            
            if (typeID > 10) //bugtiles
            {
                ydiff = 0;            
            }
                        
            console.log("placing pheromone on " + x + "," + y);
            var displayType = typeID;
            if (typeID == 9)
            {
                displayType = subTypeID;
            }

            if (typeID == 1)
            {
                dojo.place(
                this.format_block('jstpl_tunnel', {
                    id: "tile_"+tileID,
                    color: color                    
                }), placePoint);
            }
            else
            {
                dojo.place(
                this.format_block('jstpl_pheromone', {
                    id: "tile_"+tileID,
                    color: color,
                    type: displayType,
                    class: "tile"
                }), placePoint);
            }
            this.placeOnObjectPos("tile_"+tileID, placePoint, xdiff, ydiff);
                        
            var rotation = -90+60*tileRotation;
            if (typeID == 3 || typeID == 9)
            {
                rotation = -30+60*tileRotation;
            }
            if (typeID == 5 || typeID == 10)
            {
                rotation = -30+60*tileRotation;
            }
            if (typeID == 8)
            {
                rotation = 30+60*tileRotation;
            }
            
            if (typeID > 10) //bugtiles
            {            
                rotation = 0;
            }
           
            
            //dojo.style("tile_"+tileID, 'transform', 'rotate('+rotation+'deg)');
            
            if (isFromPlayerBoard)
            {
                this.attachToNewParent("tile_"+tileID, "hex_"+x+"_"+y );                
                this.slideToObjectPos("tile_"+tileID, "hex_"+x+"_"+y, 0, 0, 500, 500).play();                            
            }
            
            dojo.style("tile_"+tileID, 'transform', 'rotate('+rotation+'deg) scaleX('+scaleX+')');
            
            //TODO -  tile needs resources too. 
            //
            //They must appear after tile is placed in the case of an active move
            var delay = 0;
            if (isFromPlayerBoard)
            {
                delay = 1500;
            }
            
            var self = this;
            setTimeout(function () {
                self.placeResources(tile, player_id);                
            }, delay);
        },       
        
        placeResources: function(tile,player_id)
        {
            //var self = this;
            var tileID = tile["tile_id"];
            var typeID = tile["type_id"];
            for (var i=1; i<=6; i++)
            {
                if(tile["res"+i].length > 0)
                {
                    var destination = "hex_"+tile["x"+i]+"_"+tile["y"+i];
                    console.log("placing resource on " + destination);
                    dojo.place(
                    this.format_block('jstpl_resource', {
                        id: "res_"+i+"_"+tileID,
                        tokenType: tile["res"+i],
                        class: "token"
                    }), destination);  
                    this.placeOnObjectPos("res_"+i+"_"+tileID, destination, 0, 0);
                }                
            }   
            
            if (typeID == 1 && player_id > 0)
            {
                var destination = "hex_"+tile["x1"]+"_"+tile["y1"];
                console.log("placing resource on " + destination);
                dojo.place(
                    this.format_block('jstpl_resource', {
                        id: "res_1_"+tileID,
                        tokenType: "DIRT",
                        class: "token"
                    }), destination);  
                this.placeOnObjectPos("res_1_"+tileID, destination, 0, 0);                     
                this.slideToObjectAndDestroy("res_1_" + tileID, "overall_player_board_"+player_id, 750, 0);
            }            
        },
        
        destroyResources: function(tile)
        {
            //var self = this;
            var tileID = tile["tile_id"];
            for (var i=1; i<=6; i++)
            {
                var resID = "res_"+i+"_"+tileID;
                if (dojo.byId(resID) !== null)
                {
                    dojo.destroy(resID);
                }                
            }            
        },
        
        placeActiveWorker: function(x, y, color, playerID, isFromPlayerBoard)
        {
            this.activeWorkerX = x;
            this.activeWorkerY = y;
            
            var placePoint = "hex_"+x+"_"+y;
            if (isFromPlayerBoard)
            {
                placePoint = "overall_player_board_" + playerID;
            }
            
            dojo.place(
                this.format_block('jstpl_worker', {
                    id: "activeWorker",
                    color: color
                }), placePoint);
            this.placeOnObject("activeWorker", placePoint); 
            
            if (isFromPlayerBoard)
            {
                this.attachToNewParent("activeWorker", "hex_"+x+"_"+y );                
                this.slideToObjectPos("activeWorker", "hex_"+x+"_"+y, 0, 0, 500, 500).play();                
            }
        },
        
        moveActiveWorker: function(x, y)
        {
            this.activeWorkerX = x;
            this.activeWorkerY = y;
            //this.placeOnObject("worker_"+123, "hex_"+x+"_"+y);
            this.slideToObjectPos("activeWorker", "hex_"+x+"_"+y, 0, 0, 500, 100).play();            
        },
       
       
        setPlayerResourceCount: function (player_id, resource, count)
        {
            $(resource +'count_p'+player_id).innerHTML = count;
			
			//TODO need to handle nurses properly here... which ones go on atelier, nurse slot, etc.
			
			//for soldiers, set the colony count.			
			if (resource == "soldier")
			{				
				$soldier = dojo.byId("soldierCount_" + player_id);
				if (count > 0)
				{
					$soldier.innerHTML = count;
					dojo.removeClass("soldierCount_" + player_id, 'hidden');
				}
				else
				{
					dojo.addClass("soldierCount_" + player_id, 'hidden');
				}
			}
			
			if (resource == "worker")
			{
				$worker = dojo.byId("workerCount_" + player_id);
				if (count > 0)
				{
					$worker.innerHTML = count;
					dojo.removeClass("workerCount_" + player_id, 'hidden');
				}
				else
				{
					dojo.addClass("workerCount_" + player_id, 'hidden');
				}
			}
			
			if (resource == "larvae")
			{						
				dojo.query(".Larvae" + player_id).addClass('hidden');
				for (var i=1; i<= count; i++)
				{
					dojo.removeClass(player_id + "_larvae_" + i, 'hidden');
				}
			}
			
			if (resource == "colony")
			{				
				var colony = dojo.byId(player_id + "_colony_marker");
				this.slideToObjectPos(player_id + "_colony_marker", player_id + "_board", 415, 30 + count*60, 500, 500).play();                            
			}
				
            
            if (player_id == this.player_id)
            {
                if (resource == "food")
                {
                    this.foodCount = count;
                }
                 
                if (resource == "dirt")
                {
                    this.dirtCount = count;
                }
                
                if (resource == "stone")
                {
                    this.stoneCount = count;
                }
                
                if (resource == "larvae")
                {
                    this.larvaeCount = count;                    
                }               			
            }
        },
		
		clearAllocation:function(color)
		{
			var eventSlot = '.'+color+'Event';
			dojo.query(eventSlot).addClass('unallocated');
			dojo.query(eventSlot).removeClass('allocated');
			dojo.query(eventSlot).removeClass('active'); 
			
			var nurseSlot = '.'+color+'Nurse';
			dojo.query(nurseSlot).addClass('unallocated');
			dojo.query(nurseSlot).removeClass('allocated');
			dojo.query(nurseSlot).removeClass('active'); 	
			
			$(color + "_nurses").innerHTML = "";			
		},       
       
        setAllocation:function(color, availableNurseCount, allocated, can_allocate, can_deallocate, isPlayerActive)
        {
			this.clearAllocation(color);
						
			//if we have any nurses left, show icon + count in the nurse area
			if (availableNurseCount > 0)
			{
				dojo.removeClass(color + "_nurses", 'unallocated');
				dojo.addClass(color + "_nurses", 'allocated');
				$(color + "_nurses").innerHTML = "&nbsp;" + availableNurseCount;			
			}		
           
            for (var item in allocated)
            {
				console.log(color + "_" + allocated[item]);
                dojo.removeClass(color + "_" + allocated[item], 'unallocated');
				dojo.addClass(color + "_" + allocated[item], 'allocated');
            }
           
			if (isPlayerActive)
			{
				for (var item in can_allocate)
				{
					dojo.addClass(color +"_" + can_allocate[item], 'active');
				}
                    
				for (var item in can_deallocate)
				{
					dojo.addClass(color + "_" + can_deallocate[item], 'active');
				}
		   }
        },


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
       
        onHarvestChosen: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'chooseHarvest' ) )
            {
                return;
            }
            
            var selectedHexes = dojo.query('.selected');
            console.log(selectedHexes);
                        
            var hexes = "";
            for(var i = 0;i<selectedHexes.length; i++)
            {
                if (i > 0)
                {
                    hexes += "x";
                }
                var parts = selectedHexes[i].id.split('_');
                hexes += parts[1] + "_" + parts[2];
            }
            
            this.ajaxcall( "/aktestgame/aktestgame/harvestChosen.html", { 
                    lock: true, 
                    hexes: hexes
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onStorageDiscardFood: function(evt)
        {
            dojo.stopEvent( evt );  
            
            if( ! this.checkAction( 'discard' ) )
            {
                return;
            }
            
            this.ajaxcall( "/aktestgame/aktestgame/storageDiscardFood.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } 
            );        
        },
        
        onStorageDiscardDirt: function(evt)
        {
            dojo.stopEvent( evt );  
            
            if( ! this.checkAction( 'discard' ) )
            {
                return;
            }
            
            this.ajaxcall( "/aktestgame/aktestgame/storageDiscardDirt.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } 
            );        
        },
        
        onStorageDiscardStone: function(evt)
        {
            dojo.stopEvent( evt );  
            
            if( ! this.checkAction( 'discard' ) )
            {
                return;
            }
            
            this.ajaxcall( "/aktestgame/aktestgame/storageDiscardStone.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } 
            );        
        },
       
        onPass: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'pass' ) )
            {
                return;
            }

            this.ajaxcall( "/aktestgame/aktestgame/pass.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onMultiPass: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'pass' ) )
            {
                return;
            }

            this.ajaxcall( "/aktestgame/aktestgame/multiPass.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onConfirmTileButton: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'confirmTile' ) )
            {
                return;
            }
            
            //client side validation
            // - we will validate that the current active hex is selected
            
            //if (!dojo.hasClass("hexPoly_"+this.activeWorkerX+"_"+this.activeWorkerY, "selected"))
            if (!this.hasClass("hexPoly_"+this.activeWorkerX+"_"+this.activeWorkerY, "selected"))
            {
                this.showMessage(this.strActiveTileRequired, 'error');   
                return;
            }
            
            var selectedHexes = dojo.query('.selected');
            console.log(selectedHexes);
                        
            var hexes = "";
            for(var i = 0;i<selectedHexes.length; i++)
            {
                if (i > 0)
                {
                    hexes += "x";
                }
                var parts = selectedHexes[i].id.split('_');
                hexes += parts[1] + "_" + parts[2];
            }
            
            this.ajaxcall( "/aktestgame/aktestgame/confirmTile.html", { 
                    lock: true, 
                    hexes: hexes
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onCancelButton: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'cancel' ) )
            {
                return;
            }

            this.ajaxcall( "/aktestgame/aktestgame/cancelTile.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onPlaceTileButton: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'placeTile' ) )
            {
                return;
            }

            this.ajaxcall( "/aktestgame/aktestgame/startPlaceTile.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onMultiTileChoice: function(evt)
        {
             dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'selectTile' ) )
            {
                return;
            }
            
            var type_id = evt.currentTarget.id.split("_")[2];

            this.ajaxcall( "/aktestgame/aktestgame/multiTileChoice.html", { 
                    lock: true, 
                    type_id: type_id
                }, 
                this, function( result ) { }, function( is_error) { } );            
        },
        
        onConvertLarvae: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'convertLarvae' ) )
            {
                return;
            }

            this.ajaxcall( "/aktestgame/aktestgame/onConvertLarvae.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onDiscardWorkerButton: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'discardWorker' ) )
            {
                return;
            }

            this.ajaxcall( "/aktestgame/aktestgame/onDiscardWorker.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
       
        onNurseAllocationFinished: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'finished' ) )
            {
                return;
            }

            this.ajaxcall( "/aktestgame/aktestgame/allocateNurseFinished.html", { 
                lock: true, 
                //myArgument1: arg1, 
                //myArgument2: arg2,
                }, 
                this, function( result ) {
                    // What to do after the server call if it succeeded
                    // (most of the time: nothing)
                }, function( is_error) {
                    // What to do after the server call in anyway (success or failure)
                    // (most of the time: nothing)
                } );        
        },
        
        onHexSelected:function(evt)
        {
            dojo.stopEvent(evt);
                        
            var hexPoly = document.getElementById(evt.currentTarget.id);
            
            if (!hexPoly.classList.contains('active'))
            {
                return;
            }
            
            if( ! this.checkAction( 'selectHex' ) )
            {
                return;
            }
            
            if (!hexPoly.classList.contains('selected'))
            {
                hexPoly.classList.add("selected");
            }
            else 
            {
                hexPoly.classList.remove("selected");                
            }
            
            //if (!dojo.hasClass(evt.currentTarget.id, "active"))
            //{
            //    return;
            //}
            
            //if (!dojo.hasClass(evt.currentTarget.id, "selected"))
            //{
            //    dojo.addClass(evt.currentTarget.id, "selected");
            //}
            //else 
            //{
            //    dojo.removeClass(evt.currentTarget.id, "selected");
            //}
        },
        
        onHexClicked: function(evt)
        {            
            dojo.stopEvent(evt);
                                
            if (!this.hasClass(evt.currentTarget.id, 'active'))
            {
                return;
            }
            
            if( ! this.checkAction( 'selectHex' ) )
            {
                return;
            }
                        
            if (this.stateName == "placeTile")
            {
                this.onHexSelected(evt);
                return;
            }
            if (this.stateName == "harvest")
            {
                this.onHexSelected(evt);
                return;
            }
                        
            if (this.stateName == "atelier")
            {
                var action = dojo.attr(evt.currentTarget.id, 'data-action');
                
                this.ajaxcall( "/aktestgame/aktestgame/atelierClicked.html", { 
                    lock: true,
                    atelier: action,
                }, 
                this, function() {}, function() {});  
                return;
            }
            
            
            //normal click, submit x/y            
            var x = dojo.attr(evt.currentTarget.id, 'data-x');
            var y = dojo.attr(evt.currentTarget.id, 'data-y');
            
            this.ajaxcall( "/aktestgame/aktestgame/hexClicked.html", { 
                lock: true,
                x: x,
                y: y
                }, 
                this, function() {}, function() {});            
        },
        
        onColonyActivated: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if (!dojo.hasClass(evt.target.id, "active"))
            {
                return;
            }
            
            if( ! this.checkAction( 'activateColony' ) )
            {
                return;
            }
            
            //var type = dojo.attr(evt.target.id, 'data-type');
            var slot = dojo.attr(evt.target.id, 'data-index');
            
            this.ajaxcall( "/aktestgame/aktestgame/activateColony.html", { 
                lock: true,
                slot: slot
                }, 
                this, function() {}, function() {});                
        },
       
        onNurseAllocation: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if (!dojo.hasClass(evt.target.id, "active"))
            {
                return;
            }
            
            if( ! this.checkAction( 'allocateNurse' ) )
            {
                return;
            }
                        
            var type = dojo.attr(evt.target.id, 'data-type');
            var slot = dojo.attr(evt.target.id, 'data-index');

            this.ajaxcall( "/aktestgame/aktestgame/allocateNurse.html", { 
                lock: true,
                type: type,
                slot: slot,
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onClearPheromone: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'clearPheromone' ) )
            {
                return;
            }
            
            this.ajaxcall( "/aktestgame/aktestgame/clearPheromone.html", { 
                lock: true,                
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
                
        ///////////////////////////////////////////////////
        //// Reaction to notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your aktestgame.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'allocationConfirmed', this, "notif_allocationConfirmed" );

            this.notifqueue.setSynchronous( 'pause', 1500 );
            
            dojo.subscribe( 'yourNursesAllocated', this, "notif_playerNursesAllocated" );
            dojo.subscribe( 'birthing', this, "notif_birthing" );
            
            dojo.subscribe('workerPlaced', this, "notif_workerPlaced");
            dojo.subscribe('workerMoved', this, "notif_workerMoved");
            dojo.subscribe('activeWorkerRemoved', this, "notif_activeWorkerRemoved");
            
            dojo.subscribe('soldierLost', this, "notif_soldierLost");
            dojo.subscribe('bugEaten', this, "notif_bugEaten");
            
            dojo.subscribe('harvest', this, "notif_harvest");
            this.notifqueue.setSynchronous( 'harvest', 1000 );
            
            dojo.subscribe('discard', this, "notif_discard");
            
            dojo.subscribe('tilePlacementInvalid', this, "notif_tilePlacementInvalid");
            //this.notifqueue.setSynchronous( 'tilePlaced', 1100 );
            dojo.subscribe('tilePlaced', this, "notif_tilePlaced");
            dojo.subscribe('resourcesPlaced', this, "notif_resourcesPlaced");
            dojo.subscribe('resourcesDestroyed', this, "notif_resourcesDestroyed");
            dojo.subscribe('pheromoneCleared', this, "notif_pheromoneCleared");
            
            dojo.subscribe('setScore', this, "notif_setScore");
            dojo.subscribe('setWorkers', this, "notif_setWorkers");
            dojo.subscribe('setNurses', this, "notif_setNurses");
            
            dojo.subscribe('colonyActivated', this, "notif_colonyActivated");
            dojo.subscribe('larvaeConverted', this, "notif_larvaeConverted");
			
			//TODO - check if these are actually needed
            dojo.subscribe('atelierNurseGained', this, "notif_atelierNurseGained");
            dojo.subscribe('atelierLevelGained', this, "notif_atelierLevelGained");
        },  
        
        //from this point and below, you can write your game notifications handling methods

        notif_atelierLevelGained: function(notif)
        {
            //TODO - animate gain of level. Requires colony art
            this.setPlayerResourceCount(notif.args.player_id, "stone", notif.args.stoneCount);
            this.setPlayerResourceCount(notif.args.player_id, "dirt", notif.args.dirtCount);            
            this.setPlayerResourceCount(notif.args.player_id, "colony", notif.args.colonyLevel);            
        },
        
        notif_atelierNurseGained: function(notif)
        {
            //TODO - animate usage and/or gain of nurse. Requires colony art
            this.setPlayerResourceCount(notif.args.player_id, "food", notif.args.foodCount);
            this.setPlayerResourceCount(notif.args.player_id, "larvae", notif.args.larvaeCount);
            //this.setPlayerResourceCount(notif.args.player_id, "nurse", notif.args.nurseCount);
        },
        
        notif_colonyActivated: function(notif)
        {
            //TODO - animate usage and/or gain of nurse. Requires colony art
            this.setPlayerResourceCount(notif.args.player_id, "food", notif.args.foodCount);
            this.setPlayerResourceCount(notif.args.player_id, "larvae", notif.args.larvaeCount);
            this.setPlayerResourceCount(notif.args.player_id, "dirt", notif.args.dirtCount);
            this.setPlayerResourceCount(notif.args.player_id, "stone", notif.args.stoneCount);
        },
        
        notif_larvaeConverted: function(notif)
        {
            //TODO - animate usage and/or gain of nurse. Requires colony art
            this.setPlayerResourceCount(notif.args.player_id, "food", notif.args.foodCount);
            this.setPlayerResourceCount(notif.args.player_id, "larvae", notif.args.larvaeCount);            
        },
        
        notif_activeWorkerRemoved: function(notif)
        {
            this.activeWorkerX = 0;
            this.activeWorkerY = 0;
            //dojo.destroy("activeWorker");
            this.fadeOutAndDestroy("activeWorker");
            //this.setPlayerResourceCount(notif.args.player_id, "worker", notif.args.workers);
        },
        
        notif_pheromoneCleared: function(notif)
        {
            console.log(notif);
            this.fadeOutAndDestroy("tile_"+notif.args.tile_id);
            this.setPlayerResourceCount(notif.args.player_id, "dirt", notif.args.dirtCount);
        },
        
        notif_soldierLost: function(notif)
        {
            this.setPlayerResourceCount(notif.args.player_id, "soldier", notif.args.soldiers);
        },
        
        notif_harvest: function(notif)
        {
            console.log(notif);
            if (dojo.byId("res_" + notif.args.hex_id)!== null)
            {
                this.slideToObjectAndDestroy("res_" + notif.args.hex_id, "overall_player_board_"+notif.args.player_id, 750, 0);
            }
            this.setPlayerResourceCount(notif.args.player_id, "food", notif.args.foodCount);
            this.setPlayerResourceCount(notif.args.player_id, "dirt", notif.args.dirtCount);
            this.setPlayerResourceCount(notif.args.player_id, "stone", notif.args.stoneCount);
        },
        
        notif_discard: function(notif)
        {
            console.log(notif);
            this.setPlayerResourceCount(notif.args.player_id, "food", notif.args.foodCount);
            this.setPlayerResourceCount(notif.args.player_id, "dirt", notif.args.dirtCount);
            this.setPlayerResourceCount(notif.args.player_id, "stone", notif.args.stoneCount);
            
            if (this.player_id !== notif.args.player_id)
            {
                return;
            }
                        
            if (notif.args.foodCount == 0 && dojo.byId('button_discardFood') !== null)            {
                
                dojo.destroy("button_discardFood");
            }
            
            if (notif.args.dirtCount == 0 && dojo.byId('button_discardDirt') !== null)
            {
                dojo.destroy("button_discardDirt");
            }
            
            if (notif.args.stoneCount == 0 && dojo.byId('button_discardStone') !== null)
            {
                dojo.destroy("button_discardStone");
            }
        },
        
        notif_bugEaten: function(notif)
        {
            console.log(notif);
            this.slideToObjectAndDestroy("tile_" + notif.args.bugTileID, "overall_player_board_"+notif.args.player_id, 1000, 500);
            this.setPlayerResourceCount(notif.args.player_id, "food", notif.args.foodCount);
        },
        
        notif_birthing: function(notif)
        {
            this.setPlayerResourceCount(notif.args.player_id, "larvae", notif.args.larvaeCount);
            this.setPlayerResourceCount(notif.args.player_id, "soldier", notif.args.soldierCount);
            //this.setPlayerResourceCount(notif.args.player_id, "worker", notif.args.workerCount);
        },
        
        notif_tilePlaced: function(notif)
        {
            console.log(notif.args.tile);
            this.placeTile(notif.args.tile, true, notif.args.player_id);
            this.setTileCounts(notif.args.tileCounts);
            this.setSpecialTileCounts(notif.args.specialTileCounts);
            this.setPlayerResourceCount(notif.args.player_id, "food", notif.args.foodCount);
            this.setPlayerResourceCount(notif.args.player_id, "stone", notif.args.stoneCount);
            this.setPlayerResourceCount(notif.args.player_id, "dirt", notif.args.dirtCount);
        },
        
        notif_resourcesPlaced: function(notif)
        {
            console.log(notif.args.tile);
            this.placeResources(notif.args.tile, true);
        },
        
        notif_resourcesDestroyed: function(notif)
        {
            console.log(notif.args.tile);
            this.destroyResources(notif.args.tile, true);
        },
        
        notif_setScore:function(notif)
        {
            if (this.scoreCtrl[notif.args.player_id] != null) {
                this.scoreCtrl[notif.args.player_id].setValue(notif.args.score);
            }
        },
        
        notif_setWorkers:function(notif)
        {
            this.setPlayerResourceCount(notif.args.player_id, "worker", notif.args.workerCount);
            
            var elementID = "stock_"+notif.args.player_id+"_worker";
            if (dojo.byId(elementID) != null)
            {
                $(elementID).innerHTML = notif.args.workersRemaining;
            }   
        },
        
        notif_setNurses:function(notif)
        {
            this.setPlayerResourceCount(notif.args.player_id, "nurse", notif.args.nurseCount);
            
            var elementID = "stock_"+notif.args.player_id+"_nurse";
            if (dojo.byId(elementID) != null)
            {
                $(elementID).innerHTML = notif.args.nursesRemaining;
            }   
        },
        
        notif_tilePlacementInvalid: function(notif)
        {
            this.showMessage(notif.args.errMsg, 'error');
        },
        
        notif_workerMoved: function(notif)
        {
            this.moveActiveWorker(notif.args.x, notif.args.y);
        },
        
        notif_workerPlaced: function(notif)
        {
            this.placeActiveWorker(notif.args.x, notif.args.y, notif.args.color, notif.args.player_id, true);
        },
        
        notif_playerNursesAllocated:function(notif)
        {
            //clear active state
            dojo.query('.active').removeClass('active');            
        },
        
        notif_allocationConfirmed: function( notif )
        {
            console.log( 'notif_allocationConfirmed' );
            console.log( notif );
            
            var allocated = notif.args.allocated;
            var can_allocate = notif.args.can_allocate;
            var can_deallocate = notif.args.can_deallocate;
            
            this.setAllocation(notif.args.color, notif.args.available_nurses, allocated, can_allocate, can_deallocate, true);
        },    
        
   });             
});
