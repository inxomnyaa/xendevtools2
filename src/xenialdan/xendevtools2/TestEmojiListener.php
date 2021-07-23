<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use function array_key_exists;

/**
 * Class TestEmojiListener
 * Prints all emojis from the glyph_E1.png file
 * @package xenialdan\xendevtools2
 */
class TestEmojiListener implements Listener
{
	const GRID = 16;//16 * 16 icons

	public function onJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		$filename = basename("glyph_E1", ".png");
		$startChar = hexdec(substr($filename, strrpos($filename, "_") + 1) . "00");
		$i = 0;
		$messages = [];
		do {
			$z = ($i - ($i % self::GRID)) / self::GRID;
			$ci = $startChar + $i;//char index
			$char = mb_chr($ci);
			$messages[$z] = array_key_exists($z,$messages)?$messages[$z].$char:$char;
		} while (++$i < self::GRID ** 2);
		foreach ($messages as $row)$player->sendMessage($row);
	}
}