{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- akMyrmes implementation : © Andrew Kerrison <adesignforlife@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->
<div id="board">
    <!-- atelier-->
    <svg id="atelierSVG_nurse" class="active" style="z-index: 150; position:absolute; top: 130px; left: 125px;" xmlns="http://www.w3.org/2000/svg" version="1.1" width="125" height="110" xmlns:xlink="http://www.w3.org/1999/xlink">         
         <polygon id="atelierPoly_nurse" data-action="nurse" class="hex atelierHex" points="30,2 2,55 30,108 93,108 123,55 93,2"></polygon>
    </svg>
    <svg id="atelierSVG_level" class="active" style="z-index: 150; position:absolute; top: 7px; left: 125px;" xmlns="http://www.w3.org/2000/svg" version="1.1" width="125" height="110" xmlns:xlink="http://www.w3.org/1999/xlink">         
         <polygon id="atelierPoly_level" data-action="level" class="hex atelierHex" points="30,2 2,55 30,108 93,108 123,55 93,2"></polygon>
    </svg>  
    <svg id="atelierSVG_tunnel" class="active" style="z-index: 150; position:absolute; top: 68px; left: 19px;" xmlns="http://www.w3.org/2000/svg" version="1.1" width="125" height="110" xmlns:xlink="http://www.w3.org/1999/xlink">         
         <polygon id="atelierPoly_tunnel" data-action="tunnel" class="hex atelierHex" points="30,2 2,55 30,108 93,108 123,55 93,2"></polygon>
    </svg>  
    
    <!-- BEGIN hexold -->
    <!--<div id="hex_{X}_{Y}" data-x="{X}" data-y="{Y}" class="hex" style="position:absolute; top: {YPOS}px; left: {XPOS}px;">
        <div id="hex_clickable_{X}_{Y}" class="hexClickable">{X},{Y}</div>
    </div>-->
    <!-- END hexold -->
    <!-- BEGIN hex -->
    <div id="hex_{X}_{Y}" class="hexHolder" style="position:absolute; top: {YPOS}px; left: {XPOS}px;">&nbsp;&nbsp;{X},{Y}</div>
    <svg id="hexSVG_{X}_{Y}" class="" style="z-index: 150; position:absolute; top: {YPOS}px; left: {XPOS}px;" xmlns="http://www.w3.org/2000/svg" version="1.1" width="55" height="55" xmlns:xlink="http://www.w3.org/1999/xlink">         
         <polygon id="hexPoly_{X}_{Y}" data-x="{X}" data-y="{Y}" class="hex" points="0,12 22,0 44,12 44,38 22,50 0,38"></polygon>
    </svg>    
    <!-- END hex -->
    <!-- BEGIN hexUnusable -->
    <div id="hex_{X}_{Y}" class="hexHolder" style="position:absolute; top: {YPOS}px; left: {XPOS}px;">&nbsp;&nbsp;{X},{Y}</div>
    <svg id="hexSVG_{X}_{Y}" class="" style="position:absolute; top: {YPOS}px; left: {XPOS}px;" xmlns="http://www.w3.org/2000/svg" version="1.1" width="55" height="55" xmlns:xlink="http://www.w3.org/1999/xlink">         
         <polygon id="hexPoly_{X}_{Y}" data-x="{X}" data-y="{Y}" class="hex unusable" points="0,10 23,0 46,10 46,40 23,50 0,40"></polygon>
    </svg>    
    <!-- END hexUnusable -->
</div>
<div id="eventBoard">
</div>    
<div class="supply">
    <div id="stock_0_9" class="tile stockItem rot90 tileType9a neutral9 padLeft">X</div>
    <div id="stock_0_10" class="tile stockItem rot90 tileType10 neutral10 padLeft">X</div>
</div>
        
<!-- BEGIN playerBoard --> 
<div class="playerBoard">
    <h2>{PLAYER_NAME}</h2>    
</div>
<div class="supply">
    <div id="stock_{PLAYER_ID}_worker" class="stockItem worker {PLAYER_COLOR}Worker">X</div>
    <div id="stock_{PLAYER_ID}_nurse" class="stockItem nurse {PLAYER_COLOR}Nurse">X</div>
    <div id="stock_{PLAYER_ID}_special" class="stockItem special {PLAYER_COLOR}Special">X</div>
    <div id="stock_{PLAYER_ID}_1" class="tile stockItem rot90 tunnel {PLAYER_COLOR}Tunnel">X</div>
    <div id="stock_{PLAYER_ID}_2" class="tile stockItem rot90 tileType2 {PLAYER_COLOR}2">X</div>
    <div id="stock_{PLAYER_ID}_3" class="tile stockItem rot90 tileType3 {PLAYER_COLOR}3 padLeft">X</div>
    <div id="stock_{PLAYER_ID}_4" class="tile stockItem rot90 tileType4 {PLAYER_COLOR}4">X</div>
    <div id="stock_{PLAYER_ID}_5" class="tile stockItem rot90 tileType5 {PLAYER_COLOR}5 padLeft">X</div>
    <div id="stock_{PLAYER_ID}_6" class="tile stockItem rot90 tileType6 {PLAYER_COLOR}6">X</div>
    <div id="stock_{PLAYER_ID}_7" class="tile stockItem rot90 tileType7 {PLAYER_COLOR}7 padLeft">X</div>
    <div id="stock_{PLAYER_ID}_8" class="tile stockItem rot90 tileType8 {PLAYER_COLOR}8 padLeft">X</div>
</div>
<!-- END playerBoard --> 



<div class="temp js-larvae" data-type="larvae" data-index="1" id="larvae_1">Larvae 1</div>
<div class="temp js-larvae" data-type="larvae" data-index="2" id="larvae_2">Larvae 2</div>
<div class="temp js-larvae" data-type="larvae" data-index="3" id="larvae_3">Larvae 3</div>
<div class="temp js-larvae" data-type="soldier" data-index="1" id="soldier_1">Soldier 1</div>
<div class="temp js-larvae" data-type="soldier" data-index="2" id="soldier_2">Soldier 2</div>
<div class="temp js-larvae" data-type="worker" data-index="1" id="worker_1">Worker 1</div>
<div class="temp js-larvae" data-type="worker" data-index="2" id="worker_2">Worker 2</div>
<div class="temp js-larvae" data-type="atelier" data-index="1" id="atelier_1">Atelier 1</div>
<div class="temp js-larvae" data-type="atelier" data-index="2" id="atelier_2">Atelier 2</div>
<div class="temp js-larvae" data-type="atelier" data-index="3" id="atelier_3">Atelier 3</div>
<div class="temp js-larvae" data-type="atelier" data-index="4" id="atelier_4">Atelier 4</div>

<div class="temp js-larvae" data-type="event" data-index="0" id="event_0">Level +1</div>
<div class="temp js-larvae" data-type="event" data-index="1" id="event_1">VP +1</div>
<div class="temp js-larvae" data-type="event" data-index="2" id="event_2">Larvae +2</div>
<div class="temp js-larvae" data-type="event" data-index="3" id="event_3">Harvest +3</div>
<div class="temp js-larvae" data-type="event" data-index="4" id="event_4">Move +3</div>
<div class="temp js-larvae" data-type="event" data-index="5" id="event_5">Solder +1</div>
<div class="temp js-larvae" data-type="event" data-index="6" id="event_6">Hex +1</div>
<div class="temp js-larvae" data-type="event" data-index="7" id="event_7">Worker +1</div>

<div class="temp js-colony" data-type="colony" data-index="0" id="colony_0">Colony 0</div>
<div class="temp js-colony" data-type="colony" data-index="1" id="colony_1">Colony 1</div>
<div class="temp js-colony" data-type="colony" data-index="2a" id="colony_2a">Colony 2 (stone)</div>
<div class="temp js-colony" data-type="colony" data-index="2b" id="colony_2b">Colony 2 (dirt)</div>
<div class="temp js-colony" data-type="colony" data-index="3" id="colony_3">Colony 3</div>

<script type="text/javascript">

// Javascript HTML templates

var jstpl_player_iconsA = '<div class="iconholder" style="margin-top:5px;">' +
        '<div id="colonyicon_${id}" class="colonyicon">C:</div><div id="colonycount_p${id}" class="colonycount">0</div>' +
        '<div class="foodicon">F:</div><div id="foodcount_p${id}" class="foodcount">0</div>' + 
        '<div class="stoneicon">St:</div><div id="stonecount_p${id}" class="stonecount">0</div>' + 
        '<div class="dirticon">D:</div><div id="dirtcount_p${id}" class="dirtcount">0</div>' +
        '</div>';
var jstpl_player_iconsB = '<div class="iconholder">' +
        '<div id="nurseicon_${id}" class="nurseicon">N:</div><div id="nursecount_p${id}" class="nursecount">0</div>' +
        '<div id="larvaeicon_${id}" class="larvaeicon">L:</div><div class="larvaecount" id="larvaecount_p${id}">0</div>' +
        '<div id="soldiericon_${id}" class="soldiericon">S:</div><div class="soldiercount" id="soldiercount_p${id}">0</div>' + 
        '<div class="workericon">W:</div><div class="workercount" id="workercount_p${id}">0</div>'+
        '</div>';

var jstpl_dice = '<div id="event_${id}" class="eventDice season_${id} event${number}"></div>';
var jstpl_tunnel = '<div id="${id}" class="tunnel ${color}Tunnel"></div>';
var jstpl_pheromone = '<div id="${id}" class="${class} rot90 tileType${type} ${color}${type}"></div>';
var jstpl_worker = '<div id="${id}" class="worker ${color}Worker"></div>';
var jstpl_bug = '<div id="${id}" class="bug bug${bugType}"></div>';
var jstpl_resource = '<div id="${id}" class="${class} ${tokenType}"></div>';
var jstpl_year = '<div id="yearMarker"></div>';

</script>  

{OVERALL_GAME_FOOTER}
