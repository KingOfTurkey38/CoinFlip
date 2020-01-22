<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip;

use KingOfTurkey38\CoinFlip\Inventory\CoinFlipMenuInventory;
use KingOfTurkey38\CoinFlip\libs\muqsit\invmenu\InvMenuHandler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "examplecmd":
                $menu = new CoinFlipMenuInventory();
                $menu->sendTo($sender);
                $sender->sendMessage("Example command output");
                return true;
            default:
                return false;
        }
    }

    public function onDisable(): void
    {
    }
}
