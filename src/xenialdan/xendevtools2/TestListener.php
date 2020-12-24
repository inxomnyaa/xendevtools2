<?php

namespace xenialdan\xendevtools2;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\Location;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use xenialdan\libstructure\format\MCStructure;

class TestListener implements Listener
{
	public function onJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();

		//.mcstructure to floating blocks test
		$world = $player->getServer()->getWorldManager()->getDefaultWorld();
		if ($world !== null) {
			foreach ($world->getEntities() as $entity) {
				if ($entity instanceof TestCart || $entity instanceof TestFallingBlock) $entity->close();
			}
			$spawn = $world->getSafeSpawn()->asVector3();
			$structureFiles = glob(Loader::getInstance()->getDataFolder() . 'structures' . DIRECTORY_SEPARATOR . "*.mcstructure");
			if ($structureFiles !== false)
				foreach ($structureFiles as $file) {
					$player->getServer()->getAsyncPool()->submitTask(new class($file, $world->getId()) extends AsyncTask {
						private string $file;
						private int $worldid;
						private MCStructure $structure;

						public function __construct(string $file, int $worldid)
						{
							Loader::getInstance()->getLogger()->debug(TF::GOLD . "Loading " . basename($file));
							$this->file = $file;
							$this->worldid = $worldid;
						}

						public function onRun(): void
						{
							$structure = new MCStructure();
							$structure->parse($this->file);
							$this->structure = $structure;
						}

						public function onCompletion(): void
						{
							$world = Server::getInstance()->getWorldManager()->getWorld($this->worldid);
							$spawn = $world->getSafeSpawn()->asVector3();
							$parentId = -1;
							foreach ($this->structure->blocks() as $block) {
								if ($block->getId() === BlockLegacyIds::AIR) continue;
								$offset = $block->getPos()->asVector3();
								$at = $spawn->addVector($block->getPos()->asVector3());
								[$block->getPos()->x, $block->getPos()->y, $block->getPos()->z, $block->getPos()->world] = [$at->x, $at->y, $at->z, $world];
								$cart = $this->getCart($block);
								if ($parentId === -1) $parentId = $cart->getId();//first cart - empty links
								else {
									$cart->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $offset);
									$cart->links[] = new EntityLink($cart->getId(), $parentId, EntityLink::TYPE_PASSENGER, true, false);
								}
								$cart->spawnToAll();
							}
						}

						private function getCart(Block $block): TestFallingBlock
						{
							$cart = new TestFallingBlock(Location::fromObject($block->getPos()->add(0.5, 0, 0.5), $block->getPos()->getWorld()), $block);
							$cart->setCanSaveWithChunk(false);
							return $cart;
						}
					});
				}
		}
	}
}