<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip;

use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\UUID;
use SQLite3Result;
use SQLite3Stmt;

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
        $query = Main::getInstance()->getDatabase()->query("SELECT * FROM CoinFlips");

        if ($query instanceof SQLite3Result) {
            $data = $query->fetchArray();
            if (is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    public static function addCoinFlip(Player $player, int $type, int $wager): void
    {
        /** @var UUID $uid */
        $uid = $player->getUniqueId();
        $uuid = $uid->toString();
        $username = $player->getName();
        /** @var SQLite3Stmt $stmt */
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
        /** @var SQLite3Stmt $stmt */
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

    public static function getCoinFlipHead(Player $player): ?Item
    {
        if (!self::hasSubmittedACoinFlip($player)) {
            return null;
        }
        /** @var UUID $uid */
        $uid = $player->getUniqueId();
        $uuid = $uid->toString();
        /** @var SQLite3Stmt $stmt */
        $stmt = Main::getInstance()->getDatabase()->prepare("SELECT * FROM CoinFlips WHERE uuid=:uuid");
        $stmt->bindValue(":uuid", $uuid);
        /** @var array $itemData */
        $itemData = $stmt->execute()->fetchArray();

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

        return $item;
    }

    public static function getOppositeHead(Item $head, string $username): Item
    {
        $item = Item::get($head->getId(), $head->getDamage() === self::TAILS ? self::HEADS : self::TAILS, 1);
        $item->setCustomName(C::BOLD . C::WHITE . $username);
        /** @var NamedTag $wager */
        $wager = $head->getNamedTagEntry("wager");
        /** @var NamedTag $submitter */
        $submitter = $head->getNamedTagEntry("submitter");
        $item->setLore([
            "",
            C::BOLD . C::AQUA . "Wager",
            C::WHITE . "$" . $wager->getValue(),
            "",
            C::AQUA . C::BOLD . "Side Chosen",
            $item->getDamage() === self::HEADS ? "§fHeads" : "§fTails"]);

        $item->setNamedTagEntry(new StringTag("username", $username));
        $item->setNamedTagEntry(new StringTag("submitter", $submitter->getValue()));
        $item->setNamedTagEntry(new StringTag("type", $item->getDamage() === self::HEADS ? "Heads" : "Tails"));
        $item->setNamedTagEntry(new IntTag("wager", intval($wager->getValue())));

        return $item;
    }

    public static function removeHead(string $username): void
    {
        /** @var SQLite3Stmt $stmt */
        $stmt = Main::getInstance()->getDatabase()->prepare("DELETE FROM CoinFlips WHERE LOWER(username)=:username");
        $stmt->bindValue(":username", strtolower($username));
        $stmt->execute();
    }

    public static function hasSubmittedACoinFlip(Player $player): bool
    {
        /** @var UUID $uid */
        $uid = $player->getUniqueId();
        $uuid = $uid->toString();
        /** @var SQLite3Stmt $stmt */
        $stmt = Main::getInstance()->getDatabase()->prepare("SELECT * FROM CoinFlips WHERE uuid=:uuid");
        $stmt->bindValue(":uuid", $uuid);
        $data = $stmt->execute()->fetchArray();

        return empty($data) ? false : true;
    }
}