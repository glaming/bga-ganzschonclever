<?php


class GSCDiceSelectionManager extends APP_GameClass
{
    public static function setupNewGame()
    {
        // TODO: Implement
    }

    public static function getAvailableColorSelectionsForPlayer($player_id, $is_active_player)
    {
        $ds = new GSCDiceState();

        // Default values for the passive player
        $availableDiceInPlacement = $ds->getPlatterColors();
        $previouslySelectedDiceColors = array();

        // Active player is able to select die only from active pool, for dice not already chosen
        if ($is_active_player)
        {
            $availableDiceInPlacement = $ds->getActiveColorsOrdered();
            $previouslySelectedDiceColors = array_column( self::getPlayerDiceSelectionState( $player_id ), 'color' );
        }

        return array_values(array_diff($availableDiceInPlacement, $previouslySelectedDiceColors));
    }

    public static function getPlayerDiceSelectionState($player_id)
    {
        $sql = "SELECT color, how_selected FROM dice_selections WHERE player_id = $player_id";
        return self::getObjectListFromDB($sql);
    }

    public static function playerSelectsDie($player_id, $die_color)
    {
        $sql = "INSERT INTO dice_selections (player_id, color, how_selected) VALUES ($player_id, '$die_color', 'standard')";
        self::DbQuery($sql);
    }
}