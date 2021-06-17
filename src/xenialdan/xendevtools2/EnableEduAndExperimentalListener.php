<?php

declare(strict_types=1);

namespace xenialdan\xendevtools2;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\StartGamePacket;

class EnableEduAndExperimentalListener implements Listener
{

	public function onStartGamePacket(DataPacketSendEvent $event): void
	{
		foreach ($event->getPackets() as $packet) {
			if ($packet instanceof StartGamePacket) {
				$packet->hasEduFeaturesEnabled = true;
				$packet->experimentalGameplayOverride = true;
			}
		}
	}
}
