<?php

namespace KingOfTurkey38\CoinFlip\Tasks;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class CoinFlipRollTask extends Task
{

    private $head;
    private $wagerer;

    public function __construct(Item $head, Player $wagerer)
    {
        $this->head = $head;
        $this->wagerer = $wagerer;

    }

    public function onRun(int $currentTick)
    {
        // TODO: Implement onRun() method.
    }
}