<?php

/**
 * Represents a snapshot of the dice state at the point of instantiation
 *
 * Provides helper methods to get the current dice state in various formats
 */
class GSCDiceState extends APP_GameClass
{
    private $diceState;

    public function __construct()
    {
        $this->diceState = GSCDiceManager::getDBState();
    }

    /**
     * Get the colors of dice currently the rolled area
     *
     * @return string[]
     */
    public function getRolledColors()
    {
        return $this->getState()['placements']['rolled'];
    }

    /**
     * Get the colors of dice currently on the platter
     *
     * @return string[]
     */
    public function getPlatterColors()
    {
        return $this->getState()['placements']['platter'];
    }

    /**
     * Get the colors of dice currently in the active area
     *
     * Values in the order chosen by the active player
     *
     * @return string[]
     */
    public function getActiveColorsOrdered()
    {
        return $this->getState()['placements']['active'];
    }

    /**
     * Get associative array of dice values keyed by color
     *
     * @return array
     */
    public function getValues()
    {
        return $this->getState()['values'];
    }

    /**
     * Get value of die of given color
     *
     * @param string $color
     * @return int
     */
    public function getValue($color)
    {
        return $this->getValues()[$color];
    }

    public function getState()
    {
        $state = array(
            'placements' => array(
                'rolled' => array(),
                'active' => array(),
                'platter' => array()
            ),
            'values' => array()
        );

        foreach ($this->diceState as $die)
        {
            $state['values'][$die['color']] = $die['value'];
            $state['placements'][$die['placement']][] = $die['color'];
        }

        // Sort the active by chosen order
        usort($state['placements']['active'], function($a, $b) {
            return self::getActiveChosenOrder($a) - self::getActiveChosenOrder($b);
        });

        return $state;
    }

    private function getActiveChosenOrder($color)
    {
        foreach($this->diceState as $die)
        {
            if ($die['color'] == $color)
            {
                return $die['chosen_order'];
            }
        }
        return -1;
    }
}