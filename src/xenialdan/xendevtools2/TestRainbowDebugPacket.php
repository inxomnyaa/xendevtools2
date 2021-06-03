<?php

namespace xenialdan\xendevtools2;

use Closure;
use InvalidArgumentException;
use pocketmine\color\Color;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\network\mcpe\protocol\ClientboundDebugRendererPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;

class TestRainbowDebugPacket implements Listener
{
	public function onSneak(PlayerToggleSneakEvent $event)
	{
		$player = $event->getPlayer();

		$world = $player->getServer()->getWorldManager()->getDefaultWorld();
		if ($event->isSneaking() && $world !== null) {
			$this->runEffect([$player]);
		}
	}

	/**
	 * @param Player[] $players
	 * @throws InvalidArgumentException
	 */
	public function runEffect(array $players): void
	{
		if (count($players) > 0) {
			foreach ($players as $player) {
				for ($step = 0; $step < 15 * 20; $step++) {//15s
					$this->delay(function () use ($player, $step): void {
						$color = $this->updateColor($step);
						$player->getNetworkSession()->sendDataPacket(ClientboundDebugRendererPacket::addCube("$step", $player->getDirectionVector()->multiply(3)->floor(), (float)$color->getR() / 255, (float)$color->getG() / 255, (float)$color->getB() / 255, 1.0, 1 / 20));
					}, $step);
				}
			}
		}
	}

	/**
	 * @param Closure $closure
	 * @param int $delay
	 * @return TaskHandler
	 */
	private function delay(Closure $closure, int $delay): TaskHandler
	{
		return Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($closure), $delay);
	}

	private function updateColor(int $step): Color
	{
		$center = 128;
		$width = 127;
		$frequency = 100;//controls change speed
		$red = sin($frequency * $step + 2) * $width + $center;
		$green = sin($frequency * $step + 0) * $width + $center;
		$blue = sin($frequency * $step + 4) * $width + $center;
		return new Color($red, $green, $blue);
	}
}