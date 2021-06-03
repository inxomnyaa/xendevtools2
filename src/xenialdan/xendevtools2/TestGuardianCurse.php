<?php

namespace xenialdan\xendevtools2;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\network\mcpe\protocol\ActorEventPacket;

class TestGuardianCurse implements Listener
{
	public function onSneak(PlayerToggleSneakEvent $event)
	{
		$player = $event->getPlayer();

		$world = $player->getServer()->getWorldManager()->getDefaultWorld();
		if ($event->isSneaking() && $world !== null) {
			$actorEventPacket = ActorEventPacket::create($player->getId(), ActorEventPacket::ELDER_GUARDIAN_CURSE, 0);
			$player->getNetworkSession()->sendDataPacket($actorEventPacket);
			//LEP
			#$levelEventPacket = LevelEventPacket::create(LevelEventPacket::EVENT_GUARDIAN_CURSE,0,null);
			#$player->getNetworkSession()->sendDataPacket($levelEventPacket);
			//lol, both work!
		}
	}
}