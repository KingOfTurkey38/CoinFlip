<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip\Commands;

use KingOfTurkey38\CoinFlip\Main;
use KingOfTurkey38\CoinFlip\Menus\CoinFlipMenu;
use KingOfTurkey38\CoinFlip\Utils;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
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
        $this->setUsage("/coinflip [amount:remove] [heads/tails]");
        $this->setPermission("coinflip.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Please use this command in-game!");
            return true;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage(Utils::getPrefix() . C::GRAY . "You don't have permissions to use this command");
            return true;
        }

        if (!isset($args[0])) {
            $menu = new CoinFlipMenu();
            $menu->sendTo($sender);
            $sender->sendMessage(Utils::getPrefix() . C::GRAY . "Opening the CoinFlip menu...");
            return true;
        }

        if (isset($args[0])) {
            if (strtolower($args[0]) === "remove") {
                if (Utils::hasSubmittedACoinFlip($sender)) {
                    Utils::removeHead($sender->getName());
                    $sender->sendMessage(Utils::getPrefix() . C::GRAY . "Successfully removed your CoinFlip");
                    return true;
                } else {
                    $sender->sendMessage(Utils::getPrefix() . C::GRAY . "You don't have a CoinFlip submitted");
                    return true;
                }
            }
            if (!isset($args[1])) {
                $sender->sendMessage(Utils::getPrefix() . C::GRAY . $this->getUsage());
                return true;
            }

            if (Utils::hasSubmittedACoinFlip($sender)) {
                $sender->sendMessage(Utils::getPrefix() . C::GRAY . "You can't submit more than 1 CoinFlip at once");
                return true;
            }

            $types = ["heads", "tails"];
            if (is_int(intval($args[0])) && in_array(strtolower($args[1]), $types)) {
                $wager = abs(intval($args[0]));
                if (Main::getInstance()->getEconomy()->myMoney($sender) < $wager) {
                    $sender->sendMessage(Utils::getPrefix() . C::GRAY . "You can't CoinFlip more money than you have!");
                    return true;
                }
                $type = strtolower($args[1]) === "heads" ? Utils::HEADS : Utils::TAILS;
                Utils::addCoinFlip($sender, $type, $wager);
                $sender->sendMessage(Utils::getPrefix() . C::GRAY . "Successfully submitted your CoinFlip");
            } else {
                $sender->sendMessage(Utils::getPrefix() . C::GRAY . $this->getUsage());
            }
        } else {
            $sender->sendMessage(Utils::getPrefix() . C::GRAY . $this->getUsage());
        }

        return true;
    }

}
