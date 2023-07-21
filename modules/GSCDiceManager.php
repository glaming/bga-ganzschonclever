<?php

class GSCDiceManager extends APP_GameClass
{
    public static function setupNewGame()
    {
        self::createDice();
    }

    public static function activePlayerChoosesRolledDie($dieColor)
    {
        $dice = self::getDiceState();

        $selectedCount = 1;
        $selectedDieValue = 0;
        foreach ($dice as $die)
        {
            if ($die['placement'] == 'active')
            {
                $selectedCount++;
            }

            if ($die['color'] == $dieColor)
            {
                $selectedDieValue = $die['value'];
            }
        }

        $sql = "UPDATE dice SET placement = 'active', chosen_order = $selectedCount WHERE color = '$dieColor'";
        self::DbQuery($sql);

        // Move all dice not selected that have a value less than the chosen dice to the silver platter
        $diceToMoveToPlatter = array();
        foreach ($dice as $die)
        {
            if ($die['placement'] == 'rolled' && $die['color'] != $dieColor && $die['value'] < $selectedDieValue)
            {
                $diceToMoveToPlatter[] = $die['color'];
            }
        }

        if (count($diceToMoveToPlatter) > 0) {
            $sql = "UPDATE dice SET placement = 'platter' WHERE color IN ('" . implode("','", $diceToMoveToPlatter) . "')";
            self::DbQuery($sql);
        }

        return array(
            'chosenOrder' => $selectedCount,
            'diceMovedToPlatter' => $diceToMoveToPlatter
        );
    }

    public static function getDiceColors()
    {
        return array('white', 'yellow', 'blue', 'green', 'orange', 'purple');
    }

    public static function getDiceState() {
        $sql = "SELECT color, placement, value, chosen_order FROM dice";
        return self::getObjectListFromDB($sql);
    }

    public static function getDiceStateV2() {
        $dice = self::getDiceState();

        $rolledDice = array();
        $activeDice = array();
        $platterDice = array();
        $values = array();

        foreach ($dice as $die)
        {
            $values[$die['color']] = $die['value'];

            if ($die['placement'] == 'rolled')
            {
                $rolledDice[] = $die['color'];
            }
            else if ($die['placement'] == 'active') {
                $activeDice[] = $die['color'];
            }
            else if ($die['placement'] == 'platter') {
                $platterDice[] = $die['color'];
            }
        }

        return array(
            'rolled' => $rolledDice,
            'active' => $activeDice,
            'platter' => $platterDice,
            'values' => $values
        );
    }

    public static function isDieInRolledArea($dieColor)
    {
        $dice = self::getDiceState();
        foreach ($dice as $die)
        {
            if ($die['color'] == $dieColor)
            {
                if ($die['placement'] != 'rolled')
                {
                    return false;
                } else {
                    return true;
                }
            }
        }

        return false;
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
        $diceState = self::getDiceStateV2();
        $diceToRoll = $diceState['rolled'];

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