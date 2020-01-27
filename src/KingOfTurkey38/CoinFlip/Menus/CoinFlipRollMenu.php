<?php

namespace KingOfTurkey38\CoinFlip\Menus;

use KingOfTurkey38\CoinFlip\libs\muqsit\invmenu\inventories\BaseFakeInventory;
use KingOfTurkey38\CoinFlip\libs\muqsit\invmenu\InvMenu;
use KingOfTurkey38\CoinFlip\Utils;
use pocketmine\item\Item;
use pocketmine\Player;

class CoinFlipRollMenu
{
    /** @var InvMenu */
    private $menu;

    public function __construct(Item $head)
    {
        $menu = new InvMenu(InvMenu::TYPE_HOPPER);
        $menu->setName(Utils::getPrefix());
        $menu->readonly();
        for ($i = 0; $i < $menu->getInventory()->getSize(); $i++) {
            $menu->getInventory()->addItem(Item::get(Item::GLASS_PANE)->setCustomName("|" . str_repeat("\0x", $i)));
        }
        $menu->getInventory()->setItem(2, $head);
        $menu->setInventoryCloseListener([$this, "onClose"]);
        $this->menu = $menu;
    }

    public function onClose(Player $player, BaseFakeInventory $inventory): void
    {
        $item = $inventory->getItem(0);
        if ($player->isOnline() && !$item->getNamedTag()->hasTag("ended")) {
            $this->sendTo($player);
        }
    }

    public function getMenu(): InvMenu
    {
        return $this->menu;
    }

    public function sendTo(Player $player): void
    {
        $this->menu->send($player);
    }
}