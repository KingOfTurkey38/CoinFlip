<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip\Inventory;


use KingOfTurkey38\CoinFlip\libs\muqsit\invmenu\InvMenu;
use KingOfTurkey38\CoinFlip\Main;
use KingOfTurkey38\CoinFlip\Tasks\CoinFlipRollTask;
use KingOfTurkey38\CoinFlip\Utils;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

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
        $menu->getInventory()->setContents(Utils::getCoinFlipsAsItems());
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
        if ($itemClicked->getId() === Item::MOB_HEAD) {
            $namedTag = $itemClicked->getNamedTag();
            if ($namedTag->hasTag("wager") && $namedTag->hasTag("username") && $namedTag->hasTag("type")) {
                $username = $namedTag->getTag("username", StringTag::class);
                $type = $namedTag->getTag("type", StringTag::class);
                $wager = $namedTag->getTag("wager", IntTag::class);

                if ($username === $player->getName()) {
                    $player->sendMessage(Utils::getPrefix() . C::GRAY . " You can't CoinFlip yourself!");
                    return false;
                }

                //TODO: check if player has enough money etc
                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new CoinFlipRollTask($itemClicked, $player), Utils::getRollTaskTickInterval());
            }
        }
        return false;
    }

    public function sendTo(Player $player): void
    {
        $this->menu->send($player);
    }
}