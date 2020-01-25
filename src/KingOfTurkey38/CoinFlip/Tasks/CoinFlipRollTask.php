<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip\Tasks;

use KingOfTurkey38\CoinFlip\libs\muqsit\invmenu\InvMenu;
use KingOfTurkey38\CoinFlip\Main;
use KingOfTurkey38\CoinFlip\Utils;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as C;

class CoinFlipRollTask extends Task
{
    /** @var Item */
    private $head;
    /** @var Player */
    private $wagerer;
    /** @var InvMenu */
    private $menu;
    /** @var int */
    private $rollAmount = null;
    /** @var int */
    private $currentRoll = 0;

    public function __construct(InvMenu $menu, Item $head, Player $wagerer)
    {
        $this->menu = $menu;
        $this->head = $head;
        $this->wagerer = $wagerer;
        $this->rollAmount = mt_rand(20, 30);
    }

    public function onRun(int $currentTick)
    {
        if ($this->currentRoll >= $this->rollAmount) {
            $this->end();
            return;
        }

        $username = $this->head->getNamedTagEntry("username")->getValue() == $this->wagerer->getName() ? $this->head->getNamedTagEntry("submitter")->getValue() : $this->wagerer->getName();
        $newItem = Utils::getOppositeHead($this->head, $username);
        $this->menu->getInventory()->setItem(2, $newItem);

        $this->head = $newItem;

        ++$this->currentRoll;
    }

    public function end(): void
    {
        $winner = $this->head->getNamedTagEntry("username")->getValue();
        $submitterName = $this->head->getNamedTagEntry("submitter")->getValue();
        $submitter = Main::getInstance()->getServer()->getPlayerExact($submitterName);
        $money = (int)$this->head->getNamedTagEntry("wager")->getValue();
        $loser = "";
        if ($this->wagerer->isOnline() || $submitter) {
            if ($winner === $this->wagerer->getName()) {
                $loser = $submitterName;
                $this->wagerer->sendMessage(Utils::getPrefix() . C::GRAY . "You have won the CoinFlip against $loser and got $$money");
                if ($submitter) {
                    $submitter->sendMessage(Utils::getPrefix() . C::GRAY . "You have lost the CoinFlip against $winner and lost $$money");
                }
            } else {
                $loser = $this->wagerer->getName();
                $this->wagerer->sendMessage(Utils::getPrefix() . C::GRAY . "You have lost the CoinFlip against $winner and lost $$money");
                if ($submitter) {
                    $submitter->sendMessage(Utils::getPrefix() . C::GRAY . "You have won the CoinFlip against $loser and got $$money");
                }
            }
        }
        if ($this->wagerer->isOnline()) {
            $this->wagerer->removeWindow($this->menu->getInventory());
        }
        if ($submitter) {
            $submitter->removeWindow($this->menu->getInventory());
        }
        Main::getInstance()->getEconomy()->reduceMoney($loser, $money);
        Main::getInstance()->getEconomy()->addMoney($winner, $money);
        Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
    }
}