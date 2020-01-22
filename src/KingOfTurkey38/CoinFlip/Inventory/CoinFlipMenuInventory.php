<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip\Inventory;


use KingOfTurkey38\CoinFlip\libs\muqsit\invmenu\InvMenu;
use KingOfTurkey38\CoinFlip\Utils;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\Player;

class CoinFlipMenuInventory
{

    /** @var InvMenu */
    private $menu;

    public function __construct()
    {
        $menu = new InvMenu(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName(Utils::getPrefix());
        $menu->readonly();
        $menu->setListener([$this, "onClick"]);
        $this->menu = $menu;
    }


    /**
     * @param Player $player
     * @param Item $itemClicked
     * @param Item $itemClickedWith
     * @param SlotChangeAction $action
     *
     * @return bool
     */
    public function onClick(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action): bool
    {
        return false;
    }

    public function sendTo(Player $player): void
    {
        $this->menu->send($player);
    }
}