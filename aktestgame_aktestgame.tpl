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
<div class="playerBoard" id="{PLAYER_ID}_board">
    <h2>{PLAYER_NAME}</h2>    
	
	<!-- placeholder for solider/worker icon on player board -->
	<div class="worker {PLAYER_COLOR}Worker soldierCount" id="soldierCount_{PLAYER_ID}"></div>
	<div class="worker {PLAYER_COLOR}Worker workerCount" id="workerCount_{PLAYER_ID}"></div>
	
	<!-- placeholders for nurse slots on player board -->
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse addLarvae1" data-type="larvae" data-index="1" id="{PLAYER_COLOR}_larvae_1"></div>
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse addLarvae2" data-type="larvae" data-index="2" id="{PLAYER_COLOR}_larvae_2"></div>
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse addLarvae3" data-type="larvae" data-index="3" id="{PLAYER_COLOR}_larvae_3"></div>
	<div class="nurseIconDouble js-nurseSlot {PLAYER_COLOR}Nurse soldier1" data-type="soldier" data-index="1" id="{PLAYER_COLOR}_soldier_1"></div>
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse soldier2" data-type="soldier" data-index="2" id="{PLAYER_COLOR}_soldier_2"></div>
	<div class="nurseIconDouble js-nurseSlot {PLAYER_COLOR}Nurse worker1" data-type="worker" data-index="1" id="{PLAYER_COLOR}_worker_1"></div>
	<div class="nurseIconDouble js-nurseSlot {PLAYER_COLOR}Nurse worker2" data-type="worker" data-index="2" id="{PLAYER_COLOR}_worker_2"></div>
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse atelier1" data-type="atelier" data-index="1" id="{PLAYER_COLOR}_atelier_1"></div>
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse atelier2" data-type="atelier" data-index="2" id="{PLAYER_COLOR}_atelier_2"></div>
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse atelier3" data-type="atelier" data-index="3" id="{PLAYER_COLOR}_atelier_3"></div>
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse atelier4" data-type="atelier" data-index="4" id="{PLAYER_COLOR}_atelier_4"></div>
	<div class="nurseIcon js-nurseSlot {PLAYER_COLOR}Nurse nurse1" data-type="nurse" data-index="1" id="{PLAYER_COLOR}_nurses"></div>
	
	<!-- placeholders for event icons on player board -->
	<div class="eventSlot js-eventSlot {PLAYER_COLOR}Event event1" data-type="event" data-index="0" id="{PLAYER_COLOR}_event_0"></div>
	<div class="eventSlot js-eventSlot {PLAYER_COLOR}Event event2" data-type="event" data-index="1" id="{PLAYER_COLOR}_event_1"></div>
	<div class="eventSlot js-eventSlot {PLAYER_COLOR}Event event3" data-type="event" data-index="2" id="{PLAYER_COLOR}_event_2"></div>
	<div class="eventSlot js-eventSlot {PLAYER_COLOR}Event event4" data-type="event" data-index="3" id="{PLAYER_COLOR}_event_3"></div>
	<div class="eventSlot js-eventSlot {PLAYER_COLOR}Event event5" data-type="event" data-index="4" id="{PLAYER_COLOR}_event_4"></div>
	<div class="eventSlot js-eventSlot {PLAYER_COLOR}Event event6" data-type="event" data-index="5" id="{PLAYER_COLOR}_event_5"></div>
	<div class="eventSlot js-eventSlot {PLAYER_COLOR}Event event7" data-type="event" data-index="6" id="{PLAYER_COLOR}_event_6"></div>
	<div class="eventSlot js-eventSlot {PLAYER_COLOR}Event event8" data-type="event" data-index="7" id="{PLAYER_COLOR}_event_7"></div>
	
	<!-- placeholders for larvae -->
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae1" id="{PLAYER_ID}_larvae_1"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae2" id="{PLAYER_ID}_larvae_2"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae3" id="{PLAYER_ID}_larvae_3"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae4" id="{PLAYER_ID}_larvae_4"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae5" id="{PLAYER_ID}_larvae_5"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae6" id="{PLAYER_ID}_larvae_6"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae7" id="{PLAYER_ID}_larvae_7"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae8" id="{PLAYER_ID}_larvae_8"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae9" id="{PLAYER_ID}_larvae_9"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae10" id="{PLAYER_ID}_larvae_10"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae11" id="{PLAYER_ID}_larvae_11"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae12" id="{PLAYER_ID}_larvae_12"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae13" id="{PLAYER_ID}_larvae_13"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae14" id="{PLAYER_ID}_larvae_14"></div>
	<div class="larvaeSlot Larvae{PLAYER_ID} larvae15" id="{PLAYER_ID}_larvae_15"></div>
	
	<!-- placeholders for colony -->
	<div class="colonySlot colony0 js-colony" data-type="colony" data-index="0" id="{PLAYER_ID}_colony_0">C0</div>
	<div class="colonySlot colony1 js-colony" data-type="colony" data-index="1" id="{PLAYER_ID}_colony_1">C1</div>
	<div class="colonySlot colony2a js-colony" data-type="colony" data-index="2a" id="{PLAYER_ID}_colony_2a">C2 (S)</div>
	<div class="colonySlot colony2b js-colony" data-type="colony" data-index="2b" id="{PLAYER_ID}_colony_2b">C2 (D)</div>
	<div class="colonySlot colony3 js-colony" data-type="colony" data-index="3" id="{PLAYER_ID}_colony_3">C3</div>
	
	<div class="colonyMarker {PLAYER_COLOR}Colony" id="{PLAYER_ID}_colony_marker"></div>
	
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
