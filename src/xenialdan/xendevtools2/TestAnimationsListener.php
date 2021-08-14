<?php

namespace xenialdan\xendevtools2;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\scheduler\ClosureTask;

class TestAnimationsListener implements Listener
{
	public function onJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();

		//.mcstructure to floating blocks test
		$world = $player->getServer()->getWorldManager()->getDefaultWorld();
		if ($world !== null) {
			$e = new NPCHuman($player->getLocation());
			$e->spawnToAll();
			Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(static function () use ($e, $player): void {
				if (!$player->isOnline()) {
					return;
				}
				$anim = [
					//nazi player
					"animate.player.statue_of_liberty",
					//hero pose player
					"animation.armor_stand.hero_pose",
					//upside down player
					"animation.player.base_pose.upside_down",
					//sleeping player
					"animation.player.sleeping",
					//gliding player
					"animation.player.glide",
					//swimming player
					"animation.player.swim",
					"animation.player.crossbow_hold",
					"animation.player.sneaking",
					"animation.armor_stand.no_pose",
					"animation.armor_stand.salute_pose",
					"animation.armor_stand.wiggle",
					"animation.magma_cube.move",
					"animation.parrot.dance",
					"animation.creeper.swelling",
					"animation.pillager.crossbow.charge",
					"animation.evoker.casting",
					"animation.evoker.casting.v1.0",
					"animation.cat.lie_down",
					"animation.cat.sneak",
					"animation.npc.get_in_bed",
					"animation.npc.raise_arms",
					"animation.wolf.head_rot_z",
					"animation.wolf.shaking",
					"animation.shulker_bullet.move",
					"animation.sheep.grazing",

				];
				$animation = $anim[array_rand($anim)];
				$animpk = AnimateEntityPacket::create($animation, "", "", "", 0, [$e->getId()]);
				$player->getNetworkSession()->sendDataPacket($animpk);
				$player->sendActionBarMessage($animation);
			}), 20 * 10);
			Loader::getInstance()->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function () use ($e, $player): void {
				if (!$player->isOnline()) {
					return;
				}
				//reset npc
				$anim = AnimateEntityPacket::create("animation.player.attack.positions", "", "", "", 0, [$e->getId()]);
				$player->getNetworkSession()->sendDataPacket($anim);
			}), 20 * 9, 20 * 10);
		}
	}
}