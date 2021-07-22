<?php

namespace xenialdan\xendevtools2;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class TestAECListener implements Listener
{

	public function onChat(PlayerChatEvent $event)
	{
		$player = $event->getPlayer();

		$world = $player->getWorld();
		if (str_contains($event->getMessage(), 'aec') && $world !== null) {
			$e = new AreaEffectCloudEntity($player->getLocation());
			var_dump($e);
			$e->spawnToAll();
		}
	}
}