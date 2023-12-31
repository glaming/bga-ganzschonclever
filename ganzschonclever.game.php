<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * GanzSchonClever implementation : © <Your name here> <Your email address here>
  *
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  *
  * ganzschonclever.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class GanzSchonClever extends Table
{
    function __construct( )
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels( array(
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );
    }

    protected function getGameName( )
    {
        // Used for translations and stuff. Please do not modify.
        return "ganzschonclever";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Additionally: set the first player as the "Active Player" (per game definition)

        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, is_active_player) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."',".( $player_id == array_key_first($players) ? 1 : 0 ).")";
        }
        $sql .= implode( ',', $values );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here
        GSCDiceManager::setupNewGame();
        GSCDiceSelectionManager::setupNewGame();

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );

        // TODO: Gather all information about current game situation (visible by player $current_player_id).
        $diceState = new GSCDiceState();
        $result['dice'] = $diceState->getState();

        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function getGameActivePlayerId()
    {
        $sql = "SELECT player_id FROM player WHERE is_active_player = 1";
        return self::getUniqueValueFromDB($sql);
    }

    function isCurrentPlayerGameActivePlayer()
    {
        return self::getCurrentPlayerId() == self::getGameActivePlayerId();
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in ganzschonclever.action.php)
    */

    /*

    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' );

        $player_id = self::getActivePlayerId();

        // Add your game logic to play a card there
        ...

        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );

    }

    */

    function chooseDie( $dieColor )
    {
        self::checkAction( 'chooseDie' );

        // Validate argument
        if (!in_array($dieColor, GSCDiceManager::getDiceColors()))
        {
            throw new BgaUserException( self::_("Invalid die color") );
        }

        // Based on game state, use action handler
        $state = $this->gamestate->state();
        $stateName = $state['name'];

        if( $stateName == 'activePlayerTurn' )
        {
            self::activePlayerChoosesDie( $dieColor );
        }
        else if( $stateName == 'simultaneousDiceSelectionAndSheetMarking')
        {
            // getPrivateState has syntax error if private states not set-up
            $privateState = $this->gamestate->getPrivateState( self::getCurrentPlayerId() );
            $privateStateName = $privateState['name'];

            if ($privateStateName == 'chooseDieForScoreSheet')
            {
                self::chooseDieForScoreSheet( $dieColor );
            } else {
                throw new BgaUserException( self::_("Invalid game state '{$stateName} and private game state {$privateStateName} for action chooseDie") );
            }

        }
        else {
            throw new BgaUserException( self::_("Invalid game state '{$stateName} for action chooseDie") );
        }
    }

    function activePlayerChoosesDie( $dieColour )
    {
        // Check that the die is available to be chosen & count how many dice are already chosen
        if (!GSCDiceManager::isDieInRolledArea($dieColour))
        {
            throw new BgaUserException( self::_("This die isn't available to be chosen") );
        }

        $result = GSCDiceManager::activePlayerChoosesRolledDie($dieColour);

        // Notify all players about the die selected
        self::notifyAllPlayers( "activePlayerChoseDie", clienttranslate( '${player_name} chooses ${color}' ), array(
            'player_name' => self::getActivePlayerName(),
            'color' => $dieColour,
            'chosen_order' => $result['chosenOrder']
        ));

        // Notify all players about any dice moved to the silver platter
        $diceMovedToPlatter = $result['diceMovedToPlatter'];
        if (count($diceMovedToPlatter) > 0) {
            self::notifyAllPlayers( "diceMovedToPlatter", clienttranslate( '${colors_uc} dice moved to the silver platter' ), array(
                'colors_uc' => implode(", ", array_map('ucwords', $diceMovedToPlatter)),
                'dice' => $diceMovedToPlatter
            ));
        }

        $this->gamestate->nextState( 'chosenDie' );
    }

    function chooseDieForScoreSheet( $dieColor )
    {
        // Check that the die is available to be chosen
        $isActivePlayer = self::isCurrentPlayerGameActivePlayer();
        $availableDiceSelectionColors = GSCDiceSelectionManager::getAvailableColorSelectionsForPlayer( self::getCurrentPlayerId(), $isActivePlayer );

        if (!in_array($dieColor, $availableDiceSelectionColors))
        {
            throw new BgaUserException( self::_("This die isn't available to be chosen") );
        }

        // Set die as chosen
        // playerSelectsDie - should likely happen post marking score sheet to allow for an "undo"
        GSCDiceSelectionManager::playerSelectsDie( self::getCurrentPlayerId(), $dieColor );

        $sqlUpdatePlayer = "UPDATE player SET simultaneous_play_die_color_selected = '$dieColor' WHERE player_id = " . (self::getCurrentPlayerId());
        self::DbQuery($sqlUpdatePlayer);

        $this->gamestate->nextPrivateState(self::getCurrentPlayerId(), 'markScoreSheet');
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*

    Example for game state "MyGameState":

    function argMyGameState()
    {
        // Get some values from the current game situation in database...

        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }
    */

    function argActivePlayerTurn()
    {
        $diceState = new GSCDiceState();
        return array(
            'dice' => $diceState->getState()
        );
    }

    function argAvailableDiceForScoreSheet( $player_id )
    {
        $isActivePlayer = self::isCurrentPlayerGameActivePlayer();

        return array(
            'availableDice' => GSCDiceSelectionManager::getAvailableColorSelectionsForPlayer( $player_id, $isActivePlayer )
        );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    /*

    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...

        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }
    */

    function stActivePlayerDieChosen()
    {
        $ds = new GSCDiceState();

        // Check if the player has chosen all 3 dice
        // If they have, move all remaining dice to the silver platter and notify players
        if (count($ds->getActiveColorsOrdered()) == 3)
        {

            // Check if any remaining dice to move
            $rolledDice = $ds->getRolledColors();
            if (count($rolledDice) > 0)
            {
                GSCDiceManager::moveAllRolledDieToPlatter();

                // Notify all players about the dice moved to the silver platter
                self::notifyAllPlayers( "diceMovedToPlatter", clienttranslate( 'Remaining ${colors_uc} dice moved to the silver platter' ), array(
                    'colors_uc' => implode(", ", array_map('ucwords', $rolledDice)),
                    'dice' => $rolledDice
                ));
            }

            $this->gamestate->nextState( 'activePlayerDieChoosingComplete' );
            return;
        }

        // Check if there any dice left to choose
        if (count($ds->getRolledColors()) == 0)
        {
            $this->gamestate->nextState( 'activePlayerDieChoosingComplete' );
            return;
        }

        // Roll the remaining dice left in rolled area
        $rolledDiceUpdatedValues = GSCDiceManager::rollRemaingDiceInRollArea();

        // Notify all players about the die selected
        self::notifyAllPlayers( "diceRolled", clienttranslate( '${player_name} re-rolls remaining dice' ), array(
            'player_name' => self::getActivePlayerName(),
            'rolled_dice_updated_values' => $rolledDiceUpdatedValues
        ));

        $this->gamestate->nextState( 'activePlayerTurn' );
    }

    function stInitSimultaneousDiceSelectionAndSheetMarking()
    {
        $this->gamestate->setAllPlayersMultiactive();
        $this->gamestate->initializePrivateStateForAllActivePlayers();
    }

    function stChooseDieForScoreSheet( $player_id )
    {
        $isGameActivePlayer = self::isCurrentPlayerGameActivePlayer();

        $diceSelections = GSCDiceSelectionManager::getPlayerDiceSelectionState( $player_id );

        // Passive players allowed 1 die selection, active players allowed 3
        $diceSelectionsAllowed = 1;
        if ($isGameActivePlayer)
        {
            $diceSelectionsAllowed = 3;
        }

        if (count($diceSelections) == $diceSelectionsAllowed)
        {
            $this->gamestate->setPlayerNonMultiactive( $player_id, 'nextRound' );
            return;
        }

        // Check there are still die available to select for active player
        if ($isGameActivePlayer && count(GSCDiceSelectionManager::getAvailableColorSelectionsForPlayer( $player_id, $isGameActivePlayer )) == 0)
        {
            $this->gamestate->setPlayerNonMultiactive( $player_id, 'nextRound' );
            return;
        }

        // Otherwise, do nothing - allow player to select another die
    }

    function stNewRoundBegin()
    {

    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).

        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
    */

    function zombieTurn( $state, $active_player )
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );

            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }
}
