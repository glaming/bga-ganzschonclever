<?php

class GSCDiceManager extends APP_GameClass
{
    public static function setupNewGame()
    {
        self::createDice();
    }

    public static function activePlayerChoosesRolledDie($chosenDieColor)
    {
        $ds = new GSCDiceState();

        // Move chosen die to active area to the next available position
        $nextPositionValue = count($ds->getActiveColorsOrdered()) + 1;
        $sql = "UPDATE dice SET placement = 'active', chosen_order = $nextPositionValue WHERE color = '$chosenDieColor'";
        self::DbQuery($sql);

        // Move all dice not selected that have a value less than the chosen dice to the silver platter
        $chosenDieValue = $ds->getValue($chosenDieColor);
        $rolledDice = $ds->getRolledColors();

        $diceToMoveToPlatter = array();
        foreach ($rolledDice as $dieColor)
        {
            $dieValue = $ds->getValue($dieColor);
            if ($chosenDieColor != $dieColor && $dieValue < $chosenDieValue)
            {
                $diceToMoveToPlatter[] = $dieColor;
            }
        }

        if (count($diceToMoveToPlatter) > 0) {
            $sql = "UPDATE dice SET placement = 'platter' WHERE color IN ('" . implode("','", $diceToMoveToPlatter) . "')";
            self::DbQuery($sql);
        }

        return array(
            'chosenOrder' => $nextPositionValue,
            'diceMovedToPlatter' => $diceToMoveToPlatter
        );
    }

    public static function getDiceColors()
    {
        return array('white', 'yellow', 'blue', 'green', 'orange', 'purple');
    }

    public static function getDBState() {
        $sql = "SELECT color, placement, value, chosen_order FROM dice";
        return self::getObjectListFromDB($sql);
    }

    public static function isDieInRolledArea($dieColor)
    {
        $ds = new GSCDiceState();
        $rolledDice = $ds->getRolledColors();
        return in_array($dieColor, $rolledDice);
    }

    public static function moveAllRolledDieToPlatter()
    {
        $sql = "UPDATE dice SET placement = 'platter' WHERE placement = 'rolled'";
        self::DbQuery($sql);
    }

    public static function moveDieToPlatter($dieColor)
    {
        $sql = "UPDATE dice SET placement = 'platter' WHERE color = '$dieColor'";
        self::DbQuery($sql);
    }

    public static function rollRemaingDiceInRollArea()
    {
        $ds = new GSCDiceState();

        $diceToRoll = $ds->getRolledColors();
        $newValues = self::getNewRolledDiceValues(count($diceToRoll));

        $rolledDiceUpdatedValues = array();
        $sql = "UPDATE dice SET placement = 'rolled', value = CASE color ";

        for ($i=0; $i<count($diceToRoll); $i++)
        {
            $rolledDiceUpdatedValues[$diceToRoll[$i]] = $newValues[$i];
            $sql .= "WHEN '$diceToRoll[$i]' THEN $newValues[$i] ";
        }

        $sql .= "END WHERE color IN ('" . implode("','", $diceToRoll) . "')";
        self::DbQuery($sql);

        return $rolledDiceUpdatedValues;
    }

    private function createDice()
    {
        $sql = "INSERT INTO dice (color, placement, value) VALUES ";
        $sql_values = array();

        $colors = self::getDiceColors();
        $values = self::getNewRolledDiceValues(count($colors));

        foreach( $colors as $i => $c )
        {
            $dice_value = $values[$i];
            $sql_values[] = "('$c', 'rolled', $dice_value)";
        }

        $sql .= implode(',', $sql_values);
        self::DbQuery($sql);
    }

    private function getNewRolledDiceValues( $n )
    {
        $values = array();

        for($i=0; $i<$n; $i++)
        {
            $values[] = bga_rand(1, 6);
        }

        return $values;
    }
}