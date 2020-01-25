<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip;

use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class Utils
{

    const HEADS = 0;
    const TAILS = 1;

    /** @var string */
    const PREFIX = C::BOLD . C::GOLD . "Coin" . C::AQUA . "Flip " . C::RESET;

    /**
     * @return string
     */
    public static function getPrefix(): string
    {
        return self::PREFIX;
    }

    /**
     * @return int
     */
    public static function getRollTaskTickInterval(): int
    {
        return 10;
    }

    /**
     * @return array
     */
    public static function getRawCoinFlipsData(): array
    {
        return Main::getInstance()->getDatabase()->query("SELECT * FROM CoinFlips")->fetchArray();
    }

    public static function addCoinFlip(Player $player, int $type, int $wager): void
    {
        $uuid = $player->getUniqueId()->toString();
        $username = $player->getName();
        $stmt = Main::getInstance()->getDatabase()->prepare("INSERT OR IGNORE INTO CoinFlips (uuid, username, type, money) VALUES (:uuid, :username, :type, :money)");
        $stmt->bindValue(":type", $type);
        $stmt->bindValue(":money", $wager);
        $stmt->bindValue(":uuid", $uuid);
        $stmt->bindValue(":username", $username);
        $stmt->execute();
    }

    /**
     * @return Item[]
     */
    public static function getCoinFlipsAsItems(): array
    {
        $stmt = Main::getInstance()->getDatabase()->prepare("SELECT * FROM CoinFlips");
        $data = $stmt->execute();
        if (empty($data->fetchArray())) {
            return [];
        }
        $items = [];
        while ($itemData = $data->fetchArray()) {
            $meta = 1;
            if ($itemData["type"] === self::TAILS) {
                $meta = 0;
            }
            $item = Item::get(Item::MOB_HEAD, $meta);
            $item->setCustomName(C::BOLD . C::WHITE . $itemData["username"]);
            $item->setLore([
                "",
                C::BOLD . C::AQUA . "Wager",
                C::WHITE . "$" . intval($itemData["money"]),
                "", C::AQUA . C::BOLD . "Side Chosen",
                $meta === self::HEADS ? C::WHITE . "Heads" : C::WHITE . "Tails",
                "",
                C::GRAY . "Click here to" . C::BOLD . C::GREEN . " ENTER " . C::RESET . C::GRAY . " the bet!"]);
            $item->setNamedTagEntry(new StringTag("username", $itemData["username"])); //this changes during the coinflip
            $item->setNamedTagEntry(new StringTag("submitter", $itemData["username"]));
            $item->setNamedTagEntry(new StringTag("type", $meta === self::HEADS ? "Heads" : "Tails"));
            $item->setNamedTagEntry(new IntTag("wager", intval($itemData["money"])));
            $items[] = $item;
        }
        return $items;
    }

    public static function getOppositeHead(Item $head, string $username): Item
    {
        $item = Item::get($head->getId(), $head->getDamage() === self::TAILS ? self::HEADS : self::TAILS, 1);
        $item->setCustomName(C::BOLD . C::WHITE . $username);
        $item->setLore([
            "",
            C::BOLD . C::AQUA . "Wager",
            C::WHITE . "$" . intval($head->getNamedTagEntry("wager")->getValue()),
            "",
            C::AQUA . C::BOLD . "Side Chosen",
            $item->getDamage() === self::HEADS ? "§fHeads" : "§fTails"]);

        $item->setNamedTagEntry(new StringTag("username", $username));
        $item->setNamedTagEntry(new StringTag("submitter", $head->getNamedTagEntry("submitter")->getValue()));
        $item->setNamedTagEntry(new StringTag("type", $item->getDamage() === self::HEADS ? "Heads" : "Tails"));
        $item->setNamedTagEntry(new IntTag("wager", intval($head->getNamedTagEntry("wager")->getValue())));

        return $item;
    }

    public static function removeHead(string $username): void
    {
        $stmt = Main::getInstance()->getDatabase()->prepare("DELETE FROM CoinFlips WHERE LOWER(username)=:username");
        $stmt->bindValue(":username", strtolower($username));
        $stmt->execute();
    }

    public static function hasSubmittedACoinFlip(Player $player): bool
    {
        $uuid = $player->getUniqueId()->toString();
        $stmt = Main::getInstance()->getDatabase()->prepare("SELECT * FROM CoinFlips WHERE uuid=:uuid");
        $stmt->bindValue(":uuid", $uuid);
        $data = $stmt->execute()->fetchArray();

        return empty($data) ? false : true;
    }
}