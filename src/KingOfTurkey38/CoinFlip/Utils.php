<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip;

use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat as C;

class Utils
{

    const HEADS = 0;
    const TAILS = 1;

    /** @var string */
    const PREFIX = C::BOLD . C::GOLD . "Coin" . C::AQUA . "Flip" . C::RESET;

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

    /**
     * @return Item[]
     */
    public static function getCoinFlipsAsItems(): array
    {
        $data = Main::getInstance()->getDatabase()->query("SELECT * FROM CoinFlips")->fetchArray();
        $items = [];
        foreach ($data as $itemData) {
            $meta = 1;
            if ($itemData["type"] === self::TAILS) {
                $meta = 0;
            }
            $item = Item::get(Item::MOB_HEAD, $meta);
            $item->setLore([
                C::BOLD . C::WHITE . $itemData["username"],
                "",
                C::BOLD . C::AQUA . "Wager", C::GRAY . " $" . intval($itemData["money"]),
                "", C::AQUA . C::BOLD . "Side Chosen",
                "", C::GRAY . " " . $meta === self::HEADS ? "Heads" : "Tails",
                "",
                C::GRAY . "Click here to" . C::BOLD . C::GREEN . " ENTER " . C::RESET . C::GRAY . " the bet!"]);
            $item->setNamedTagEntry(new StringTag("username", $itemData["username"]));
            $item->setNamedTagEntry(new StringTag("type", $meta === self::HEADS ? "Heads" : "Tails"));
            $item->setNamedTagEntry(new IntTag("wager", intval($itemData["money"])));
            $items[] = $item;
        }
        return $items;
    }
}