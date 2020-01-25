<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip\Menus;


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

class CoinFlipMenu
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
                $username = $namedTag->getTag("username", StringTag::class)->getValue();
                $wager = intval($namedTag->getTag("wager", IntTag::class)->getValue());

                if ($username === $player->getName()) {
                    $player->sendMessage(Utils::getPrefix() . C::GRAY . "You can't CoinFlip yourself!");
                    return false;
                }

                if (Main::getInstance()->getEconomy()->myMoney($player) < $wager) {
                    $player->sendMessage(Utils::getPrefix() . C::GRAY . "You don't have enough money to do this CoinFlip!");
                    return false;
                }
                $player->removeWindow($action->getInventory());
                $this->menu->getInventory()->removeItem($itemClicked);
                Utils::removeHead($itemClicked->getNamedTagEntry("submitter")->getValue());
                $p = Main::getInstance()->getServer()->getPlayerExact((string)$username);
                $menu = new CoinFlipRollMenu($itemClicked);
                $menu->getMenu()->getInventory()->setDefaultSendDelay(20);
                if ($p) {
                    $menu->sendTo($p);
                }
                $menu->sendTo($player);

                Main::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(new CoinFlipRollTask($menu->getMenu(), $itemClicked, $player), 20, Utils::getRollTaskTickInterval());
            }
        }
        return false;
    }

    public function sendTo(Player $player): void
    {
        $this->menu->send($player);
    }
}