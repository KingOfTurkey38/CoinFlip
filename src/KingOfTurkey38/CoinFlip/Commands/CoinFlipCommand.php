<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip\Commands;

use KingOfTurkey38\CoinFlip\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat as C;

class CoinFlipCommand extends PluginCommand
{
    /** @var Main */
    private $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("coinflip", $plugin);
        $this->plugin = $plugin;
        $this->setDescription("CoinFlip Command");
        $this->setUsage("/coinflip [amount] [heads/tails]");
        $this->setPermission("coinflip.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$this->testPermission($sender)) {
            $sender->sendMessage(C::GRAY . "You don't have permissions to use this command");
            return true;
        }
        //TODO: Finish the command and subcommands
        return true;
    }

}
