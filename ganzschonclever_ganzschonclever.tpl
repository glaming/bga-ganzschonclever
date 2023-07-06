{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- GanzSchonClever implementation : © <Your name here> <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    ganzschonclever_ganzschonclever.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="dice-selection-container">
    <div class="dice-selection-half whiteblock">
        Silver Platter
        <div id="silver-platter">
            <div class="die-placeholder-container">
                <div id="die-placeholder-platter-white" class="die-placeholder"></div>
                <div id="die-placeholder-platter-yellow" class="die-placeholder"></div>
                <div id="die-placeholder-platter-blue" class="die-placeholder"></div>
                <div id="die-placeholder-platter-green" class="die-placeholder"></div>
                <div id="die-placeholder-platter-orange" class="die-placeholder"></div>
                <div id="die-placeholder-platter-purple" class="die-placeholder"></div>
            </div>
        </div>
    </div>

    <div class="dice-selection-half">
        <div id="dice-rolling-area" class="whiteblock">
            Rolled Dice
            <div class="die-placeholder-container">
                <div id="die-placeholder-rolled-white" class="die-placeholder"></div>
                <div id="die-placeholder-rolled-yellow" class="die-placeholder"></div>
                <div id="die-placeholder-rolled-blue" class="die-placeholder"></div>
                <div id="die-placeholder-rolled-green" class="die-placeholder"></div>
                <div id="die-placeholder-rolled-orange" class="die-placeholder"></div>
                <div id="die-placeholder-rolled-purple" class="die-placeholder"></div>
            </div>
        </div>
        <div id="active-plater-selection" class="whiteblock">
            Active Player Chosen
            <div class="die-placeholder-container">
                <div id="die-placeholder-active-1" class="die-placeholder"></div>
                <div id="die-placeholder-active-2" class="die-placeholder"></div>
                <div id="die-placeholder-active-3" class="die-placeholder"></div>
            </div>
        </div>
    </div>

    <div id="die-container">
    </div>
</div>


<div id="player-sheet">
</div>


<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

var jstpl_die='<div id="die-color-${color}" class="die die-color-${color} die-face-${pips}"></div>';

</script>  

{OVERALL_GAME_FOOTER}
