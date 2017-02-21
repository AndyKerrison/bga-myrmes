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
<a href="akmyrmes_akmyrmes.tpl"></a>


<div id="board">
    <!-- BEGIN hex -->
    <div id="hex_{X}_{Y}" data-x="{X}" data-y="{Y}" class="hex" style="position:absolute; top: {YPOS}px; left: {XPOS}px;">
        <div id="hex_clickable_{X}_{Y}" class="hexClickable">{X},{Y}</div>
    </div>
    <!-- END hex -->
</div>

<!-- BEGIN playerBoard --> 
<div>
    <h2>{PLAYER_NAME}</h2>    
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

var jstpl_tunnel = '<div id="${id}" class="tunnel ${color}"></div>';
var jstpl_worker = '<div id="${id}" class="worker"></div>';

</script>  

{OVERALL_GAME_FOOTER}
