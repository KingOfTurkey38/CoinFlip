<?php

declare(strict_types=1);

namespace KingOfTurkey38\CoinFlip;

use pocketmine\utils\TextFormat as C;

class Utils
{

    /** @var string */
    const PREFIX = C::BOLD . C::GOLD . "Coin" . C::AQUA . "Flip";

    /**
     * @return string
     */
    public static function getPrefix(): string
    {
        return self::PREFIX;
    }
}