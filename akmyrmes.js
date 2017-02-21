/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * akMyrmes implementation : © Andrew Kerrison <adesignforlife@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * akmyrmes.js
 *
 * akMyrmes user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.akmyrmes", ebg.core.gamegui, {
        constructor: function(){
            console.log('akmyrmes constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

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
                
                //this.addTooltip("strawicon_"+player_id, this.strStrawTooltip, '');
                //this.addTooltip("woodicon_"+player_id, this.strWoodTooltip, '');
                //this.addTooltip("stoneicon_"+player_id, this.strStoneTooltip, '');               
            }
            
            // TODO: Set up your game interface here, according to "gamedatas"
            dojo.query('.js-larvae').connect('onclick', this, 'onNurseAllocation');  
            dojo.query('.js-colony').connect('onclick', this, 'onColonyActivated');  
            dojo.query('.hex').connect('onclick', this, 'onHexClicked');
            
            var tunnels = gamedatas['tunnels'];
            for(var i=0; i<tunnels.length; i++)
            {
                dojo.place(
                this.format_block('jstpl_tunnel', {
                    id: "tile_"+tunnels[i]['tile_id'],
                    color: tunnels[i]['color']
                }), "hex_"+tunnels[i].x1+"_"+tunnels[i].y1);
                this.placeOnObject("tile_"+tunnels[i]['tile_id'], "hex_"+tunnels[i].x1+"_"+tunnels[i].y1)
            }
            
            if (gamedatas['activeWorker'] != null)
            {
                this.placeActiveWorker(gamedatas['activeWorker'].x, gamedatas['activeWorker'].y);
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
            console.log( 'Entering state: '+stateName );
            console.log(args);
            
            //clear active state
            dojo.query('.active').removeClass('active');
            
            if (!this.isCurrentPlayerActive()) {
                return;
            }
            
            //dojo.removeClass('event_'+eventID, 'currentEvent');
            //dojo.query('.larvae').style('display', 'none');     
            
            switch( stateName )
            {
                case 'births':
                    var allocated = args.args._private.allocated;
                    var can_allocate = args.args._private.can_allocate;
                    var can_deallocate = args.args._private.can_deallocate;
                    
                    this.setAllocation(allocated, can_allocate, can_deallocate);
                                        
                    //dojo.query('.larvae').style('display', 'block');   
                    //dojo.style('larvae_1','display', 'block');
                    //dojo.style('larvae_2','display', 'block');
                    //dojo.style('larvae_3','display', 'block');
                    
                    //TODO - unset this when state ends
                    var eventID = args.args.event;
                    dojo.addClass('event_'+eventID, 'currentEvent');                                      
                                                      
                    break;
                    
                case 'placeWorker':
                    
                    var availableColonySpots = args.args.availableColony;
                    var tunnels = args.args.tunnels;
                    
                    for (var item in availableColonySpots)
                    {
                        dojo.addClass(availableColonySpots[item], 'active');
                    }
                    
                    for (var item in tunnels)
                    {
                        //var x = tunnels[item].x1;
                        //var y = tunnels[item].y1;
                        //dojo.addClass("hex_"+x+"_"+y, 'active');
                        dojo.addClass(tunnels[item], 'active');
                    }
                    
                    break;
                case 'moveWorker':
                    
                    var validHexMoves = args.args.validMoves;
                    for (var item in validHexMoves)
                    {
                        //var x = validHexMoves[item].X;
                        //var y = validHexMoves[item].Y;
                        //alert("hex_"+x+"_"+y);
                        //var elem = dojo.byId("hex_"+x+"_"+y);
                        //f(elem != null){
                            //dojo.addClass("hex_"+x+"_"+y, 'active');
                        //}
                        dojo.addClass(validHexMoves[item], 'active');
                    }
                    
                    break;
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
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
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                    case 'births' :
                        this.addActionButton( 'button_1_id', _('stuff'), 'onNurseAllocationFinished' ); 
                        break;
                        
                    case 'placeWorker' :
                    case 'atelier' :
                        this.addActionButton( 'button_pass', _('Pass'), 'onPass' ); 
                        break;
                    
                    case 'harvest' :
                        this.addActionButton( 'button_harvest', _('Done'), 'onHarvestChosen' ); 
                        break;
                        
                    case 'storage' :
                        this.addActionButton( 'button_storage', _('Done'), 'onStorageChosen' ); 
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
        
        //todo - will need a colour and id at some point.
        placeActiveWorker: function(x, y)
        {
            dojo.place(
                this.format_block('jstpl_worker', {
                    id: "worker_"+123,
                }), "hex_"+x+"_"+y);
            this.placeOnObject("worker_"+123, "hex_"+x+"_"+y);            
        },
        
        moveActiveWorker: function(x, y)
        {
            //this.placeOnObject("worker_"+123, "hex_"+x+"_"+y);
            this.slideToObjectPos("worker_"+123, "hex_"+x+"_"+y, 0, 0, 500, 100).play();            
        },
       
       
        setPlayerResourceCount: function (player_id, resource, count)
        {
            $(resource +'count_p'+player_id).innerHTML = count;
        },
       
       
        setAllocation:function(allocated, can_allocate, can_deallocate)
        {
            dojo.query('.js-larvae').removeClass('allocated');
            dojo.query('.js-larvae').removeClass('active');                 
           
            for (var item in allocated)
            {
                dojo.addClass(allocated[item], 'allocated');
            }
           
            for (var item in can_allocate)
            {
                dojo.addClass(can_allocate[item], 'active');
            }
                    
            for (var item in can_deallocate)
            {
                dojo.addClass(can_deallocate[item], 'active');
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
            
            //if( ! this.checkAction( 'pass' ) )
            //{
            //    return;
            //}

            this.ajaxcall( "/akmyrmes/akmyrmes/harvestChosen.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        onStorageChosen: function(evt)
        {
            dojo.stopEvent( evt );           
            
            //if( ! this.checkAction( 'pass' ) )
            //{
            //    return;
            //}

            this.ajaxcall( "/akmyrmes/akmyrmes/storageChosen.html", { 
                    lock: true, 
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
       
        onPass: function(evt)
        {
            dojo.stopEvent( evt );           
            
            if( ! this.checkAction( 'pass' ) )
            {
                return;
            }

            this.ajaxcall( "/akmyrmes/akmyrmes/pass.html", { 
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

            this.ajaxcall( "/akmyrmes/akmyrmes/allocateNurseFinished.html", { 
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
        
        onHexClicked: function(evt)
        {
            dojo.stopEvent(evt);
            
            if (!dojo.hasClass(evt.currentTarget.id, "active"))
            {
                return;
            }
            
            var x = dojo.attr(evt.currentTarget.id, 'data-x');
            var y = dojo.attr(evt.currentTarget.id, 'data-y');
            
            this.ajaxcall( "/akmyrmes/akmyrmes/hexClicked.html", { 
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
            
            this.ajaxcall( "/akmyrmes/akmyrmes/activateColony.html", { 
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

            this.ajaxcall( "/akmyrmes/akmyrmes/allocateNurse.html", { 
                lock: true,
                type: type,
                slot: slot,
                }, 
                this, function( result ) { }, function( is_error) { } );        
        },
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/akmyrmes/akmyrmes/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        
        ///////////////////////////////////////////////////
        //// Reaction to notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your akmyrmes.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'allocationConfirmed', this, "notif_allocationConfirmed" );
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
            
            dojo.subscribe( 'yourNursesAllocated', this, "notif_playerNursesAllocated" );
            
            dojo.subscribe('workerPlaced', this, "notif_workerPlaced");
            dojo.subscribe('workerMoved', this, "notif_workerMoved");
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_workerMoved: function(notif)
        {
            this.moveActiveWorker(notif.args.x, notif.args.y);
        },
        
        notif_workerPlaced: function(notif)
        {
            this.placeActiveWorker(notif.args.x, notif.args.y);
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
            
            this.setAllocation(allocated, can_allocate, can_deallocate);
        },    
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
