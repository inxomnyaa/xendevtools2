<?php

namespace xenialdan\xendevtools2;

use pocketmine\entity\Zombie;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;

class TestListenerSpawnLaunchpad implements Listener
{
	public function onSneak(PlayerToggleSneakEvent $event)
	{
		$player = $event->getPlayer();

		$world = $player->getWorld();
		if ($event->isSneaking() && $world !== null) {
			$e = new TestLaunchpad($player->getLocation());
			$e->spawnToAll();
		}
	}

	public function onChat(PlayerChatEvent $event)
	{
		$player = $event->getPlayer();

		$world = $player->getWorld();
		if (str_contains($event->getMessage(), 'spawn1') && $world !== null) {
			$e = new Zombie($player->getLocation());
			$e->spawnToAll();
		} else if (str_contains($event->getMessage(), 'spawn2') && $world !== null) {
			$e = new NPCHuman($player->getLocation());
			$e->spawnToAll();
		}
	}
}